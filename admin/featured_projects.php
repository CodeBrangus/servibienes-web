<?php
require_once '../config.php';
requireAdmin();

// Crear tabla si no existe (compatibilidad con instalaciones existentes)
dbQuery("CREATE TABLE IF NOT EXISTS featured_projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  image_url VARCHAR(255) NOT NULL,
  title VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  display_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$message = '';
$error = '';

// Crear / actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Actualización rápida
    if (($_POST['action'] ?? '') === 'update' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $display_order = intval($_POST['display_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        dbUpdate('featured_projects', [
            'title' => $title,
            'description' => $description,
            'display_order' => $display_order,
            'is_active' => $is_active
        ], 'id = ?', [$id]);
        $message = 'Imagen destacada actualizada correctamente.';
    }

    // Crear nuevas imágenes (múltiples)
    if (($_POST['action'] ?? '') === 'create') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $display_order = intval($_POST['display_order'] ?? 0);

        if (!isset($_FILES['images'])) {
            $error = 'Debe seleccionar al menos una imagen.';
        } else {
            $uploaded = 0;
            foreach ($_FILES['images']['tmp_name'] as $k => $tmp) {
                if ($_FILES['images']['error'][$k] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $file = [
                    'name' => $_FILES['images']['name'][$k],
                    'type' => $_FILES['images']['type'][$k],
                    'tmp_name' => $tmp,
                    'error' => $_FILES['images']['error'][$k],
                    'size' => $_FILES['images']['size'][$k],
                ];

                $result = uploadFile($file, 'image', 'featured');
                if (!$result['success']) {
                    $error = $result['error'] ?? 'Error al subir imagen.';
                    break;
                }

                dbInsert('featured_projects', [
                    'image_url' => $result['path'],
                    'title' => $title,
                    'description' => $description,
                    'display_order' => $display_order,
                    'is_active' => 1
                ]);
                $uploaded++;
            }

            if (!$error && $uploaded > 0) {
                $message = "$uploaded imagen(es) agregada(s) a Proyectos Destacados.";
            } elseif (!$error) {
                $error = 'No se subió ninguna imagen. Verifique los archivos.';
            }
        }
    }
}

// Eliminar
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $row = dbSelect('featured_projects', '*', 'id = ?', [$id])[0] ?? null;
    if ($row) {
        $filepath = BASE_PATH . '/' . ltrim($row['image_url'], '/');
        if (file_exists($filepath)) {
            @unlink($filepath);
        }
        dbDelete('featured_projects', 'id = ?', [$id]);
        $message = 'Imagen eliminada correctamente.';
    }
}

$items = dbSelect('featured_projects', '*', '', [], 'display_order ASC, id DESC');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyectos Destacados (Sección) - Panel Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        <?php include '../assets/css/style.css'; ?>

        .admin-content { padding: 20px; }

        .card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 20px;
            margin-bottom: 20px;
        }

        .message {
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 18px;
        }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .form-grid .full { grid-column: 1 / -1; }

        input[type="text"], input[type="number"], textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        textarea { min-height: 90px; resize: vertical; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            border-radius: 10px;
            padding: 10px 14px;
            cursor: pointer;
            font-weight: 700;
            text-decoration: none;
        }
        .btn-primary { background: #0f6fb1; color: #fff; }
        .btn-danger { background: #e74c3c; color: #fff; }
        .btn-secondary { background: #0b2340; color: #fff; }

        .grid{
            display:grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }
        .item{
            background:#fff;
            border-radius:14px;
            box-shadow:0 2px 10px rgba(0,0,0,0.08);
            overflow:hidden;
        }
        .item img{
            width:100%;
            height:200px;
            object-fit: contain;
            background:#f6f6f6;
            display:block;
        }
        .body{ padding:14px; }
        .actions{ display:flex; gap:10px; justify-content:flex-end; padding:0 14px 14px; }
        .toggle-line{ display:flex; align-items:center; gap:8px; font-size:14px; color:#333; }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="admin-content">
    <h1>Proyectos Destacados (Sección independiente)</h1>
    <p style="color:#555; margin: 6px 0 18px;">Lo que subas aquí se mostrará en <strong>“Nuestros Proyectos Destacados”</strong> y no afecta a la sección “Proyectos”.</p>

    <?php if ($message): ?><div class="message success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="message error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="card">
        <h3>Agregar imágenes a Proyectos Destacados</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">
            <div class="form-grid" style="margin-top:12px;">
                <div class="full">
                    <label><strong>Título (opcional)</strong></label>
                    <input type="text" name="title" placeholder="Ej: Condominio Reze II">
                </div>
                <div class="full">
                    <label><strong>Descripción (opcional)</strong></label>
                    <textarea name="description" placeholder="Texto de apoyo (opcional)"></textarea>
                </div>
                <div>
                    <label><strong>Orden</strong></label>
                    <input type="number" name="display_order" value="0">
                </div>
                <div>
                    <label><strong>Imágenes *</strong></label>
                    <input type="file" name="images[]" accept="image/*" multiple required>
                    <div style="font-size:12px; color:#666; margin-top:6px;">Puedes seleccionar múltiples imágenes. No hay límite en cantidad (depende del hosting).</div>
                </div>
            </div>
            <div style="margin-top:14px;">
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-upload"></i> Subir imágenes</button>
                <a class="btn btn-secondary" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i> Volver</a>
            </div>
        </form>
    </div>

    <h3 style="margin: 6px 0 14px;">Imágenes existentes</h3>

    <?php if (empty($items)): ?>
        <div class="card">Aún no hay imágenes cargadas en esta sección.</div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($items as $it): ?>
                <div class="item">
                    <img src="<?php echo htmlspecialchars($it['image_url']); ?>" alt="<?php echo htmlspecialchars($it['title'] ?? ''); ?>">
                    <div class="body">
                        <form method="post">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo intval($it['id']); ?>">

                            <label><strong>Título</strong></label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($it['title'] ?? ''); ?>" style="margin:6px 0 10px;">

                            <label><strong>Descripción</strong></label>
                            <textarea name="description" style="margin:6px 0 10px;"><?php echo htmlspecialchars($it['description'] ?? ''); ?></textarea>

                            <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 10px;">
                                <div>
                                    <label><strong>Orden</strong></label>
                                    <input type="number" name="display_order" value="<?php echo intval($it['display_order'] ?? 0); ?>">
                                </div>
                                <div class="toggle-line" style="margin-top:22px;">
                                    <input type="checkbox" name="is_active" <?php echo !empty($it['is_active']) ? 'checked' : ''; ?> />
                                    <span>Activo</span>
                                </div>
                            </div>

                            <div class="actions" style="margin-top:12px;">
                                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-save"></i> Guardar</button>
                                <a class="btn btn-danger" href="?delete=<?php echo intval($it['id']); ?>" onclick="return confirm('¿Eliminar esta imagen?');"><i class="fa-solid fa-trash"></i> Eliminar</a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
