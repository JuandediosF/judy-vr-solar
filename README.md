# JuDy Simulator

Plataforma web PHP para capacitación en seguridad industrial con realidad virtual (NOM-009 / NOM-029). Incluye registro con aprobación por correo, roles `empleado` / `gerencia`, panel de avances VR y formulario de contacto.

## Estructura

- `*.php` — páginas públicas, panel y procesadores de formularios.
- `config.php` — credenciales reales (ignorado por Git; **no subir**).
- `config.sample.php` — plantilla sin secretos para crear `config.php`.
- `JuDyb.sql` — esquema MySQL compatible con InfinityFree (sin `CREATE DATABASE` ni `USE`).
- `assets/` — CSS, imágenes y PHPMailer.
- `GEMINI.md` — reglas obligatorias de producción.
- `.gemini/commands/` — comandos de auditoría y adaptación a InfinityFree.
- `chatbot.php` / `chat_api.php` — asistente virtual con Gemini (clave solo en `config.php`; sin acceso a datos personales).

## Desarrollo local

1. Importa `JuDyb.sql` en tu MySQL local (WAMP/XAMPP).
2. Copia `config.sample.php` a `config.php` y ajusta `DB_*` y `APP_URL`.
3. Verifica sintaxis con PHP 8.1 o superior:

   ```sh
   php -l *.php
   ```

> Guarda todos los `.php` en **UTF-8 sin BOM**. Un BOM antes de `<?php` envía salida antes de las cabeceras y rompe `header('Location: ...')` y `session_start()`.

## Despliegue en InfinityFree

**Sitio en producción:** https://judysimulator.wuaze.com — cuenta InfinityFree `if0_43331816`.
Las credenciales (MySQL/FTP/Gmail) no se versionan; se obtienen en el vPanel y se cargan como GitHub Secrets o en `config.php` del servidor.

1. Crea el subdominio y la base de datos en el vPanel (ej. `if0_XXXXXXX_judyb`).
2. Importa `JuDyb.sql` desde phpMyAdmin.
3. Sube todos los archivos a `htdocs` (excepto `config.php`).
4. Crea `config.php` en el servidor usando `config.sample.php` como guía con los datos reales del vPanel y tu contraseña de aplicación de Gmail.
5. Asegúrate de que la rama por defecto del repositorio apunte al código actual (`master` contiene la última versión; `main` puede estar desactualizada).
6. El flujo `.github/workflows/deploy.yml` sincroniza `master` por FTP al subir cambios, pero **excluye `config.php`**. Configura los secrets `FTP_SERVER`, `FTP_USERNAME` y `FTP_PASSWORD` en GitHub y crea `config.php` manualmente una vez en `htdocs`.

## Seguridad aplicada

- Contraseñas con `password_hash()` / `password_verify()`.
- Consultas preparadas y `$conexion` centralizada con `utf8mb4`.
- Aprobación por token con expiración de 7 días; el rol se elige mediante `POST`.
- Panel de gerencia con token CSRF y aprobación solo de usuarios pendientes.
- Validación de correo y límite de envíos en el formulario de contacto.
- Errores PHP ocultos y registrados en `error_log`.

## Antes de publicar

- Rota cualquier contraseña de Gmail que haya aparecido en chats o historiales y usa solo una contraseña de aplicación en `config.php`.
- Revisa los datos de prueba de `JuDyb.sql` (correos y teléfonos de ejemplo).
