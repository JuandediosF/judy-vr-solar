<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JuDy Simulator — Inicio</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<?php $nav_tipo = 'publica'; include __DIR__ . '/navbar.php'; ?>

<section class="hero-container">
    <div class="hero-content">
        <h1>Entrenamiento Seguro en <span class="texto-naranja">Sistemas Fotovoltaicos</span></h1>
        <p>
            Una plataforma diseñada para evaluar y capacitar a tu personal técnico en un entorno
            de <strong>Realidad Virtual</strong> seguro. Evita riesgos eléctricos y caídas
            practicando las normativas de la STPS (<strong>NOM-009, NOM-029</strong>) antes de salir al campo.
        </p>
        <div>
            <?php if (isset($_SESSION['id_usuario'])): ?>
                <a href="seguimiento.php" class="btn-registro" style="font-size:20px; padding:15px 30px;">
                    Ir a Mi Panel
                </a>
            <?php else: ?>
                <a href="login.php" class="btn-registro" style="font-size:20px; padding:15px 30px;">
                    Comenzar Ahora
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

</body>
</html>