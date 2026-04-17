<?php
require_once '../config.php';
requireAdmin();

$message = '';
$error = '';

// Procesar subida de imágenes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    $uploaded = 0;
    $failed = 0;
    
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $file = [
                'name' => $_FILES['images']['name'][$key],
                'type' => $_FILES['images']['type'][$key],
                'tmp_name' => $tmp_name,
                'error' => $_FILES['images']['error'][$key],
                'size' => $_FILES['images']['size'][$key]
            ];
            
            // Subir imagen
            $result = uploadFile($file, 'image', 'gallery');
            
            if ($result['success']) {
                // Guardar en base de datos
                dbInsert('gallery', [
                    'image_url' => $result['path'],
                    'title' => pathinfo($file['name'], PATHINFO_FILENAME),
                    'is_active' => 1
                ]);
                $uploaded++;
            } else {
                $failed++;
            }
        }
    }
    
    if ($uploaded > 0) {
        $message = "{$uploaded} imagen(es) subida(s) correctamente";
    }
    if ($failed > 0) {
        $error = "{$failed} imagen(es) no se pudieron subir";
    }
}

// Procesar eliminación
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Obtener información de la imagen
    $image = dbSelect('gallery', '*', 'id = ?', [$id])[0] ?? null;
    
    if ($image) {
        // Eliminar archivo físico
        $filepath = BASE_PATH . '/' . $image['image_url'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        // Eliminar de la base de datos
        dbDelete('gallery', 'id = ?', [$id]);
        $message = "Imagen eliminada correctamente";
    }
}

// Obtener todas las imágenes
$images = dbSelect('gallery', '*', '', [], 'created_at DESC');

