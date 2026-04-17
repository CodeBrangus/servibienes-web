<?php
require_once '../config.php';
requireAdmin();

// Rutas con compatibilidad (algunos hostings usan /imagenes/...)
function pickFirstExisting($candidates){
  foreach($candidates as $c){
    if (file_exists($c['abs'])) return $c;
  }
  return $candidates[0];
}

$logo = pickFirstExisting([
  ['rel'=>'imagenes/portada/logotipo.png', 'abs'=>BASE_PATH.'/imagenes/portada/logotipo.png'],
  ['rel'=>'imagenes/portada/logo.png',     'abs'=>BASE_PATH.'/imagenes/portada/logo.png'],
  ['rel'=>'assets/images/logo.png',        'abs'=>BASE_PATH.'/assets/images/logo.png'],
]);

$hero = pickFirstExisting([
  ['rel'=>'imagenes/portada/portada.jpg', 'abs'=>BASE_PATH.'/imagenes/portada/portada.jpg'],
  ['rel'=>'imagenes/portada/heroe.jpg',   'abs'=>BASE_PATH.'/imagenes/portada/heroe.jpg'],
  ['rel'=>'assets/images/portada.jpg',    'abs'=>BASE_PATH.'/assets/images/portada.jpg'],
  ['rel'=>'assets/images/hero.jpg',       'abs'=>BASE_PATH.'/assets/images/hero.jpg'],
]);

$rep  = pickFirstExisting([
  ['rel'=>'imagenes/agentes/representante.jpg', 'abs'=>BASE_PATH.'/imagenes/agentes/representante.jpg'],
  ['rel'=>'assets/uploads/agents/representante.jpg', 'abs'=>BASE_PATH.'/assets/uploads/agents/representante.jpg'],
]);

$logoRel = $logo['rel'];
$heroRel = $hero['rel'];
$repRel  = $rep['rel'];

$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ajustes - Panel Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f5f7fa;margin:0;color:#333;}
    .header{background:#0b2340;color:#fff;padding:18px 22px;display:flex;justify-content:space-between;align-items:center;}
    .header a{color:#fff;text-decoration:none;opacity:.9}
    .wrap{max-width:1100px;margin:0 auto;padding:22px;}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:18px;}
    .card{background:#fff;border-radius:14px;box-shadow:0 6px 20px rgba(0,0,0,.06);padding:18px;}
    .card h2{margin:0 0 10px;font-size:18px;color:#0b2340;}
    .preview{display:flex;gap:14px;align-items:center;}
    .preview img{max-width:140px;max-height:140px;border-radius:12px;border:1px solid #eee;background:#fafafa}
    .btn{display:inline-block;background:#0f6fb1;color:#fff;padding:10px 14px;border-radius:10px;cursor:pointer;border:none;text-decoration:none;font-weight:600}
    .muted{color:#666;font-size:13px}
    input[type=file]{display:none}
    .msg{padding:12px 14px;border-radius:10px;margin-bottom:14px}
    .ok{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
    .bad{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
  </style>
</head>
<body>
  <div class="header">
    <div><i class="fas fa-gear"></i> Ajustes</div>
    <div>
      <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Volver</a>
      &nbsp;|&nbsp;
      <a href="logout.php"><i class="fas fa-right-from-bracket"></i> Salir</a>
    </div>
  </div>

  <div class="wrap">
    <?php if ($success): ?>
      <div class="msg ok">Cambios guardados correctamente.</div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="msg bad">No se pudo guardar. Revisa el formato/permiso de archivos.</div>
    <?php endif; ?>

    <div class="grid">
      <div class="card">
        <h2>Logo de la empresa</h2>
        <div class="preview">
          <?php if (file_exists($logoAbs)): ?>
            <img src="../<?php echo $logoRel; ?>?v=<?php echo time(); ?>" alt="Logo">
          <?php else: ?>
            <div class="muted">Aún no hay logo cargado.</div>
          <?php endif; ?>
          <div>
            <p class="muted">Formatos: JPG/PNG/WEBP/GIF. Se guarda como <b>assets/images/logo.png</b>.</p>
            <form action="../process/upload_logo.php" method="post" enctype="multipart/form-data">
              <label class="btn">
                <i class="fas fa-upload"></i> Cambiar logo
                <input type="file" name="logo" accept="image/*" onchange="this.form.submit()">
              </label>
            </form>
          </div>
        </div>
      </div>

      <div class="card">
        <h2>Imagen principal (Inicio)</h2>
        <div class="preview">
          <?php if (file_exists($heroAbs)): ?>
            <img src="../<?php echo $heroRel; ?>?v=<?php echo time(); ?>" alt="Hero">
          <?php else: ?>
            <div class="muted">Aún no hay imagen principal cargada.</div>
          <?php endif; ?>
          <div>
            <p class="muted">Formato: <b>JPG</b>. Se guarda como <b>assets/images/hero.jpg</b> y se muestra como fondo en el inicio.
              La página aplica un contraste y una <b>marca de agua del logo</b> automáticamente.</p>
            <form action="../process/upload_hero.php" method="post" enctype="multipart/form-data">
              <label class="btn">
                <i class="fas fa-image"></i> Cambiar imagen de inicio
                <input type="file" name="hero" accept="image/jpeg" onchange="this.form.submit()">
              </label>
            </form>
          </div>
        </div>
      </div>

      <div class="card">
        <h2>Foto del representante legal</h2>
        <div class="preview">
          <?php if (file_exists($repAbs)): ?>
            <img src="../<?php echo $repRel; ?>?v=<?php echo time(); ?>" alt="Representante">
          <?php else: ?>
            <div class="muted">Aún no hay foto cargada.</div>
          <?php endif; ?>
          <div>
            <p class="muted">Formatos: JPG/PNG/WEBP/GIF. Se guarda como <b>assets/uploads/agents/representante.jpg</b>.</p>
            <form action="../process/upload_representante.php" method="post" enctype="multipart/form-data">
              <label class="btn">
                <i class="fas fa-user"></i> Cambiar foto
                <input type="file" name="photo" accept="image/*" onchange="this.form.submit()">
              </label>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="card" style="margin-top:18px;">
      <h2>Nota sobre rutas en cPanel</h2>
      <p class="muted">Si subiste el ZIP y te quedó una carpeta <b>inmobiliaria/</b> dentro de <b>public_html</b>, entonces el panel y los procesos estarían en <b>/inmobiliaria/admin</b> y <b>/inmobiliaria/process</b>. Lo recomendado es mover el contenido de esa carpeta directamente a <b>public_html</b>.</p>
    </div>
  </div>
</body>
</html>
