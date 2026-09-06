<?php
// ============================================================
// Servibienes — Landing individual por asesor
// Recibe ?slug= vía la RewriteRule del .htaccess
// ============================================================
require_once __DIR__ . '/includes/iconos.php';

$asesores = require __DIR__ . '/asesores.php';
$slug     = $_GET['slug'] ?? '';
$asesor   = $asesores[$slug] ?? null;

if (!$asesor) {
  http_response_code(404);
  echo "Asesor no encontrado.";
  exit;
}

$whatsapp     = $asesor['whatsapp'];
$waHref       = "https://wa.me/{$whatsapp}";
$fotoBase     = "home-nuevo/assets/equipo/perfiles/";
$asesoresBase = "home-nuevo/assets/equipo/asesores/";

// Categoría que se muestra por defecto al cargar la página.
// Cambiar aquí si el cliente pide otra por defecto: 'venta' | 'anticretico' | 'alquiler'
$categoriaDefaultCalientes = 'venta';

// Inmuebles calientes: misma carpeta global que usa index.php, dividida en
// 3 subcarpetas fijas — una sola fuente, se actualiza subiendo/borrando
// archivos ahí, no hay nada que tocar en código.
$categoriasCalientes = [
  'venta'       => 'Venta',
  'anticretico' => 'Anticrético',
  'alquiler'    => 'Alquiler',
];

$calientesPorCategoria = [];
foreach ($categoriasCalientes as $catSlug => $label) {
  $fotos = glob(__DIR__ . "/home-nuevo/assets/equipo/calientes/$catSlug/*.{png,jpg,webp,jpeg}", GLOB_BRACE);
  sort($fotos);
  if (!empty($fotos)) {
    $calientesPorCategoria[$catSlug] = $fotos;
  }
}

$categoriaActivaCalientes = array_key_exists($categoriaDefaultCalientes, $calientesPorCategoria)
  ? $categoriaDefaultCalientes
  : (array_key_first($calientesPorCategoria) ?? null);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($asesor['nombre']) ?> · Servibienes</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="home-nuevo/css/style.css">
  <link rel="stylesheet" href="home-nuevo/css/asesor.css">
</head>

