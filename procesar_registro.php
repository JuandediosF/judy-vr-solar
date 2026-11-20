<?php
// ============================================================
//  procesar_registro.php — Crea un nuevo usuario pendiente
//  y notifica al administrador por correo con un enlace de aprobación.
// ============================================================
require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/assets/lib/PHPMailer/Exception.php';
require_once __DIR__ . '/assets/lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/assets/lib/PHPMailer/SMTP.php';

// Solo acepta peticiones POST
// csrf-exception: registro público sin sesión que proteger; la aprobación posterior sí valida token.
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

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    header('Location: registro.php?error=correo');
    exit();
}

// Hash seguro de la contraseña
$password_hash  = password_hash($password, PASSWORD_BCRYPT);
$rol_temporal   = 'por_asignar';
$estatus        = 'pendiente';

// Token aleatorio seguro y expiración del enlace (7 días)
$token          = bin2hex(random_bytes(32));
$token_expira   = date('Y-m-d H:i:s', strtotime('+7 days'));

// $conexion se inicializa en config.php
$sql  = 'INSERT INTO usuarios
            (matricula, nombre_completo, email, telefono, password, rol, estatus, token_aprobacion, token_expira_en)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
$stmt = $conexion->prepare($sql);
$stmt->bind_param('sssssssss',
    $matricula, $nombre, $correo, $telefono,
    $password_hash, $rol_temporal, $estatus, $token, $token_expira
);

try {
    $stmt->execute();

    // Un solo enlace: el rol se elige después, en el formulario de aprobación.
    $link_aprobacion = APP_URL . '/aprobar.php?m=' . urlencode($matricula) . '&t=' . $token;

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
            <p>Revisa los datos y asigna la jerarquía en el formulario de aprobación:</p>
            <ul style='font-size: 16px; line-height: 1.8;'>
                <li><strong>Matrícula:</strong> " . htmlspecialchars($matricula) . "</li>
                <li><strong>Nombre:</strong> "    . htmlspecialchars($nombre)    . "</li>
                <li><strong>Correo:</strong> "    . htmlspecialchars($correo)    . "</li>
                <li><strong>Teléfono:</strong> "  . htmlspecialchars($telefono)  . "</li>
            </ul>
            <p style='margin-top: 20px;'>
                <a href='" . htmlspecialchars($link_aprobacion, ENT_QUOTES, 'UTF-8') . "'
                   style='display:inline-block; padding:12px 20px; background:#f37021;
                          color:#fff; text-decoration:none; border-radius:5px; font-weight:bold;'>
                    Revisar y aprobar solicitud
                </a>
            </p>
            <p style='font-size:13px; color:#475569;'>
                El enlace vence en 7 días y solo puede usarse una vez.
            </p>
        </div>";

    $mail->AltBody = "Nueva solicitud de: $nombre ($matricula). Abre el enlace de aprobación para asignar el rol.";

    $mail->send();

} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1062) {
        header('Location: registro.php?error=duplicado');
        exit();
    }
    error_log('DB Error: ' . $e->getMessage());
    die('Error en el registro.');
} catch (Exception $e) {
    // Si el correo falla, el usuario ya quedó registrado; lo informamos sin exponer detalles internos.
    error_log('PHPMailer Error: ' . $mail->ErrorInfo);
}

$stmt->close();

header('Location: login.php?registro=exito');
exit();
