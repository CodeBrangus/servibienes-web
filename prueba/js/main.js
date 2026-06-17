// ============================================================
// CARRUSEL — Servibienes
// ============================================================
const carruseles = {
  lideres:  { indice: 0, timer: null },
  asesores: { indice: 0, timer: null },
  calientes: { indice: 0, timer: null },
  videos:    { indice: 0, timer: null }
};

function getSlides(id) {
  return document.querySelectorAll(`#track-${id} .slide`);
}

function actualizarCarrusel(id) {
  const slides  = getSlides(id);
  const total   = slides.length;
  if (total === 0) return;

  const c       = carruseles[id];
  const prev    = (c.indice - 1 + total) % total;
  const next    = (c.indice + 1) % total;

  slides.forEach((s, i) => {
    s.classList.remove('activo');
    // solo muestra 3: anterior, actual, siguiente
    if (i === prev || i === c.indice || i === next) {
      s.style.display = 'block';
    } else {
      s.style.display = 'none';
    }
  });

  slides[c.indice].classList.add('activo');
}

function moverCarrusel(id, direccion) {
  const slides = getSlides(id);
  const total  = slides.length;
  if (total === 0) return;

  carruseles[id].indice = (carruseles[id].indice + direccion + total) % total;
  actualizarCarrusel(id);

  // reinicia el timer al hacer clic manual
  clearInterval(carruseles[id].timer);
  iniciarAuto(id);
}

function iniciarAuto(id) {
  carruseles[id].timer = setInterval(() => {
    moverCarrusel(id, 1);
  }, 4000);
}

document.addEventListener('DOMContentLoaded', () => {
  // con auto-movimiento
  ['lideres', 'asesores', 'calientes'].forEach(id => {
    actualizarCarrusel(id);
    iniciarAuto(id);
  });

  // videos — sin auto, con loop
  actualizarCarruselVideos();

  // lightbox calientes
  const slides = document.querySelectorAll('#track-calientes .slide img');
  lightboxData.calientes.imagenes = Array.from(slides).map(img => img.src);
});

function actualizarCarruselVideos() {
  const slides = document.querySelectorAll('#track-videos .slide');
  const total  = slides.length;
  if (total === 0) return;

  const c    = carruseles['videos'];
  const prev = (c.indice - 1 + total) % total;
  const next = (c.indice + 1) % total;

  // oculta todos primero
  slides.forEach(s => {
    s.style.display = 'none';
    s.style.order   = '0';
  });

  // muestra solo 3 en orden: prev | activo | next
  slides[prev].style.display  = 'block';
  slides[prev].style.order    = '1';

  slides[c.indice].style.display = 'block';
  slides[c.indice].style.order   = '2';

  slides[next].style.display  = 'block';
  slides[next].style.order    = '3';
}

function moverCarruselVideos(dir) {
  const slides = document.querySelectorAll('#track-videos .slide');
  const total  = slides.length;
  if (total === 0) return;
  carruseles['videos'].indice = (carruseles['videos'].indice + dir + total) % total;
  actualizarCarruselVideos();
}

// ============================================================
// LIGHTBOX — Inmuebles Calientes
// ============================================================
const lightboxData = {
  calientes: { imagenes: [], indice: 0 }
};

// recolecta las imágenes del carrusel al cargar

function abrirLightbox(grupo, indice) {
  const data     = lightboxData[grupo];
  data.indice    = indice;
  actualizarLightbox(grupo);
  document.getElementById('lightbox').classList.add('activo');
  document.body.style.overflow = 'hidden';
}

function cerrarLightbox() {
  document.getElementById('lightbox').classList.remove('activo');
  document.body.style.overflow = '';
}

function cerrarLightboxFondo(e) {
  if (e.target === document.getElementById('lightbox')) cerrarLightbox();
}

function navLightbox(dir) {
  const data  = lightboxData.calientes;
  const total = data.imagenes.length;
  data.indice = (data.indice + dir + total) % total;
  actualizarLightbox('calientes');
}

function actualizarLightbox(grupo) {
  const data    = lightboxData[grupo];
  const total   = data.imagenes.length;
  const src     = data.imagenes[data.indice];

  document.getElementById('lightbox-img').src       = src;
  document.getElementById('lightbox-contador').textContent =
    `${data.indice + 1} / ${total}`;
  document.getElementById('lightbox-wa').href =
    `https://wa.me/${WA_NUMBER}?text=Hola, me interesa este inmueble`
}

// cerrar con ESC
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') cerrarLightbox();
  if (e.key === 'ArrowRight') navLightbox(1);
  if (e.key === 'ArrowLeft')  navLightbox(-1);
});

// ============================================================
// LIGHTBOX — Videos Vimeo
// ============================================================
const videosData = [
  { archivo: "CapacitacionMensualWp.mp4", titulo: "Capacitacion mensual", formato: "vertical" },
  { archivo: "AsesoresMasterWp.mp4",      titulo: "Asesores master",      formato: "vertical" },
  { archivo: "CapacitacionWp.mp4",        titulo: "Capacitacion",         formato: "vertical" },
  { archivo: "ComiunityDonEdgarWp.mp4",   titulo: "Comunity don edgar",   formato: "horizontal" },
];

let videoIndiceActual = 0;

function abrirVideoLightbox(indice) {
  videoIndiceActual = indice;
  actualizarVideoLightbox();
  document.getElementById('lightbox-video').classList.add('activo');
  document.body.style.overflow = 'hidden';
}

function cerrarVideoLightbox() {
  // detiene el video al cerrar
  const player = document.getElementById('lightbox-video-player');
  player.pause();
  player.currentTime = 0;
  document.getElementById('lightbox-video').classList.remove('activo');
  document.body.style.overflow = '';
}

function cerrarVideoLightboxFondo(e) {
  if (e.target === document.getElementById('lightbox-video')) cerrarVideoLightbox();
}

function navVideoLightbox(dir) {
  videoIndiceActual = (videoIndiceActual + dir + videosData.length) % videosData.length;
  actualizarVideoLightbox();
}

function actualizarVideoLightbox() {
  const v    = videosData[videoIndiceActual];
  const wrap = document.querySelector('.video-iframe-wrap');

  if (v.formato === 'vertical') {
    wrap.style.width  = '380px';
    wrap.style.height = '680px';
  } else {
    wrap.style.width  = '960px';
    wrap.style.height = '540px';  /* 16:9 */
  }

  const player = document.getElementById('lightbox-video-player');
  player.querySelector('source').src = `assets/videos/${v.archivo}`;
  player.load();
  player.play();

  document.getElementById('lightbox-video-contador').textContent =
    `${videoIndiceActual + 1} / ${videosData.length}`;
  document.getElementById('lightbox-video-titulo').textContent = v.titulo;
}