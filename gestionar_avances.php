<?php
// ============================================================
//  gestionar_avances.php — Panel de administración de avances VR
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

// Estados permitidos para un avance
$estados_permitidos = array('En Progreso', 'Completado', 'Pendiente');

// Acciones de escritura (POST + CSRF, nunca por GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'], $_POST['accion'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die('Token CSRF inválido.');
    }

    $accion = $_POST['accion'];

    if ($accion === 'crear') {
        $id_usuario = (int)($_POST['id_usuario'] ?? 0);
        $modulo     = trim($_POST['modulo'] ?? '');
        $porcentaje = (int)($_POST['porcentaje'] ?? -1);
        $estado     = $_POST['estado'] ?? '';
        $retro      = trim($_POST['retroalimentacion'] ?? '');

        if ($id_usuario <= 0 || $modulo === '' || $porcentaje < 0 || $porcentaje > 100
            || !in_array($estado, $estados_permitidos, true)) {
            header('Location: gestionar_avances.php?error=datos');
            exit();
        }

        $stmt = $conexion->prepare(
            "INSERT INTO avances_vr (id_usuario, modulo, porcentaje, estado, retroalimentacion)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('isiss', $id_usuario, $modulo, $porcentaje, $estado, $retro);
        $stmt->execute();
        $stmt->close();

        header('Location: gestionar_avances.php?ok=creado');
        exit();

    } elseif ($accion === 'editar') {
        $id_avance  = (int)($_POST['id_avance'] ?? 0);
        $porcentaje = (int)($_POST['porcentaje'] ?? -1);
        $estado     = $_POST['estado'] ?? '';
        $retro      = trim($_POST['retroalimentacion'] ?? '');

        if ($id_avance <= 0 || $porcentaje < 0 || $porcentaje > 100
            || !in_array($estado, $estados_permitidos, true)) {
            header('Location: gestionar_avances.php?error=datos');
            exit();
        }

        $stmt = $conexion->prepare(
            "UPDATE avances_vr
                SET porcentaje = ?, estado = ?, retroalimentacion = ?
              WHERE id_avance = ?"
        );
        $stmt->bind_param('issi', $porcentaje, $estado, $retro, $id_avance);
        $stmt->execute();
        $stmt->close();

        header('Location: gestionar_avances.php?ok=editado');
        exit();

    } elseif ($accion === 'eliminar') {
        $id_avance = (int)($_POST['id_avance'] ?? 0);
        if ($id_avance <= 0) {
            header('Location: gestionar_avances.php?error=datos');
            exit();
        }

        $stmt = $conexion->prepare("DELETE FROM avances_vr WHERE id_avance = ?");
        $stmt->bind_param('i', $id_avance);
        $stmt->execute();
        $stmt->close();

        header('Location: gestionar_avances.php?ok=eliminado');
        exit();
    }
}

// Consultas de solo lectura
$sql_avances = "SELECT a.id_avance, a.modulo, a.porcentaje, a.estado, a.retroalimentacion,
                       u.id_usuario, u.matricula, u.nombre_completo
                  FROM avances_vr AS a
                  INNER JOIN usuarios AS u ON u.id_usuario = a.id_usuario
                 ORDER BY u.nombre_completo, a.id_avance";
$resultado_avances = $conexion->query($sql_avances);

