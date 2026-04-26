<?php
require_once 'config.php';

require_once 'functions.php';

// Listar archivos del proyecto desde carpeta (por nombre en el archivo).
function projectFileList(string $project, string $folderRel, array $exts): array {
  $folderAbs = __DIR__ . '/' . trim($folderRel,'/');
  if (!is_dir($folderAbs)) return [];
  $norm = function_exists('mb_strtoupper') ? mb_strtoupper($project, 'UTF-8') : strtoupper($project);
  $norm = preg_replace('/\s+/', ' ', $norm);
  $files = [];
  foreach (scandir($folderAbs) as $f) {
    if ($f === '.' || $f === '..') continue;
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    if (!in_array($ext, $exts, true)) continue;
    $fNorm = function_exists('mb_strtoupper') ? mb_strtoupper($f, 'UTF-8') : strtoupper($f);
    if (strpos($fNorm, $norm) === false) continue;
    $files[] = rtrim($folderRel,'/') . '/' . $f;
  }
  sort($files, SORT_NATURAL | SORT_FLAG_CASE);
  return $files;
}

// Normaliza rutas guardadas en BD: si viene solo el nombre, lo convierte a carpeta estándar.
function normalizeUploadPath(?string $path, string $defaultFolder): ?string {
  if (!$path) return null;
  $path = trim($path);
  if ($path === '') return null;
  if (strpos($path, '://') !== false) return $path;
  if (strpos($path, '/') === false) return rtrim($defaultFolder,'/') . '/' . $path;
  return $path;
}

$project = sanitize($_GET['proyecto'] ?? $_GET['nombre'] ?? '');
if ($project === '') {
  header("Location: index.php");
  exit;
}

// Imágenes del proyecto (coincidencia en título o descripción)
// IMPORTANTE: la tabla `projects` en tu base de datos tiene columnas: id, image_url, title, description...
// No depende de una columna `project_name` (que puede no existir y causar error fatal).
$imgs = dbSelect(
  'projects',
  'id,image_url,title,description',
  'title LIKE ? OR description LIKE ?',
  ['%'.$project.'%','%'.$project.'%'],
  'display_order ASC, created_at DESC',
  '200'
);

// Videos del proyecto
// Normalizar rutas de imágenes desde BD
$imgs = array_map(function($row){
  $row['image_url'] = normalizeUploadPath($row['image_url'] ?? null, 'assets/uploads/projects');
  return $row;
}, $imgs);


// Quitar filas sin image_url (evita <img src=""> y permite fallback a carpeta)
$imgs = array_values(array_filter($imgs, function($r){ return !empty($r['image_url']); }));

// Si no hay imágenes en BD, buscar por nombre en carpeta de uploads
if (empty($imgs)) {
  $paths = projectFileList($project, 'assets/uploads/projects', ['jpg','jpeg','png','webp','gif']);
  foreach ($paths as $p) {
    $imgs[] = ['id'=>0,'image_url'=>$p,'title'=>$project,'description'=>''];
  }
}

 
$vids = dbSelect('videos', 'id,video_url,title,description', 'title LIKE ?', ['%'.$project.'%'], 'display_order ASC, created_at DESC', '200');

$vids = array_map(function($row){
  $row['video_url'] = normalizeUploadPath($row['video_url'] ?? null, 'assets/uploads/videos');
  return $row;
}, $vids);

