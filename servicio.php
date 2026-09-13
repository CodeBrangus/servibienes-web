<?php
// ============================================================
// Servibienes — Página de detalle por servicio (Constructora)
// Una sola plantilla para los 16 servicios: /servicio.php?s=slug
// ============================================================
$whatsapp  = "59177657257";
$servicios = require 'includes/servicios.php';

// Validar el slug de la URL contra la whitelist de servicios.
// Nunca se usa $_GET directo para construir una ruta de archivo.
$slug = $_GET['s'] ?? '';
$servicioActual = $servicios[$slug] ?? null;

if (!$servicioActual) {
  http_response_code(404);
}

// Si el slug es válido, buscar las imágenes de contenido de ese servicio.
$fotosServicio = [];
if ($servicioActual) {
  $fotosServicio = glob("home-nuevo/assets/servicios/$slug/material/*.{png,jpg,jpeg,webp}", GLOB_BRACE);
  sort($fotosServicio);
}

$tituloPagina = $servicioActual
  ? htmlspecialchars($servicioActual['titulo']) . ' — Servibienes Constructora'
  : 'Servicio no encontrado — Servibienes';

$mensajeWa = $servicioActual
  ? urlencode('Hola, quisiera más información sobre ' . $servicioActual['titulo'])
  : '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $tituloPagina ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="home-nuevo/css/style.css">
  <link rel="stylesheet" href="home-nuevo/css/lightbox.css">
</head>

<body>

  <?php include 'includes/navbar.php'; ?>

  <?php if (!$servicioActual): ?>

    <!-- SERVICIO NO ENCONTRADO -->
    <section class="servicio-detalle servicio-404">
      <p class="servicio-404-texto">No encontramos el servicio que buscas.</p>
      <a href="index.php#servicios" class="servicio-volver">Volver al catálogo de servicios</a>
    </section>

  <?php else: ?>

    <!-- DETALLE DEL SERVICIO -->
    <section class="servicio-detalle">

      <div class="servicio-detalle-header">
        <img
          src="home-nuevo/assets/servicios/<?= $slug ?>/icono.png"
          alt="<?= htmlspecialchars($servicioActual['titulo']) ?>"
          class="servicio-detalle-icono"
          onerror="this.src='home-nuevo/assets/servicios/_generico.png'">
        <h1 class="servicio-detalle-titulo"><?= htmlspecialchars($servicioActual['titulo']) ?></h1>
        <p class="servicio-detalle-descripcion"><?= htmlspecialchars($servicioActual['descripcion']) ?></p>
      </div>

      <a href="https://wa.me/<?= $whatsapp ?>?text=<?= $mensajeWa ?>" target="_blank" class="servicio-wa-btn">
        Consultar por WhatsApp
      </a>

      <div class="servicio-galeria lightbox-gallery">
        <?php if (!empty($fotosServicio)): ?>
          <?php foreach ($fotosServicio as $foto): ?>
            <img src="<?= $foto ?>" alt="<?= htmlspecialchars($servicioActual['titulo']) ?>" class="servicio-galeria-img">
          <?php endforeach; ?>
        <?php else: ?>
          <p class="servicio-sin-contenido">Contenido en preparación. Muy pronto encontrarás aquí las imágenes de este servicio.</p>
        <?php endif; ?>
      </div>

      <a href="https://wa.me/<?= $whatsapp ?>?text=<?= $mensajeWa ?>" target="_blank" class="servicio-wa-btn">
        Consultar por WhatsApp
      </a>

    </section>

  <?php endif; ?>

  <script>
    const WA_NUMBER = "<?= $whatsapp ?>";
  </script>
  <script src="home-nuevo/js/main.js"></script>
  <script src="home-nuevo/js/lightbox.js"></script>

</body>

</html>