<?php
// ============================================================
//  procesar_contacto.php — Envía el formulario de contacto
//  al administrador vía SMTP Gmail (PHPMailer).
// ============================================================
require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/assets/lib/PHPMailer/Exception.php';
require_once __DIR__ . '/assets/lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/assets/lib/PHPMailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contacto.php');
    exit();
}

$nombre  = trim($_POST['nombre']  ?? '');
$correo  = trim($_POST['correo']  ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

if ($nombre === '' || $correo === '' || $mensaje === '') {
    header('Location: contacto.php?error=campos');
    exit();
}

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USER;
    $mail->Password   = MAIL_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = 'UTF-8';

    // Remitente: siempre la cuenta autenticada (evita rechazo SPF)
    $mail->setFrom(MAIL_USER, MAIL_FROM_NAME);
    $mail->addAddress(MAIL_USER);
    // Reply-To apunta al visitante para poder responderle directamente
    $mail->addReplyTo($correo, $nombre);

    $mail->isHTML(true);
    $mail->Subject = 'Mensaje de contacto — JuDy Simulator';
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto;'>
            <h2 style='color: #0b132b;'>Nuevo mensaje desde la web</h2>
            <p><strong>Nombre:</strong> "  . htmlspecialchars($nombre)  . "</p>
            <p><strong>Correo:</strong> "  . htmlspecialchars($correo)  . "</p>
            <p><strong>Mensaje:</strong></p>
            <blockquote style='border-left: 4px solid #f37021; padding-left: 12px; color: #333;'>
                " . nl2br(htmlspecialchars($mensaje)) . "
            </blockquote>
        </div>";
    $mail->AltBody = "Nombre: $nombre\nCorreo: $correo\nMensaje:\n$mensaje";

    $mail->send();
    header('Location: contacto.php?enviado=1');
    exit();
} catch (Exception $e) {
    error_log('PHPMailer Error (contacto): ' . $mail->ErrorInfo);
    header('Location: contacto.php?error=envio');
    exit();
}
