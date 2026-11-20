#!/usr/bin/env python3
"""Construye el ZIP portable del proyecto y comprueba que no filtre secretos.

Uso:
    python tools/build_portable_zip.py

El ZIP resultante se crea en la raíz del repositorio como
``JuDy_Simulator_orca_env.zip``. Incluye el historial Git y todas las gems,
skills, comandos y scripts, pero excluye ``config.php`` y cualquier credencial
real presente en el historial.
"""

from __future__ import annotations

import hashlib
import re
import shutil
import subprocess
import sys
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile

ROOT = Path(__file__).resolve().parents[1]
OUTPUT = ROOT / "JuDy_Simulator_orca_env.zip"
PREFIX = "JuDy_Simulator/"

EXCLUDED_NAMES = {"config.php", ".DS_Store", "Thumbs.db", "desktop.ini"}
EXCLUDED_SUFFIXES = {".pyc", ".log", ".tmp", ".zip", ".bak", ".swp"}
EXCLUDED_PARTS = {"__pycache__", ".venv", "node_modules"}

# Solo contraseñas y tokens se consideran secretos. El correo, el host y la URL
# son datos de configuración: pueden aparecer en el historial sin filtrar claves.
SECRET_KEYS = ("MAIL_PASS", "DB_PASS")
PLACEHOLDERS = {
    "tu_app_password_de_gmail",
    "TU_PASSWORD_VPANEL",
}
SECRET_PATTERNS = (
    rb"ghp_[A-Za-z0-9]{20,}",
    rb"github_pat_[A-Za-z0-9_]{20,}",
)


def should_include(rel: Path) -> bool:
    parts = rel.parts
    if not parts:
        return False
    if any(part in EXCLUDED_PARTS for part in parts):
        return False
    if rel.name in EXCLUDED_NAMES:
        return False
    if rel.suffix.lower() in EXCLUDED_SUFFIXES:
        return False
    # De .gemini solo se exportan los comandos compartidos; nunca chats ni datos de cuenta.
    if parts[0] == ".gemini":
        return len(parts) >= 2 and parts[1] == "commands"
    return True


def iter_files() -> list[tuple[Path, str]]:
    result = []
    for path in sorted(ROOT.rglob("*")):
        if not path.is_file() or path == OUTPUT:
            continue
        rel = path.relative_to(ROOT)
        if should_include(rel):
            result.append((path, rel.as_posix()))
    return result


def real_secrets() -> list[bytes]:
    """Lee config.php local solo para detectar (sin imprimir) credenciales reales."""
    config = ROOT / "config.php"
    if not config.exists():
        return []
    text = config.read_text(encoding="utf-8-sig", errors="ignore")
    secrets: list[bytes] = []
    for key in SECRET_KEYS:
        match = re.search(
            r"define\s*\(\s*['\"]" + key + r"['\"]\s*,\s*['\"]([^'\"]+)['\"]",
            text,
            flags=re.IGNORECASE,
        )
        if not match:
            continue
        value = match.group(1).strip()
        if value and value not in PLACEHOLDERS and len(value) >= 6:
            secrets.append(value.encode("utf-8"))
    return secrets


def contains_secret(data: bytes, secrets: list[bytes]) -> bool:
    return any(secret in data for secret in secrets) or any(
        re.search(pattern, data) for pattern in SECRET_PATTERNS
    )


def git_history_contains(secrets: list[bytes]) -> bool:
    if not (secrets or SECRET_PATTERNS) or not shutil.which("git"):
        return False
    try:
        proc = subprocess.run(
            ["git", "-C", str(ROOT), "cat-file", "--batch-all-objects", "--batch"],
            stdout=subprocess.PIPE,
            stderr=subprocess.DEVNULL,
            check=True,
        )
    except subprocess.SubprocessError:
        return False
    return contains_secret(proc.stdout, secrets)


def build() -> None:
    secrets = real_secrets()
    if git_history_contains(secrets):
        raise SystemExit(
            "ERROR: se detectó una credencial real en el historial Git. "
            "Rótala y reescribe el historial antes de generar el ZIP."
        )

    files = iter_files()
    with ZipFile(OUTPUT, "w", ZIP_DEFLATED, compresslevel=9) as zf:
        for path, arcname in files:
            zf.write(path, PREFIX + arcname)

    with ZipFile(OUTPUT) as zf:
        names = zf.namelist()
        forbidden = []
        for name in names:
            rel = Path(name)
            if rel.name in EXCLUDED_NAMES or rel.suffix.lower() in EXCLUDED_SUFFIXES:
                forbidden.append(name)
            parts = rel.parts
            if parts and parts[0] == ".gemini" and (
                len(parts) < 2 or parts[1] != "commands"
            ):
                forbidden.append(name)
        if forbidden:
            raise SystemExit(f"ERROR: archivos no permitidos en el ZIP: {forbidden[:5]}")

        for info in zf.infolist():
            if info.file_size > 5_000_000:
                continue
            data = zf.read(info)
            if contains_secret(data, secrets):
                raise SystemExit(
                    f"ERROR: se detectó una credencial real dentro de {info.filename}"
                )

    checksum = hashlib.sha256(OUTPUT.read_bytes()).hexdigest()
    print(f"ZIP creado: {OUTPUT.name}")
    print(f"Archivos: {len(files)}")
    print(f"Tamano: {OUTPUT.stat().st_size / 1024:.1f} KB")
    print(f"SHA256: {checksum}")
    print("Verificacion: sin config.php ni credenciales reales")


if __name__ == "__main__":
    try:
        build()
    except SystemExit as exc:
        if exc.code:
            print(exc, file=sys.stderr)
        raise
