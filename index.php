<?php
// ============================================================
// Servibienes — Página de prueba
// ============================================================
$whatsapp = "59177657257";

// Categoría que se muestra por defecto al cargar la página.
// Cambiar aquí si el cliente pide otra por defecto: 'venta' | 'anticretico' | 'alquiler'
$categoriaDefaultCalientes = 'venta';

$videos = [
  ["archivo" => "CapacitacionMensualWp.mp4", "titulo" => "Capacitacion mensual", "formato" => "vertical"],
  ["archivo" => "AsesoresMasterWp.mp4",      "titulo" => "Asesores master",      "formato" => "vertical"],
  ["archivo" => "CapacitacionWp.mp4",        "titulo" => "Capacitacion",         "formato" => "vertical"],
  ["archivo" => "ComiunityDonEdgarWp.mp4",   "titulo" => "Comunity don edgar",   "formato" => "horizontal"],
];

// ------------------------------------------------------------
// Inmuebles calientes: se leen 3 subcarpetas fijas.
// Cada carpeta se actualiza subiendo/borrando archivos ahí,
// no hay nada que tocar en código.
// ------------------------------------------------------------
$categoriasCalientes = [
  'venta'       => 'Venta',
  'anticretico' => 'Anticrético',
  'alquiler'    => 'Alquiler',
];

$calientesPorCategoria = [];
foreach ($categoriasCalientes as $slug => $label) {
  $fotos = glob("home-nuevo/assets/equipo/calientes/$slug/*.{png,jpg,webp,jpeg}", GLOB_BRACE);
  sort($fotos);
  if (!empty($fotos)) {
    $calientesPorCategoria[$slug] = $fotos;
  }
}

// Si la categoría por defecto no tiene fotos todavía, usar la primera que sí tenga.
$categoriaActivaCalientes = array_key_exists($categoriaDefaultCalientes, $calientesPorCategoria)
  ? $categoriaDefaultCalientes
  : (array_key_first($calientesPorCategoria) ?? null);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Servibienes S.R.L.</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="home-nuevo/css/style.css">
</head>

