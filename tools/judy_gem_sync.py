#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
judy_gem_sync.py — Mantiene sincronizadas las "gems" entre plataformas.

Fuentes canónicas: .agents/skills/<nombre>/SKILL.md  (Antigravity / Gemini CLI)
Derivados:
  - .gemini/commands/judy/<nombre>.toml   (comando /judy:<nombre>)
  - gems/<Nombre>.md                      (instrucciones para un Gem de Gemini)

Por seguridad NUNCA sobrescribe archivos existentes salvo con --force.

Uso:
    python tools/judy_gem_sync.py
    python tools/judy_gem_sync.py --write
    python tools/judy_gem_sync.py --write --force
"""

from __future__ import annotations

import argparse
import re
import sys
from pathlib import Path

FRONTMATTER = re.compile(r"^---\s*\n(.*?)\n---\s*\n", re.S)


def parse_frontmatter(text: str) -> dict[str, str]:
    match = FRONTMATTER.match(text)
    if not match:
        return {}
    data = {}
    for line in match.group(1).splitlines():
        if ":" in line:
            key, value = line.split(":", 1)
            data[key.strip()] = value.strip().strip("\"'")
    return data


ACRONYMS = {"sql": "SQL", "php": "PHP", "http": "HTTP", "json": "JSON"}


def title_case(name: str) -> str:
    return " ".join(
        ACRONYMS.get(part.lower(), part.capitalize())
        for part in re.split(r"[-_]", name) if part
    )


def command_toml(name: str, description: str) -> str:
    return (
        f'description = "{description}"\n'
        'prompt = """\n'
        f"Aplica la skill JuDy `{name}`. Lee y sigue literalmente las instrucciones de "
        f"`.agents/skills/{name}/SKILL.md` antes de responder.\n\n"
        "Reglas de ejecución:\n"
        "1. Ejecuta primero la herramienta determinista indicada en la skill (si existe).\n"
        "2. No inventes datos: si falta información crítica, pregunta una sola vez.\n"
        "3. Entrega el resultado usando la plantilla de salida de la skill.\n"
        "4. Nunca muestres credenciales; usa **** si una auditoría las detecta.\n"
        '"""\n'
    )


def gem_markdown(name: str, description: str, body: str) -> str:
    instructions = body.strip() or "Sigue las instrucciones de la skill canonica."
    return (
        f"# Gem: JuDy {title_case(name)}\n\n"
        f"> Pega el bloque `Instrucciones del sistema` en la configuración de tu Gem en Gemini.\n\n"
        f"**Nombre sugerido:** JuDy {title_case(name)}\n\n"
        f"**Descripción:** {description}\n\n"
        "## Instrucciones del sistema\n\n"
        "Eres el especialista JuDy encargado de esta tarea dentro del proyecto "
        "JuDy Simulator (PHP + MySQL en InfinityFree).\n\n"
        f"{instructions}\n\n"
        "## Restricciones permanentes\n\n"
        "- Nunca reveles credenciales ni las escribas en chats, issues ni repositorios.\n"
        "- Tablas SQL siempre en minúsculas: `usuarios`, `avances_vr`.\n"
        "- Responde en español, de forma breve y accionable.\n"
    )


def main() -> int:
    parser = argparse.ArgumentParser(description="Sincroniza skills, comandos y Gems")
    parser.add_argument("--root", default=".")
    parser.add_argument("--write", action="store_true", help="Crear archivos derivados que falten")
    parser.add_argument("--force", action="store_true", help="Sobrescribir derivados existentes")
    parser.add_argument("--gems-only", action="store_true", help="Regenerar solo los Gems de Gemini")
    args = parser.parse_args()

    root = Path(args.root).resolve()
    skills_dir = root / ".agents" / "skills"
    commands_dir = root / ".gemini" / "commands" / "judy"
    gems_dir = root / "gems"

    if not skills_dir.exists():
        print(f"No existe {skills_dir}", file=sys.stderr)
        return 2

    created, skipped, invalid = [], [], []
    for skill_dir in sorted(p for p in skills_dir.iterdir() if p.is_dir()):
        skill_file = skill_dir / "SKILL.md"
        if not skill_file.exists():
            invalid.append(str(skill_dir))
            continue
        text = skill_file.read_text(encoding="utf-8", errors="replace")
        meta = parse_frontmatter(text)
        if not meta.get("name") or not meta.get("description"):
            invalid.append(str(skill_file))
            continue
        name = meta["name"] or skill_dir.name
        command_name = meta.get("command") or name
        description = meta["description"]
        body = FRONTMATTER.sub("", text)

        targets = []
        if not args.gems_only:
            targets.append((commands_dir / f"{command_name}.toml", command_toml(name, description)))
        targets.append((gems_dir / f"{title_case(name)}.md", gem_markdown(name, description, body)))
        if args.write:
            commands_dir.mkdir(parents=True, exist_ok=True)
            gems_dir.mkdir(parents=True, exist_ok=True)
        for target, content in targets:
            rel = target.relative_to(root)
            if target.exists() and not args.force:
                skipped.append(str(rel))
                continue
            if args.write:
                target.write_text(content, encoding="utf-8", newline="\n")
                created.append(str(rel))
            else:
                skipped.append(str(rel))

    print(f"Skills válidas: {len(list(skills_dir.iterdir())) - len(invalid)}")
    if invalid:
        print("Inválidas:")
        for item in invalid:
            print(f"  - {item}")
    if created:
        print("Creados:")
        for item in created:
            print(f"  + {item}")
    if skipped:
        print("Sin cambios (usa --force para regenerar):")
        for item in skipped:
            print(f"  = {item}")
    if not args.write:
        print("\nModo chequeo. Ejecuta con --write para generar los derivados faltantes.")
    return 1 if invalid else 0


if __name__ == "__main__":
    sys.exit(main())
