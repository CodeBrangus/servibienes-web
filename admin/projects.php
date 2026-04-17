<?php
require_once '../config.php';
requireAdmin();

// Lista editable de proyectos (para subir imágenes por proyecto)
// Se administra desde: admin/project_names.php
$namesPath = BASE_PATH . '/assets/data/project_names.json';
$projectNames = [];
if (file_exists($namesPath)) {
    $decoded = json_decode(file_get_contents($namesPath), true);
    if (is_array($decoded)) {
        $projectNames = array_values(array_filter(array_map('strval', $decoded)));
    }
}
if (empty($projectNames)) {
    // Fallback por si el archivo no existe
    $projectNames = [
        'ICARO','PALMERAS GARDEN','OBLOX ONE','RAIZANT BLEND','ERGO','GIARDINO','SINAI URUBO',
        'BERCHATTI HOME','BERCHATTI BN2','VERTICAL TERRA','CONDOMINIO REZE II','MATT - 51'
    ];
}

$message = '';
$error = '';

// Procesar subida de proyectos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    $uploaded = 0;

    $selectedProject = sanitize($_POST['project_name'] ?? '');
    if ($selectedProject === '' || !in_array($selectedProject, $projectNames, true)) {
        $error = 'Selecciona un proyecto válido.';
    }
    
    if (!$error) foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $file = [
                'name' => $_FILES['images']['name'][$key],
                'type' => $_FILES['images']['type'][$key],
                'tmp_name' => $tmp_name,
                'error' => $_FILES['images']['error'][$key],
                'size' => $_FILES['images']['size'][$key]
            ];
            
            $result = uploadFile($file, 'image', 'projects');
            
            if ($result['success']) {
                dbInsert('projects', [
                    'image_url' => $result['path'],
                    'title' => $selectedProject,
                    'description' => pathinfo($file['name'], PATHINFO_FILENAME),
                    'is_active' => 1
                ]);
                $uploaded++;
            }
        }
    }
    
    if ($uploaded > 0) {
        $message = "{$uploaded} proyecto(s) agregado(s) correctamente";
    }
}

// Eliminar proyecto
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $project = dbSelect('projects', '*', 'id = ?', [$id])[0] ?? null;
    
    if ($project) {
        $filepath = BASE_PATH . '/' . $project['image_url'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        dbDelete('projects', 'id = ?', [$id]);
        $message = "Proyecto eliminado correctamente";
    }
}

// Obtener proyectos
$projects = dbSelect('projects', '*', '', [], 'created_at DESC');

// Agrupar por nombre de proyecto
$projectsByName = [];
foreach ($projects as $p) {
    $key = $p['title'] ?: 'SIN_NOMBRE';
    if (!isset($projectsByName[$key])) $projectsByName[$key] = [];
    $projectsByName[$key][] = $p;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyectos - Panel Admin</title>
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
        
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .project-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            position: relative;
        }
        
        .project-card:hover {
            transform: translateY(-5px);
        }
        
        .project-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .project-controls {
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
        
        .project-info {
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
        <h1>Proyectos Destacados</h1>
        
        <?php if ($message): ?>
        <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Estadísticas -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($projects); ?></div>
                <div>Total de Proyectos</div>
            </div>
        </div>
        
        <!-- Subida de imagenes por proyecto -->
        <div class="upload-section">
            <h2>Subir imágenes por proyecto</h2>
            <p class="muted">Selecciona el proyecto y sube las imágenes que quieras (sin límite en la web; el límite real lo define tu hosting). Se guardan en <b>projects</b>.</p>
            <?php if ($error): ?>
                <div class="message" style="background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Proyecto:</label>
                    <select name="project_name" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <option value="" disabled selected>Selecciona un proyecto...</option>
                        <?php foreach ($projectNames as $pn): ?>
                            <option value="<?php echo htmlspecialchars($pn); ?>"><?php echo htmlspecialchars($pn); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Imágenes (JPG/PNG/WEBP/GIF):</label>
                    <input type="file" name="images[]" accept="image/*" multiple required>
                </div>
                <button type="submit" class="btn">Subir imágenes</button>
            </form>
        </div>
        
        <!-- Lista de proyectos agrupada -->
        <h2>Imágenes por proyecto</h2>
        
        <?php
            // Agrupar por titulo (nombre del proyecto)
            $grouped = [];
            foreach ($projects as $p) {
                $key = $p['title'] ?: 'SIN_TITULO';
                if (!isset($grouped[$key])) $grouped[$key] = [];
                $grouped[$key][] = $p;
            }
        ?>

        <?php foreach ($projectNames as $pn): ?>
            <h3 style="margin:22px 0 10px; color:#0b2340;">PROYECTO: <?php echo htmlspecialchars($pn); ?> (<?php echo isset($grouped[$pn]) ? count($grouped[$pn]) : 0; ?>)</h3>
            <?php if (empty($grouped[$pn])): ?>
                <div class="message">Aún no hay imágenes para este proyecto.</div>
            <?php else: ?>
                <div class="projects-grid">
                    <?php foreach ($grouped[$pn] as $project): ?>
                        <div class="project-card">
                            <img src="<?php echo htmlspecialchars($project['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($project['title']); ?>" 
                                 class="project-image">
                            <div class="project-controls">
                                <a href="?delete=<?php echo $project['id']; ?>" 
                                   class="btn-icon btn-delete"
                                   onclick="return confirm('¿Eliminar esta imagen del proyecto?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                            <div class="project-info">
                                <p><strong><?php echo htmlspecialchars($project['description'] ?: $project['title']); ?></strong></p>
                                <small><?php echo date('d/m/Y', strtotime($project['created_at'])); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (empty($projects)): ?>
            <div class="message">No hay imágenes registradas todavía.</div>
        <?php endif; ?>
    </div>
    
    <script>
    function editProject(id) {
        const newTitle = prompt('Nuevo título para el proyecto:');
        if (newTitle !== null) {
            fetch('process/update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    table: 'projects',
                    id: id,
                    field: 'title',
                    value: newTitle
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Proyecto actualizado');
                    location.reload();
                } else {
                    alert('Error al actualizar');
                }
            });
        }
    }
    </script>
</body>
</html>