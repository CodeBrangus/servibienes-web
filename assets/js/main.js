// ========== SISTEMA DE GALERÍA ==========
class GallerySlider {
    constructor(containerId) {
        // Compatibilidad:
        // - En algunas secciones el ID se puso en el "track" y no en el contenedor.
        // - La seccion de proyectos usa .projects-track y botones .projects-prev/.projects-next.
        const el = document.getElementById(containerId);
        if (!el) return;

        // Si el ID pertenece al track, tomamos su parent como contenedor.
        const looksLikeTrack = el.classList.contains('slider-track') || el.classList.contains('projects-track');
        this.container = looksLikeTrack ? el.parentElement : el;

        this.track = looksLikeTrack ? el : (this.container?.querySelector('.slider-track') || this.container?.querySelector('.projects-track'));

        this.prevBtn = this.container?.querySelector('.slider-prev') || this.container?.querySelector('.projects-prev');
        this.nextBtn = this.container?.querySelector('.slider-next') || this.container?.querySelector('.projects-next');
        this.index = 0;
        
        if (this.track && this.prevBtn && this.nextBtn) {
            this.init();
        }
    }
    
    init() {
        this.prevBtn.addEventListener('click', () => this.prev());
        this.nextBtn.addEventListener('click', () => this.next());

        // --- Auto-slide robusto (desktop + móviles) ---
        // En algunos móviles el ancho inicial es 0 hasta que cargan imágenes.
        // Recalcular cuando carguen y al redimensionar.
        const recalc = () => this.update();
        window.addEventListener('resize', recalc);

        // Esperar a que carguen imágenes dentro del track
        const imgs = this.track.querySelectorAll('img');
        imgs.forEach(img => {
            if (!img.complete) {
                img.addEventListener('load', recalc, { once: true });
                img.addEventListener('error', recalc, { once: true });
            }
        });

        // Detectar si es touch (móvil/tablet)
        this.isTouch = (('ontouchstart' in window) || (navigator.maxTouchPoints > 0));

        // Auto-slide cada 4.5s
        const startAuto = () => {
            // Evitar múltiples intervals si el slider se re-inicializa.
            this.stopAuto();
            this.autoSlide = setInterval(() => {
                this.next();
            }, 4500);
        };

        // Marcar como inicializado (si el contenedor existe)
        if (this.container) this.container.dataset.carouselInit = '1';

        startAuto();
    }

    // Detener auto-slide (evita errores y dobles intervalos)
    stopAuto() {
        if (this.autoSlide) {
            clearInterval(this.autoSlide);
            this.autoSlide = null;
        }
    }

    getItemWidth() {
        const first = this.track?.children?.[0];
        const gap = 10;
        const w = first ? (first.getBoundingClientRect().width || 250) : 250;
        return w + gap;
    }
    
    prev() {
        if (!this.track.children.length) return;
        
        this.index = Math.max(this.index - 1, 0);
        this.update();
    }
    
    next() {
        if (!this.track.children.length) return;
        
        this.index = (this.index + 1) % this.track.children.length;
        this.update();
    }

    update() {
        // Calcular ancho real (mejor que un número fijo)
        if (!this.track || !this.track.children.length) return;
        const itemWidth = this.getItemWidth();

        // En touch (móvil/tablet) usamos scroll nativo para que el auto-slide funcione.
        if (this.isTouch) {
            const left = this.index * itemWidth;

            const trackScrollable = this.track.scrollWidth > this.track.clientWidth + 2;
            const containerScrollable = this.container && (this.container.scrollWidth > this.container.clientWidth + 2);

            // Preferir el elemento que realmente hace scroll (track o container)
            const scrollEl = trackScrollable ? this.track : (containerScrollable ? this.container : null);

            // Si hay scroll nativo disponible, úsalo en móviles.
            if (scrollEl) {
                try { scrollEl.scrollTo({ left, behavior: 'smooth' }); }
                catch (e) { scrollEl.scrollLeft = left; }
                return;
            }
            // Si NO hay scroll (por CSS overflow hidden), caemos al modo transform (no se rompe en móvil).
        }

        this.track.style.transform = `translateX(-${this.index * itemWidth}px)`;
    }
}

