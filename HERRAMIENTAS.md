# Ecosistema de gems y herramientas (JuDy Simulator)

Una sola fuente de verdad para que **Gemini CLI, Antigravity, Gemini (app) y
cualquier otro agente** trabajen con las mismas reglas y las mismas comprobaciones.

## Mapa rápido

| Plataforma | Dónde están las "gems" | Cómo se invocan |
|---|---|---|
| Antigravity (IDE/CLI) | `.agents/skills/<nombre>/SKILL.md` | Coincidencia por `description` |
| Antigravity (agente dedicado) | `.agents/agents/*.md` | Selección de agente en la UI |
| Antigravity (reglas) | `GEMINI.md` + `.agents/rules/*.md` | Siempre activas |
| Gemini CLI | `.gemini/commands/judy/*.toml` | `/judy:audit`, `/judy:monitor`, `/judy:seguridad`, `/judy:sql`, `/judy:sync` |
| Gemini CLI (skills) | `.agents/skills/` (alias soportado) | Coincidencia por descripción |
| Gemini (app, "Gems") | `gems/*.md` | Pegar el bloque "Instrucciones del sistema" al crear el Gem |
| Cualquier agente | `tools/*.py` | `python tools/<script>.py` |

## Skills incluidas

- **predeploy-audit** → `/judy:audit`: auditoría antes de commit/push/FTP.
- **prod-monitor** → `/judy:monitor`: auditoría HTTP de los 6 endpoints públicos.
- **seguridad-php** → `/judy:seguridad`: CSRF, SQLi, XSS, secretos y correo.
- **infinity-sql** → `/judy:sql`: compatibilidad del dump con InfinityFree.
- **gem-sync** → `/judy:sync`: mantiene sincronizados skills, comandos y Gems.

Agentes dedicados: `judy-release-guardian` (aprobar/bloquear despliegues) y
`judy-security-auditor` (revisión de PRs de seguridad).

## Puesta en marcha (30 segundos)

```bash
# 1. Comprobar que las gems están sincronizadas
python tools/judy_gem_sync.py

# 2. Auditoría obligatoria antes de cualquier cambio
python tools/judy_audit.py --strict

# 3. Tras el despliegue
python tools/judy_monitor.py --url https://TU-DOMINIO.infinityfreeapp.com
```

Si un agente no ve un comando:

```bash
python tools/judy_gem_sync.py --write
python tools/judy_audit.py --strict
```

## Reglas de oro para agentes

1. `GEMINI.md` manda: tablas en minúsculas, `config.php` centralizado, sin BOM, sin secretos.
2. Ningún agente modifica credenciales; si las ve, usa `****` y pide rotarlas.
3. Todo cambio termina con `judy_audit.py`. Todo despliegue termina con `judy_monitor.py`.
4. Una excepción de seguridad debe llevar `// csrf-exception: <motivo>` en el código.
5. Las Gems de la app Gemini se generan desde las skills; edita la skill, nunca el derivado.

## Notas de repositorio

- `.gitignore` versiona `.gemini/commands/` pero ignora el resto de `.gemini/`.
- `.agents/` está versionado (para compartirlo) pero **excluido del FTP**.
- `tools/`, `gems/`, `*.sql` y `*.md` también quedan fuera del despliegue.
