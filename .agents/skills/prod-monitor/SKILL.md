---
name: prod-monitor
command: monitor
description: Auditoría HTTP post-despliegue de JuDy Simulator en InfinityFree. Úsala tras cada push a master o cuando se reporte "la web no carga", para detectar 500, errores PHP y fallos de MySQL.
---

# Monitor de producción (JuDy Simulator)

## Objetivo
Verificar en minutos que los seis endpoints públicos responden 200 y que el
cuerpo de las respuestas no contiene errores PHP ni de base de datos.

## Cuándo usarla
- Después de que GitHub Actions termine el despliegue FTP.
- Ante reportes de pantalla en blanco, redirecciones rotas o "error de conexión".
- Antes de dar por cerrada una incidencia de producción.

## Pasos
1. Obtén la URL pública. Prioridad: parámetro del usuario → variable `JUDY_URL`
   → `APP_URL` de `config.php`. Nunca escribas credenciales en la URL.
2. Ejecuta:
   ```bash
   python tools/judy_monitor.py --url https://TU-DOMINIO.infinityfreeapp.com
   ```
   Añade `--json` si otro agente va a procesar el resultado.
3. Revisa cada fila: `HTTP` distinto de 200, `ALERTA`, o tiempos > 10 s.
4. Para cada alerta, busca en el cuerpo el patrón detectado y aplica la causa:
   - `Fatal/Parse/Warning/Notice/Deprecated` → error PHP, revisar `php -l` y `display_errors`.
   - `Access denied / Unknown database / Table doesn't exist` → credenciales o
     prefijo `if0_XXXXXXX_` en `config.php` del servidor.
   - `301/302` inesperadas → `header('Location: ...')` con salida previa o BOM.
5. Si el fallo continúa, pide al usuario los últimos mensajes del log de
   InfinityFree; no intentes adivinar credenciales.

## Plantilla de salida
```
== Monitor JuDy | <URL> ==
endpoint        HTTP   MS     ESTADO
index.php       200    312    OK
...
Diagnóstico: <causa probable y acción concreta>
Estado: OK | CON INCIDENCIAS
```

## Restricciones
- No ejecutar auditorías contra dominios de terceros sin autorización.
- No imprimir cookies, tokens de aprobación ni correos de usuarios reales.
- `APP_URL` en `localhost` es solo desarrollo: avisar, no tratar como fallo de producción.
