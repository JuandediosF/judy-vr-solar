<?php
// ============================================================
//  asistente.php — Asistente virtual con Gemini
// ============================================================
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistente Virtual — JuDy Simulator</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .chat-container { max-width:820px; margin:0 auto; padding:40px 5%; min-height:calc(100vh - 80px); }
        .chat-caja      { background:var(--fondo-tarjeta); border:1px solid var(--borde-color);
                          border-radius:12px; padding:20px; }
        .chat-mensajes  { height:380px; overflow-y:auto; padding:10px; display:flex;
                          flex-direction:column; gap:12px; }
        .chat-burbuja   { padding:12px 16px; border-radius:12px; max-width:85%; line-height:1.5;
                          white-space:pre-wrap; word-wrap:break-word; }
        .chat-user      { background:var(--letras-especiales); color:#fff; align-self:flex-end; }
        .chat-bot       { background:var(--fondo-principal); border:1px solid var(--borde-color);
                          align-self:flex-start; }
        .chat-form      { display:flex; gap:10px; margin-top:15px; border-top:1px solid var(--borde-color);
                          padding-top:15px; }
        .chat-form input{ flex:1; padding:12px; border-radius:6px; border:1px solid var(--borde-color);
                          background:var(--fondo-principal); color:var(--letras-principales); font-size:16px; }
        .chat-form button { padding:12px 20px; background:var(--letras-especiales); color:#fff; border:none;
                          border-radius:6px; font-weight:bold; cursor:pointer; }
        .chat-aviso     { font-size:13px; color:var(--letras-secundarias); margin-top:12px; text-align:center; }
    </style>
</head>
<body>

<?php $nav_tipo = 'publica'; include __DIR__ . '/navbar.php'; ?>

<div class="chat-container">
    <h1 style="font-size:30px; text-align:center;">Asistente Virtual</h1>
    <p style="text-align:center; color:var(--letras-secundarias); margin-bottom:20px;">
        ¿Dudas sobre el simulador VR, el registro o las normas NOM-009/NOM-029? Pregúntame.
    </p>

    <div class="chat-caja">
        <div class="chat-mensajes" id="chat-mensajes" aria-live="polite">
            <div class="chat-burbuja chat-bot">¡Hola! Soy el asistente de JuDy Simulator. ¿En qué puedo ayudarte?</div>
        </div>
        <form class="chat-form" id="chat-form">
            <input type="text" id="chat-input" placeholder="Escribe tu pregunta..." maxlength="1000"
                   autocomplete="off" required>
            <button type="submit" id="chat-enviar">Enviar</button>
        </form>
        <p class="chat-aviso">
            Por tu privacidad, el asistente no tiene acceso a datos personales ni a tu cuenta.
            Para asuntos específicos, usa el <a href="contacto.php" style="color:var(--azul-acento);">formulario de contacto</a>.
        </p>
    </div>
</div>

<script>
(function () {
    var form    = document.getElementById('chat-form');
    var input   = document.getElementById('chat-input');
    var caja    = document.getElementById('chat-mensajes');
    var boton   = document.getElementById('chat-enviar');
    var csrf    = <?= json_encode($csrf_token, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    function agregar(clase, texto) {
        var b = document.createElement('div');
        b.className = 'chat-burbuja ' + clase;
        b.textContent = texto;
        caja.appendChild(b);
        caja.scrollTop = caja.scrollHeight;
        return b;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var mensaje = input.value.trim();
        if (!mensaje) return;

        agregar('chat-user', mensaje);
        input.value = '';
        boton.disabled = true;
        var esperando = agregar('chat-bot', 'Escribiendo...');

        fetch('asistente_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: 'mensaje=' + encodeURIComponent(mensaje) + '&csrf_token=' + encodeURIComponent(csrf)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            esperando.textContent = data.respuesta || data.error || 'No se pudo obtener una respuesta.';
        })
        .catch(function () {
            esperando.textContent = 'Error de conexión. Inténtalo de nuevo más tarde.';
        })
        .finally(function () { boton.disabled = false; input.focus(); });
    });
})();
</script>

</body>
</html>