<body class="pagina-asesor">

  <div class="asesor-contenedor">

    <!-- HERO: foto + nombre + redes, igual estructura que el mockup del diseñador -->
    <section class="asesor-hero">
      <img src="<?= $fotoBase . htmlspecialchars($asesor['foto']) ?>"
        alt="<?= htmlspecialchars($asesor['nombre']) ?>" class="asesor-hero-foto">

      <div class="asesor-hero-texto">
        <h1><?= implode('<br>', array_map('htmlspecialchars', explode(' ', $asesor['nombre']))) ?></h1>
        <?php
        // slugs de Simple Icons: https://simpleicons.org
        $iconosRedes = [
          'tiktok'    => 'tiktok',
          'facebook'  => 'facebook',
          'instagram' => 'instagram',
          'web'       => 'googlechrome', // no hay ícono "web" genérico; alternativa abajo
        ];
        ?>
        <ul class="asesor-redes">
          <?php foreach ($asesor['redes'] as $r): ?>
            <li>
              <a href="<?= htmlspecialchars($r['url']) ?>" target="_blank" class="red-item red-<?= htmlspecialchars($r['tipo']) ?>">
                <span class="red-icono">
                  <img src="https://cdn.simpleicons.org/<?= $iconosRedes[$r['tipo']] ?? 'link' ?>"
                    alt="<?= htmlspecialchars($r['tipo']) ?>" loading="lazy">
                </span>
                <span class="red-label"><?= htmlspecialchars($r['label']) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <img src="home-nuevo/assets/logo.png" alt="Servibienes" class="asesor-hero-logo">
    </section>

    <!-- BIO -->
    <section class="asesor-bio">
      <p class="asesor-cargo"><?= htmlspecialchars($asesor['cargo']) ?></p>
      <p class="asesor-subtitulo">| <?= htmlspecialchars($asesor['subtitulo']) ?></p>
      <p class="asesor-bio-texto"><?= nl2br(htmlspecialchars($asesor['bio'])) ?></p>

      <?php if (!empty($calientesPorCategoria)): ?>
        <!-- Pestañas de filtro: solo se muestran las categorías que tienen fotos -->
        <div class="calientes-tabs">
          <?php foreach ($calientesPorCategoria as $catSlug => $fotos): ?>
            <button
              class="calientes-tab<?= $catSlug === $categoriaActivaCalientes ? ' activo' : '' ?>"
              data-categoria="<?= $catSlug ?>"
              onclick="cambiarCategoriaCalientes('<?= $catSlug ?>')">
              <?= htmlspecialchars($categoriasCalientes[$catSlug]) ?>
            </button>
          <?php endforeach; ?>
        </div>

        <?php foreach ($calientesPorCategoria as $catSlug => $fotos): ?>
          <div
            class="calientes-grupo"
            id="grupo-calientes-<?= $catSlug ?>"
            style="display: <?= $catSlug === $categoriaActivaCalientes ? 'block' : 'none' ?>;">
            <div class="carrusel-wrap">
              <button class="carrusel-btn prev" onclick="moverCarrusel('calientes-<?= $catSlug ?>', -1)">&#8249;</button>
              <div class="carrusel-track" id="track-calientes-<?= $catSlug ?>">
                <?php foreach ($fotos as $i => $foto): ?>
                  <?php $rel = str_replace(__DIR__ . '/', '', $foto); ?>
                  <div class="slide" onclick="abrirLightbox('calientes-<?= $catSlug ?>', <?= $i ?>)">
                    <img src="<?= htmlspecialchars($rel) ?>" alt="Inmueble en <?= htmlspecialchars($categoriasCalientes[$catSlug]) ?>">
                    <div class="slide-overlay"><span>Ver detalle</span></div>
                  </div>
                <?php endforeach; ?>
              </div>
              <button class="carrusel-btn next" onclick="moverCarrusel('calientes-<?= $catSlug ?>', 1)">&#8250;</button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

    <!-- BADGES -->
    <?php if (!empty($asesor['badges'])): ?>
      <section class="asesor-badges">
        <?php foreach ($asesor['badges'] as $b): ?>
          <div class="asesor-badge">
            <div class="asesor-badge-icono">
              <img src="home-nuevo/assets/diseno/iconos/<?= htmlspecialchars($b['icono']) ?>.png" alt="<?= htmlspecialchars($b['texto']) ?>">
            </div>
            <p><?= htmlspecialchars($b['texto']) ?></p>
          </div>
        <?php endforeach; ?>
      </section>
  </div>
<?php endif; ?>

<!-- LIGHTBOX: sigue igual, ahora alimentado por los 3 carruseles de arriba -->
<?php if (!empty($calientesPorCategoria)): ?>
  <div id="lightbox" class="lightbox-overlay" onclick="cerrarLightboxFondo(event)">
    <button class="lightbox-close" onclick="cerrarLightbox()">&#215;</button>
    <button class="lightbox-nav prev" onclick="navLightbox(-1)">&#8249;</button>
    <div class="lightbox-contenido">
      <img id="lightbox-img" src="" alt="">
      <div class="lightbox-footer">
        <span id="lightbox-contador"></span>
        <a id="lightbox-wa" href="" target="_blank" class="lightbox-wa-btn">Consultar por WhatsApp</a>
      </div>
    </div>
    <button class="lightbox-nav next" onclick="navLightbox(1)">&#8250;</button>
  </div>
<?php endif; ?>

<!-- CTA flotante -->
<a href="<?= $waHref ?>" target="_blank" class="asesor-wsp-flotante" aria-label="Hablar por WhatsApp">
  <img src="https://cdn.simpleicons.org/whatsapp/ffffff" alt="WhatsApp" class="wsp-icono">
  <span class="wsp-texto">Hablar con <?= htmlspecialchars(explode(' ', $asesor['nombre'])[0]) ?></span>
</a>

<script>
  // WA_NUMBER queda atado a ESTE asesor, no al número global de la empresa
  const WA_NUMBER = "<?= $whatsapp ?>";
</script>
<script src="home-nuevo/js/main.js"></script>

</body>

</html>