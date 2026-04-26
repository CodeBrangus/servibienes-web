<?php
require_once '../config.php';
requireAdmin();

// --- Banners por categoría (JSON) ---
$bannerFile = __DIR__ . '/../assets/data/category_banners.json';
if (!is_dir(dirname($bannerFile))) { @mkdir(dirname($bannerFile), 0755, true); }
$banners = [];
if (file_exists($bannerFile)) {
  $banners = json_decode(file_get_contents($bannerFile), true);
  if (!is_array($banners)) $banners = [];
}

function saveBanners($file, $data){
  file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}

function cleanCat($cat){
  $cat = strtolower(trim($cat));
  return in_array($cat, ['venta','alquiler','anticretico'], true) ? $cat : '';
}

// Subir banner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_banner') {
  $cat = cleanCat($_POST['category'] ?? '');
  if (!$cat) { die('Categoría inválida'); }

  if (!isset($_FILES['banner']) || $_FILES['banner']['error'] !== UPLOAD_ERR_OK) { die('Error subiendo archivo'); }

  $tmp = $_FILES['banner']['tmp_name'];
  $info = @getimagesize($tmp);
  if ($info === false) { die('El archivo no es una imagen válida'); }

  $ext = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) { die('Formato no permitido'); }

  $destDir = __DIR__ . '/../assets/uploads/offers';
  if (!is_dir($destDir)) { @mkdir($destDir, 0755, true); }

  $fileName = $cat . '_banner_' . date('Ymd_His') . '.' . $ext;
  $destAbs = $destDir . '/' . $fileName;
  if (!move_uploaded_file($tmp, $destAbs)) { die('No se pudo guardar el archivo'); }

  $banners[$cat] = 'assets/uploads/offers/' . $fileName;
  saveBanners($bannerFile, $banners);
  header('Location: offers.php'); exit;
}

// Eliminar banner
if (isset($_GET['delete_banner'])) {
  $cat = cleanCat($_GET['delete_banner']);
  if ($cat && !empty($banners[$cat])) {
    $abs = __DIR__ . '/../' . $banners[$cat];
    if (file_exists($abs)) { @unlink($abs); }
    unset($banners[$cat]);
    saveBanners($bannerFile, $banners);
  }
  header('Location: offers.php'); exit;
}


$cats = [
  'venta' => 'Ventas de Casas',
  'anticretico' => 'Anticréticos',
  'alquiler' => 'Alquileres'
];

function countProps($cat){
  $rows = dbSelect('properties', 'COUNT(*) AS c', 'is_active=1 AND category=?', [$cat]);
  return (int)($rows[0]['c'] ?? 0);
}