// ========== VIDEO LAZY LOAD (mejora compatibilidad móvil + evita que se rompan al cargar muchos) ==========
function initLazyVideos() {
    const videoHolders = document.querySelectorAll('[data-video-src]');
    if (!videoHolders.length) return;

    const mount = (holder) => {
        if (holder.dataset.mounted === '1') return;
        const src = holder.getAttribute('data-video-src');
        if (!src) return;
        const video = document.createElement('video');
        video.controls = true;
        video.preload = 'metadata';
        video.playsInline = true;
        video.setAttribute('playsinline', '');
        video.setAttribute('webkit-playsinline', '');
        video.style.width = '100%';
        video.style.height = '100%';
        video.style.objectFit = 'cover';
        const source = document.createElement('source');
        source.src = src;
        source.type = 'video/mp4';
        video.appendChild(source);
        holder.appendChild(video);
        holder.dataset.mounted = '1';
    };

    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    mount(e.target);
                    io.unobserve(e.target);
                }
            });
        }, { rootMargin: '200px' });

        videoHolders.forEach(h => io.observe(h));
    } else {
        // fallback
        videoHolders.forEach(mount);
    }
}

// ========== FORMULARIO CLIENTE ==========
class ClientForm {
    constructor() {
        this.form = document.getElementById('clientForm');
        this.imageInput = document.getElementById('clientImages');
        this.videoInput = document.getElementById('clientVideos');
        this.imagePreview = document.getElementById('imagePreview');
        this.videoPreview = document.getElementById('videoPreview');
        this.maxImages = 10;
        this.maxVideos = 999;// sin limite practico (depende del hosting)
        
        this.init();
    }
    
    init() {
        if (this.imageInput) {
            this.imageInput.addEventListener('change', (e) => this.handleImageUpload(e));
        }
        
        if (this.videoInput) {
            this.videoInput.addEventListener('change', (e) => this.handleVideoUpload(e));
        }
        
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.validateForm(e));
        }
    }
    
    handleImageUpload(event) {
        const files = Array.from(event.target.files);
        
        if (files.length > this.maxImages) {
            alert(`Máximo ${this.maxImages} imágenes permitidas`);
            event.target.value = '';
            return;
        }
        
        files.forEach(file => {
            if (file.size > 5 * 1024 * 1024) {
                alert(`La imagen ${file.name} es muy grande (máximo 5MB)`);
                return;
            }
            
            const reader = new FileReader();
            reader.onload = (e) => {
                this.addImagePreview(e.target.result, file.name);
            };
            reader.readAsDataURL(file);
        });
    }
    
    handleVideoUpload(event) {
        const files = Array.from(event.target.files);
        
        if (files.length > this.maxVideos) {
            alert(`Máximo ${this.maxVideos} videos permitidos`);
            event.target.value = '';
            return;
        }
        
        files.forEach(file => {
            if (file.size > 500 * 1024 * 1024) {
                alert(`El video ${file.name} es muy grande (máximo 50MB)`);
                return;
            }
            
            const reader = new FileReader();
            reader.onload = (e) => {
                this.addVideoPreview(e.target.result, file.name);
            };
            reader.readAsDataURL(file);
        });
    }
    
    addImagePreview(src, filename) {
        const item = document.createElement('div');
        item.className = 'preview-item';
        item.innerHTML = `
            <img src="${src}" alt="${filename}">
            <button type="button" class="remove-preview" onclick="this.parentElement.remove()">×</button>
        `;
        this.imagePreview.appendChild(item);
    }
    
    addVideoPreview(src, filename) {
        const item = document.createElement('div');
        item.className = 'preview-item';
        item.innerHTML = `
            <video src="${src}" controls></video>
            <button type="button" class="remove-preview" onclick="this.parentElement.remove()">×</button>
        `;
        this.videoPreview.appendChild(item);
    }
    
    validateForm(event) {
        const requiredFields = this.form.querySelectorAll('[required]');
        let isValid = true;
        
        for (let field of requiredFields) {
            if (!field.value.trim()) {
                field.style.borderColor = '#e74c3c';
                isValid = false;
                
                // Scroll al primer error
                if (isValid === false) {
                    field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    field.focus();
                    break;
                }
            } else {
                field.style.borderColor = '';
            }
        }
        
        if (!isValid) {
            event.preventDefault();
            alert('Por favor complete todos los campos obligatorios');
        }
        
        return isValid;
    }
}

