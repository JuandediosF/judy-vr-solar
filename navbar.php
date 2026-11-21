<?php
// ============================================================
//  navbar.php — Barra de navegación compartida
//
//  Uso: include 'navbar.php';
//  Debe incluirse DESPUÉS de session_start().
//
//  Variables de contexto opcionales:
//    $nav_tipo = 'publica'   → menú completo + botón login/panel
//    $nav_tipo = 'panel'     → menú interno (seguimiento/gestión)
//    $nav_tipo = 'auth'      → solo "Volver" (login, registro)
// ============================================================
if (!isset($nav_tipo)) $nav_tipo = 'publica';

$sesion_activa = isset($_SESSION['id_usuario']);
$es_gerencia   = $sesion_activa && ($_SESSION['rol'] ?? '') === 'gerencia';
?>
<nav class="navbar">
    <div class="navinicio">
        <a href="<?= $sesion_activa ? 'seguimiento.php' : 'index.php' ?>" class="logo-container">
            <picture>
                <source srcset="assets/img/logo_oscuro.jpg" media="(prefers-color-scheme: dark)">
                <img src="assets/img/logo_blanco.jpg" alt="Logo JuDy Simulator" class="nav-logo">
            </picture>
            <div class="navtitulo">Ju<span class="navtitulo1">Dy</span> Simulator</div>
        </a>
    </div>

    <?php if ($nav_tipo === 'publica'): ?>
    <ul class="navlinks">
        <li><a href="index.php">Inicio</a></li>
        <li><a href="avances.php">Avances VR</a></li>
        <li><a href="acerca.php">Acerca de</a></li>
        <li><a href="chatbot.php">Asistente Virtual</a></li>
        <li><a href="contacto.php">¡Contáctanos!</a></li>
    </ul>
    <div class="navacciones">
        <?php if ($sesion_activa): ?>
            <a href="seguimiento.php" class="btn-registro">Ir al Panel</a>
            <a href="logout.php" class="btn-registro" style="background-color:#dc2626; margin-left:10px;">Cerrar Sesión</a>
        <?php else: ?>
            <a href="login.php" class="btn-registro">Iniciar Sesión</a>
        <?php endif; ?>
    </div>

    <?php elseif ($nav_tipo === 'panel'): ?>
    <ul class="navlinks">
        <li><a href="index.php">Portada</a></li>
        <li><a href="seguimiento.php">Mi Panel</a></li>
        <?php if ($es_gerencia): ?>
            <li><a href="gestionar_usuarios.php" style="color:var(--letras-especiales);">Gestionar Usuarios</a></li>
            <li><a href="gestionar_avances.php" style="color:var(--letras-especiales);">Gestionar Avances VR</a></li>
        <?php endif; ?>
        <li><a href="contacto.php">Contacto</a></li>
    </ul>
    <div class="navacciones">
        <a href="logout.php" class="btn-registro" style="background-color:#dc2626;">Cerrar Sesión</a>
    </div>

    <?php elseif ($nav_tipo === 'auth'): ?>
    <ul class="navlinks">
        <li><a href="index.php">Volver al Inicio</a></li>
    </ul>
    <?php endif; ?>
</nav>