function latestProps($cat, $limit=3){
  $limit = (int)$limit;
  return dbSelect('properties', 'id,title,provincia,departamento,price,image_url', 'is_active=1 AND category=?', [$cat], 'id DESC', (string)$limit);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ofertas por Categoría - Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #0b2340 0%, #082034 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .admin-header h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .dashboard-container {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .welcome-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .welcome-section h2 {
            color: #0b2340;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .welcome-section p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            border-left: 5px solid #0f6fb1;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-card i {
            font-size: 40px;
            color: #0f6fb1;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #0b2340;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 16px;
        }
        
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .module-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
            border: 2px solid transparent;
        }
        
        .module-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border-color: #0f6fb1;
            color: #0f6fb1;
        }
        
        .module-card i {
            font-size: 50px;
            color: #0f6fb1;
            margin-bottom: 20px;
        }
        
        .module-card h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }
        
        .module-card p {
            color: #777;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .recent-activity {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .recent-activity h2 {
            color: #0b2340;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #0f6fb1;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            background: #0f6fb1;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 18px;
        }
        
        .activity-info {
            flex: 1;
        }
        
        .activity-info strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .activity-time {
            color: #888;
            font-size: 13px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #888;
        }
        
        .empty-state i {
            font-size: 50px;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .property-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
        }
        
        .property-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .property-info {
            padding: 20px;
        }
        
        .property-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .property-price {
            color: #0f6fb1;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .property-location {
            color: #666;
            font-size: 14px;
        }
        
        .section-title {
            color: #0b2340;
            margin: 40px 0 20px 0;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .view-all {
            text-align: center;
            margin-top: 20px;
        }
        
        .view-all-btn {
            display: inline-block;
            padding: 10px 25px;
            background: #0f6fb1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .view-all-btn:hover {
            background: #0d5a96;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 20px;
            }
            
            .admin-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .modules-grid {
                grid-template-columns: 1fr;
            }
            
            .properties-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
  <style>
    .grid3{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;margin-top:18px}
    .card{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:18px}
    .mini-item img{display:block}
    .mini-item{padding:10px;border-radius:12px;background:rgba(0,0,0,.15);border:1px solid rgba(255,255,255,.06)}
    .mini-item:hover{background:rgba(0,0,0,.22)}
    .btn.small{padding:8px 12px;font-size:.9em}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1><i class="fa-solid fa-tags"></i> Ofertas por Categoría</h1>
      <a href="dashboard.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Volver</a>
    </div>

    <div class="content">
      <p style="opacity:.9;margin-bottom:10px">
        Esta sección se alimenta automáticamente del <b>Catálogo de Propiedades</b>. Para que aparezcan ofertas, agrega propiedades desde <b>Propiedades</b>.
      </p>

      <div class="card" style="margin:18px 0;padding:16px">
        <h2 style="margin:0 0 10px 0;font-size:18px">Banners (fotos) por categoría</h2>
        <p style="margin:0 0 14px 0;color:#555">Sube una foto para mostrar una portada por categoría en la web pública. (Ventas / Alquileres / Anticréticos)</p>

        <div class="grid3">
          <?php foreach($cats as $key=>$label): 
              $banner = $banners[$key] ?? '';
          ?>
            <div class="card" style="border:1px solid #e6e8ee">
              <h3 style="margin-top:0"><?php echo htmlspecialchars($label); ?></h3>

              <?php if($banner): ?>
                <div style="margin:10px 0">
                  <img src="../<?php echo htmlspecialchars($banner); ?>" alt="banner" style="width:100%;height:120px;object-fit:cover;border-radius:12px;border:1px solid #dfe3ea">
                </div>
                <a class="btn" href="offers.php?delete_banner=<?php echo urlencode($key); ?>" onclick="return confirm('¿Eliminar banner de <?php echo htmlspecialchars($label); ?>?')">Eliminar foto</a>
              <?php else: ?>
                <p style="margin:8px 0;color:#666">No hay foto cargada.</p>
              <?php endif; ?>

              <form method="post" enctype="multipart/form-data" style="margin-top:12px">
                <input type="hidden" name="action" value="upload_banner">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($key); ?>">
                <input type="file" name="banner" accept="image/*" required style="width:100%;margin:8px 0">
                <button class="btn btn-primary" type="submit">
                  Subir foto de <?php echo htmlspecialchars($label); ?>
                </button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="grid3">
        <?php foreach($cats as $key=>$label):
          $count = countProps($key);
          $items = latestProps($key, 3);
        ?>
          <div class="card">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
              <h3 style="margin:0"><?php echo htmlspecialchars($label); ?></h3>
              <a class="btn btn-primary small" href="../propiedades.php?tipo=<?php echo urlencode($key); ?>" target="_blank">Ver todas</a>
            </div>

            <div style="margin-top:10px;opacity:.85">
              <?php if($count===0): ?>
                Aún no hay registros.
              <?php else: ?>
                Total: <b><?php echo $count; ?></b>
              <?php endif; ?>
            </div>

            <?php if(!empty($items)): ?>
              <div style="margin-top:14px;display:grid;gap:10px">
                <?php foreach($items as $it): ?>
                  <a class="mini-item" href="../propiedades.php?tipo=<?php echo urlencode($key); ?>" target="_blank" style="text-decoration:none;color:inherit;display:flex;gap:10px;align-items:center">
                    <div style="width:64px;height:44px;border-radius:10px;overflow:hidden;background:#0b1b2a;flex:0 0 auto">
                      <?php if(!empty($it['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($it['image_url']); ?>" style="width:100%;height:100%;object-fit:cover" alt="">
                      <?php endif; ?>
                    </div>
                    <div style="min-width:0">
                      <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo htmlspecialchars($it['title']); ?></div>
                      <div style="font-size:.9em;opacity:.8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?php echo htmlspecialchars(trim(($it['provincia'] ?? '').' '.($it['departamento'] ?? ''))); ?>
                      </div>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</body>
</html>
