<?php
require_once '../config.php';
requireAdmin();

$namesPath = BASE_PATH . '/assets/data/project_names.json';

$success = '';
$error = '';

// Cargar lista actual
$current = [];
if (file_exists($namesPath)) {
    $decoded = json_decode(file_get_contents($namesPath), true);
    if (is_array($decoded)) $current = $decoded;
}

// Guardar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = $_POST['project_names'] ?? '';
    $lines = preg_split('/\R+/', $raw);
    $clean = [];
    foreach ($lines as $line) {
        $name = trim($line);
        if ($name === '') continue;
        // Evitar duplicados (case-insensitive)
        $key = mb_strtolower($name);
        if (!isset($clean[$key])) $clean[$key] = $name;
    }
    $list = array_values($clean);

    if (empty($list)) {
        $error = 'Debes ingresar al menos un nombre de proyecto.';
    } else {
        if (!is_dir(dirname($namesPath))) {
            @mkdir(dirname($namesPath), 0755, true);
        }
        $ok = @file_put_contents($namesPath, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($ok === false) {
            $error = 'No se pudo guardar. Revisa permisos de assets/data/.';
        } else {
            $success = 'Lista de proyectos actualizada.';
            $current = $list;
        }
    }
}

$value = implode("\n", array_map('strval', $current));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proyectos (Nombres) - Panel Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f5f7fa;margin:0;color:#333;}
    .header{background:#0b2340;color:#fff;padding:18px 22px;display:flex;justify-content:space-between;align-items:center;}
    .header a{color:#fff;text-decoration:none;opacity:.9}
    .wrap{max-width:1000px;margin:0 auto;padding:22px;}
    .card{background:#fff;border-radius:14px;box-shadow:0 6px 20px rgba(0,0,0,.06);padding:18px;}
    h1{margin:0;font-size:18px;display:flex;align-items:center;gap:10px;}
    textarea{width:100%;min-height:340px;border-radius:12px;border:1px solid #dfe6ee;padding:14px;font-size:14px;line-height:1.4;}
    .btn{display:inline-block;background:#0f6fb1;color:#fff;padding:10px 14px;border-radius:10px;cursor:pointer;border:none;text-decoration:none;font-weight:700}
    .muted{color:#666;font-size:13px}
    .msg{padding:12px 14px;border-radius:10px;margin-bottom:14px}
    .ok{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
    .bad{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
  </style>
</head>
<body>
  <div class="header">
    <h1><i class="fas fa-list"></i> Nombres de Proyectos</h1>
    <div>
      <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Volver</a>
      &nbsp;|&nbsp;
      <a href="logout.php"><i class="fas fa-right-from-bracket"></i> Salir</a>
    </div>
  </div>

  <div class="wrap">
    <?php if ($success): ?><div class="msg ok"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="msg bad"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="card">
      <p class="muted">Escribe <b>un proyecto por línea</b>. Puedes agregar nuevos proyectos o renombrar los existentes. Esto controla el selector en <b>Proyectos</b> (subida de imágenes).</p>
      <form method="post">
        <textarea name="project_names" placeholder="ICARO\nPALMERAS GARDEN\n..." required><?php echo htmlspecialchars($value); ?></textarea>
        <div style="margin-top:12px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
          <button class="btn" type="submit"><i class="fas fa-save"></i> Guardar</button>
          <span class="muted">Archivo: <code>assets/data/project_names.json</code></span>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
