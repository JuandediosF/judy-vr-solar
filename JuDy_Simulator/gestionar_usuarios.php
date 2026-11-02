<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'gerencia') {
    header("Location: login.php");
    exit();
}

$conexion = new mysqli("localhost", "root", "", "JuDyb");
if ($conexion->connect_error) die("Error de conexión");

// Si el gerente aprueba desde el panel web seleccionando el rol
if (isset($_GET['aprobar']) && isset($_GET['rol'])) {
    $id_a_aprobar = intval($_GET['aprobar']);
    $nuevo_rol = $_GET['rol'];
    
    if($nuevo_rol === 'empleado' || $nuevo_rol === 'gerencia') {
        $sql_aprobar = "UPDATE USUARIOS SET estatus = 'aprobado', rol = ? WHERE id_usuario = ?";
        $stmt = $conexion->prepare($sql_aprobar);
        $stmt->bind_param("si", $nuevo_rol, $id_a_aprobar);
        $stmt->execute();
    }
    header("Location: gestionar_usuarios.php");
    exit();
}

// Consultas a la base de datos
$resultado_pendientes = $conexion->query("SELECT * FROM USUARIOS WHERE estatus = 'pendiente'");
$resultado_todos = $conexion->query("SELECT * FROM USUARIOS");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - JuDy Simulator</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .container { padding: 40px 5%; max-width: 1200px; margin: 0 auto; min-height: calc(100vh - 80px); }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; background: var(--fondo-tarjeta); border-radius: 8px; overflow: hidden; }
        .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--borde-color); }
        .btn-empleado { padding: 6px 12px; background: #22c55e; color: #fff; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: bold; margin-right: 5px; display: inline-block; }
        .btn-gerente { padding: 6px 12px; background: #f37021; color: #fff; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: bold; display: inline-block; }
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
            <li><a href="seguimiento.php">Volver al Panel</a></li>
            <li><a href="logout.php" style="color: #dc2626;">Cerrar Sesión</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Gestión y Autorización de Operadores</h1>
        <p style="color: var(--letras-secundarias); margin-bottom: 30px;">Administra los accesos y jerarquías de los nuevos registros.</p>

        <h2>Solicitudes Pendientes</h2>
        <?php if ($resultado_pendientes->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <tr><th>Matrícula</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Asignar Jerarquía</th></tr>
                    <?php while($row = $resultado_pendientes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['matricula']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_completo']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                            <td style="min-width: 250px;">
                                <a href="gestionar_usuarios.php?aprobar=<?php echo $row['id_usuario']; ?>&rol=empleado" class="btn-empleado">Aprobar como Empleado</a>
                                <a href="gestionar_usuarios.php?aprobar=<?php echo $row['id_usuario']; ?>&rol=gerencia" class="btn-gerente">Aprobar como Gerente</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        <?php else: ?>
            <p style="margin-top: 15px; color: var(--letras-secundarias); background: var(--fondo-tarjeta); padding: 15px; border-radius: 8px; border: 1px solid var(--borde-color);">
                No hay solicitudes pendientes en este momento.
            </p>
        <?php endif; ?>

        <h2 style="margin-top: 40px;">Plantilla General de Usuarios</h2>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <tr><th>Matrícula</th><th>Nombre</th><th>Jerarquía</th><th>Estatus</th></tr>
                <?php while($row = $resultado_todos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['matricula']); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre_completo']); ?></td>
                        <td><strong style="color: var(--azul-acento);"><?php echo strtoupper($row['rol']); ?></strong></td>
                        <td>
                            <strong style="color: <?php echo $row['estatus'] == 'aprobado' ? '#22c55e' : '#f59e0b'; ?>;">
                                <?php echo ucfirst($row['estatus']); ?>
                            </strong>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>