/**
 * Lightbox liviano, sin dependencias.
 *
 * Uso en el HTML:
 *   <div class="lightbox-gallery">
 *     <img src="thumb1.jpg" data-full="full1.jpg" alt="...">
 *     <img src="thumb2.jpg" data-full="full2.jpg" alt="...">
 *   </div>
 *
 * Si una imagen no tiene data-full, se usa el src tal cual.
 * Cada .lightbox-gallery es un grupo independiente (swipe/flechas navegan dentro del grupo).
 */
(function () {
  const MAX_ZOOM = 4;
  const ZOOM_STEP_TAP = 2.5;
  const SWIPE_NAV_THRESHOLD = 50;   // px horizontales para cambiar de imagen
  const SWIPE_CLOSE_THRESHOLD = 120; // px verticales para cerrar

  let overlay, stage, imgEl, closeBtn, prevBtn, nextBtn, counterEl;
  let group = [];
  let currentIndex = 0;
  let scale = 1;
  let originX = 0, originY = 0; // desplazamiento actual de la imagen (para pan/drag)
  let lastTap = 0;

  function buildDom() {
    overlay = document.createElement('div');
    overlay.className = 'lightbox-overlay';
    overlay.innerHTML = `
      <button class="lightbox-close" aria-label="Cerrar">&times;</button>
      <button class="lightbox-arrow prev" aria-label="Anterior">&#8249;</button>
      <div class="lightbox-stage">
        <img class="lightbox-img" alt="">
      </div>
      <button class="lightbox-arrow next" aria-label="Siguiente">&#8250;</button>
      <div class="lightbox-counter"></div>
    `;
    document.body.appendChild(overlay);

    stage = overlay.querySelector('.lightbox-stage');
    imgEl = overlay.querySelector('.lightbox-img');
    closeBtn = overlay.querySelector('.lightbox-close');
    prevBtn = overlay.querySelector('.lightbox-arrow.prev');
    nextBtn = overlay.querySelector('.lightbox-arrow.next');
    counterEl = overlay.querySelector('.lightbox-counter');

    closeBtn.addEventListener('click', close);
    prevBtn.addEventListener('click', () => go(-1));
    nextBtn.addEventListener('click', () => go(1));

    // clic fuera de la imagen (en el stage) cierra — solo si no está haciendo zoom/drag
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay || e.target === stage) close();
    });

    // desktop: doble clic para zoom, flechas de teclado, Escape
    imgEl.addEventListener('dblclick', (e) => toggleZoom(e.clientX, e.clientY));
    document.addEventListener('keydown', (e) => {
      if (!overlay.classList.contains('is-active')) return;
      if (e.key === 'Escape') close();
      if (e.key === 'ArrowLeft') go(-1);
      if (e.key === 'ArrowRight') go(1);
    });

    attachTouchHandlers();
  }

  function setImage(index) {
    currentIndex = (index + group.length) % group.length;
    const src = group[currentIndex];
    resetZoom();
    imgEl.src = src;
    counterEl.textContent = group.length > 1 ? `${currentIndex + 1} / ${group.length}` : '';
    const showArrows = group.length > 1;
    prevBtn.style.display = showArrows ? 'flex' : 'none';
    nextBtn.style.display = showArrows ? 'flex' : 'none';
  }

  function go(delta) {
    setImage(currentIndex + delta);
  }

  function open(groupImages, startIndex) {
    group = groupImages;
    overlay.classList.add('is-active');
    document.body.style.overflow = 'hidden';
    setImage(startIndex);
  }

  function close() {
    overlay.classList.remove('is-active');
    document.body.style.overflow = '';
  }

  function resetZoom() {
    scale = 1;
    originX = 0;
    originY = 0;
    imgEl.style.transform = 'translate(0px, 0px) scale(1)';
    imgEl.style.opacity = '1';
    imgEl.classList.remove('is-zoomed');
  }

  function applyTransform() {
    imgEl.style.transform = `translate(${originX}px, ${originY}px) scale(${scale})`;
    imgEl.classList.toggle('is-zoomed', scale > 1.05);
  }

  function toggleZoom(clientX, clientY) {
    if (scale > 1) {
      resetZoom();
    } else {
      scale = ZOOM_STEP_TAP;
      applyTransform();
    }
  }

  // ---------- Gestos táctiles: swipe nav, pinch zoom, swipe-down close ----------
  function attachTouchHandlers() {
    let touchStartX = 0, touchStartY = 0;
    let dragStartOriginX = 0, dragStartOriginY = 0;
    let pinchStartDist = 0, pinchStartScale = 1;
    let mode = null; // 'swipe-nav' | 'swipe-close' | 'pinch' | 'pan'

    function dist(t0, t1) {
      const dx = t0.clientX - t1.clientX;
      const dy = t0.clientY - t1.clientY;
      return Math.hypot(dx, dy);
    }

    stage.addEventListener('touchstart', (e) => {
      imgEl.classList.add('is-dragging');

      if (e.touches.length === 2) {
        mode = 'pinch';
        pinchStartDist = dist(e.touches[0], e.touches[1]);
        pinchStartScale = scale;
        return;
      }

      const t = e.touches[0];
      touchStartX = t.clientX;
      touchStartY = t.clientY;
      dragStartOriginX = originX;
      dragStartOriginY = originY;

      // doble tap para zoom
      const now = Date.now();
      if (now - lastTap < 280) {
        toggleZoom(t.clientX, t.clientY);
        mode = null;
      } else {
        mode = scale > 1 ? 'pan' : null; // se decide swipe-nav vs swipe-close en el primer move
      }
      lastTap = now;
    }, { passive: true });

    stage.addEventListener('touchmove', (e) => {
      if (mode === 'pinch' && e.touches.length === 2) {
        const newDist = dist(e.touches[0], e.touches[1]);
        scale = Math.min(MAX_ZOOM, Math.max(1, pinchStartScale * (newDist / pinchStartDist)));
        applyTransform();
        return;
      }

      const t = e.touches[0];
      const dx = t.clientX - touchStartX;
      const dy = t.clientY - touchStartY;

      if (mode === 'pan') {
        originX = dragStartOriginX + dx;
        originY = dragStartOriginY + dy;
        applyTransform();
        return;
      }

      if (mode === null) {
        // decide el gesto según la dirección dominante, una sola vez
        if (Math.abs(dy) > Math.abs(dx) && scale === 1) {
          mode = 'swipe-close';
        } else if (scale === 1) {
          mode = 'swipe-nav';
        }
      }

      if (mode === 'swipe-close') {
        originY = dy;
        const fade = Math.max(0, 1 - Math.abs(dy) / 400);
        imgEl.style.transform = `translateY(${dy}px)`;
        imgEl.style.opacity = String(fade);
      } else if (mode === 'swipe-nav') {
        originX = dx;
        imgEl.style.transform = `translateX(${dx}px)`;
      }
    }, { passive: true });

    stage.addEventListener('touchend', () => {
      imgEl.classList.remove('is-dragging');

      if (mode === 'pinch') {
        if (scale < 1.08) resetZoom();
        mode = null;
        return;
      }

      if (mode === 'swipe-close') {
        if (Math.abs(originY) > SWIPE_CLOSE_THRESHOLD) {
          close();
        } else {
          originY = 0;
          imgEl.style.transition = 'transform 0.2s ease, opacity 0.2s ease';
          imgEl.style.transform = 'translateY(0px)';
          imgEl.style.opacity = '1';
          setTimeout(() => { imgEl.style.transition = ''; }, 200);
        }
      } else if (mode === 'swipe-nav') {
        if (Math.abs(originX) > SWIPE_NAV_THRESHOLD) {
          go(originX < 0 ? 1 : -1);
        } else {
          originX = 0;
          imgEl.style.transition = 'transform 0.2s ease';
          imgEl.style.transform = 'translateX(0px)';
          setTimeout(() => { imgEl.style.transition = ''; }, 200);
        }
      }
      mode = null;
    });
  }

  // ---------- Enganche automático de galerías en la página ----------
  function initGalleries() {
    document.querySelectorAll('.lightbox-gallery').forEach((galleryEl) => {
      const imgs = Array.from(galleryEl.querySelectorAll('img'));
      const fullSrcs = imgs.map((img) => img.dataset.full || img.src);

      imgs.forEach((img, i) => {
        img.style.cursor = 'zoom-in';
        img.addEventListener('click', () => open(fullSrcs, i));
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    buildDom();
    initGalleries();
  });
})();
