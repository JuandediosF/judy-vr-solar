<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'assets/lib/PHPMailer/Exception.php';
require 'assets/lib/PHPMailer/PHPMailer.php';
require 'assets/lib/PHPMailer/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $mensaje = $_POST['mensaje'];

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dilan.cano2025@gmail.com'; 
        $mail->Password = 'slnxdhfpbufyxwpf'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($correo, $nombre);
        $mail->addAddress('dilan.cano2025@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'Nuevo Mensaje de Contacto - JuDy Simulator';
        $mail->Body = "
            <h2>Mensaje recibido desde la web</h2>
            <p><strong>Nombre:</strong> $nombre</p>
            <p><strong>Correo:</strong> $correo</p>
            <p><strong>Mensaje:</strong><br>$mensaje</p>
        ";

        $mail->send();
        header("Location: contacto.php?enviado=1");
        exit();
    } catch (Exception $e) {
        echo "Error al enviar el mensaje: {$mail->ErrorInfo}";
    }
}
?>