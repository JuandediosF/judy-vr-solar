<?php
if(isset($_GET['error'])) {
    if($_GET['error'] == '1') {
        echo '<p class="error-msg">Matrícula o contraseña incorrectos.</p>';
    } elseif($_GET['error'] == 'pendiente') {
        echo '<p class="error-msg" style="color: #f59e0b;">Tu cuenta está pendiente de aprobación por el administrador.</p>';
    }
}
if(isset($_GET['registro'])) {
    echo '<p style="color: #22c55e; font-weight: bold; margin-bottom:15px;">Registro exitoso. Espera la aprobación por correo.</p>';
}
?>