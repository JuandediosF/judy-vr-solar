#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
judy_audit.py — Auditor determinista del proyecto JuDy Simulator.

Centraliza las comprobaciones que todos los agentes (Gemini CLI, Antigravity,
OpenCode, etc.) necesitan repetir antes de tocar código o desplegar:
UTF-8 sin BOM, secretos, compatibilidad InfinityFree, SQL, CSRF y excludes FTP.

Uso:
    python tools/judy_audit.py
    python tools/judy_audit.py --json
    python tools/judy_audit.py --strict      # warnings también fallan

Salida: exit 1 si hay ERROR (o WARN con --strict), 0 en caso contrario.
"""

from __future__ import annotations

import argparse
import json
import re
import shutil
import subprocess
import sys
from pathlib import Path

ERROR, WARN, INFO = "ERROR", "WARN", "INFO"

PLACEHOLDERS = {
    p.lower() for p in {
        "", "tu_app_password_de_gmail", "tu_password", "TU_PASSWORD_VPANEL",
        "xxxxx", "xxxx", "password", "cambiar", "changeme", "root",
    }
}
REQUIRED_DEPLOY_EXCLUDES = {
    "config.php", ".git*", ".agents/**", ".gemini/**", "**/*.sql",
    "**/*.md", "tools/**", "gems/**",
}
ERROR_PATTERNS_PHP = [
    "Fatal error", "Parse error", "Warning:", "Notice:", "Deprecated:",
    "Uncaught Error", "Uncaught Exception",
]
ERROR_PATTERNS_DB = [
    "Access denied for user", "doesn't exist", "Unknown database",
    "Connection refused", "mysqli_sql_exception", "Error de conexion",
]


def add(findings, level, code, location, message):
    findings.append({
        "level": level,
        "code": code,
        "file": str(location),
        "message": message,
    })


def php_files(root: Path):
    return sorted(
        p for p in root.rglob("*.php")
        if ".git" not in p.parts and "assets" not in p.parts
    )


def git_tracked(root: Path) -> set[str]:
    try:
        result = subprocess.run(
            ["git", "ls-files"], cwd=root, capture_output=True,
            text=True, check=True, timeout=15,
        )
        return set(result.stdout.splitlines())
    except (OSError, subprocess.SubprocessError):
        return set()


def check_encoding(root: Path, files, tracked, findings):
    for path in files:
        raw = path.read_bytes()
        rel = path.relative_to(root)
        if raw.startswith(b"\xef\xbb\xbf"):
            add(findings, ERROR, "ENC-001", rel, "BOM UTF-8 antes de <?php: rompe header()/session_start().")
            continue
        try:
            text = raw.decode("utf-8")
        except UnicodeDecodeError:
            add(findings, ERROR, "ENC-002", rel, "El archivo no es UTF-8 válido.")
            continue
        if re.search(r"Ã|Â|â€", text[:4000]):
            level = INFO if rel.as_posix() not in tracked else WARN
            suffix = " (archivo ignorado; no bloquea)" if level == INFO else ""
            add(findings, level, "ENC-003", rel,
                f"Posible doble codificación (mojibake) en comentarios/cadenas.{suffix}")


def check_secrets(root: Path, files, tracked, findings):
    patterns = [
        (r"define\(\s*['\"]MAIL_PASS['\"]\s*,\s*['\"]([^'\"]+)['\"]", "MAIL_PASS"),
        (r"define\(\s*['\"]DB_PASS['\"]\s*,\s*['\"]([^'\"]+)['\"]", "DB_PASS"),
    ]
    for path in files:
        text = path.read_text(encoding="utf-8", errors="replace")
        rel = path.relative_to(root)
        for regex, label in patterns:
            for match in re.finditer(regex, text, re.I):
                value = match.group(1).strip()
                if not value or value.lower() in PLACEHOLDERS:
                    continue
                if rel.as_posix() in tracked:
                    add(findings, ERROR, "SEC-001", rel,
                        f"{label} con valor literal en archivo versionado. Mover a config.php (ignorado) y rotar la credencial.")
                else:
                    add(findings, INFO, "SEC-002", rel,
                        f"{label} con valor literal en archivo NO versionado (valor oculto: ****). Verificar que nunca se suba.")
    if "config.php" in tracked:
        add(findings, ERROR, "SEC-003", "config.php", "config.php está versionado en Git; debe estar solo en .gitignore.")


def check_sql(root: Path, findings):
    sql_files = sorted(root.rglob("*.sql"))
    if not sql_files:
        add(findings, WARN, "SQL-000", ".", "No se encontró ningún archivo .sql.")
    for path in sql_files:
        text = path.read_text(encoding="utf-8", errors="replace")
        rel = path.relative_to(root)
        if re.search(r"^\s*CREATE\s+DATABASE\b", text, re.I | re.M):
            add(findings, ERROR, "SQL-001", rel, "CREATE DATABASE falla en InfinityFree (Access denied).")
        if re.search(r"^\s*USE\s+", text, re.I | re.M):
            add(findings, ERROR, "SQL-002", rel, "USE ... falla en InfinityFree; quitar del dump.")

        allowed = {"usuarios", "avances_vr"}
        for match in re.finditer(
            r"\b(FROM|JOIN|INTO|UPDATE|TABLE|REFERENCES)\s+`?([A-Za-z_][A-Za-z0-9_]*)`?",
            text, re.I,
        ):
            name = match.group(2)
            if name.lower() in allowed and name != name.lower():
                add(findings, ERROR, "SQL-003", rel,
                    f"Tabla '{name}' en mayúsculas; lower_case_table_names=0 exige '{name.lower()}'.")

        if "CREATE TABLE" in text.upper():
            if "utf8mb4" not in text.lower():
                add(findings, WARN, "SQL-004", rel, "No se declara charset utf8mb4.")
            if "utf8mb4_unicode_ci" not in text.lower():
                add(findings, WARN, "SQL-005", rel, "No se declara collation utf8mb4_unicode_ci.")
            if not re.search(r"ENGINE\s*=\s*InnoDB", text, re.I):
                add(findings, WARN, "SQL-006", rel, "No se declara ENGINE=InnoDB (requerido para FK).")


def strip_php_comments(text: str) -> str:
    """Elimina comentarios PHP completos para evitar falsos positivos."""
    text = re.sub(r"/\*.*?\*/", "", text, flags=re.S)
    return re.sub(r"(?m)^\s*(?://|#).*$", "", text)


def check_php_conventions(root: Path, files, findings):
    for path in files:
        text = path.read_text(encoding="utf-8", errors="replace")
        code = strip_php_comments(text)
        rel = path.relative_to(root)
        name = path.name

        if name not in {"config.php", "config.sample.php"}:
            if "$conexion" in code and not re.search(r"(require|include)(_once)?\b.*config\.php", code):
                add(findings, ERROR, "PHP-001", rel, "Usa $conexion sin incluir config.php mediante ruta absoluta.")
            if re.search(r"new\s+mysqli\s*\(", code, re.I):
                add(findings, ERROR, "PHP-002", rel, "Crea su propia conexión mysqli; debe reutilizar $conexion de config.php.")
            if re.search(r"(require|include)(_once)?\s*\(?\s*['\"](?:assets/|navbar\.php)", code):
                add(findings, WARN, "PHP-003", rel, "Include relativo sin __DIR__; en Linux puede romperse.")

        if re.search(r"display_errors'\s*,\s*'1'", code, re.I):
            add(findings, ERROR, "PHP-004", rel, "display_errors=1 expone rutas y rompe redirecciones.")

        if name in {"config.php", "config.sample.php"} and "display_errors', '0'" not in code:
            add(findings, WARN, "PHP-005", rel, "Falta ini_set('display_errors', '0').")

        if "$_POST" in code and re.search(r"\b(UPDATE|INSERT|DELETE)\b", code, re.I):
            if "csrf_token" not in code and "hash_equals" not in code and "csrf-exception:" not in text:
                add(findings, WARN, "PHP-006", rel,
                    "Acción que escribe con POST sin token CSRF ni token de un solo uso verificado.")


def check_deploy(root: Path, findings):
    gitignore = root / ".gitignore"
    if gitignore.exists() and "config.php" not in gitignore.read_text(encoding="utf-8", errors="replace"):
        add(findings, ERROR, "DEP-001", ".gitignore", "config.php no está en .gitignore.")

    workflow = root / ".github" / "workflows" / "deploy.yml"
    if not workflow.exists():
        add(findings, WARN, "DEP-002", ".github/workflows/deploy.yml", "No hay workflow de despliegue.")
        return
    lines = workflow.read_text(encoding="utf-8", errors="replace").splitlines()
    excludes = set()
    in_exclude = False
    block_indent = 0
    for line in lines:
        if re.match(r"^\s*exclude:\s*\|?\s*$", line):
            in_exclude = True
            block_indent = len(line) - len(line.lstrip())
            continue
        if in_exclude:
            stripped = line.strip()
            if not stripped:
                continue
            indent = len(line) - len(line.lstrip())
            if indent <= block_indent:
                in_exclude = False
                continue
            excludes.add(stripped[2:].strip() if stripped.startswith("- ") else stripped)

    # El action usa patrones **/prefijo; los normalizamos para comparar.
    normalized = set(excludes) | {item[3:] for item in excludes if item.startswith("**/")}
    for item in sorted(REQUIRED_DEPLOY_EXCLUDES):
        if item not in normalized:
            level = ERROR if item == "config.php" else WARN
            add(findings, level, "DEP-003", ".github/workflows/deploy.yml",
                f"Falta '{item}' en exclude; se subiría al FTP.")


def check_skills(root: Path, findings):
    skills_dir = root / ".agents" / "skills"
    if not skills_dir.exists():
        add(findings, WARN, "GEM-001", ".agents/skills", "No hay skills compartidas (.agents/skills).")
        return
    for skill_dir in sorted(p for p in skills_dir.iterdir() if p.is_dir()):
        skill_file = skill_dir / "SKILL.md"
        if not skill_file.exists():
            add(findings, ERROR, "GEM-002", skill_dir, "Falta SKILL.md.")
            continue
        head = skill_file.read_text(encoding="utf-8", errors="replace")[:1500]
        if not re.search(r"^name:\s*\S+", head, re.M):
            add(findings, WARN, "GEM-003", skill_file, "Falta el campo 'name' en el frontmatter.")
        if not re.search(r"^description:\s*\S+", head, re.M):
            add(findings, WARN, "GEM-004", skill_file, "Falta el campo 'description' en el frontmatter.")
        command_match = re.search(r"^command:\s*(\S+)", head, re.M)
        command_name = command_match.group(1) if command_match else skill_dir.name
        command = root / ".gemini" / "commands" / "judy" / f"{command_name}.toml"
        if not command.exists():
            add(findings, WARN, "GEM-005", command.relative_to(root),
                f"Skill '{skill_dir.name}' sin comando Gemini CLI; ejecuta judy_gem_sync.py --write.")


def check_php_lint(root: Path, files, findings):
    if not shutil.which("php"):
        add(findings, INFO, "LINT-000", "php", "PHP no instalado localmente; se omite php -l.")
        return
    for path in files:
        try:
            result = subprocess.run(
                ["php", "-l", str(path)], capture_output=True,
                text=True, timeout=30,
            )
        except (OSError, subprocess.SubprocessError):
            add(findings, WARN, "LINT-002", path.relative_to(root), "No se pudo ejecutar php -l.")
            continue
        if result.returncode != 0:
            add(findings, ERROR, "LINT-001", path.relative_to(root),
                (result.stdout or result.stderr).strip()[:240])


def main() -> int:
    parser = argparse.ArgumentParser(description="Auditor JuDy Simulator / InfinityFree")
    parser.add_argument("--root", default=".", help="Raíz del proyecto (por defecto: .)")
    parser.add_argument("--json", action="store_true", help="Salida en JSON")
    parser.add_argument("--strict", action="store_true", help="Los WARN también devuelven exit 1")
    args = parser.parse_args()

    root = Path(args.root).resolve()
    files = php_files(root)
    tracked = git_tracked(root)
    findings = []

    check_encoding(root, files, tracked, findings)
    check_secrets(root, files, tracked, findings)
    check_sql(root, findings)
    check_php_conventions(root, files, findings)
    check_deploy(root, findings)
    check_skills(root, findings)
    check_php_lint(root, files, findings)

    findings.sort(key=lambda f: ({"ERROR": 0, "WARN": 1, "INFO": 2}[f["level"]], f["code"], f["file"]))

    if args.json:
        print(json.dumps({"root": str(root), "findings": findings}, ensure_ascii=False, indent=2))
    else:
        print("== JuDy Audit ==")
        if not findings:
            print("Sin hallazgos.")
        for item in findings:
            print(f"[{item['level']:<5}] {item['code']:<8} {item['file']}: {item['message']}")
        counts = {lvl: sum(1 for f in findings if f["level"] == lvl) for lvl in (ERROR, WARN, INFO)}
        print(f"\nResumen: {counts[ERROR]} ERROR, {counts[WARN]} WARN, {counts[INFO]} INFO")

    errors = any(f["level"] == ERROR for f in findings)
    warns = any(f["level"] == WARN for f in findings)
    return 1 if errors or (args.strict and warns) else 0


if __name__ == "__main__":
    sys.exit(main())
