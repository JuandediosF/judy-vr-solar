# Despliegue en InfinityFree — JuDy Simulator

Guía operativa con los datos concretos de la cuenta. **Ninguna contraseña se guarda en este repositorio.**

## Datos de la cuenta (no sensibles)

| Dato | Valor |
|---|---|
| Dominio | `https://judysimulator.wuaze.com` |
| Cuenta vPanel | `if0_43331816` |
| MySQL Hostname | `sql310.infinityfree.com` |
| MySQL Username | `if0_43331816` |
| MySQL Database | `if0_43331816_JuDyb` |
| FTP Hostname | `ftpupload.net` (confirmar en vPanel → FTP Details) |
| FTP Username | `if0_43331816` |

> Nota: la BD `if0_43331816_wp806` es de otro sitio (WordPress). La de JuDy es `if0_43331816_JuDyb`.

## 1. Base de datos (una sola vez)

1. vPanel → **MySQL Databases** → phpMyAdmin de `if0_43331816_JuDyb`.
2. Importa `JuDyb.sql` (usa la pestaña *Import*). El dump no incluye `CREATE DATABASE` ni `USE`, compatible con InfinityFree.
3. Verifica que existan las tablas `usuarios` y `avances_vr` (en minúsculas; `lower_case_table_names=0`).

## 2. config.php en el servidor (una sola vez)

1. Copia `config.sample.php` como `config.php` en `htdocs` (por FTP, no por Git).
2. Rellena SOLO estos tres valores con tus contraseñas reales:
   - `DB_PASS` → contraseña MySQL del vPanel.
   - `MAIL_USER` / `MAIL_PASS` → correo Gmail y su **contraseña de aplicación** (16 caracteres).
3. Los demás valores ya vienen correctos (host, usuario, BD y `APP_URL`).
4. `config.php` está en `.gitignore` y excluido del workflow FTP: **nunca se sube al repo ni se sobrescribe en cada deploy**.

## 3. GitHub Secrets para el deploy automático

En https://github.com/zimmercanoa/judy-simulator-orca/settings/secrets/actions crea:

| Secret | Valor |
|---|---|
| `FTP_SERVER` | `ftpupload.net` |
| `FTP_USERNAME` | `if0_43331816` |
| `FTP_PASSWORD` | contraseña FTP del vPanel (rotar la que se compartió en el chat) |

Al hacer push a `master`, `.github/workflows/deploy.yml` sincroniza el código por FTP a `htdocs/`, excluyendo `config.php`, `.git`, `.agents`, `.gemini`, `tools`, `gems`, `*.sql` y `*.md`.

## 4. Asistente virtual (Gemini)

El chatbot (`asistente.php` + `asistente_api.php`) usa Gemini. La clave **nunca** se sube al repo:

1. Obtén una clave en https://aistudio.google.com/app/apikey.
2. En `config.php` de `htdocs`, añade:
   ```php
   define('GEMINI_API_KEY', 'TU_CLAVE_REAL_AQUI');
   define('GEMINI_MODEL',   'gemini-3.8-flash');
   ```
3. Si la clave no está configurada, el chatbot responde de forma segura invitando a usar el formulario de contacto (no se rompe la web).
4. El modelo se puede cambiar en `GEMINI_MODEL` sin tocar código.

**Privacidad:** el asistente no consulta la base de datos, exige CSRF, limita a 30 mensajes por hora y por sesión, y aplica un filtro de salida que enmascara correos, teléfonos, matrículas y datos de infraestructura. El prompt del sistema prohíbe expresamente revelar información personal.

## 5. Verificación

```sh
python tools/judy_monitor.py --url https://judysimulator.wuaze.com
```

Esperado: **6/6 endpoints OK** (estado actual). Tras el primer deploy con los secrets, vuelve a ejecutarlo.

## Seguridad

- Si compartiste una contraseña en cualquier chat, **rótala** en el vPanel antes de cargarla como secret.
- Usa contraseña de aplicación de Gmail, nunca la contraseña normal de la cuenta.
- No pegues credenciales en chats, issues ni commits.

## Restricción de nombres en InfinityFree

InfinityFree responde **403** (redirección a `errors.infinityfree.net/errors/403/`) a cualquier URL cuya ruta contenga `chat` en minúsculas (`chat.php`, `mychat.php`, `chat_api.txt`, `/assets/chat/`…), exista o no el archivo. Por eso el asistente usa `asistente.php` y `asistente_api.php`. No nombres archivos ni carpetas públicas con `chat`.
