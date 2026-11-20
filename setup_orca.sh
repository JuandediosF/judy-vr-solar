#!/bin/sh
set -e
cd "$(dirname "$0")"

echo "== JuDy Simulator - preparacion de entorno Orca =="

if command -v python3 >/dev/null 2>&1; then
    PY=python3
elif command -v python >/dev/null 2>&1; then
    PY=python
else
    echo "Python 3 no encontrado. Instala Python 3.9+." >&2
    exit 1
fi

"$PY" tools/judy_gem_sync.py
"$PY" tools/judy_audit.py --strict

echo ""
echo "Entorno listo. Abre esta carpeta en Orca y revisa LEEME_ENTORNO.md."
