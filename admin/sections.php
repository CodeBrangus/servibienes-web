<?php
require_once '../config.php';
requireAdmin();

$path = BASE_PATH . '/assets/data/sections.json';

$sections = [];
if (file_exists($path)) {
    $decoded = json_decode(file_get_contents($path), true);
    if (is_array($decoded)) $sections = $decoded;
}

if (empty($sections)) {
    $sections = [
        ['id'=>'ofertas','label'=>'Ofertas por Categoría'],
        ['id'=>'proyectos','label'=>'Proyectos'],
        ['id'=>'catalogo','label'=>'Catálogo de Propiedades']
    ];
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = trim((string)($_POST['sections_json'] ?? ''));
    $decoded = json_decode($raw, true);

    if (!is_array($decoded)) {
        $error = 'JSON inválido. Verifica la estructura.';
    } else {
        // Validar y limpiar
        $clean = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) continue;
            $id = sanitize($row['id'] ?? '');
            $label = trim((string)($row['label'] ?? ''));
            if ($id === '' || $label === '') continue;
            $clean[] = ['id' => $id, 'label' => $label];
        }
        if (empty($clean)) {
            $error = 'Debes ingresar al menos 1 sección válida.';
        } else {
            if (!is_dir(dirname($path))) @mkdir(dirname($path), 0777, true);
            file_put_contents($path, json_encode($clean, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
            $sections = $clean;
            $message = 'Secciones actualizadas correctamente.';
        }
    }
}

$value = json_encode($sections, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Secciones - Panel Admin</title>
  <style>
    <?php include '../assets/css/style.css'; ?>
    .wrap{padding:20px;}
    .cardx{background:#fff;padding:22px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.08);max-width:980px;margin:0 auto;}
    textarea{width:100%;min-height:360px;font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;}
    .hint{color:#666;font-size:13px;line-height:1.4;margin-top:8px;}
    .actions{margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;}
    .btn{padding:10px 14px;border-radius:10px;border:none;background:#0f6fb1;color:#fff;cursor:pointer;font-weight:800;}
    .btn.secondary{background:#ddd;color:#111;}
    .msg{padding:10px 12px;border-radius:10px;margin-bottom:12px;}
    .ok{background:#e7f7ed;border:1px solid #bfe8cd;color:#0f5a2b;}
    .bad{background:#fff0f0;border:1px solid #f2b6b6;color:#8a1f1f;}
    code{background:#f3f3f3;padding:2px 6px;border-radius:8px;}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="cardx">
      <h2 style="margin:0 0 8px 0;">Secciones del sitio</h2>
      <div class="hint">
        Esto controla el menú desplegable <b>Proyectos</b> de la portada (los nombres y a qué sección lleva).<br>
        Formato: una lista de objetos con <code>id</code> (el id del &lt;section&gt;) y <code>label</code> (texto que se muestra).
      </div>

      <?php if ($message): ?><div class="msg ok"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
      <?php if ($error): ?><div class="msg bad"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <form method="post">
        <textarea name="sections_json" required><?php echo htmlspecialchars($value); ?></textarea>
        <div class="actions">
          <button class="btn" type="submit">Guardar</button>
          <a class="btn secondary" href="dashboard.php" style="text-decoration:none;display:inline-flex;align-items:center;">Volver</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
