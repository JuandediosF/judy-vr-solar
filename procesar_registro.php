<?php
// ============================================================
//  procesar_registro.php — Crea un nuevo usuario pendiente
//  y notifica al administrador por correo con botones de rol.
// ============================================================
require_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'assets/lib/PHPMailer/Exception.php';
require 'assets/lib/PHPMailer/PHPMailer.php';
require 'assets/lib/PHPMailer/SMTP.php';

// Solo acepta peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registro.php');
    exit();
}

$matricula = trim($_POST['matricula'] ?? '');
$nombre    = trim($_POST['nombre']    ?? '');
$correo    = trim($_POST['correo']    ?? '');
$telefono  = trim($_POST['telefono']  ?? '');
$password  = $_POST['password'] ?? '';

// Validación básica de campos
if ($matricula === '' || $nombre === '' || $correo === '' || $password === '') {
    header('Location: registro.php?error=campos');
    exit();
}

// Hash seguro de la contraseña
$password_hash  = password_hash($password, PASSWORD_BCRYPT);
$rol_temporal   = 'por_asignar';
$estatus        = 'pendiente';

// Token aleatorio seguro para el enlace de aprobación por correo
$token          = bin2hex(random_bytes(32));

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conexion->connect_error) {
    die('Error de conexión a la base de datos.');
}

$sql  = 'INSERT INTO usuarios
            (matricula, nombre_completo, email, telefono, password, rol, estatus, token_aprobacion)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
$stmt = $conexion->prepare($sql);
$stmt->bind_param('ssssssss',
    $matricula, $nombre, $correo, $telefono,
    $password_hash, $rol_temporal, $estatus, $token
);

try {
    $stmt->execute();

    // Generar enlaces de aprobación con token seguro
    $link_empleado = APP_URL . '/aprobar.php?m=' . urlencode($matricula) . '&t=' . $token . '&r=empleado';
    $link_gerente  = APP_URL . '/aprobar.php?m=' . urlencode($matricula) . '&t=' . $token . '&r=gerencia';

    // Notificar al administrador por correo
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USER;
    $mail->Password   = MAIL_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = 'UTF-8';

    // El remitente siempre es la cuenta autenticada
    $mail->setFrom(MAIL_USER, MAIL_FROM_NAME);
    $mail->addAddress(MAIL_USER);
    // Reply-To apunta al correo del solicitante para responderle directamente
    $mail->addReplyTo($correo, $nombre);

    $mail->isHTML(true);
    $mail->Subject = 'Nueva solicitud de registro — JuDy Simulator';
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto;'>
            <h2 style='color: #0b132b;'>Nueva Solicitud de Cuenta</h2>
            <p>Se ha registrado un nuevo usuario. Revisa sus datos y asigna su nivel de jerarquía:</p>
            <ul style='font-size: 16px; line-height: 1.8;'>
                <li><strong>Matrícula:</strong> " . htmlspecialchars($matricula) . "</li>
                <li><strong>Nombre:</strong> "    . htmlspecialchars($nombre)    . "</li>
                <li><strong>Correo:</strong> "    . htmlspecialchars($correo)    . "</li>
                <li><strong>Teléfono:</strong> "  . htmlspecialchars($telefono)  . "</li>
            </ul>
            <p style='margin-top: 20px;'>Selecciona el rol para este usuario (el enlace solo puede usarse una vez):</p>
            <a href='" . $link_empleado . "'
               style='display:inline-block; padding:12px 20px; background:#22c55e;
                      color:#fff; text-decoration:none; border-radius:5px;
                      font-weight:bold; margin-right:10px;'>
                ✅ Aprobar como EMPLEADO
            </a>
            <a href='" . $link_gerente . "'
               style='display:inline-block; padding:12px 20px; background:#f37021;
                      color:#fff; text-decoration:none; border-radius:5px; font-weight:bold;'>
                🔑 Aprobar como GERENTE
            </a>
        </div>";

    $mail->AltBody = "Nueva solicitud de: $nombre ($matricula). Accede al panel para gestionar la aprobación.";

    $mail->send();

} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1062) {
        header('Location: registro.php?error=duplicado');
        exit();
    }
    die('Error en la base de datos: ' . htmlspecialchars($e->getMessage()));
} catch (Exception $e) {
    // Si el correo falla, el usuario ya quedó registrado; lo informamos sin exponer detalles internos.
    error_log('PHPMailer Error: ' . $mail->ErrorInfo);
}

$stmt->close();
$conexion->close();

header('Location: login.php?registro=exito');
exit();