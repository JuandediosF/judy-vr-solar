# Reglas siempre activas — JuDy Simulator

Contexto mínimo que todo agente debe tener antes de proponer cambios.
La especificación completa está en `GEMINI.md`; esta regla es el recordatorio operativo.

## Stack y destino
- PHP + MySQL/MariaDB desplegado por FTP (GitHub Actions) en **InfinityFree**: Linux, Apache, `lower_case_table_names=0`.
- Tablas **siempre** en minúsculas: `usuarios`, `avances_vr`. Nunca `Usuarios`/`Avances_VR`.
- `JuDyb.sql`: sin `CREATE DATABASE`, sin `USE`; solo `DROP/CREATE/INSERT`, `utf8mb4`, `utf8mb4_unicode_ci`, `InnoDB`.

## Configuración y secretos
- Todo script con BD o sesión incluye `require_once __DIR__ . '/config.php';` como primera línea ejecutable.
- `$conexion` se crea **solo** en `config.php`; nunca `new mysqli` en otros archivos.
- `config.php` está en `.gitignore`: jamás subirlo, jamás pegar credenciales en chats/issues/PRs.
- En producción: `display_errors=0`, `display_startup_errors=0`, `error_reporting(0)`.
- UTF-8 **sin BOM** en todo `.php` (un BOM rompe `header()` y `session_start()`).

## Seguridad
- Cambios de estado (aprobar, asignar rol) → `POST` con CSRF o token de aprobación de un solo uso con expiración. Nunca `GET`.
- Consultas parametrizadas; salida HTML con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- Contraseñas con `password_hash()`; tokens comparados con `hash_equals()`.

## Flujo de trabajo obligatorio
1. Antes de tocar código: ejecuta `python tools/judy_audit.py --strict`.
2. Usa la skill que corresponda: `predeploy-audit`, `infinity-sql`, `seguridad-php`, `prod-monitor`, `gem-sync`.
3. Después del deploy: `python tools/judy_monitor.py --url <URL_PUBLICA>`.
4. Si añades una herramienta nueva, actualiza la skill canónica y ejecuta `python tools/judy_gem_sync.py --write`.