// ========== SISTEMA DE CATEGORÍAS ==========
class CategoryManager {
    constructor() {
        this.selectors = document.querySelectorAll('.category-selector');
        this.contents = document.querySelectorAll('.category-content');
        
        this.init();
    }
    
    init() {
        this.selectors.forEach(selector => {
            selector.addEventListener('click', () => {
                const category = selector.getAttribute('data-category') || 
                               selector.textContent.toLowerCase().replace('propiedades en ', '');
                this.showCategory(category);
            });
        });
    }
    
    showCategory(category) {
        // Desactivar todos
        this.selectors.forEach(s => s.classList.remove('active'));
        this.contents.forEach(c => c.classList.remove('active'));
        
        // Activar seleccionado (usa data-category para evitar problemas de tildes como "Anticrético")
        this.selectors.forEach(s => {
            if ((s.getAttribute('data-category') || '').toLowerCase() === String(category).toLowerCase()) {
                s.classList.add('active');
            }
        });
        
        const content = document.getElementById(`${category}-content`);
        if (content) {
            content.classList.add('active');
        }
    }
}

// ========== PROVINCIAS POR DEPARTAMENTO ==========
// Se usa en index.php y en admin/properties.php (onchange="updateProvincias(...)")
window.provinciasData = window.provinciasData || {
    santacruz: [
        "Andrés Ibáñez",
        "Warnes",
        "Ichilo",
        "Sara",
        "Obispo Santistevan",
        "Chiquitos",
        "Cordillera",
        "Vallegrande",
        "Florida",
        "Velasco",
        "Ángel Sandóval",
        "Ñuflo de Chávez",
        "Guarayos",
        "Germán Busch",
        "Caballero"
    ],
    lapaz: ["Murillo", "Omasuyos", "Pacajes", "Camacho", "Muñecas", "Larecaja"],
    cochabamba: ["Cercado", "Campero", "Ayopaya", "Esteban Arce", "Arani", "Arque"],
    oruro: ["Cercado", "Carangas", "Sajama", "Litoral", "Poopó", "Pantaleón Dalence"],
    potosi: ["Tomas Frías", "Rafael Bustillo", "Cornelio Saavedra", "Chayanta", "Charcas"],
    tarija: ["Cercado", "Arce", "Gran Chaco", "Aviles", "Mendez", "Burdet O'Connor"],
    chuquisaca: ["Oropeza", "Azurduy", "Zudáñez", "Tomina", "Hernando Siles", "Yamparáez"],
    beni: ["Cercado", "Vaca Díez", "Yacuma", "Moxos", "Marbán", "Mamoré"],
    pando: ["Nicolás Suárez", "Manuripi", "Madre de Dios", "Abuná", "Federico Román"]
};

window.updateProvincias = function updateProvincias(departamento, selectId) {
    const select = document.getElementById(selectId);
    if (!select) return;
    select.innerHTML = '<option value="">Seleccione provincia</option>';

    const key = String(departamento || '').toLowerCase();
    const list = window.provinciasData[key];
    if (!list || !Array.isArray(list)) return;

    list.forEach((provincia) => {
        const option = document.createElement('option');
        option.value = String(provincia).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, '-');
        option.textContent = provincia;
        select.appendChild(option);
    });
};

