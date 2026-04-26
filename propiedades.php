<?php
require_once 'config.php';
require_once 'functions.php';

// Tipo de catálogo (venta|alquiler|anticretico|all)
$tipo = isset($_GET['tipo']) ? strtolower(trim($_GET['tipo'])) : 'venta';
if (!in_array($tipo, ['venta','alquiler','anticretico','all'], true)) {
    $tipo = 'venta';
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$sub = isset($_GET['sub']) ? strtolower(trim($_GET['sub'])) : '';

// Construir consulta
$where = 'is_active = 1';
$params = [];

if ($tipo !== 'all') {
    $where .= ' AND category = ?';
    $params[] = $tipo;
}

if ($sub !== '') {
    $where .= ' AND subcategory = ?';
    $params[] = $sub;
}

if ($q !== '') {
    // Buscar por titulo, ubicacion, subcategoria, descripcion
    $where .= ' AND (title LIKE ? OR location LIKE ? OR subcategory LIKE ? OR description LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}

$properties = dbSelect('properties', '*', $where, $params, 'id DESC', '500');

$tituloMapa = [
    'venta' => 'Propiedades en Venta',
    'alquiler' => 'Propiedades en Alquiler',
    'anticretico' => 'Propiedades en Anticrético',
    'all' => 'Todas las Propiedades'
];

$pageTitle = $tituloMapa[$tipo] ?? 'Catálogo de Propiedades';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle); ?> - Servibienes</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    <?php include 'assets/css/style.css'; ?>
    :root { font-family: 'Roboto', Arial, sans-serif; }
    html, body { height:100%; background: linear-gradient(135deg, #0b2340 0%, #082034 50%, #041826 100%); color:#e9f2fb; overflow-x:hidden; }
    .site { max-width: 1400px; margin: 20px auto; padding: 30px; border-radius: 24px; background: linear-gradient(165deg, rgba(255,255,255,0.02) 0%, rgba(255,255,255,0.01) 100%); box-shadow: 0 8px 32px rgba(0,0,0,0.3); backdrop-filter: blur(10px); }
    .topbar { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:18px; }
    .backlink { color:#cfeefc; text-decoration:none; opacity:.9; }
    .backlink:hover { opacity:1; text-decoration:underline; }
    .page-title { font-size: 34px; font-weight: 800; letter-spacing: -0.5px; }
    .muted { color:#bcd6ee; }
    .filters { display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin: 18px 0 22px; }
    .filters a.cat { padding: 10px 14px; border-radius: 12px; background: rgba(255,255,255,0.06); color:#e9f2fb; text-decoration:none; border:1px solid rgba(255,255,255,0.08); }
    .filters a.cat.active { background: rgba(15,111,177,0.35); border-color: rgba(15,111,177,0.65); }
    .search { flex:1; min-width: 260px; display:flex; gap:10px; }
    .search input { flex:1; padding: 12px 14px; border-radius: 12px; border:1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.06); color:#e9f2fb; outline:none; }
    .search button { padding: 12px 14px; border-radius: 12px; border:none; background: rgba(15,111,177,0.9); color:#fff; cursor:pointer; }
    .grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px; }
    .card { background: rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 16px; }
    .card img { width:100%; height: 190px; object-fit: cover; border-radius: 12px; margin-bottom: 12px; }
    .price { color: #0f6fb1; font-weight: 800; margin-top: 6px; }
    .desc { margin-top: 10px; line-height: 1.45; max-height: 120px; overflow:auto; padding-right: 6px; }
    .pill { display:inline-block; padding:6px 10px; border-radius: 999px; background: rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.08); color:#bcd6ee; font-size: 12px; margin-top:10px; }
    .empty { text-align:center; padding: 40px 20px; background: rgba(255,255,255,0.03); border-radius: 16px; border:1px dashed rgba(255,255,255,0.12); }
  </style>
</head>
<body>
  <div class="site">
    <div class="topbar">
      <a class="backlink" href="index.php"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
      <div class="muted">Catálogo · Servibienes</div>
    </div>

    <div>
      <div class="page-title"><?php echo htmlspecialchars($pageTitle); ?></div>
      <div class="muted">Filtra por tipo y busca por nombre, ubicación o palabra clave.</div>
    </div>

    <div class="filters">
      <a class="cat <?php echo $tipo==='venta'?'active':''; ?>" href="propiedades.php?tipo=venta">Venta</a>
      <a class="cat <?php echo $tipo==='alquiler'?'active':''; ?>" href="propiedades.php?tipo=alquiler">Alquiler</a>
      <a class="cat <?php echo $tipo==='anticretico'?'active':''; ?>" href="propiedades.php?tipo=anticretico">Anticrético</a>
      <a class="cat <?php echo $tipo==='all'?'active':''; ?>" href="propiedades.php?tipo=all">Todo</a>

      <form class="search" method="get" action="propiedades.php">
        <select name="sub" style="padding:12px 12px;border-radius:12px;border:1px solid rgba(255,255,255,0.12);background: rgba(255,255,255,0.06);color:#e9f2fb;">
          <option value="">Todas las categorías</option>
          <?php
            $subs = ['casa'=>'Casa','departamento'=>'Departamento','monoambiente'=>'Monoambiente','terreno'=>'Terreno','local'=>'Local Comercial','oficina'=>'Oficina','habitacion'=>'Habitación'];
            foreach($subs as $k=>$lbl){
              $sel = ($sub===$k) ? 'selected' : '';
              echo "<option value=\"".htmlspecialchars($k)."\" $sel>".htmlspecialchars($lbl)."</option>";
            }
          ?>
        </select>

        <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo); ?>">
        <input type="hidden" name="sub" value="<?php echo htmlspecialchars($sub); ?>">
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Buscar propiedades...">
        <button type="submit"><i class="fas fa-search"></i></button>
      </form>
    </div>

    <?php if (empty($properties)): ?>
      <div class="empty">
        <i class="fas fa-home" style="font-size:48px;opacity:.7;margin-bottom:10px;"></i>
        <div style="font-weight:800;font-size:18px;margin-bottom:6px;">No se encontraron propiedades</div>
        <div class="muted">Prueba con otra categoría o cambia el texto de búsqueda.</div>
      </div>
    <?php else: ?>
      <div class="grid">
        <?php foreach ($properties as $property): ?>
          <div class="card">
            <?php if (!empty($property['image_url'])): ?>
              <img src="<?php echo htmlspecialchars($property['image_url']); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" loading="lazy">
            <?php endif; ?>
            <div style="font-size:18px;font-weight:800;line-height:1.2;">
              <?php echo htmlspecialchars($property['title']); ?>
            </div>
            <div class="muted" style="margin-top:6px;">
              <?php echo htmlspecialchars($property['location']); ?> · <?php echo htmlspecialchars($property['subcategory']); ?>
            </div>
            <div class="price"><?php echo htmlspecialchars($property['price']); ?></div>
            <div class="desc"><?php echo nl2br(htmlspecialchars($property['description'])); ?></div>
            <span class="pill"><?php echo htmlspecialchars($property['category']); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
