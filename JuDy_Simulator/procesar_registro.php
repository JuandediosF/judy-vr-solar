<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'assets/lib/PHPMailer/Exception.php';
require 'assets/lib/PHPMailer/PHPMailer.php';
require 'assets/lib/PHPMailer/SMTP.php';

$conexion = new mysqli("localhost", "root", "", "JuDyb");
if ($conexion->connect_error) die("Error de conexión");

$matricula = $_POST['matricula'];
$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$telefono = $_POST['telefono'];
$password = $_POST['password'];
$rol = 'empleado'; 
$estatus = 'pendiente';

$sql = "INSERT INTO USUARIOS (matricula, nombre_completo, email, telefono, password, rol, estatus) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sssssss", $matricula, $nombre, $correo, $telefono, $password, $rol, $estatus);

if ($stmt->execute()) {
    $token = md5($matricula . "JuDy2026"); 
    
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        
        $mail->Username = 'dilan.cano2025@gmail.com'; 
        $mail->Password = 'slnxdhfpbufyxwpf'; 
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('dilan.cano2025@gmail.com', 'JuDy Simulator System');
        $mail->addAddress('dilan.cano2025@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'Nueva solicitud de registro - JuDy Simulator';
        
        $enlace_aprobacion = "http://localhost/JuDy_Simulator/aprobar.php?m=" . urlencode($matricula) . "&t=" . $token;

        $mail->Body = "
            <h2>Nueva Solicitud de Registro</h2>
            <p>Se ha registrado un nuevo operador y requiere tu autorización:</p>
            <ul>
                <li><strong>Matrícula:</strong> $matricula</li>
                <li><strong>Nombre:</strong> $nombre</li>
                <li><strong>Correo:</strong> $correo</li>
                <li><strong>Teléfono:</strong> $telefono</li>
            </ul>
            <p>Para autorizar el acceso de este usuario, haz clic en el siguiente enlace (debes abrirlo desde la PC del servidor):</p>
            <br>
            <a href='$enlace_aprobacion' style='padding:10px 20px; background-color:#f37021; color:#fff; text-decoration:none; border-radius:5px;'>Aprobar Usuario</a>
        ";

        $mail->send();
        header("Location: login.php?registro=exito");
    } catch (Exception $e) {
        echo "El registro se guardó, pero hubo un error enviando el correo: {$mail->ErrorInfo}";
    }
} else {
    echo "Error al registrar: Puede que la matrícula ya exista.";
}
$stmt->close();
$conexion->close();
?>