// Obtener estadísticas
$stats = [
    'total' => count($images),
    'active' => count(array_filter($images, fn($img) => $img['is_active'])),
    'size' => array_sum(array_map(function($img) {
        $path = BASE_PATH . '/' . $img['image_url'];
        return file_exists($path) ? filesize($path) : 0;
    }, $images))
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería - Panel Admin</title>
    <style>
        <?php include '../assets/css/style.css'; ?>
        
        .admin-content {
            padding: 20px;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #0f6fb1;
            margin-bottom: 5px;
        }
        
        .upload-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
        }
        
        .gallery-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .gallery-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
        }
        
        .btn-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }
        
        .btn-delete {
            background: #e74c3c;
        }
        
        .btn-edit {
            background: #3498db;
        }
        
        .image-info {
            padding: 10px;
            background: white;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-content">
        <h1>Galería de Imágenes</h1>
        
        <?php if ($message): ?>
        <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Estadísticas -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div>Total de Imágenes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['active']; ?></div>
                <div>Imágenes Activas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo formatBytes($stats['size']); ?></div>
                <div>Tamaño Total</div>
            </div>
        </div>
        
        <!-- Subida de imágenes -->
        <div class="upload-section">
            <h2>Subir Nuevas Imágenes</h2>
            <form id="galleryUploadForm" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Seleccionar imágenes (hasta 130 por vez; se subirán en tandas de 20 automáticamente. Formatos: JPG/PNG/WEBP/GIF, 5MB c/u):</label>
                    <input id="galleryImages" type="file" name="images[]" accept="image/*" multiple required>
                </div>
                <button type="submit" class="btn">Subir Imágenes</button>
                <div id="uploadProgress" class="message" style="display:none; margin-top:15px;"></div>
            </form>
        </div>
        
        <!-- Galería -->
        <h2>Imágenes Existentes (<?php echo count($images); ?>)</h2>
        
        <?php if (empty($images)): ?>
        <div class="message">No hay imágenes en la galería</div>
        <?php else: ?>
        <div class="gallery-grid">
            <?php foreach ($images as $image): ?>
            <div class="gallery-item">
                <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($image['title']); ?>">
                
                <div class="gallery-controls">
                    <a href="?delete=<?php echo $image['id']; ?>" 
                       class="btn-icon btn-delete"
                       onclick="return confirm('¿Eliminar esta imagen?')">
                        <i class="fas fa-trash"></i>
                    </a>
                    <button class="btn-icon btn-edit" 
                            onclick="editImage(<?php echo $image['id']; ?>)">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
                
                <div class="image-info">
                    <p><strong><?php echo htmlspecialchars($image['title']); ?></strong></p>
                    <small><?php echo date('d/m/Y H:i', strtotime($image['created_at'])); ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    function editImage(id) {
        const newTitle = prompt('Nuevo título para la imagen:');
        if (newTitle !== null) {
            // Enviar petición AJAX para actualizar
            fetch('process/update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    table: 'gallery',
                    id: id,
                    field: 'title',
                    value: newTitle
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Título actualizado');
                    location.reload();
                } else {
                    alert('Error al actualizar');
                }
            });
        }
    }
    
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    

    // ===== Subida en tandas (evita límite típico de 20 archivos por request en muchos hostings) =====
    (function(){
        const form = document.getElementById('galleryUploadForm');
        const input = document.getElementById('galleryImages');
        const progress = document.getElementById('uploadProgress');
        if (!form || !input) return;

        const MAX_SELECT = 130;   // lo que el usuario quiere seleccionar
        const BATCH_SIZE = 20;    // compatible con el límite más común del servidor

        input.addEventListener('change', () => {
            const files = Array.from(input.files || []);
            if (files.length > MAX_SELECT) {
                alert('Máximo ' + MAX_SELECT + ' imágenes por vez.');
                input.value = '';
                return;
            }
        });

        form.addEventListener('submit', async (e) => {
            // Si no hay archivos, dejar que el form siga normal
            const files = Array.from(input.files || []);
            if (!files.length) return;

            // Interceptar y subir por fetch en lotes
            e.preventDefault();

            if (progress) {
                progress.style.display = '';
                progress.classList.remove('error','success');
                progress.textContent = 'Preparando subida...';
            }

            const total = files.length;
            let uploadedTotal = 0;
            let errorCount = 0;

            // Subir en tandas
            for (let i = 0; i < files.length; i += BATCH_SIZE) {
                const chunk = files.slice(i, i + BATCH_SIZE);

                if (progress) {
                    const lote = Math.floor(i / BATCH_SIZE) + 1;
                    const lotes = Math.ceil(total / BATCH_SIZE);
                    progress.textContent = `Subiendo lote ${lote}/${lotes}... (${uploadedTotal}/${total})`;
                }

                const fd = new FormData();
                fd.append('type', 'gallery');
                chunk.forEach(f => fd.append('files[]', f));

                try {
                    const res = await fetch('../process/upload.php', { method: 'POST', body: fd, credentials: 'same-origin' });
                    const data = await res.json().catch(() => ({}));
                    const count = Number(data.count || 0);
                    uploadedTotal += count;

                    if (Array.isArray(data.errors) && data.errors.length) {
                        errorCount += data.errors.length;
                    }
                } catch (err) {
                    errorCount++;
                }
            }

            if (progress) {
                progress.classList.add(errorCount ? 'error' : 'success');
                progress.textContent = errorCount
                    ? `Subida terminada: ${uploadedTotal}/${total} imágenes subidas. Errores: ${errorCount}. (Si fallaron algunas, intenta nuevamente).`
                    : `Subida terminada: ${uploadedTotal}/${total} imágenes subidas correctamente.`;
            }

            // Recargar para mostrar nuevas imágenes
            setTimeout(() => { window.location.reload(); }, 1200);
        });
    })();
    
</script>
</body>
</html>
<?php
function formatBytes($bytes, $decimals = 2) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $dm = $decimals < 0 ? 0 : $decimals;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return number_format($bytes / pow($k, $i), $dm) . ' ' . $sizes[$i];
}
?>