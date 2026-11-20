<?php
// ============================================================
//  procesar_login.php — Valida credenciales y abre sesión
// ============================================================
session_start();
require_once __DIR__ . '/config.php';

// Solo acepta peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$matricula = trim($_POST['matricula'] ?? '');
$password  = $_POST['password']  ?? '';

if ($matricula === '' || $password === '') {
    header('Location: login.php?error=1');
    exit();
}

// $conexion se inicializa de forma centralizada en config.php
$sql  = 'SELECT id_usuario, nombre_completo, rol, password, estatus
           FROM usuarios
          WHERE matricula = ?
          LIMIT 1';
$stmt = $conexion->prepare($sql);
$stmt->bind_param('s', $matricula);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $fila = $resultado->fetch_assoc();

    // password_verify compara la contraseña con el hash bcrypt
    if (password_verify($password, $fila['password'])) {

        // Solo los usuarios aprobados pueden entrar (cualquier otro estatus se bloquea)
        if ($fila['estatus'] !== 'aprobado') {
            header('Location: login.php?error=pendiente');
            exit();
        }

        // Sesión segura
        session_regenerate_id(true);
        $_SESSION['id_usuario'] = $fila['id_usuario'];
        $_SESSION['nombre']     = $fila['nombre_completo'];
        $_SESSION['rol']        = $fila['rol'];

        header('Location: seguimiento.php');
        exit();
    }
}

// Credenciales incorrectas
header('Location: login.php?error=1');
exit();
