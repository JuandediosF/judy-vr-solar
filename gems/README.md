# Gems para Gemini (app)

Cada `*.md` de esta carpeta contiene un bloque **"Instrucciones del sistema"**
listo para pegar al crear un Gem en <https://gemini.google.com>.

| Archivo | Gem sugerido | Cuándo usarlo |
|---|---|---|
| `Predeploy Audit.md` | JuDy Predeploy Audit | Antes de subir cambios a producción |
| `Prod Monitor.md` | JuDy Monitor | Después del deploy o ante caída de la web |
| `Seguridad PHP.md` | JuDy Seguridad PHP | Revisión de formularios y PRs |
| `Infinity SQL.md` | JuDy SQL Guardian | Cambios en `JuDyb.sql` o consultas |
| `Gem Sync.md` | JuDy Gem Sync | Nuevas herramientas o agentes desincronizados |

## Cómo importar

1. Abre Gemini → **Gems** → **New Gem**.
2. Nombre y descripción: los indicados al inicio de cada archivo.
3. Pega solo el contenido de `## Instrucciones del sistema` (y la sección de
   restricciones) en el campo de instrucciones.
4. Opcionalmente, sube `GEMINI.md` y `HERRAMIENTAS.md` como archivos de conocimiento.

## Regeneración

Estos archivos son **derivados**. La fuente canónica está en `.agents/skills/`.

```bash
python tools/judy_gem_sync.py              # solo comprueba
python tools/judy_gem_sync.py --write      # crea los que falten
python tools/judy_gem_sync.py --write --force  # regenera todos (con confirmación del usuario)
```

Si el Gem de Gemini no tiene acceso a tu repositorio, pégale únicamente el resumen
del informe que produce la herramienta Python (`python tools/judy_audit.py --json`).
Nunca subas `config.php` ni credenciales a un Gem.
