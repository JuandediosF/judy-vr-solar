
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre_usuario = $_SESSION['nombre'];
$rol_usuario = $_SESSION['rol'];

$conexion = new mysqli("localhost", "root", "", "JuDyb");
if ($conexion->connect_error) die("Error de conexión");
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
        <ul class="navlinks">
            <li><a href="index.php">Ir a la Portada</a></li>
            <?php if ($rol_usuario === 'gerencia'): ?>
                <li><a href="gestionar_usuarios.php" style="color: var(--letras-especiales);">Gestionar Usuarios</a></li>
            <?php endif; ?>
        </ul>
        <div class="navacciones">
            <a href="logout.php" class="btn-registro" style="background-color: #dc2626;">Cerrar Sesión</a>
        </div>
    </nav>

    <div style="padding: 40px 5%; max-width: 1200px; margin: 0 auto; min-height: calc(100vh - 80px);">
        <h1 style="font-size: 32px;">Panel de Operaciones</h1>
        <p style="color: var(--letras-secundarias); font-size: 18px; margin-bottom: 30px;">
            Usuario: <span class="texto-naranja"><?php echo htmlspecialchars($nombre_usuario); ?></span> | Rol: <?php echo strtoupper($rol_usuario); ?>
        </p>

        <?php if ($rol_usuario === 'gerencia'): 
            // Consultas gerenciales
            $pendientes = $conexion->query("SELECT COUNT(*) as total FROM USUARIOS WHERE estatus = 'pendiente'")->fetch_assoc()['total'];
            $aprobados = $conexion->query("SELECT COUNT(*) as total FROM USUARIOS WHERE estatus = 'aprobado'")->fetch_assoc()['total'];
        ?>
            <div class="dashboard-grid">
                <div class="card">
                    <h3>Estado de Plantilla</h3>
                    <p style="font-size: 24px; font-weight: bold; color: #22c55e; margin-top: 10px;"><?php echo $aprobados; ?> Operadores Activos</p>
                </div>
                <div class="card">
                    <h3>Solicitudes de Registro</h3>
                    <p style="font-size: 24px; font-weight: bold; color: var(--letras-especiales); margin-top: 10px;"><?php echo $pendientes; ?> Pendientes</p>
                    <a href="gestionar_usuarios.php" style="color: var(--azul-acento); font-size: 14px; display:inline-block; margin-top:10px;">Gestionar autorizaciones &rarr;</a>
                </div>
            </div>

        <?php elseif ($rol_usuario === 'empleado'): 
            // Consultas de avances VR del empleado en sesión
            $sql_avances = "SELECT * FROM AVANCES_VR WHERE id_usuario = ?";
            $stmt = $conexion->prepare($sql_avances);
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $resultado_avances = $stmt->get_result();
        ?>
            <div class="dashboard-grid">
                <?php while($av = $resultado_avances->fetch_assoc()): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($av['modulo']); ?></h3>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $av['porcentaje']; ?>%;"></div>
                        </div>
                        <p style="margin-top: 10px; font-weight: bold; color: var(--letras-especiales);">
                            <?php echo htmlspecialchars($av['estado']); ?> (<?php echo $av['porcentaje']; ?>%)
                        </p>
                        <?php if (!empty($av['retroalimentacion'])): ?>
                            <p style="font-size: 14px; margin-top: 15px; border-left: 3px solid var(--azul-acento); padding-left: 10px; color: var(--letras-secundarias);">
                                "<?php echo htmlspecialchars($av['retroalimentacion']); ?>"
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>