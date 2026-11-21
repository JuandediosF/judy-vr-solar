# Entorno portable JuDy Simulator / Orca

Este ZIP contiene el proyecto completo y las herramientas compartidas para que otro
equipo con **Orca** pueda trabajar con las mismas reglas, skills y comandos.

## Qué incluye

- Código PHP, `assets/`, `JuDyb.sql`, `config.sample.php` y `README.md` (sin `config.php` real).
- `GEMINI.md`, `HERRAMIENTAS.md` y reglas de agente en `.agents/`.
- Skills canónicas en `.agents/skills/`: `gem-sync`, `infinity-sql`, `predeploy-audit`, `prod-monitor`, `seguridad-php`.
- Agentes y reglas: `.agents/agents/`, `.agents/rules/`.
- Comandos derivados para Gemini CLI: `.gemini/commands/judy/*.toml` y `.gemini/commands/*.toml`.
- Gems para la app Gemini en `gems/`.
- Scripts deterministas en `tools/`: `judy_gem_sync.py`, `judy_audit.py`, `judy_monitor.py` y `build_portable_zip.py`.
- Workflow de despliegue en `.github/workflows/deploy.yml`.
- Historial Git (`.git`), con `origin` apuntando a `github.com/JuandediosF/judy-vr-solar`.

**No incluye** credenciales, `config.php`, sesiones de Orca/Gemini, ni binarios de Orca/PHP.

## Requisitos en el otro equipo

- Orca instalado (abre esta carpeta como workspace).
- Python 3.9 o superior (solo biblioteca estándar; sin `pip install`).
- Opcional: PHP 8.1+ en PATH para que el auditor ejecute `php -l`.
- Git (opcional; el ZIP ya trae el historial).

## Puesta en marcha

```bash
# Windows PowerShell
.\setup_orca.ps1

# Linux / macOS
sh setup_orca.sh
```

O manualmente:

```bash
python tools/judy_gem_sync.py       # comprueba skills -> comandos -> Gems
python tools/judy_audit.py --strict # auditoría previa
python tools/build_portable_zip.py  # reconstruye el ZIP sin secretos
```

Resultado esperado: `Skills válidas: 5`, `Estado: SINCRONIZADO` y
`0 ERROR, 0 WARN` (pueden aparecer INFO sobre `config.php` y PHP no instalado).

## Uso diario con Orca

- Los agentes leen `GEMINI.md` y `.agents/skills/*/SKILL.md` automáticamente.
- Si un agente no ve una skill, ejecuta `python tools/judy_gem_sync.py --write`.
- Comandos Gemini CLI: `/judy:audit`, `/judy:monitor`, `/judy:seguridad`, `/judy:sql`, `/judy:sync`.
- Para OpenCode u otros agentes: ejecuta directamente `python tools/<script>.py --json`.
- Antes de cualquier push/FTP: `python tools/judy_audit.py --strict`.
- Después de desplegar: `python tools/judy_monitor.py --url https://judysimulator.wuaze.com`.

## Despliegue en el nuevo equipo

1. Copia `config.sample.php` como `config.php` solo en el servidor (`htdocs`) y rellena
   los datos del vPanel y la contraseña de aplicación de Gmail.
2. Nunca subas `config.php` al repositorio; el workflow FTP lo excluye.
3. Configura los GitHub Secrets `FTP_SERVER`, `FTP_USERNAME` y `FTP_PASSWORD`.
4. Importa `JuDyb.sql` en phpMyAdmin.
5. Recuerda rotar cualquier contraseña que haya aparecido en chats o capturas.

## Notas de sincronización

La fuente de verdad es `.agents/skills/<nombre>/SKILL.md`. No edites a mano los
derivados de `.gemini/commands/judy/` ni `gems/`; regeneralos con `judy_gem_sync.py`.
