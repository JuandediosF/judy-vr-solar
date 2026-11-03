<?php
if(isset($_GET['m']) && isset($_GET['t'])) {
    $matricula = $_GET['m'];
    $token_recibido = $_GET['t'];
    
    // Verificamos que el enlace no haya sido alterado
    $token_esperado = md5($matricula . "JuDy2026");
    
    if($token_recibido === $token_esperado) {
        $conexion = new mysqli("localhost", "root", "", "JuDyb");
        
        $sql = "UPDATE USUARIOS SET estatus = 'aprobado' WHERE matricula = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $matricula);
        $stmt->execute();
        
        if($stmt->affected_rows > 0) {
            echo "<h2 style='font-family:sans-serif; color:#22c55e; text-align:center; margin-top:50px;'>¡Usuario aprobado con éxito!</h2>";
            echo "<p style='text-align:center;'><a href='login.php'>Ir al inicio de sesión</a></p>";
        } else {
            echo "<h2 style='font-family:sans-serif; text-align:center; margin-top:50px;'>El usuario ya estaba aprobado o no existe.</h2>";
        }
    } else {
        echo "Token de seguridad inválido.";
    }
} else {
    echo "Faltan parámetros.";
}
?>