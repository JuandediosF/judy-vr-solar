#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
judy_monitor.py — Auditoría HTTP de producción para JuDy Simulator.

Lee APP_URL de config.php (o usa --url / JUDY_URL), consulta los endpoints
públicos y detecta códigos HTTP anómalos, errores PHP y fallos MySQL.

Uso:
    python tools/judy_monitor.py
    python tools/judy_monitor.py --url https://tu-dominio.infinityfreeapp.com
    python tools/judy_monitor.py --json
"""

from __future__ import annotations

import argparse
import json
import os
import re
import sys
import time
import urllib.error
import urllib.request
from pathlib import Path

ENDPOINTS = [
    "index.php", "login.php", "registro.php",
    "avances.php", "contacto.php", "acerca.php",
]
PHP_ERRORS = [
    "Fatal error", "Parse error", "Warning:", "Notice:",
    "Deprecated:", "Uncaught Error", "Uncaught Exception",
]
DB_ERRORS = [
    "Access denied for user", "doesn't exist", "Unknown database",
    "Connection refused", "mysqli_sql_exception", "Error de conexion",
]


def read_app_url(root: Path) -> str:
    config = root / "config.php"
    if config.exists():
        text = config.read_text(encoding="utf-8", errors="replace")
        match = re.search(r"define\(\s*['\"]APP_URL['\"]\s*,\s*['\"]([^'\"]+)['\"]", text, re.I)
        if match:
            return match.group(1).rstrip("/")
    return ""


def audit(url: str, timeout: float, insecure: bool):
    results = []
    for endpoint in ENDPOINTS:
        target = f"{url}/{endpoint}"
        started = time.perf_counter()
        status = "ERR"
        body = ""
        note = ""
        try:
            context = None
            if insecure:
                import ssl
                context = ssl._create_unverified_context()
            request = urllib.request.Request(
                target,
                headers={"User-Agent": "JuDy-Monitor/1.0 (+auditoria)"},
            )
            with urllib.request.urlopen(request, timeout=timeout, context=context) as response:
                status = response.status
                body = response.read(2_000_000).decode("utf-8", errors="replace")
        except urllib.error.HTTPError as exc:
            status = exc.code
            body = exc.read(2_000_000).decode("utf-8", errors="replace")
            note = f"HTTP {exc.code}"
        except Exception as exc:  # noqa: BLE001 - queremos cualquier fallo de red
            note = f"{type(exc).__name__}: {exc}"

        elapsed = round((time.perf_counter() - started) * 1000)
        problems = []
        if not isinstance(status, int) or status != 200:
            problems.append(f"HTTP={status}")
        for pattern in PHP_ERRORS + DB_ERRORS:
            if pattern.lower() in body.lower():
                problems.append(pattern)
        alert = "ALERTA" if problems or note else "OK"
        results.append({
            "endpoint": endpoint,
            "http": status,
            "ms": elapsed,
            "estado": alert,
            "detalles": problems + ([note] if note else []),
        })
    return results


def main() -> int:
    parser = argparse.ArgumentParser(description="Monitor HTTP JuDy Simulator")
    parser.add_argument("--root", default=".", help="Raíz del proyecto")
    parser.add_argument("--url", default="", help="URL base (tiene prioridad sobre config.php)")
    parser.add_argument("--timeout", type=float, default=15.0, help="Timeout por petición")
    parser.add_argument("--insecure", action="store_true", help="No verificar TLS (solo diagnóstico)")
    parser.add_argument("--json", action="store_true", help="Salida en JSON")
    args = parser.parse_args()

    root = Path(args.root).resolve()
    url = args.url or os.environ.get("JUDY_URL", "") or read_app_url(root)
    if not url:
        print("No se encontró APP_URL. Usa --url o JUDY_URL.", file=sys.stderr)
        return 2
    url = url.rstrip("/")
    if "localhost" in url or "127.0.0.1" in url:
        print(f"AVISO: APP_URL apunta a local ({url}). Usa --url para auditar producción.", file=sys.stderr)

    results = audit(url, args.timeout, args.insecure)
    alerts = [r for r in results if r["estado"] == "ALERTA"]

    if args.json:
        print(json.dumps({"url": url, "results": results}, ensure_ascii=False, indent=2))
    else:
        print(f"== JuDy Monitor | {url} ==")
        print(f"{'ENDPOINT':<16} {'HTTP':>5} {'MS':>7}  ESTADO")
        for row in results:
            print(f"{row['endpoint']:<16} {str(row['http']):>5} {row['ms']:>7}  {row['estado']}")
            for detail in row["detalles"]:
                print(f"    - {detail}")
        print(f"\n{len(results) - len(alerts)}/{len(results)} endpoints OK.")
    return 1 if alerts else 0


if __name__ == "__main__":
    sys.exit(main())