// ========== SISTEMA DE TRADUCCIÓN ==========
class Translator {
    constructor() {
        this.language = localStorage.getItem('language') || 'es';
        this.translations = {
            es: {
                'welcome_message': 'Bienvenido a Servibienes',
                'hero_title': 'Buscamos los mejores lugares para cumplir tus sueños',
                'upload_label': 'Subir archivos',
                'submit_button': 'Enviar formulario'
            },
            en: {
                'welcome_message': 'Welcome to Servibienes',
                'hero_title': 'We find the best places to fulfill your dreams',
                'upload_label': 'Upload files',
                'submit_button': 'Submit form'
            }
        };
        
        this.init();
    }
    
    init() {
        const languageSelect = document.getElementById('languageSelect');
        if (languageSelect) {
            languageSelect.value = this.language;
            languageSelect.addEventListener('change', (e) => this.changeLanguage(e.target.value));
        }
        
        this.updateTranslations();
    }
    
    changeLanguage(lang) {
        this.language = lang;
        localStorage.setItem('language', lang);
        this.updateTranslations();
    }
    
    updateTranslations() {
        document.querySelectorAll('[data-translate]').forEach(element => {
            const key = element.getAttribute('data-translate');
            if (this.translations[this.language] && this.translations[this.language][key]) {
                element.textContent = this.translations[this.language][key];
            }
        });
    }
}


// ========== ORGANIZAR VIDEOS POR ORIENTACIÓN (PRO vs CELULAR) ==========
function sortVideosByOrientation(){
    const pro = document.getElementById('videosGridPro') || document.getElementById('videosGrid');
    const mobile = document.getElementById('videosGridMobile');
    if(!pro || !mobile) return;

    const cards = pro.querySelectorAll('.video-card');
    cards.forEach(card => {
        const v = card.querySelector('video');
        if(!v) return;

        const apply = () => {
            const vw = v.videoWidth || 0;
            const vh = v.videoHeight || 0;
            if(!vw || !vh) return;

            const isPortrait = vh > vw;
            card.classList.toggle('portrait', isPortrait);
            card.classList.toggle('landscape', !isPortrait);

            if(isPortrait){
                mobile.appendChild(card);
            }
        };

        if(v.readyState >= 1){
            apply();
        }else{
            v.addEventListener('loadedmetadata', apply, { once:true });
        }
    });
}

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar galerías (si no fueron inicializadas por otro script)
    const mg = document.getElementById('mainGallery');
    if (mg && mg.dataset.carouselInit !== '1') new GallerySlider('mainGallery');

    const pg = document.getElementById('projectsGallery');
    if (pg && pg.dataset.carouselInit !== '1') new GallerySlider('projectsGallery');
