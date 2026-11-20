# Reglas de Producción para InfinityFree — JuDy Simulator

Este documento establece las directrices obligatorias de arquitectura, configuración y compatibilidad para el despliegue del proyecto en el hosting compartido de InfinityFree (entorno Linux con Apache, PHP y MySQL/MariaDB).

---

## 1. Servidor Linux y Sensibilidad a Mayúsculas/Minúsculas (Case Sensitivity)
- **Sistema de archivos:** En Linux, los nombres de archivos y directorios distinguen mayúsculas de minúsculas (`index.php` != `Index.php`, `navbar.php` != `NavBar.php`).
- **Base de datos (MySQL / MariaDB):** La directiva del servidor está configurada con `lower_case_table_names = 0`. Esto significa que los nombres de las tablas son estrictamente sensibles a mayúsculas y minúsculas.
- **Nombres de tabla obligatorios en minúsculas:**
  - `usuarios`
  - `avances_vr`
- **Regla estricta:** Todas las consultas SQL (`SELECT`, `INSERT`, `UPDATE`, `DELETE`, `JOIN`) deben escribir los nombres de las tablas exactamente en minúsculas. Nunca usar `Usuarios`, `Avances_VR` o variaciones en mayúsculas.

---

## 2. Adaptación de Base de Datos (`JuDyb.sql`) para InfinityFree
- **Restricción de privilegios:** En InfinityFree, las bases de datos son creadas exclusivamente desde el panel de control (vPanel) con prefijos obligatorios (ej. `if0_XXXXXXX_judyb`).
- **Sentencias prohibidas:**
  - `CREATE DATABASE ...` (Provoca error `Access denied for user...`).
  - `USE ...` (Provoca fallo de permisos en phpMyAdmin).
- **Estructura correcta del script SQL:**
  - Solo debe contener sentencias `DROP TABLE IF EXISTS ...`, `CREATE TABLE ...` e `INSERT INTO ...`.
  - Charset obligatorio: `utf8mb4` con collation `utf8mb4_unicode_ci`.
  - Claves foráneas compatibles con motor `InnoDB`.

---

## 3. Ocultamiento de Errores en Producción (`display_errors = 0`)
- **Directivas en producción:**
  - `ini_set('display_errors', '0');`
  - `ini_set('display_startup_errors', '0');`
  - `error_reporting(0);` (o registro en archivo log interno no accesible vía web).
- **Motivo de seguridad:** Mostrar errores en pantalla (`display_errors = 1`) expone rutas absolutas del servidor, nombres de variables y credenciales, además de romper redirecciones HTTP (`header('Location: ...')`) al enviar salida prematura.

---

## 4. Uso Obligatorio y Centralizado de `config.php`
- **Inclusión obligatoria:** Todo script PHP que realice consultas a la base de datos o gestione sesiones debe incluir `config.php` en su primera línea ejecutable:
  ```php
  require_once __DIR__ . '/config.php';
  ```
- **Centralización de la conexión:**
  - `config.php` debe definir las credenciales y además inicializar la instancia global de conexión `$conexion` (`new mysqli(...)`).
  - Debe establecer el juego de caracteres a `utf8mb4`: `$conexion->set_charset('utf8mb4');`.
  - La verificación de fallos debe manejar el error de forma segura sin exponer detalles al usuario final.
  - Los scripts individuales (`procesar_login.php`, `gestionar_usuarios.php`, etc.) no deben crear instancias independientes de `new mysqli(...)`.
- **Integridad de credenciales:**
  - **No modificar** las credenciales ni constantes existentes de base de datos o correo en `config.php`.
  - Asegurar que `config.php` permanezca excluido del control de versiones mediante `.gitignore`.

---

## 5. Manejo de Rutas y Componentes
- **Rutas absolutas mediante `__DIR__`:** Para evitar problemas de resolución de rutas en el árbol de directorios de Linux, todos los `require` o `include` de bibliotecas (como PHPMailer) o plantillas (`navbar.php`) deben utilizar `__DIR__`:
  ```php
  require_once __DIR__ . '/assets/lib/PHPMailer/PHPMailer.php';
  include __DIR__ . '/navbar.php';
  ```
- **Rutas públicas y enlaces:** Usar la constante `APP_URL` para enlaces absolutos y recursos estáticos cuando sea necesario.

---

## 6. Codificación y secretos
- Todos los archivos `.php` deben guardarse en **UTF-8 sin BOM**. Un BOM antes de `<?php` envía bytes antes de las cabeceras HTTP y rompe `header('Location: ...')` y `session_start()`.
- Ninguna credencial, contraseña de aplicación ni token real debe escribirse en chats, issues, respuestas de agentes ni repositorios. Usa `config.example.php` como plantilla y mantén `config.php` solo en el servidor.

---

## 7. Cambios de estado y formularios
- Las acciones que modifican datos (aprobar usuarios, asignar roles) deben usar `POST` con token CSRF, nunca `GET`.
- Los enlaces de aprobación por correo deben tener expiración y no permitir elegir jerarquía con un simple clic.
