<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avances VR — JuDy Simulator</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<?php $nav_tipo = 'publica'; include __DIR__ . '/navbar.php'; ?>

<section class="hero-container" style="align-items:flex-start; padding-top:60px;">
    <div class="hero-content" style="max-width:1000px; text-align:left;">
        <h1 style="font-size:40px; margin-bottom:20px;">
            Avances <span class="texto-naranja">VR</span>
        </h1>
        <p style="font-size:20px;">
            Explora el progreso de nuestros módulos de Realidad Virtual para <strong>Meta Quest</strong>.
            Aquí se visualizarán los modelos 3D y las mecánicas de interacción utilizadas para simular
            la instalación y mantenimiento de celdas fotovoltaicas conforme a las normas
            <strong>NOM-009</strong> y <strong>NOM-029</strong>.
        </p>

        <div style="background:var(--fondo-tarjeta); border:1px solid var(--borde-color);
                    border-radius:12px; padding:60px 20px; text-align:center; margin-top:40px;">
            <p style="color:var(--letras-secundarias); font-size:18px;">
                [ Galería de imágenes y videos del entorno Unity — Próximamente ]
            </p>
        </div>
    </div>
</section>

</body>
</html>