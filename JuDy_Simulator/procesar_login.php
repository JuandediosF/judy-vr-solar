<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

$conexion = new mysqli("localhost", "root", "", "JuDyb");
if ($conexion->connect_error) {
    die("Error de conexión a la base de datos: " . $conexion->connect_error);
}

$matricula = $_POST['matricula'];
$password = $_POST['password'];

$sql = "SELECT id_usuario, nombre_completo, rol, password, estatus FROM USUARIOS WHERE matricula = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $matricula);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $fila = $resultado->fetch_assoc();
    
    if ($password === $fila['password']) {
        if ($fila['estatus'] === 'pendiente') {
            header("Location: login.php?error=pendiente");
            exit();
        }
        
        $_SESSION['id_usuario'] = $fila['id_usuario'];
        $_SESSION['nombre'] = $fila['nombre_completo'];
        $_SESSION['rol'] = $fila['rol'];
        header("Location: seguimiento.php");
        exit();
    } else {
        header("Location: login.php?error=1");
        exit();
    }
} else {
    header("Location: login.php?error=1");
    exit();
}
$stmt->close();
$conexion->close();
?>