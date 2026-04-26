<?php
require_once '../config.php';
requireAdmin();

// Crear tabla si no existe (compatibilidad con instalaciones existentes)
dbQuery("CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  image_url VARCHAR(255) NOT NULL,
  display_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$message = '';
$error = '';

// Crear / actualizar evento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Guardar cambios rápidos (titulo/descripcion/orden/activo)
    if (isset($_POST['action']) && $_POST['action'] === 'update' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $display_order = intval($_POST['display_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($title === '') {
            $error = 'El título es obligatorio.';
        } else {
            dbUpdate('events', [
                'title' => $title,
                'description' => $description,
                'display_order' => $display_order,
                'is_active' => $is_active
            ], 'id = ?', [$id]);
            $message = 'Evento actualizado correctamente.';
        }
    }

    // Nuevo evento
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $display_order = intval($_POST['display_order'] ?? 0);

        if ($title === '') {
            $error = 'El título es obligatorio.';
        } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Debe subir una imagen del evento.';
        } else {
            $result = uploadFile($_FILES['image'], 'image', 'events');
            if (!$result['success']) {
                $error = $result['error'] ?? 'Error al subir imagen.';
            } else {
                dbInsert('events', [
                    'title' => $title,
                    'description' => $description,
                    'image_url' => $result['path'],
                    'display_order' => $display_order,
                    'is_active' => 1
                ]);
                $message = 'Evento creado correctamente.';
            }
        }
    }
}

// Eliminar evento
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $ev = dbSelect('events', '*', 'id = ?', [$id])[0] ?? null;
    if ($ev) {
        $filepath = BASE_PATH . '/' . ltrim($ev['image_url'], '/');
        if (file_exists($filepath)) {
            @unlink($filepath);
        }
        dbDelete('events', 'id = ?', [$id]);
        $message = 'Evento eliminado correctamente.';
    }
}

// Listado
$events = dbSelect('events', '*', '', [], 'display_order ASC, id DESC');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos - Panel Admin</title>
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
        }
        .btn-primary { background: #0f6fb1; color: #fff; }
        .btn-danger { background: #e74c3c; color: #fff; }
        .btn-secondary { background: #0b2340; color: #fff; }

        .events-grid{
            display:grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }
        .event-item{
            background:#fff;
            border-radius:14px;
            box-shadow:0 2px 10px rgba(0,0,0,0.08);
            overflow:hidden;
        }
        .event-item img{
            width:100%;
            height:170px;
            object-fit:cover;
            display:block;
        }
        .event-body{ padding:14px; }
        .event-actions{ display:flex; gap:10px; justify-content:flex-end; padding:0 14px 14px; }
        .toggle-line{ display:flex; align-items:center; gap:8px; font-size:14px; color:#333; }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="admin-content">
    <h1>Eventos (Galería)</h1>

    <?php if ($message): ?><div class="message success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="message error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="card">
        <h3>Agregar nuevo evento</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">
            <div class="form-grid" style="margin-top:12px;">
                <div class="full">
                    <label><strong>Título *</strong></label>
                    <input type="text" name="title" required>
                </div>
                <div class="full">
                    <label><strong>Descripción</strong></label>
                    <textarea name="description" placeholder="Texto del evento (editable)"></textarea>
                </div>
                <div>
                    <label><strong>Orden</strong></label>
                    <input type="number" name="display_order" value="0">
                </div>
                <div>
                    <label><strong>Imagen *</strong></label>
                    <input type="file" name="image" accept="image/*" required>
                </div>
            </div>
            <div style="margin-top:14px;">
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-plus"></i> Crear evento</button>
                <a class="btn btn-secondary" href="dashboard.php" style="text-decoration:none;"><i class="fa-solid fa-arrow-left"></i> Volver</a>
            </div>
        </form>
    </div>

    <h3 style="margin: 6px 0 14px;">Eventos existentes</h3>

    <?php if (empty($events)): ?>
        <div class="card">Aún no hay eventos cargados.</div>
    <?php else: ?>
        <div class="events-grid">
            <?php foreach ($events as $ev): ?>
                <div class="event-item">
                    <img src="<?php echo htmlspecialchars($ev['image_url']); ?>" alt="<?php echo htmlspecialchars($ev['title']); ?>">
                    <div class="event-body">
                        <form method="post">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo intval($ev['id']); ?>">

                            <label><strong>Título *</strong></label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($ev['title']); ?>" required style="margin:6px 0 10px;">

                            <label><strong>Descripción</strong></label>
                            <textarea name="description" style="margin:6px 0 10px;"><?php echo htmlspecialchars($ev['description'] ?? ''); ?></textarea>

                            <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap:10px;">
                                <div>
                                    <label><strong>Orden</strong></label>
                                    <input type="number" name="display_order" value="<?php echo intval($ev['display_order'] ?? 0); ?>">
                                </div>
                                <div class="toggle-line" style="align-self:end;">
                                    <input type="checkbox" name="is_active" <?php echo !empty($ev['is_active']) ? 'checked' : ''; ?>>
                                    <span>Activo</span>
                                </div>
                            </div>

                            <div class="event-actions">
                                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                                <a class="btn btn-danger" href="?delete=<?php echo intval($ev['id']); ?>" onclick="return confirm('¿Eliminar este evento?');" style="text-decoration:none;">
                                    <i class="fa-solid fa-trash"></i> Eliminar
                                </a>
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
PHP