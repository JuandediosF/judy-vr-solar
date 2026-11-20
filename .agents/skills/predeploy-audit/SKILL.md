---
name: predeploy-audit
command: audit
description: Auditoría previa al despliegue de JuDy Simulator en InfinityFree. Úsala antes de commit, push o FTP, o cuando se sospeche que un cambio romperá producción.
---

# Pre-deploy audit (JuDy Simulator)

## Objetivo
Detectar, antes del FTP, cualquier cambio que falle en el hosting compartido de
InfinityFree (Linux, `lower_case_table_names=0`, `display_errors=0`, PHP 8.x).

## Cuándo usarla
- Antes de `git commit` / `push` a `master` (dispara el deploy automático).
- Después de editar `JuDyb.sql`, `config.php`, formularios o el workflow FTP.
- Cuando otro agente diga "listo" pero no haya validado compatibilidad.

## Pasos
1. Ejecuta la herramienta determinista desde la raíz del proyecto:
   ```bash
   python tools/judy_audit.py --strict
   ```
   Si necesitas datos para otro agente, añade `--json`.
2. Para cada `ERROR`, corrige la causa en el archivo indicado; para cada `WARN`,
   decide y documenta si es excepción legítima (por ejemplo, un formulario público
   sin CSRF de sesión).
3. Si `php` está instalado, revisa además los `php -l` del informe. Si no está,
   indica explícitamente que la validación de sintaxis quedó pendiente en local
   y será ejecutada por CI o en el servidor.
4. Comprueba que `config.php` siga **sin versionar** y que el workflow excluya
   `config.php`, `.agents/**`, `.gemini/**`, `tools/**`, `gems/**` y `*.sql`.
5. Vuelve a ejecutar el auditor. Solo reporta "listo para desplegar" con 0 ERROR.

## Plantilla de salida
```
== Pre-deploy JuDy ==
ERROR (n): <archivo> — <causa> — <corrección aplicada>
WARN  (n): <archivo> — <decisión: corregido / excepción porque ...>
Pendiente: <php -l u otra validación que no pudo ejecutarse>
Estado: LISTO PARA DESPLEGAR | BLOQUEADO
```

## Restricciones
- No modifiques credenciales de `config.php`; si están expuestas, reporta y sugiere rotarlas.
- No subas `.sql` ni `.md` al FTP: el workflow ya los excluye.
- No sustituyas el auditor por "leer el código y parece bien": ejecútalo siempre.
- Nombres de tabla obligatorios: `usuarios`, `avances_vr` (minúsculas).
