<?php
require_once '../config.php';
requireAdmin();

$message = '';
$error = '';

// Procesar nuevo agente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_agent'])) {
    $data = [
        'name' => trim($_POST['name']),
        'position' => trim($_POST['position']),
        'phone' => trim($_POST['phone']),
        'email' => trim($_POST['email']),
        'is_active' => 1
    ];
    
    // Subir foto si existe
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $result = uploadFile($_FILES['photo'], 'image', 'agents');
        if ($result['success']) {
            $data['photo_url'] = $result['path'];
        }
    }
    
    try {
        $id = dbInsert('agents', $data);
        $message = "Agente agregado correctamente";
    } catch (Exception $e) {
        $error = "Error al agregar agente: " . $e->getMessage();
    }
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_agent'])) {
    $id = intval($_POST['id']);
    $data = [
        'name' => trim($_POST['name']),
        'position' => trim($_POST['position']),
        'phone' => trim($_POST['phone']),
        'email' => trim($_POST['email'])
    ];
    
    // Actualizar foto si se subió nueva
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $result = uploadFile($_FILES['photo'], 'image', 'agents');
        if ($result['success']) {
            $data['photo_url'] = $result['path'];
        }
    }
    
    dbUpdate('agents', $data, 'id = ?', [$id]);
    $message = "Agente actualizado correctamente";
}

// Procesar eliminación
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $agent = dbSelect('agents', '*', 'id = ?', [$id])[0] ?? null;
    
    if ($agent && !empty($agent['photo_url'])) {
        $filepath = BASE_PATH . '/' . $agent['photo_url'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
    
    dbDelete('agents', 'id = ?', [$id]);
    $message = "Agente eliminado correctamente";
}

// Obtener todos los agentes
$agents = dbSelect('agents', '*', '', [], 'name ASC');

// Modo edición
$edit_agent = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_agent = dbSelect('agents', '*', 'id = ?', [$edit_id])[0] ?? null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agentes - Panel Admin</title>
    <style>
        <?php include '../assets/css/style.css'; ?>
        
        .admin-content {
            padding: 20px;
        }
        
        .agent-form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .photo-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e0e0e0;
            margin-bottom: 15px;
        }
        
        .agents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .agent-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            position: relative;
        }
        
        .agent-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .agent-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e0e0e0;
            margin: 0 auto 20px;
        }
        
        .agent-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        
        .agent-position {
            color: #0f6fb1;
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .agent-contact {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .agent-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
        }
        
        .btn-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            transition: transform 0.3s;
        }
        
        .btn-icon:hover {
            transform: scale(1.1);
        }
        
        .btn-edit {
            background: #3498db;
        }
        
        .btn-delete {
            background: #e74c3c;
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
        <h1>Gestión de Agentes</h1>
        
        <?php if ($message): ?>
        <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Estadísticas -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($agents); ?></div>
                <div>Total de Agentes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($agents, fn($a) => $a['is_active'])); ?></div>
                <div>Agentes Activos</div>
            </div>
        </div>
        
        <!-- Formulario de agente -->
        <div class="agent-form">
            <h2><?php echo $edit_agent ? 'Editar Agente' : 'Agregar Nuevo Agente'; ?></h2>
            
            <form method="POST" enctype="multipart/form-data" id="agentForm">
                <?php if ($edit_agent): ?>
                <input type="hidden" name="update_agent" value="1">
                <input type="hidden" name="id" value="<?php echo $edit_agent['id']; ?>">
                
                <!-- Vista previa de la foto actual -->
                <?php if (!empty($edit_agent['photo_url'])): ?>
                <div style="text-align: center;">
                    <img src="<?php echo htmlspecialchars($edit_agent['photo_url']); ?>" 
                         class="photo-preview" id="currentPhoto">
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <input type="hidden" name="add_agent" value="1">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre completo *</label>
                        <input type="text" name="name" required 
                               value="<?php echo $edit_agent ? htmlspecialchars($edit_agent['name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Cargo/Posición *</label>
                        <input type="text" name="position" required 
                               value="<?php echo $edit_agent ? htmlspecialchars($edit_agent['position']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Teléfono *</label>
                        <input type="tel" name="phone" required 
                               value="<?php echo $edit_agent ? htmlspecialchars($edit_agent['phone']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" 
                               value="<?php echo $edit_agent ? htmlspecialchars($edit_agent['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Foto del agente</label>
                        <input type="file" name="photo" accept="image/*" 
                               onchange="previewPhoto(this)">
                        <small>Recomendado: 400x400px, formato JPG o PNG</small>
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <button type="submit" class="btn">
                        <?php echo $edit_agent ? 'Actualizar Agente' : 'Agregar Agente'; ?>
                    </button>
                    
                    <?php if ($edit_agent): ?>
                    <a href="agents.php" class="btn" style="background: #666; margin-left: 10px;">
                        Cancelar
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Lista de agentes -->
        <h2>Agentes Registrados (<?php echo count($agents); ?>)</h2>
        
        <?php if (empty($agents)): ?>
        <div class="message">No hay agentes registrados</div>
        <?php else: ?>
        <div class="agents-grid">
            <?php foreach ($agents as $agent): ?>
            <div class="agent-card">
                <div class="agent-actions">
                    <a href="?edit=<?php echo $agent['id']; ?>" class="btn-icon btn-edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="?delete=<?php echo $agent['id']; ?>" class="btn-icon btn-delete"
                       onclick="return confirm('¿Eliminar este agente?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
                
                <?php if (!empty($agent['photo_url'])): ?>
                <img src="<?php echo htmlspecialchars($agent['photo_url']); ?>" 
                     alt="<?php echo htmlspecialchars($agent['name']); ?>" 
                     class="agent-photo">
                <?php else: ?>
                <div class="agent-photo" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user-tie" style="font-size: 40px; color: #ccc;"></i>
                </div>
                <?php endif; ?>
                
                <div class="agent-name"><?php echo htmlspecialchars($agent['name']); ?></div>
                <div class="agent-position"><?php echo htmlspecialchars($agent['position']); ?></div>
                
                <div class="agent-contact">
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($agent['phone']); ?></p>
                    <?php if (!empty($agent['email'])): ?>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($agent['email']); ?></p>
                    <?php endif; ?>
                </div>
                
                <div style="margin-top: 15px;">
                    <span class="badge <?php echo $agent['is_active'] ? 'badge-success' : 'badge-secondary'; ?>">
                        <?php echo $agent['is_active'] ? 'Activo' : 'Inactivo'; ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    function previewPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Crear o actualizar vista previa
                let preview = document.getElementById('photoPreview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.id = 'photoPreview';
                    preview.className = 'photo-preview';
                    preview.style.display = 'block';
                    preview.style.margin = '0 auto 15px';
                    input.parentNode.insertBefore(preview, input.nextSibling);
                }
                preview.src = e.target.result;
                
                // Ocultar foto actual si estamos editando
                const currentPhoto = document.getElementById('currentPhoto');
                if (currentPhoto) {
                    currentPhoto.style.display = 'none';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Validar formulario
    document.getElementById('agentForm').addEventListener('submit', function(e) {
        const name = this.querySelector('input[name="name"]').value.trim();
        const position = this.querySelector('input[name="position"]').value.trim();
        const phone = this.querySelector('input[name="phone"]').value.trim();
        
        if (!name || !position || !phone) {
            e.preventDefault();
            alert('Por favor complete todos los campos obligatorios (*)');
            return false;
        }
        
        return true;
    });
    </script>
</body>
</html>