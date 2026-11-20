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

// Token CSRF para las acciones del panel
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Aprobar usuario desde el panel web (POST + CSRF, nunca por GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aprobar'], $_POST['rol'], $_POST['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die('Token CSRF inválido.');
    }

    $id_aprobar = (int)$_POST['aprobar'];
    $nuevo_rol  = $_POST['rol'];

    if ($nuevo_rol !== 'empleado' && $nuevo_rol !== 'gerencia') {
        header('Location: gestionar_usuarios.php?error=rol');
        exit();
    }

    // Solo se pueden aprobar solicitudes pendientes
    $check = $conexion->prepare("SELECT estatus FROM usuarios WHERE id_usuario = ? LIMIT 1");
    $check->bind_param('i', $id_aprobar);
    $check->execute();
    $fila = $check->get_result()->fetch_assoc();
    $check->close();

    if ($fila && $fila['estatus'] === 'pendiente') {
        $sql_ap = "UPDATE usuarios
                      SET estatus = 'aprobado', rol = ?, token_aprobacion = NULL, token_expira_en = NULL
                    WHERE id_usuario = ? AND estatus = 'pendiente'";
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
                        border:none; border-radius:4px; font-size:13px; font-weight:bold;
                        margin-right:5px; display:inline-block; cursor:pointer; }
        .btn-gerente  { padding:6px 12px; background:#f37021; color:#fff; text-decoration:none;
                        border:none; border-radius:4px; font-size:13px; font-weight:bold;
                        display:inline-block; cursor:pointer; }
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
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'rol'): ?>
        <p style="color:#dc2626; font-weight:bold; margin-bottom:20px;">El rol especificado no es válido.</p>
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
                            <form method="POST" action="gestionar_usuarios.php" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="aprobar" value="<?= (int)$row['id_usuario'] ?>">
                                <button type="submit" name="rol" value="empleado" class="btn-empleado">✅ Empleado</button>
                                <button type="submit" name="rol" value="gerencia" class="btn-gerente">🔑 Gerente</button>
                            </form>
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
