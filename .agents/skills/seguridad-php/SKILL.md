---
name: seguridad-php
command: seguridad
description: Revisión de seguridad de los formularios y scripts PHP de JuDy Simulator (CSRF, SQLi, XSS, secretos, abuso de correo). Úsala al modificar login, registro, aprobación, gestión de usuarios o contacto.
---

# Revisión de seguridad PHP (JuDy Simulator)

## Objetivo
Garantizar que los flujos de autenticación, aprobación y contacto cumplan las
reglas de `GEMINI.md` sin introducir regresiones funcionales.

## Cuándo usarla
- Al tocar `procesar_login.php`, `procesar_registro.php`, `procesar_contacto.php`,
  `aprobar.php`, `gestionar_usuarios.php` o `seguimiento.php`.
- Al revisar un PR de otro agente antes de aprobarlo.

## Pasos
1. Ejecuta `python tools/judy_audit.py --json` y filtra los códigos `SEC-*`, `PHP-006`.
2. Para cada script con `$_POST`, verifica:
   - **Estado:** usa `POST` + token CSRF (`hash_equals`) **o** token de aprobación
     de un solo uso con expiración. `GET` nunca aprueba nada. Las excepciones
     legítimas (p. ej. registro público) llevan un comentario
     `// csrf-exception: <motivo>` y el auditor las excluye de `PHP-006`.
   - **SQL:** sentencias preparadas (`prepare`/`bind_param`); nunca interpolación
     de `$_POST`/`$_GET` en la consulta.
   - **Salida:** `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` en todo dato
     procedente de BD o formulario.
   - **Correo:** `setFrom(MAIL_USER)` fijo y `addReplyTo` con el visitante;
     limitar frecuencia por sesión en `procesar_contacto.php`.
   - **Errores:** `error_log()` internamente, mensaje genérico al usuario.
3. Comprueba que `password_hash()` se use para contraseñas y `hash_equals()`
   para comparar tokens.
4. Si detectas una credencial real en un archivo versionado: **no la repitas**;
   marca `CRÍTICO`, pide rotarla y eliminarla del historial.

## Plantilla de salida
```
== Seguridad PHP ==
CRÍTICO: <archivo:línea> — <riesgo> — <corrección>
MEDIO:   <archivo:línea> — <riesgo> — <corrección>
OK: <lista de controles verificados>
Veredicto: APROBAR | CAMBIOS REQUERIDOS
```

## Restricciones
- No ejecutes `DELETE` ni `DROP` contra la base de datos real durante la revisión.
- No pruebes credenciales ni fuerza bruta: solo análisis estático y logs autorizados.
- No reveles el valor de tokens ni contraseñas en el informe.
