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

$rol_temporal = 'por_asignar'; 
$estatus = 'pendiente';

$sql = "INSERT INTO USUARIOS (matricula, nombre_completo, email, telefono, password, rol, estatus) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sssssss", $matricula, $nombre, $correo, $telefono, $password, $rol_temporal, $estatus);

try {
    $stmt->execute();
    $token = md5($matricula . "JuDy2026"); 
    
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'dilan.cano2025@gmail.com'; 
    $mail->Password = 'slnxdhfpbufyxwpf'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('dilan.cano2025@gmail.com', 'JuDy Simulator System');
    $mail->addAddress('dilan.cano2025@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'Nueva solicitud de registro y asignación de jerarquía';
    
    $link_empleado = "http://localhost/JuDy_Simulator/aprobar.php?m=" . urlencode($matricula) . "&t=" . $token . "&r=empleado";
    $link_gerente = "http://localhost/JuDy_Simulator/aprobar.php?m=" . urlencode($matricula) . "&t=" . $token . "&r=gerencia";

    $mail->Body = "
        <h2 style='color: #0b132b;'>Nueva Solicitud de Cuenta</h2>
        <p>Se ha registrado un nuevo usuario. Por favor revisa sus datos y asigna su nivel de jerarquía para autorizar su acceso:</p>
        <ul style='font-size: 16px;'>
            <li><strong>Matrícula:</strong> $matricula</li>
            <li><strong>Nombre:</strong> $nombre</li>
            <li><strong>Correo:</strong> $correo</li>
            <li><strong>Teléfono:</strong> $telefono</li>
        </ul>
        <br>
        <p>Selecciona el rol para este usuario (solo puedes hacer clic en uno):</p>
        <a href='$link_empleado' style='display:inline-block; padding:12px 20px; background-color:#22c55e; color:#fff; text-decoration:none; border-radius:5px; font-weight:bold; margin-right:10px;'>Aprobar como EMPLEADO</a>
        <a href='$link_gerente' style='display:inline-block; padding:12px 20px; background-color:#f37021; color:#fff; text-decoration:none; border-radius:5px; font-weight:bold;'>Aprobar como GERENTE</a>
    ";

    $mail->send();
    header("Location: login.php?registro=exito");

} catch (mysqli_sql_exception $e) {
    // Si el código de error es 1062, es una entrada duplicada
    if ($e->getCode() == 1062) {
        header("Location: registro.php?error=duplicado");
        exit();
    } else {
        die("Error en la base de datos: " . $e->getMessage());
    }
} catch (Exception $e) {
    echo "Error al enviar el correo a Dilan: {$mail->ErrorInfo}";
}

$stmt->close();
$conexion->close();
?>