// Inicializar formulario
    new ClientForm();
    
    // Inicializar categorías
    const categoryManager = new CategoryManager();

    /* Compatibilidad: algunos elementos usan onclick=showCategory() en HTML */
    window.showCategory = (cat) => categoryManager.showCategory(String(cat || "").trim());

    /* Búsqueda simple en el catálogo (título, ubicación, tipo) */
    window.searchProperties = () => {
        const input = document.getElementById("propertySearch");
        const q = (input ? input.value : "").toLowerCase().trim();
        document.querySelectorAll(".category-properties .category-property").forEach(card => {
            const text = (card.innerText || "").toLowerCase();
            card.style.display = (!q || text.includes(q)) ? "" : "none";
        });
    };

    
    // Inicializar traductor
    new Translator();
    
    // Welcome overlay
    const welcomeOverlay = document.getElementById('welcomeOverlay');
    const enterButton = document.getElementById('enterButton');

    const hideWelcomeOverlay = () => {
        if (!welcomeOverlay) return;
        // Evita bloquear clics aunque quede transparente
        welcomeOverlay.style.pointerEvents = 'none';
        welcomeOverlay.style.opacity = '0';
        setTimeout(() => {
            // fallback final
            welcomeOverlay.style.display = 'none';
        }, 600);
    };

    if (welcomeOverlay) {
        // Ocultar después de 2.5 segundos
        setTimeout(hideWelcomeOverlay, 2500);
        if (enterButton) {
            enterButton.addEventListener('click', (e) => {
                e.preventDefault();
                hideWelcomeOverlay();
            });
        }
    }
    // Navegación del encabezado a secciones
    // Nota: muchos hostings / navegadores móviles pueden bloquear/romper JS si hay
    // algún script externo que falla. Por eso hacemos una navegación robusta:
    // - Siempre actualiza el hash (fallback nativo)
    // - Si el destino existe, hacemos scroll suave con offset del header
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (!targetId || targetId === '#') return;

            const target = document.querySelector(targetId);

            // Si estamos en otra página (ej: proyecto.php) y el ancla no existe,
            // redirigimos a la portada con el hash para que funcione el menú.
            if (!target) {
                const current = window.location.pathname.split('/').pop() || 'index.php';
                if (current !== 'index.php') {
                    window.location.href = 'index.php' + targetId;
                    e.preventDefault();
                }
                return;
            }

            e.preventDefault();

            // Ajuste por header fijo (si existe)
            const header = document.querySelector('.hero-topbar') || document.querySelector('header');
            const offset = header ? header.offsetHeight + 12 : 12;
            const top = target.getBoundingClientRect().top + window.pageYOffset - offset;

            // Scroll suave
            window.scrollTo({ top, behavior: 'smooth' });

            // Asegurar navegación nativa también (por si el usuario refresca)
            // y para que el browser enfoque correctamente la sección.
            setTimeout(() => {
                try { window.location.hash = targetId; } catch(_) {}
            }, 50);

            // Cerrar dropdown de Proyectos si está abierto
            const dd = this.closest('.hero-dropmenu');
            if (dd) {
                const wrap = dd.closest('.hero-dropdown');
                if (wrap) {
                    wrap.classList.remove('open');
                    const b = wrap.querySelector('.hero-dropbtn');
                    if (b) b.setAttribute('aria-expanded','false');
                }
            }
        });
    });

    
    // ===== Menú superior (móvil): abrir/cerrar =====
    const topbar = document.querySelector('.hero-topbar');
    const burger = document.getElementById('heroBurger');
    const nav = document.getElementById('heroNav');
    if (topbar && burger && nav) {
        const closeNav = () => topbar.classList.remove('nav-open');
        burger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            topbar.classList.toggle('nav-open');
        });
        // Cerrar menú al tocar un link
        nav.querySelectorAll('a').forEach(a => a.addEventListener('click', closeNav));

        // Navegación robusta en móvil (muchos navegadores disparan "click" raro tras scroll)
        // - Cierra el menú
        // - Cierra dropdown
        // - Luego hace scroll al destino con el offset correcto
        const goToHash = (hash) => {
            if (!hash || hash === '#') return;
            const target = document.querySelector(hash);
            if (!target) return;
            // Esperar a que el menú se cierre para calcular bien el offset
            setTimeout(() => {
                const header = document.querySelector('.hero-topbar') || document.querySelector('header');
                const offset = header ? header.offsetHeight + 12 : 12;
                const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({ top, behavior: 'smooth' });
                try { window.location.hash = hash; } catch(_) {}
            }, 180);
        };

        const onNavTap = (e) => {
            const a = e.target.closest('a[href^="#"]');
            if (!a) return;
            const hash = a.getAttribute('href');
            if (!hash || hash === '#') return;
            // Si existe destino en esta página, gestionamos nosotros
            const target = document.querySelector(hash);
            if (target) {
                e.preventDefault();
                e.stopPropagation();
                closeNav();
                // cerrar dropdown si venimos del menú de proyectos
                const wrap = a.closest('.hero-dropdown');
                if (wrap) {
                    wrap.classList.remove('open');
                    const b = wrap.querySelector('.hero-dropbtn');
                    if (b) b.setAttribute('aria-expanded','false');
                }
                goToHash(hash);
            }
        };

        // touchend primero (mejor en celulares), y click como respaldo
        nav.addEventListener('touchend', onNavTap, { passive: false });
        nav.addEventListener('click', onNavTap);
        document.addEventListener('click', (e) => {
            if (!topbar.contains(e.target)) closeNav();
        });
    }

