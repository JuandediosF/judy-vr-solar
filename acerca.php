<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acerca de — JuDy Simulator</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<?php $nav_tipo = 'publica'; include __DIR__ . '/navbar.php'; ?>

<section class="hero-container" style="align-items:flex-start; padding-top:60px;">
    <div class="hero-content" style="max-width:1000px; text-align:left;">
        <h1 style="font-size:40px; margin-bottom:20px;">
            Acerca de <span class="texto-naranja">Nosotros</span>
        </h1>
        <p style="font-size:20px; margin-bottom:20px;">
            Somos un equipo de desarrollo de la
            <strong>Universidad Tecnológica del Suroeste de Guanajuato (UTSOE)</strong>.
        </p>
        <p style="font-size:20px; margin-bottom:20px;">
            Nuestro objetivo es fusionar la <strong>ingeniería de software multiplataforma</strong>
            con la <strong>seguridad industrial</strong>, creando herramientas innovadoras e
            inmersivas para garantizar el cumplimiento de las normativas STPS en trabajos de alto riesgo.
        </p>
        <p style="font-size:20px;">
            JuDy Simulator utiliza entornos de <strong>Realidad Virtual (Meta Quest)</strong>
            para simular instalación y mantenimiento de celdas fotovoltaicas, permitiendo que
            los operadores practiquen de forma segura antes de trabajar en campo.
        </p>
    </div>
</section>

</body>
</html>