if (empty($vids)) {
  $vpaths = projectFileList($project, 'assets/uploads/videos', ['mp4','webm','ogg']);
  foreach ($vpaths as $p) {
    $vids[] = ['id'=>0,'video_url'=>$p,'title'=>$project,'description'=>''];
  }
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($project); ?> - Proyectos</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .proj-hero{margin:18px 0;display:flex;align-items:center;justify-content:space-between;gap:12px}
    .proj-carousel{position:relative;border-radius:18px;overflow:hidden;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04)}
    .proj-carousel-track{display:flex;transition:transform .5s ease}
    .proj-slide{min-width:100%}
    .proj-slide img{width:100%;height:min(38vh,340px);object-fit:contain;background:rgba(0,0,0,.25);display:block}
    .proj-nav{position:absolute;inset:0;display:flex;align-items:center;justify-content:space-between;padding:0 10px;pointer-events:none}
    .proj-btn{pointer-events:auto;width:44px;height:44px;border-radius:999px;border:none;background:rgba(0,0,0,.35);color:#fff;font-size:18px;cursor:pointer}
    .proj-dots{display:flex;gap:8px;justify-content:center;margin-top:12px;flex-wrap:wrap}
    .proj-dot{width:10px;height:10px;border-radius:999px;background:rgba(255,255,255,.25);border:none;cursor:pointer}
    .proj-dot.active{background:rgba(255,255,255,.9)}
    .proj-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-top:16px}
    .video-card video{width:100%;aspect-ratio:16/9;object-fit:contain;background:rgba(0,0,0,.25);background:#000;border-radius:14px;max-height:320px}
      @media (max-width:560px){
      .proj-slide img{height:min(32vh,260px);}
      .video-card video{max-height:220px;}
    }
  
    .lightbox{position:fixed;inset:0;background:rgba(0,0,0,.78);display:none;align-items:center;justify-content:center;z-index:9999;padding:18px;}
    .lightbox.open{display:flex;}
    .lightbox-inner{max-width:980px;width:100%;background:rgba(11,35,64,.92);border:1px solid rgba(255,255,255,.12);border-radius:16px;overflow:hidden;}
    .lightbox-img{width:100%;max-height:70vh;object-fit:contain;background:#000;display:block;}
    .lightbox-bar{display:flex;justify-content:space-between;align-items:center;padding:10px 12px;gap:10px;}
    .lightbox-close{background:rgba(255,255,255,.12);border:none;color:#fff;border-radius:10px;padding:10px 12px;cursor:pointer;}
    .lightbox-nav{display:flex;gap:8px;}
    .lightbox-nav button{background:rgba(255,255,255,.12);border:none;color:#fff;border-radius:10px;padding:10px 12px;cursor:pointer;}
    
  </style>
</head>
<body>
  <div class="container">
    <div class="proj-hero">
      <h1 style="margin:0"><?php echo htmlspecialchars('PROYECTO: '.$project); ?></h1>
      <a class="btn" href="index.php#proyectos">Volver</a>
    </div>

    <section>
      <h2 style="margin:0 0 10px 0">Imágenes</h2>

      <?php if (!empty($imgs)): ?>
        <div class="proj-carousel" id="projCarousel">
          <div class="proj-carousel-track" id="projTrack">
            <?php foreach($imgs as $im): ?>
              <div class="proj-slide">
                <img src="<?php echo hurl($im['image_url']); ?>" alt="<?php echo htmlspecialchars($im['title'] ?: $project); ?>" loading="lazy">
              </div>
            <?php endforeach; ?>
          </div>
          <div class="proj-nav">
            <button class="proj-btn" type="button" id="projPrev">‹</button>
            <button class="proj-btn" type="button" id="projNext">›</button>
          </div>
        </div>
        <div class="proj-dots" id="projDots"></div>
      <?php else: ?>
        <div class="muted" style="padding:12px">Aún no hay imágenes para este proyecto.</div>
      <?php endif; ?>
    </section>

    <?php if (!empty($vids)): ?>
      <section style="margin-top:26px">
        <h2 style="margin:0 0 10px 0">Videos</h2>
        <div class="proj-grid">
          <?php foreach($vids as $v): ?>
            <div class="card video-card">
              <video controls playsinline preload="metadata">
                <source src="<?php echo hurl($v['video_url']); ?>" type="video/mp4">
              </video>
              <div class="video-title" style="margin-top:10px"><?php echo htmlspecialchars($v['title'] ?: $project); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

  </div>

  <div class="lightbox" id="lightbox" aria-hidden="true">
    <div class="lightbox-inner">
      <img id="lightboxImg" class="lightbox-img" src="" alt="Imagen del proyecto">
      <div class="lightbox-bar">
        <button class="lightbox-close" id="lightboxClose" type="button">Cerrar ✕</button>
        <div class="lightbox-nav">
          <button type="button" id="lightboxPrev">‹</button>
          <button type="button" id="lightboxNext">›</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    (function(){
      const track = document.getElementById('projTrack');
      const carousel = document.getElementById('projCarousel');
      if(!track || !carousel) return;

      const slides = Array.from(track.children);
      const dotsWrap = document.getElementById('projDots');
      let idx = 0;
      let timer = null;
      const lb = document.getElementById('lightbox');
      const lbImg = document.getElementById('lightboxImg');
      const lbClose = document.getElementById('lightboxClose');
      const lbPrev = document.getElementById('lightboxPrev');
      const lbNext = document.getElementById('lightboxNext');

      function renderDots(){
        dotsWrap.innerHTML = '';
        slides.forEach((_, i) => {
          const b = document.createElement('button');
          b.type='button';
          b.className='proj-dot' + (i===idx ? ' active':'');
          b.addEventListener('click', () => { idx=i; update(); restart(); });
          dotsWrap.appendChild(b);
        });
      }

      function update(){
        const w = carousel.clientWidth;
        track.style.transform = 'translateX(' + (-idx*w) + 'px)';
        Array.from(dotsWrap.children).forEach((d,i)=>d.classList.toggle('active', i===idx));
      }

      function next(){ idx = (idx+1) % slides.length; update(); }
      function prev(){ idx = (idx-1+slides.length) % slides.length; update(); }

      
      function stop(){
        if(timer) { clearInterval(timer); timer = null; }
      }

      function openLB(){
        if(!lb || !lbImg) return;
        stop();
        lbImg.src = slides[idx].querySelector('img')?.src || '';
        lb.classList.add('open');
        lb.setAttribute('aria-hidden','false');
      }
      function closeLB(){
        if(!lb) return;
        lb.classList.remove('open');
        lb.setAttribute('aria-hidden','true');
        restart();
      }

      if (lbClose) lbClose.addEventListener('click', closeLB);
      if (lb) lb.addEventListener('click', (e)=>{ if(e.target===lb) closeLB(); });
      if (lbPrev) lbPrev.addEventListener('click', ()=>{ prev(); if(lbImg) lbImg.src = slides[idx].querySelector('img')?.src || ''; });
      if (lbNext) lbNext.addEventListener('click', ()=>{ next(); if(lbImg) lbImg.src = slides[idx].querySelector('img')?.src || ''; });

      slides.forEach((s)=>{ s.addEventListener('click', openLB); });

      function restart(){
        if(timer) clearInterval(timer);
        if(slides.length > 1) timer = setInterval(next, 4500);
      }

      document.getElementById('projNext')?.addEventListener('click', ()=>{ next(); restart(); });
      document.getElementById('projPrev')?.addEventListener('click', ()=>{ prev(); restart(); });

      window.addEventListener('resize', update);

      renderDots();
      update();
      restart();
    })();
  </script>
</body>
</html>