// ---------------------------------------------
    // Validaciones / helpers (dentro de DOMContentLoaded)
    // ---------------------------------------------
    
    // Validar teléfono
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+]/g, '');
        });
    });
    
    // Formatear precio
    const priceInputs = document.querySelectorAll('input[name="price"]');
    priceInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value) {
                const value = parseFloat(this.value).toFixed(2);
                this.value = value;
            }
        });
    });

    // ========== LAZY LOAD DE VIDEOS (mejora móviles y evita que a partir del 5to algunos no reproduzcan) ==========
    // Cargamos el src solo cuando el video entra en pantalla.
    const lazyVideos = document.querySelectorAll('video.video-player');
    if (lazyVideos.length) {
        const loadVideo = (video) => {
            const source = video.querySelector('source[data-src]');
            if (!source) return;
            if (source.getAttribute('src')) return; // ya cargado
            source.setAttribute('src', source.getAttribute('data-src'));
            video.load();
        };

		// Si el usuario intenta reproducir antes de que IntersectionObserver dispare,
		// cargamos el src al primer toque/click para que SIEMPRE pueda reproducir.
		const armUserGestureLoad = (video) => {
			const handler = () => {
				loadVideo(video);
				// Intentar reproducir (solo funciona si fue un gesto del usuario)
				try {
					const p = video.play();
					if (p && typeof p.catch === 'function') p.catch(() => {});
				} catch (_) {}
				video.removeEventListener('pointerdown', handler);
				video.removeEventListener('touchstart', handler);
				video.removeEventListener('click', handler);
			};
			video.addEventListener('pointerdown', handler, { passive: true });
			video.addEventListener('touchstart', handler, { passive: true });
			video.addEventListener('click', handler);
		};
		lazyVideos.forEach(v => armUserGestureLoad(v));

        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        loadVideo(entry.target);
                        io.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '200px 0px' });

            lazyVideos.forEach(v => io.observe(v));
        } else {
            // Fallback
            lazyVideos.forEach(v => loadVideo(v));
        }
    }
});

// ===== Mini carrusel automático en tarjetas de proyectos =====
document.addEventListener('DOMContentLoaded', () => {
  const tracks = document.querySelectorAll('.thumb-track.auto-slide');
  tracks.forEach(track => {
    // Si hay solo 1 imagen, nada
    const imgs = track.querySelectorAll('img');
    if (imgs.length < 2) return;

    // Duplicar contenido para que el loop sea suave
    imgs.forEach(img => track.appendChild(img.cloneNode(true)));

    let offset = 0;
    const step = () => {
      const first = track.querySelector('img');
      if (!first) return;
      const w = first.getBoundingClientRect().width + 10; // gap
      offset += w;
      track.style.transform = `translateX(${-offset}px)`;
      // Reinicio cuando pasamos la mitad (porque duplicamos)
      const half = track.scrollWidth / 2;
      if (offset >= half) {
        offset = 0;
        track.style.transform = 'translateX(0px)';
      }
    };
    setInterval(step, 2800);
  });
});



