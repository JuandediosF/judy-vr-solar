<?php
// ============================================================
//  seguimiento.php — Panel principal del usuario autenticado
// ============================================================
session_start();
require_once __DIR__ . '/config.php';

// Solo usuarios con sesión activa
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

$id_usuario     = (int)$_SESSION['id_usuario'];
$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';
$rol_usuario    = $_SESSION['rol'] ?? 'sin_rol';

// $conexion global se inicializa en config.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Seguimiento — JuDy Simulator</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .dashboard-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));
                          gap:20px; margin-top:20px; }
        .card           { background:var(--fondo-tarjeta); padding:25px; border-radius:12px;
                          border:1px solid var(--borde-color); }
        .progress-bar   { width:100%; background:var(--fondo-principal); border-radius:10px;
                          height:12px; margin-top:10px; overflow:hidden; border:1px solid var(--borde-color); }
        .progress-fill  { height:100%; background:var(--letras-especiales); border-radius:10px; }
        .data-table     { width:100%; border-collapse:collapse; margin-top:15px; }
        .data-table th,
        .data-table td  { padding:12px; text-align:left; border-bottom:1px solid var(--borde-color); }
    </style>
</head>
<body>

<?php $nav_tipo = 'panel'; include __DIR__ . '/navbar.php'; ?>

<div style="padding:40px 5%; max-width:1200px; margin:0 auto; min-height:calc(100vh - 80px);">
    <h1 style="font-size:32px;">Panel de Operaciones</h1>
    <p style="color:var(--letras-secundarias); font-size:18px; margin-bottom:30px;">
        Usuario: <span class="texto-naranja"><?= htmlspecialchars($nombre_usuario) ?></span>
        &nbsp;|&nbsp; Rol: <strong><?= strtoupper(htmlspecialchars($rol_usuario)) ?></strong>
    </p>

    <?php if ($rol_usuario === 'gerencia'):
        // ---- Vista de gerencia ------------------------------------------------
        $pendientes = $conexion->query("SELECT COUNT(*) AS total FROM usuarios WHERE estatus = 'pendiente'")->fetch_assoc()['total'];
        $aprobados  = $conexion->query("SELECT COUNT(*) AS total FROM usuarios WHERE estatus = 'aprobado'")->fetch_assoc()['total'];
    ?>
        <div class="dashboard-grid">
            <div class="card">
                <h3>Estado de Plantilla</h3>
                <p style="font-size:24px; font-weight:bold; color:#22c55e; margin-top:10px;">
                    <?= $aprobados ?> Operadores Activos
                </p>
            </div>
            <div class="card">
                <h3>Solicitudes de Registro</h3>
                <p style="font-size:24px; font-weight:bold; color:var(--letras-especiales); margin-top:10px;">
                    <?= $pendientes ?> Pendientes
                </p>
                <a href="gestionar_usuarios.php"
                   style="color:var(--azul-acento); font-size:14px; display:inline-block; margin-top:10px;">
                    Gestionar autorizaciones &rarr;
                </a>
                <a href="gestionar_avances.php"
                   style="color:var(--azul-acento); font-size:14px; display:inline-block; margin-top:10px;">
                    Gestionar avances VR &rarr;
                </a>
            </div>
        </div>

    <?php elseif ($rol_usuario === 'empleado'):
        // ---- Vista de empleado ------------------------------------------------
        $sql_avances = 'SELECT * FROM avances_vr WHERE id_usuario = ?';
        $stmt        = $conexion->prepare($sql_avances);
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        $resultado_avances = $stmt->get_result();
    ?>
        <?php if ($resultado_avances->num_rows === 0): ?>
            <div class="card" style="margin-top:20px; text-align:center; padding:40px;">
                <p style="color:var(--letras-secundarias); font-size:18px;">
                    Aún no tienes módulos de entrenamiento asignados.<br>
                    Contacta a tu gerente para comenzar.
                </p>
            </div>
        <?php else: ?>
            <div class="dashboard-grid">
                <?php while ($av = $resultado_avances->fetch_assoc()): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($av['modulo']) ?></h3>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:<?= (int)$av['porcentaje'] ?>%;"></div>
                        </div>
                        <p style="margin-top:10px; font-weight:bold; color:var(--letras-especiales);">
                            <?= htmlspecialchars($av['estado']) ?> (<?= (int)$av['porcentaje'] ?>%)
                        </p>
                        <?php if (!empty($av['retroalimentacion'])): ?>
                            <p style="font-size:14px; margin-top:15px; border-left:3px solid var(--azul-acento);
                                      padding-left:10px; color:var(--letras-secundarias);">
                                "<?= htmlspecialchars($av['retroalimentacion']) ?>"
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

    <?php else:
        // ---- Rol no reconocido (por_asignar) ---------------------------------
    ?>
        <div class="card" style="margin-top:20px; text-align:center; padding:40px;">
            <p style="color:var(--letras-secundarias); font-size:18px;">
                Tu cuenta está activa pero aún no tiene un rol asignado.<br>
                Por favor espera a que el administrador configure tu jerarquía.
            </p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
