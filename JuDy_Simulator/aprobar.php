<?php
if(isset($_GET['m']) && isset($_GET['t']) && isset($_GET['r'])) {
    
    $matricula = $_GET['m'];
    $token_recibido = $_GET['t'];
    $rol_asignado = $_GET['r']; 
    
    $token_esperado = md5($matricula . "JuDy2026");
    
    if($token_recibido === $token_esperado) {
        if($rol_asignado !== 'empleado' && $rol_asignado !== 'gerencia') {
            die("<h2 style='text-align:center; color:red; margin-top:50px;'>Rol no válido.</h2>");
        }

        $conexion = new mysqli("localhost", "root", "", "JuDyb");
        if ($conexion->connect_error) die("Error de conexión");
        
        $sql = "UPDATE USUARIOS SET estatus = 'aprobado', rol = ? WHERE matricula = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ss", $rol_asignado, $matricula);
        $stmt->execute();
        
        if($stmt->affected_rows > 0) {
            echo "<div style='text-align:center; font-family:sans-serif; margin-top:50px;'>";
            echo "<h2 style='color:#22c55e;'>¡Usuario aprobado con éxito!</h2>";
            echo "<p style='font-size:18px;'>Se le ha asignado la jerarquía de: <strong>" . strtoupper($rol_asignado) . "</strong></p>";
            echo "<br><a href='login.php' style='padding:10px 20px; background:#0b132b; color:#fff; text-decoration:none; border-radius:5px;'>Ir al Login</a>";
            echo "</div>";
        } else {
            echo "<h2 style='font-family:sans-serif; text-align:center; margin-top:50px;'>El usuario ya estaba aprobado o no existe.</h2>";
        }
        $stmt->close();
        $conexion->close();
    } else {
        echo "<h2 style='text-align:center; margin-top:50px;'>Token de seguridad inválido.</h2>";
    }
} else {
    echo "<h2 style='text-align:center; margin-top:50px;'>Enlace incompleto o dañado.</h2>";
}
?>