// ========== HERO DROPDOWN (MENÚ PROYECTOS) ==========
// Nota: lo inicializamos en DOMContentLoaded para que siempre encuentre los nodos
// (en algunos hostings/minificadores el script puede cargarse antes del HTML).
document.addEventListener('DOMContentLoaded', () => {
  const dropdown = document.querySelector('.hero-dropdown');
  const btn = document.querySelector('.hero-dropbtn');
  const menu = document.querySelector('.hero-dropmenu');
  if (!dropdown || !btn || !menu) return;

  const isTouch = ('ontouchstart' in window) || (navigator.maxTouchPoints && navigator.maxTouchPoints > 0);

  const close = () => {
    dropdown.classList.remove('open');
    btn.setAttribute('aria-expanded', 'false');
  };
  const open = () => {
    dropdown.classList.add('open');
    btn.setAttribute('aria-expanded', 'true');
  };
  const toggle = () => (dropdown.classList.contains('open') ? close() : open());

  // Abrir/cerrar con click (y tap)
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    toggle();
  });

  // Evitar que toques dentro del menú lo cierren (especialmente al hacer scroll en móvil)
  ['click','pointerdown','touchstart'].forEach(evt => {
    menu.addEventListener(evt, (e) => e.stopPropagation(), { passive: true });
  });

  // En desktop: cerrar al hacer click fuera.
  // En móviles (touch): NO cerramos por click fuera para que el usuario pueda deslizar/seleccionar sin que se cierre solo.
  if (!isTouch) {
    document.addEventListener('click', (e) => {
      if (!dropdown.contains(e.target)) close();
    });
  }

  // Cerrar con ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') close();
  });

  // Al elegir una opción del menú:
  // En móviles algunos navegadores NO disparan el "click" (solo touchend),
  // o el click no llega al nav por stopPropagation. Por eso navegamos aquí
  // de forma robusta y con fallback.
  const topbar = document.querySelector('.hero-topbar');
  let lastTapAt = 0;

  const safeScrollTo = (target) => {
    if (!target) return;
    // Preferimos scrollIntoView porque respeta scroll-margin-top (ya definido en CSS)
    // y es más compatible; si no, hacemos scroll manual con offset.
    try {
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      return;
    } catch (_) {
      try { target.scrollIntoView(); } catch (_) {}
    }

    // Fallback manual
    try {
      const header = document.querySelector('.hero-topbar') || document.querySelector('header');
      const offset = header ? header.offsetHeight + 12 : 12;
      const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
      try { window.scrollTo({ top, behavior: 'smooth' }); }
      catch (e) { window.scrollTo(0, top); }
    } catch (_) {}
  };

  const navigateHash = (hash) => {
    if (!hash || hash === '#') return;
    const target = document.querySelector(hash);

    // Si no existe en esta página, redirigir a la portada
    if (!target) {
      const current = window.location.pathname.split('/').pop() || 'index.php';
      if (current !== 'index.php') {
        window.location.href = 'index.php' + hash;
      } else {
        // fallback: al menos actualizar el hash
        try { window.location.hash = hash; } catch (_) {}
      }
      return;
    }

    // Cerrar menú móvil y dropdown antes de hacer scroll
    close();
    if (topbar) topbar.classList.remove('nav-open');

    // Esperar a que cierre el menú para calcular bien el offset
    setTimeout(() => {
      safeScrollTo(target);
      try { window.location.hash = hash; } catch (_) {}
    }, 180);
  };

  // Evitar doble ejecución (touchend + click)
  const markTap = () => { lastTapAt = Date.now(); };
  const recentlyTapped = () => (Date.now() - lastTapAt) < 650;

  menu.querySelectorAll('a[href^="#"]').forEach(a => {
    // Touchend primero (robusto en móvil)
    a.addEventListener('touchend', (e) => {
      const href = a.getAttribute('href');
      if (!href || href === '#') return;
      e.preventDefault();
      // Evitar que otros handlers (nav/global anchors) interfieran
      try { e.stopImmediatePropagation(); } catch (_) { e.stopPropagation(); }
      markTap();
      navigateHash(href);
    }, { passive: false });

    // Click como respaldo (desktop / algunos Android)
    a.addEventListener('click', (e) => {
      const href = a.getAttribute('href');
      if (!href || href === '#') return;
      // Si ya se manejó por touchend, ignorar
      if (recentlyTapped()) {
        e.preventDefault();
        return;
      }
      e.preventDefault();
      try { e.stopImmediatePropagation(); } catch (_) { e.stopPropagation(); }
      navigateHash(href);
    });
  });

  // Ordenar videos (profesional vs celular)
  sortVideosByOrientation();
});
