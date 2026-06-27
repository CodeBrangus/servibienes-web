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

  slides.forEach(s => {
    s.classList.remove('activo');
    s.style.display = 'none';
    s.style.order   = '0';
  });

  slides[prev].style.display  = 'block';
  slides[prev].style.order    = '1';

  slides[c.indice].style.display = 'block';
  slides[c.indice].style.order   = '2';
  slides[c.indice].classList.add('activo');

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

// ============================================================
// NAVBAR MOBILE — Hamburguesa + WSP
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  const navbar   = document.getElementById('navbar');
  const navLinks = navbar.querySelector('.nav-links');
  const waNumber = typeof WA_NUMBER !== 'undefined' ? WA_NUMBER : '59177657257';

  // Crear contenedor de acciones mobile (WSP + hamburguesa)
  // Se crea siempre en el DOM pero solo es visible en mobile via CSS
  const actions = document.createElement('div');
  actions.className = 'nav-mobile-actions';

  // Botón WhatsApp
  const wspBtn = document.createElement('a');
  wspBtn.href = `https://wa.me/${waNumber}`;
  wspBtn.target = '_blank';
  wspBtn.className = 'nav-wsp-btn';
  wspBtn.setAttribute('aria-label', 'Contactar por WhatsApp');
  wspBtn.innerHTML = `
    <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
      <path d="M16 2C8.268 2 2 8.268 2 16c0 2.492.68 4.83 1.865 6.83L2 30l7.374-1.835A13.94 13.94 0 0016 30c7.732 0 14-6.268 14-14S23.732 2 16 2zm0 25.5a11.44 11.44 0 01-5.82-1.587l-.418-.247-4.373 1.088 1.12-4.254-.272-.436A11.46 11.46 0 014.5 16C4.5 9.649 9.649 4.5 16 4.5S27.5 9.649 27.5 16 22.351 27.5 16 27.5zm6.29-8.61c-.344-.172-2.04-1.006-2.356-1.12-.316-.115-.546-.172-.776.172-.23.344-.89 1.12-1.09 1.35-.2.23-.4.258-.744.086-.344-.172-1.452-.535-2.766-1.707-1.022-.912-1.712-2.037-1.912-2.381-.2-.344-.021-.53.15-.702.155-.155.344-.4.516-.6.172-.2.23-.344.344-.573.115-.23.058-.43-.029-.602-.086-.172-.776-1.87-1.063-2.56-.28-.672-.564-.581-.776-.592l-.66-.011a1.265 1.265 0 00-.918.43c-.315.344-1.204 1.177-1.204 2.87s1.233 3.329 1.405 3.558c.172.23 2.428 3.707 5.882 5.197.822.355 1.463.567 1.963.726.825.263 1.576.226 2.17.137.662-.099 2.04-.834 2.327-1.638.287-.804.287-1.493.2-1.638-.086-.144-.315-.23-.66-.4z"/>
    </svg>`;

  // Botón hamburguesa
  const hamburger = document.createElement('button');
  hamburger.className = 'nav-hamburger';
  hamburger.setAttribute('aria-label', 'Abrir menú');
  hamburger.innerHTML = `<span></span><span></span><span></span>`;

  actions.appendChild(wspBtn);
  actions.appendChild(hamburger);

  // ✅ Insertar ANTES del nav-cta para no desplazarlo
  const navCta = navbar.querySelector('.nav-cta');
  navbar.insertBefore(actions, navCta);

  // Toggle menú
  hamburger.addEventListener('click', () => {
    const abierto = navLinks.classList.toggle('abierto');
    hamburger.classList.toggle('abierto', abierto);
    hamburger.setAttribute('aria-label', abierto ? 'Cerrar menú' : 'Abrir menú');
  });

  // Cerrar al hacer clic en un link
  navLinks.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      navLinks.classList.remove('abierto');
      hamburger.classList.remove('abierto');
    });
  });

  // Cerrar al hacer clic fuera
  document.addEventListener('click', (e) => {
    if (!navbar.contains(e.target)) {
      navLinks.classList.remove('abierto');
      hamburger.classList.remove('abierto');
    }
  });
});