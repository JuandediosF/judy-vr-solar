# Herramientas JuDy (deterministas)

Scripts sin dependencias externas (solo Python 3) que cualquier agente puede ejecutar
para no repetir comprobaciones manuales.

| Herramienta | Para qué sirve | Ejemplo |
|---|---|---|
| `judy_audit.py` | Pre-deploy: BOM/UTF-8, secretos, SQL InfinityFree, `config.php`, CSRF, excludes FTP, `php -l` si está disponible | `python tools/judy_audit.py --strict` |
| `judy_monitor.py` | Auditoría HTTP tras el despliegue: códigos, errores PHP y MySQL | `python tools/judy_monitor.py --url https://dominio.infinityfreeapp.com` |
| `judy_gem_sync.py` | Sincroniza `.agents/skills/` → comandos Gemini CLI y Gems de Gemini | `python tools/judy_gem_sync.py --write` |
| `build_portable_zip.py` | Genera el ZIP portable y verifica que no incluya `config.php` ni credenciales | `python tools/build_portable_zip.py` |

Convenciones:

- Salida legible por defecto; `--json` para que otro agente la parsee.
- `exit 0` = OK, `exit 1` = hallazgos críticos (o warnings con `--strict`), `exit 2` = error de uso.
- Ninguna herramienta imprime valores de credenciales: se muestran como `****`.
