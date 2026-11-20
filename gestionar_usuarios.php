<?php
// ============================================================
//  gestionar_usuarios.php — Panel de administración de usuarios
//  Solo accesible para usuarios con rol 'gerencia'.
// ============================================================
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'gerencia') {
    header('Location: login.php');
    exit();
}

// $conexion global se inicializa en config.php

// Aprobar usuario desde el panel web (sin enlace de correo)
if (isset($_GET['aprobar'], $_GET['rol'])) {
    $id_aprobar = intval($_GET['aprobar']);
    $nuevo_rol  = $_GET['rol'];

    if ($nuevo_rol === 'empleado' || $nuevo_rol === 'gerencia') {
        $sql_ap = "UPDATE usuarios SET estatus = 'aprobado', rol = ?, token_aprobacion = NULL WHERE id_usuario = ?";
        $stmt   = $conexion->prepare($sql_ap);
        $stmt->bind_param('si', $nuevo_rol, $id_aprobar);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: gestionar_usuarios.php?ok=1');
    exit();
}

// Consultas
$resultado_pendientes = $conexion->query("SELECT * FROM usuarios WHERE estatus = 'pendiente'");
$resultado_todos      = $conexion->query("SELECT * FROM usuarios ORDER BY estatus, nombre_completo");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios — JuDy Simulator</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .container    { padding:40px 5%; max-width:1200px; margin:0 auto; min-height:calc(100vh - 80px); }
        .data-table   { width:100%; border-collapse:collapse; margin-top:15px;
                        background:var(--fondo-tarjeta); border-radius:8px; overflow:hidden; }
        .data-table th,
        .data-table td { padding:12px; text-align:left; border-bottom:1px solid var(--borde-color); }
        .btn-empleado { padding:6px 12px; background:#22c55e; color:#fff; text-decoration:none;
                        border-radius:4px; font-size:13px; font-weight:bold; margin-right:5px;
                        display:inline-block; }
        .btn-gerente  { padding:6px 12px; background:#f37021; color:#fff; text-decoration:none;
                        border-radius:4px; font-size:13px; font-weight:bold; display:inline-block; }
    </style>
</head>
<body>

<?php $nav_tipo = 'panel'; include __DIR__ . '/navbar.php'; ?>

<div class="container">
    <h1>Gestión y Autorización de Operadores</h1>
    <p style="color:var(--letras-secundarias); margin-bottom:30px;">
        Administra los accesos y jerarquías de los nuevos registros.
    </p>

    <?php if (isset($_GET['ok'])): ?>
        <p style="color:#22c55e; font-weight:bold; margin-bottom:20px;">✅ Usuario aprobado correctamente.</p>
    <?php endif; ?>

    <!-- Solicitudes pendientes -->
    <h2>Solicitudes Pendientes</h2>
    <?php if ($resultado_pendientes->num_rows > 0): ?>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <tr>
                    <th>Matrícula</th><th>Nombre</th>
                    <th>Correo</th><th>Teléfono</th>
                    <th>Asignar Jerarquía</th>
                </tr>
                <?php while ($row = $resultado_pendientes->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['matricula']) ?></td>
                        <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                        <td><?= htmlspecialchars($row['email'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($row['telefono'] ?? '—') ?></td>
                        <td style="min-width:250px;">
                            <a href="gestionar_usuarios.php?aprobar=<?= (int)$row['id_usuario'] ?>&rol=empleado"
                               class="btn-empleado">✅ Empleado</a>
                            <a href="gestionar_usuarios.php?aprobar=<?= (int)$row['id_usuario'] ?>&rol=gerencia"
                               class="btn-gerente">🔑 Gerente</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <p style="margin-top:15px; color:var(--letras-secundarias); background:var(--fondo-tarjeta);
                  padding:15px; border-radius:8px; border:1px solid var(--borde-color);">
            No hay solicitudes pendientes en este momento.
        </p>
    <?php endif; ?>

    <!-- Plantilla general -->
    <h2 style="margin-top:40px;">Plantilla General de Usuarios</h2>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <tr>
                <th>Matrícula</th><th>Nombre</th>
                <th>Jerarquía</th><th>Estatus</th>
            </tr>
            <?php while ($row = $resultado_todos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['matricula']) ?></td>
                    <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                    <td>
                        <strong style="color:var(--azul-acento);">
                            <?= strtoupper(htmlspecialchars($row['rol'])) ?>
                        </strong>
                    </td>
                    <td>
                        <strong style="color:<?= $row['estatus'] === 'aprobado' ? '#22c55e' : '#f59e0b' ?>;">
                            <?= ucfirst(htmlspecialchars($row['estatus'])) ?>
                        </strong>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
