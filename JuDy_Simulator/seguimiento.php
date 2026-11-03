<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
$nombre_usuario = $_SESSION['nombre'];
$rol_usuario = $_SESSION['rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Seguimiento - JuDy Simulator</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .card { background: var(--fondo-tarjeta); padding: 25px; border-radius: 12px; border: 1px solid var(--borde-color); }
        .progress-bar { width: 100%; background: var(--fondo-principal); border-radius: 10px; height: 12px; margin-top: 10px; overflow: hidden; border: 1px solid var(--borde-color); }
        .progress-fill { height: 100%; background: var(--letras-especiales); border-radius: 10px; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--borde-color); }
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; }
        .badge-success { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
        .badge-warning { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navinicio">
            <a href="seguimiento.php" class="logo-container">
                <picture>
                    <source srcset="assets/img/logo_oscuro.jpg" media="(prefers-color-scheme: dark)">
                    <img src="assets/img/logo_blanco.jpg" alt="Logo" class="nav-logo">
                </picture>
                <div class="navtitulo">Ju<span class="navtitulo1">Dy</span> Simulator</div>
            </a>
        </div>
        <ul class="navlinks"><li><a href="index.php">Ir a la Portada</a></li></ul>
        <div class="navacciones">
            <a href="logout.php" class="btn-registro" style="background-color: #dc2626;">Cerrar Sesión</a>
        </div>
    </nav>

    <div style="padding: 40px 5%; max-width: 1200px; margin: 0 auto; min-height: calc(100vh - 80px);">
        <h1 style="font-size: 32px;">Panel de Operaciones</h1>
        <p style="color: var(--letras-secundarias); font-size: 18px; margin-bottom: 30px;">
            Usuario: <span class="texto-naranja"><?php echo htmlspecialchars($nombre_usuario); ?></span> | Rol: <?php echo strtoupper($rol_usuario); ?>
        </p>

        <?php if ($rol_usuario === 'gerencia'): ?>
            <div class="dashboard-grid">
                <div class="card">
                    <h3>Cumplimiento Global (NOM-009)</h3>
                    <div class="progress-bar"><div class="progress-fill" style="width: 75%;"></div></div>
                    <p style="margin-top: 10px; font-size: 14px; color: var(--letras-secundarias);">75% de la plantilla certificada</p>
                </div>
                <div class="card">
                    <h3>Solicitudes de Registro</h3>
                    <p style="font-size: 24px; font-weight: bold; color: var(--letras-especiales);">3 Pendientes</p>
                    <a href="#" style="color: var(--azul-acento); font-size: 14px;">Revisar autorizaciones</a>
                </div>
            </div>
            <div class="card" style="margin-top: 20px;">
                <h3>Directorio de Operadores</h3>
                <table class="data-table">
                    <tr><th>Matrícula</th><th>Nombre</th><th>Fase VR</th><th>Estatus</th></tr>
                    <tr><td>EMP-001</td><td>Carlos Rivera</td><td>Mantenimiento</td><td><span class="badge badge-success">Aprobado</span></td></tr>
                    <tr><td>EMP-002</td><td>Laura Gómez</td><td>Instalación</td><td><span class="badge badge-warning">En Proceso</span></td></tr>
                </table>
            </div>

        <?php elseif ($rol_usuario === 'empleado'): ?>
            <div class="dashboard-grid">
                <div class="card">
                    <h3>Fase 1: Instalación (NOM-029)</h3>
                    <p style="color: var(--letras-secundarias); font-size: 14px;">Riesgo eléctrico y manejo de paneles.</p>
                    <div class="progress-bar"><div class="progress-fill" style="width: 100%; background: #22c55e;"></div></div>
                    <p style="margin-top: 10px; font-weight: bold; color: #22c55e;">Módulo Completado</p>
                </div>
                <div class="card">
                    <h3>Fase 2: Mantenimiento (NOM-009)</h3>
                    <p style="color: var(--letras-secundarias); font-size: 14px;">Trabajos en altura y arneses.</p>
                    <div class="progress-bar"><div class="progress-fill" style="width: 40%;"></div></div>
                    <p style="margin-top: 10px; font-weight: bold; color: var(--letras-especiales);">En Progreso (40%)</p>
                </div>
                <div class="card">
                    <h3>Retroalimentación del Instructor</h3>
                    <p style="font-size: 14px; margin-top: 10px; border-left: 3px solid var(--azul-acento); padding-left: 10px;">
                        "Excelente control de herramientas en altura, pero debes revisar el protocolo de bloqueo de energía antes de tocar el inversor."
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>