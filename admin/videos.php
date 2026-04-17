<?php
require_once '../config.php';
requireAdmin();

$message = '';
$error = '';

// Procesar subida de videos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['videos'])) {
    $uploaded = 0;
    
    foreach ($_FILES['videos']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['videos']['error'][$key] === UPLOAD_ERR_OK) {
            $file = [
                'name' => $_FILES['videos']['name'][$key],
                'type' => $_FILES['videos']['type'][$key],
                'tmp_name' => $tmp_name,
                'error' => $_FILES['videos']['error'][$key],
                'size' => $_FILES['videos']['size'][$key]
            ];
            
            $result = uploadFile($file, 'video', 'videos');
            
            if ($result['success']) {
                dbInsert('videos', [
                    'video_url' => $result['path'],
                    'title' => pathinfo($file['name'], PATHINFO_FILENAME),
                    'is_active' => 1
                ]);
                $uploaded++;
            }
        }
    }
    
    if ($uploaded > 0) {
        $message = "{$uploaded} video(s) subido(s) correctamente";
    }
}

// Eliminar video
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $video = dbSelect('videos', '*', 'id = ?', [$id])[0] ?? null;
    
    if ($video) {
        $filepath = BASE_PATH . '/' . $video['video_url'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        dbDelete('videos', 'id = ?', [$id]);
        $message = "Video eliminado correctamente";
    }
}

// Obtener videos
$videos = dbSelect('videos', '*', '', [], 'created_at DESC');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos - Panel Admin</title>
    <style>
        <?php include '../assets/css/style.css'; ?>
        
        .admin-content {
            padding: 20px;
        }
        
        .upload-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .video-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            position: relative;
        }
        
        .video-card:hover {
            transform: translateY(-5px);
        }
        
        video {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #000;
        }
        
        .video-controls {
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
        
        .video-info {
            padding: 15px;
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
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #0f6fb1;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-content">
        <h1>Videos de Proyectos</h1>
        
        <?php if ($message): ?>
        <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Estadísticas -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($videos); ?></div>
                <div>Total de Videos</div>
            </div>
        </div>
        
        <!-- Subida de videos -->
        <div class="upload-section">
            <h2>Subir Nuevos Videos</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Seleccionar videos (puedes subir muchos; si tu hosting limita por carga, súbelos en varias tandas). Formatos: MP4/WEBM/AVI/MOV. Tamaño máximo real depende del hosting.</label>
                    <input type="file" name="videos[]" accept="video/*" multiple required>
                </div>
                <button type="submit" class="btn">Subir Videos</button>
            </form>
        </div>
        
        <!-- Lista de videos -->
        <h2>Videos Existentes (<?php echo count($videos); ?>)</h2>
        
        <?php if (empty($videos)): ?>
        <div class="message">No hay videos registrados</div>
        <?php else: ?>
        <div class="videos-grid">
            <?php foreach ($videos as $video): ?>
            <div class="video-card">
                <video controls>
                    <source src="<?php echo htmlspecialchars($video['video_url']); ?>" type="video/mp4">
                    Tu navegador no soporta videos.
                </video>
                
                <div class="video-controls">
                    <a href="?delete=<?php echo $video['id']; ?>" 
                       class="btn-icon btn-delete"
                       onclick="return confirm('¿Eliminar este video?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
                
                <div class="video-info">
                    <p><strong><?php echo htmlspecialchars($video['title']); ?></strong></p>
                    <small><?php echo date('d/m/Y', strtotime($video['created_at'])); ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>