<body>

  <!-- NAVBAR -->
  <nav id="navbar">
    <!-- Zona 1: Logo -->
    <a href="#inicio" class="nav-logo">
      <img src="home-nuevo/assets/logo.png" alt="Servibienes"
        onerror="this.outerHTML='<span style=\'font-size:1.4rem;font-weight:900;color:#1B2E6B\'>SB</span>'">
    </a>

    <!-- Zona 2: Links centrados -->
    <ul class="nav-links">
      <li><a href="#inicio" class="activo">Inicio</a></li>
      <li><a href="#proyectos" class="normal">Proyectos</a></li>
      <li><a href="#contactos" class="activo">Contactos</a></li>
      <li><a href="#nosotros" class="normal">Nosotros</a></li>
    </ul>

    <!-- Zona 3: CTA separado -->
    <a href="https://wa.me/<?= $whatsapp ?>" target="_blank" class="nav-cta">
      Contactar<br>con ventas
    </a>
  </nav>

  <!-- HERO -->
  <section id="inicio">
    <img src="home-nuevo/assets/portada.webp" alt="Tú mereces la casa de tus sueños" class="hero-img">
  </section>

  <!-- MISION / VISION -->
  <section id="nosotros">
    <div class="mv-fila">
      <div class="mv-titulo turquesa">MISIÓN</div>
      <p class="mv-texto">Servibienes se dedica a conectar a las personas con el hogar ideal, ofreciendo soluciones inmobiliarias integrales basadas en honestidad, transparencia y profesionalismo. Cada inversión está respaldada por la seguridad jurídica necesaria, garantizando confianza y tranquilidad.</p>
    </div>
    <div class="mv-fila derecha">
      <div class="mv-titulo azul">VISIÓN</div>
      <p class="mv-texto">Servibienes aspira a ser sinónimo de excelencia inmobiliaria en Bolivia, anticipándose al mercado y ofreciendo propiedades que son proyectos de vida. Su visión es ser el puente para las familias bolivianas hacia el crecimiento patrimonial, impulsando el desarrollo urbano sostenible y el progreso económico de Santa Cruz y del país. El objetivo final: ser la primera opción para quienes buscan no solo una propiedad, sino un legado para futuras generaciones.</p>
    </div>
  </section>

  <!-- EQUIPO — DON MAURICIO -->
  <section id="equipo">
    <div class="director-wrap">
      <img src="home-nuevo/assets/don-mauricio.png" alt="Lic. Mauricio Cespedes" class="director-img">
      <span class="equipo-titulo-badge">CONOCE A NUESTRO EQUIPO</span>
    </div>
  </section>

  <!-- TEAM LEADERS -->
  <section id="lideres">
    <div class="carrusel-wrap">
      <button class="carrusel-btn prev" onclick="moverCarrusel('lideres', -1)">&#8249;</button>
      <div class="carrusel-track" id="track-lideres">
        <?php
        $lideres = glob("home-nuevo/assets/equipo/lideres/*.{png,jpg,webp}", GLOB_BRACE);
        sort($lideres);
        foreach ($lideres as $foto): ?>
          <div class="slide">
            <img src="<?= $foto ?>" alt="Team Leader">
          </div>
        <?php endforeach; ?>
      </div>
      <button class="carrusel-btn next" onclick="moverCarrusel('lideres', 1)">&#8250;</button>
    </div>
  </section>

  <div class="separador-equipo"></div>

  <!-- ASESORES COMERCIALES -->
  <section id="asesores">
    <div class="carrusel-wrap">
      <button class="carrusel-btn prev" onclick="moverCarrusel('asesores', -1)">&#8249;</button>
      <div class="carrusel-track" id="track-asesores">
        <?php
        $asesores = glob("home-nuevo/assets/equipo/asesores/*.{png,jpg,webp}", GLOB_BRACE);
        sort($asesores);
        foreach ($asesores as $foto): ?>
          <div class="slide">
            <img src="<?= $foto ?>" alt="Asesor Comercial">
          </div>
        <?php endforeach; ?>
      </div>
      <button class="carrusel-btn next" onclick="moverCarrusel('asesores', 1)">&#8250;</button>
    </div>
  </section>

  <!-- INMUEBLES CALIENTES -->
  <section id="calientes">
    <div class="calientes-header">
      <span class="calientes-badge">INMUEBLES CALIENTES</span>
    </div>

    <?php if (!empty($calientesPorCategoria)): ?>
      <!-- Pestañas de filtro: solo se muestran las categorías que tienen fotos -->
      <div class="calientes-tabs">
        <?php foreach ($calientesPorCategoria as $slug => $fotos): ?>
          <button
            class="calientes-tab<?= $slug === $categoriaActivaCalientes ? ' activo' : '' ?>"
            data-categoria="<?= $slug ?>"
            onclick="cambiarCategoriaCalientes('<?= $slug ?>')">
            <?= htmlspecialchars($categoriasCalientes[$slug]) ?>
          </button>
        <?php endforeach; ?>
      </div>

      <?php foreach ($calientesPorCategoria as $slug => $fotos): ?>
        <div
          class="calientes-grupo"
          id="grupo-calientes-<?= $slug ?>"
          style="display: <?= $slug === $categoriaActivaCalientes ? 'block' : 'none' ?>;">
          <div class="carrusel-wrap">
            <button class="carrusel-btn prev" onclick="moverCarrusel('calientes-<?= $slug ?>', -1)">&#8249;</button>
            <div class="carrusel-track" id="track-calientes-<?= $slug ?>">
              <?php foreach ($fotos as $i => $foto): ?>
                <div class="slide" onclick="abrirLightbox('calientes-<?= $slug ?>', <?= $i ?>)">
                  <img src="<?= $foto ?>" alt="Inmueble en <?= htmlspecialchars($categoriasCalientes[$slug]) ?>">
                  <div class="slide-overlay">
                    <span>Ver detalle</span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <button class="carrusel-btn next" onclick="moverCarrusel('calientes-<?= $slug ?>', 1)">&#8250;</button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align:center; color:#888; padding: 40px;">Aún no hay inmuebles cargados.</p>
    <?php endif; ?>
  </section>

  <!-- LIGHTBOX INMUEBLES -->
  <div id="lightbox" class="lightbox-overlay" onclick="cerrarLightboxFondo(event)">
    <button class="lightbox-close" onclick="cerrarLightbox()">&#215;</button>
    <button class="lightbox-nav prev" onclick="navLightbox(-1)">&#8249;</button>
    <div class="lightbox-contenido">
      <img id="lightbox-img" src="" alt="">
      <div class="lightbox-footer">
        <span id="lightbox-contador"></span>
        <a id="lightbox-wa" href="" target="_blank" class="lightbox-wa-btn">
          Consultar por WhatsApp
        </a>
      </div>
    </div>
    <button class="lightbox-nav next" onclick="navLightbox(1)">&#8250;</button>
  </div>

  <!-- VIDEOS -->
  <section id="videos">
    <div class="carrusel-wrap">
      <button class="carrusel-btn prev" onclick="moverCarruselVideos(-1)">&#8249;</button>
      <div class="carrusel-track" id="track-videos">
        <?php foreach ($videos as $i => $v): ?>
          <div class="slide slide-video formato-<?= $v['formato'] ?>" onclick="abrirVideoLightbox(<?= $i ?>)">
            <div class="video-thumb-wrap">
              <video src="home-nuevo/assets/videos/<?= $v['archivo'] ?>" class="video-thumb-img" preload="metadata" muted></video>
              <div class="video-play-overlay">
                <div class="video-play-btn">&#9654;</div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <button class="carrusel-btn next" onclick="moverCarruselVideos(1)">&#8250;</button>
    </div>
  </section>

  <!-- LIGHTBOX VIDEO -->
  <div id="lightbox-video" class="lightbox-overlay" onclick="cerrarVideoLightboxFondo(event)">
    <button class="lightbox-close" onclick="cerrarVideoLightbox()">&#215;</button>
    <button class="lightbox-nav prev" onclick="navVideoLightbox(-1)">&#8249;</button>
    <div class="lightbox-contenido lightbox-video-contenido">
      <div class="video-iframe-wrap">
        <video id="lightbox-video-player" controls autoplay playsinline>
          <source src="" type="video/mp4">
        </video>
      </div>
      <div class="lightbox-footer">
        <span id="lightbox-video-contador"></span>
        <span id="lightbox-video-titulo"></span>
      </div>
    </div>
    <button class="lightbox-nav next" onclick="navVideoLightbox(1)">&#8250;</button>
  </div>

  <!-- PROYECTOS -->
  <section id="proyectos">
    <div class="proyectos-fondo">
      <div class="proyectos-overlay"></div>
      <div class="proyectos-contenido">
        <h2 class="proyectos-titulo">NUESTROS PROYECTOS</h2>
        <p class="proyectos-texto">
          Servibienes cuenta con un portafolio sólido de proyectos que reflejan calidad, innovación y
          compromiso con el crecimiento urbano de Santa Cruz y Bolivia. Entre nuestros proyectos
          destacados se encuentran: Raizant Blend, Olivos del Norte, Community Alto Norte, Vistara
          Equipetrol, Palmera Garden y Auras del Sur.
        </p>
      </div>
    </div>
  </section>

  <!-- TARJETAS DE PROYECTOS -->
  <section id="proyectos-detalle">

    <!-- 1. Community Alto Norte — imagen izq, texto der -->
    <div class="proyecto-card">
      <div class="proyecto-media">
        <video controls preload="metadata" class="proyecto-video">
          <source src="/home-nuevo/assets/videos/ComiunityWp.mp4" type="video/mp4">
          Tu navegador no soporta el elemento de video.
        </video>
      </div>
      <div class="proyecto-info">
        <div class="proyecto-badge turquesa">Community Alto Norte</div>
        <p>Community Alto Norte ofrece departamentos desde 32 m² hasta 65 m², con opciones de monoambientes, 1 y 2 dormitorios, todos con balcón y distribución funcional. Además, cuenta con completas áreas sociales que aportan comodidad y estilo de vida moderno.</p>
        <p>El condominio está situado en el 5to anillo de Santa Cruz de la Sierra, con accesos por la Av. Radial 27 y cercanía a puntos clave como colegios, centros comerciales y áreas verdes.</p>
      </div>
    </div>

    <!-- 2. Raizant — texto izq, imagen der -->
    <div class="proyecto-card">
      <div class="proyecto-info">
        <div class="proyecto-badge azul">RAIZANT</div>
        <p>RAIZANT | Blend: donde todo encaja. productividad con comunidad. enfoque con pausas. Todo diseñado para que tu vida fluya con equilibrio. Flexibilidad para coexistir, equilibrio para vivir. Seguridad para invertir.</p>
        <p>Espacios diseñados para recargar energía, compartir y construir comunidad. Ambientes versátiles que se adaptan a distintos estilos de vida y necesidades.</p>
      </div>
      <div class="proyecto-media">
        <video controls preload="metadata" class="proyecto-video">
          <source src="/home-nuevo/assets/videos/RaizantWp.mp4" type="video/mp4">
          Tu navegador no soporta el elemento de video.
        </video>
      </div>
    </div>

    <!-- 3. Vistara — imagen izq, texto der -->
    <div class="proyecto-card">
      <div class="proyecto-media">
        <video controls preload="metadata" class="proyecto-video">
          <source src="/home-nuevo/assets/videos/VistaraWp.mp4" type="video/mp4">
          Tu navegador no soporta el elemento de video.
        </video>
      </div>
      <div class="proyecto-info">
        <div class="proyecto-badge turquesa">VISTARA</div>
        <p>Espacios diseñados para recargar energía, compartir y construir comunidad. Ambientes versátiles que se adaptan a distintos estilos de vida y necesidades.</p>
      </div>
    </div>

  </section>

  <!-- SERVIBIENES CONSTRUCTORA — BANNER -->
  <section id="constructora">
    <img src="home-nuevo/assets/constructora.png" alt="Servibienes Constructora" class="hero-img-constructora">
  </section>

  <!-- SERVIBIENES CONSTRUCTORA — DETALLE -->
  <section id="constructora-detalle">
    <div class="proyecto-card">
      <div class="proyecto-info">
        <div class="proyecto-badge turquesa">EN SERVIBIENES S.R.L. CONSTRUCTORA</div>
        <ul class="proyecto-lista">
          <li>Diseñamos tu proyecto ideal y lo convertimos en realidad.</li>
          <li>Viviendas modernas</li>
          <li>Edificios seguros</li>
          <li>Locales comerciales funcionales</li>
          <li>Interiores</li>
          <li>Avalúos y presupuestos</li>
          <li>Supervisión de obra</li>
        </ul>
        <p>SERVIBIENES S.R.L. — Construimos confianza, edificamos futuro.</p>
      </div>
      <div class="proyecto-media">
        <img src="home-nuevo/assets/disenoProyecto.png" alt="Diseñamos tu proyecto ideal">
      </div>
    </div>
  </section>

  <script>
    const WA_NUMBER = "<?= $whatsapp ?>";
  </script>
  <script src="home-nuevo/js/main.js"></script>

</body>

</html>