$resultado_usuarios = $conexion->query(
    "SELECT id_usuario, matricula, nombre_completo
       FROM usuarios
      WHERE estatus = 'aprobado'
      ORDER BY nombre_completo"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Avances VR — JuDy Simulator</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .container    { padding:40px 5%; max-width:1200px; margin:0 auto; min-height:calc(100vh - 80px); }
        .data-table   { width:100%; border-collapse:collapse; margin-top:15px;
                        background:var(--fondo-tarjeta); border-radius:8px; overflow:hidden; }
        .data-table th,
        .data-table td { padding:12px; text-align:left; border-bottom:1px solid var(--borde-color);
                         vertical-align:top; }
        .panel-form   { background:var(--fondo-tarjeta); border:1px solid var(--borde-color);
                        border-radius:8px; padding:20px; margin-top:15px; }
        .panel-form label { display:block; margin-top:10px; font-weight:bold; font-size:14px; }
        .panel-form input,
        .panel-form select,
        .panel-form textarea { width:100%; padding:8px; margin-top:5px; box-sizing:border-box;
                               background:var(--fondo-principal); color:inherit;
                               border:1px solid var(--borde-color); border-radius:4px; }
        .btn-guardar  { padding:8px 16px; background:var(--letras-especiales); color:#fff;
                        border:none; border-radius:4px; font-weight:bold; cursor:pointer; margin-top:15px; }
        .btn-eliminar { padding:6px 12px; background:#dc2626; color:#fff; border:none;
                        border-radius:4px; font-size:13px; font-weight:bold; cursor:pointer; }
        .btn-editar   { padding:6px 12px; background:var(--azul-acento); color:#fff; border:none;
                        border-radius:4px; font-size:13px; font-weight:bold; cursor:pointer; }
        .inline-form  { display:inline; }
    </style>
</head>
<body>

<?php $nav_tipo = 'panel'; include __DIR__ . '/navbar.php'; ?>

<div class="container">
    <h1>Gestión de Avances VR</h1>
    <p style="color:var(--letras-secundarias); margin-bottom:20px;">
        Asigna módulos a los operadores y actualiza su progreso, estado y retroalimentación.
    </p>
    <p style="margin-bottom:20px;">
        <a href="seguimiento.php" style="color:var(--azul-acento);">&larr; Volver a Mi Panel</a>
    </p>

    <?php if (isset($_GET['ok'])): ?>
        <?php
            $mensajes_ok = array(
                'creado'    => 'Avance creado correctamente.',
                'editado'   => 'Avance actualizado correctamente.',
                'eliminado' => 'Avance eliminado correctamente.',
            );
            $msg_ok = $mensajes_ok[$_GET['ok']] ?? 'Cambios guardados correctamente.';
        ?>
        <p style="color:#22c55e; font-weight:bold; margin-bottom:20px;">✅ <?= htmlspecialchars($msg_ok) ?></p>
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'datos'): ?>
        <p style="color:#dc2626; font-weight:bold; margin-bottom:20px;">
            Los datos enviados no son válidos (porcentaje entre 0 y 100 y estado reconocido).
        </p>
    <?php endif; ?>

    <!-- Asignar nuevo avance -->
    <h2>Asignar nuevo módulo</h2>
    <form method="POST" action="gestionar_avances.php" class="panel-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="accion" value="crear">

        <label for="id_usuario">Operador</label>
        <select name="id_usuario" id="id_usuario" required>
            <option value="">Selecciona un operador</option>
            <?php while ($u = $resultado_usuarios->fetch_assoc()): ?>
                <option value="<?= (int)$u['id_usuario'] ?>">
                    <?= htmlspecialchars($u['matricula'] . ' — ' . $u['nombre_completo'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="modulo">Módulo</label>
        <input type="text" name="modulo" id="modulo" maxlength="100"
               placeholder="Ej. Fase 3: Seguridad en altura" required>

        <label for="porcentaje">Porcentaje (0–100)</label>
        <input type="number" name="porcentaje" id="porcentaje" min="0" max="100" value="0" required>

        <label for="estado">Estado</label>
        <select name="estado" id="estado" required>
            <?php foreach ($estados_permitidos as $est): ?>
                <option value="<?= htmlspecialchars($est, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($est) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="retroalimentacion">Retroalimentación</label>
        <textarea name="retroalimentacion" id="retroalimentacion" rows="3"
                  placeholder="Observaciones para el operador"></textarea>

        <button type="submit" class="btn-guardar">Asignar módulo</button>
    </form>

    <!-- Listado de avances -->
    <h2 style="margin-top:40px;">Avances registrados</h2>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <tr>
                <th>Operador</th><th>Módulo</th><th>Progreso</th>
                <th>Estado</th><th>Retroalimentación</th><th>Acciones</th>
            </tr>
            <?php while ($av = $resultado_avances->fetch_assoc()): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($av['nombre_completo'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                        <small style="color:var(--letras-secundarias);"><?= htmlspecialchars($av['matricula'], ENT_QUOTES, 'UTF-8') ?></small>
                    </td>
                    <td><?= htmlspecialchars($av['modulo'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int)$av['porcentaje'] ?>%</td>
                    <td><?= htmlspecialchars($av['estado'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($av['retroalimentacion'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <form method="POST" action="gestionar_avances.php" class="inline-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id_avance" value="<?= (int)$av['id_avance'] ?>">
                            <button type="submit" class="btn-eliminar"
                                    onclick="return confirm('¿Eliminar este avance?');">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td colspan="6" style="background:var(--fondo-principal);">
                        <form method="POST" action="gestionar_avances.php" class="panel-form"
                              style="margin-top:0;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="accion" value="editar">
                            <input type="hidden" name="id_avance" value="<?= (int)$av['id_avance'] ?>">
                            <label>Editar progreso / estado / retroalimentación</label>
                            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                <input type="number" name="porcentaje" min="0" max="100"
                                       value="<?= (int)$av['porcentaje'] ?>" style="width:110px;" required>
                                <select name="estado" style="width:180px;">
                                    <?php foreach ($estados_permitidos as $est): ?>
                                        <option value="<?= htmlspecialchars($est, ENT_QUOTES, 'UTF-8') ?>"
                                            <?= $av['estado'] === $est ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($est) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="retroalimentacion"
                                       value="<?= htmlspecialchars($av['retroalimentacion'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="Retroalimentación" style="flex:1; min-width:200px;">
                                <button type="submit" class="btn-editar">Guardar</button>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
