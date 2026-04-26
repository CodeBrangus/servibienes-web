<?php
require_once '../config.php';
requireAdmin();

$message = '';
$error = '';

// Estados disponibles
$statuses = ['pendiente', 'contactado', 'procesado'];
$current_status = $_GET['status'] ?? 'pendiente';

// Cambiar estado
if (isset($_GET['change_status'])) {
    $id = intval($_GET['id']);
    $new_status = $_GET['new_status'] ?? 'contactado';
    
    if (in_array($new_status, $statuses)) {
        dbUpdate('client_submissions', ['status' => $new_status], 'id = ?', [$id]);
        $message = "Estado actualizado a: " . ucfirst($new_status);
    }
}

// Eliminar formulario
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $submission = dbSelect('client_submissions', '*', 'id = ?', [$id])[0] ?? null;
    
    if ($submission) {
        // Eliminar imágenes
        $images = json_decode($submission['images'] ?? '[]', true);
        foreach ($images as $image) {
            $filepath = BASE_PATH . '/' . $image;
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        
        // Eliminar videos
        $videos = json_decode($submission['videos'] ?? '[]', true);
        foreach ($videos as $video) {
            $filepath = BASE_PATH . '/' . $video;
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        
        dbDelete('client_submissions', 'id = ?', [$id]);
        $message = "Formulario eliminado correctamente";
    }
}

// Ver detalles
$view_id = $_GET['view'] ?? null;
$view_submission = null;
if ($view_id) {
    $view_submission = dbSelect('client_submissions', '*', 'id = ?', [$view_id])[0] ?? null;
    if ($view_submission) {
        $view_submission['images'] = json_decode($view_submission['images'] ?? '[]', true);
        $view_submission['videos'] = json_decode($view_submission['videos'] ?? '[]', true);
    }
}

// Obtener formularios
$submissions = dbSelect('client_submissions', '*', 
                       $current_status !== 'todos' ? 'status = ?' : '', 
                       $current_status !== 'todos' ? [$current_status] : [], 
                       'submitted_at DESC');

// Estadísticas
$total_submissions = dbSelect('client_submissions', 'COUNT(*) as total')[0]['total'];
$pending_count = dbSelect('client_submissions', 'COUNT(*) as total', "status = 'pendiente'")[0]['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Panel Admin</title>
    <style>
        <?php include '../assets/css/style.css'; ?>
        
        .admin-content {
            padding: 20px;
        }
        
        .status-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
            flex-wrap: wrap;
        }
        
        .status-tab {
            padding: 10px 20px;
            background: #f5f5f5;
            border: none;
            border-radius: 5px 5px 0 0;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        
        .status-tab.active {
            background: #0f6fb1;
            color: white;
        }
        
        .status-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .submissions-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .submissions-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .submissions-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .submissions-table tr:hover {
            background: #f9f9f9;
        }
        
        .status-indicator {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pendiente {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-contactado {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-procesado {
            background: #d4edda;
            color: #155724;
        }
        
        .btn-action {
            padding: 5px 10px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
            text-decoration: none;
            display: inline-block;
            color: white;
        }
        
        .btn-view {
            background: #3498db;
        }
        
        .btn-contact {
            background: #2ecc71;
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
        
        /* Modal de detalles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            padding: 20px;
        }
        
        .modal-content {
            background: white;
            border-radius: 15px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 20px;
            background: #0f6fb1;
            color: white;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-group {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            color: #333;
        }
        
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .media-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .media-item img, .media-item video {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
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
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #0f6fb1;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-content">
        <h1>Formularios de Clientes</h1>
        
        <?php if ($message): ?>
        <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Estadísticas -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_submissions; ?></div>
                <div>Total de Formularios</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pending_count; ?></div>
                <div>Pendientes</div>
            </div>
        </div>
        
        <!-- Tabs de estados -->
        <div class="status-tabs">
            <button class="status-tab <?php echo $current_status === 'todos' ? 'active' : ''; ?>"
                    onclick="window.location.href='?status=todos'">
                Todos
            </button>
            
            <?php foreach ($statuses as $status): 
                $count = dbSelect('client_submissions', 'COUNT(*) as total', "status = '$status'")[0]['total'];
            ?>
            <button class="status-tab <?php echo $current_status === $status ? 'active' : ''; ?>"
                    onclick="window.location.href='?status=<?php echo $status; ?>'">
                <?php echo ucfirst($status); ?>
                <?php if ($count > 0): ?>
                <span class="status-badge"><?php echo $count; ?></span>
                <?php endif; ?>
            </button>
            <?php endforeach; ?>
        </div>
        
        <!-- Tabla de formularios -->
        <div class="submissions-table">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Propiedad</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px;">
                            No hay formularios en este estado
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($submissions as $sub): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($sub['name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($sub['email']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($sub['phone']); ?></td>
                        <td>
                            <?php echo ucfirst($sub['operation']); ?> - 
                            <?php echo htmlspecialchars($sub['property_type']); ?><br>
                            <small><?php echo htmlspecialchars($sub['location']); ?></small>
                        </td>
                        <td>$<?php echo number_format($sub['price'], 2); ?> USD</td>
                        <td>
                            <span class="status-indicator status-<?php echo $sub['status']; ?>">
                                <?php echo ucfirst($sub['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($sub['submitted_at'])); ?></td>
                        <td>
                            <a href="?view=<?php echo $sub['id']; ?>" class="btn-action btn-view">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            <?php if ($sub['status'] === 'pendiente'): ?>
                            <a href="?change_status&id=<?php echo $sub['id']; ?>&new_status=contactado" 
                               class="btn-action btn-contact">
                                <i class="fas fa-check"></i>
                            </a>
                            <?php endif; ?>
                            
                            <a href="?delete=<?php echo $sub['id']; ?>" 
                               class="btn-action btn-delete"
                               onclick="return confirm('¿Eliminar este formulario?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal de detalles -->
    <?php if ($view_submission): ?>
    <div class="modal-overlay" id="detailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 style="margin: 0;">Detalles del Formulario</h2>
                <button class="close-btn" onclick="closeModal()">×</button>
            </div>
            
            <div class="modal-body">
                <!-- Información principal -->
                <div class="info-grid">
                    <div class="info-group">
                        <div class="info-label">Nombre completo</div>
                        <div class="info-value"><?php echo htmlspecialchars($view_submission['name']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Teléfono</div>
                        <div class="info-value"><?php echo htmlspecialchars($view_submission['phone']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($view_submission['email']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Operación</div>
                        <div class="info-value"><?php echo ucfirst($view_submission['operation']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Tipo de propiedad</div>
                        <div class="info-value"><?php echo htmlspecialchars($view_submission['property_type']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Precio estimado</div>
                        <div class="info-value">$<?php echo number_format($view_submission['price'], 2); ?> USD</div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Ubicación</div>
                        <div class="info-value"><?php echo htmlspecialchars($view_submission['location']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Estado</div>
                        <div class="info-value">
                            <span class="status-indicator status-<?php echo $view_submission['status']; ?>">
                                <?php echo ucfirst($view_submission['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Fecha de envío</div>
                        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($view_submission['submitted_at'])); ?></div>
                    </div>
                </div>
                
                <!-- Descripción -->
                <div class="info-group" style="grid-column: 1 / -1;">
                    <div class="info-label">Descripción de la propiedad</div>
                    <div class="info-value" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 10px;">
                        <?php echo nl2br(htmlspecialchars($view_submission['description'])); ?>
                    </div>
                </div>
                
                <!-- Imágenes -->
                <?php if (!empty($view_submission['images'])): ?>
                <div class="info-group" style="grid-column: 1 / -1;">
                    <div class="info-label">Imágenes (<?php echo count($view_submission['images']); ?>)</div>
                    <div class="media-grid">
                        <?php foreach ($view_submission['images'] as $image): ?>
                        <div class="media-item">
                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                 alt="Imagen propiedad">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Videos -->
                <?php if (!empty($view_submission['videos'])): ?>
                <div class="info-group" style="grid-column: 1 / -1;">
                    <div class="info-label">Videos (<?php echo count($view_submission['videos']); ?>)</div>
                    <div class="media-grid">
                        <?php foreach ($view_submission['videos'] as $video): ?>
                        <div class="media-item">
                            <video controls>
                                <source src="<?php echo htmlspecialchars($video); ?>" type="video/mp4">
                            </video>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Acciones -->
                <div style="display: flex; gap: 15px; margin-top: 30px; justify-content: center;">
                    <?php if ($view_submission['status'] !== 'procesado'): ?>
                    <a href="?change_status&id=<?php echo $view_submission['id']; ?>&new_status=procesado" 
                       class="btn-action btn-contact" style="padding: 10px 20px;">
                        <i class="fas fa-check-circle"></i> Marcar como Procesado
                    </a>
                    <?php endif; ?>
                    
                    <a href="https://wa.me/591<?php echo preg_replace('/[^0-9]/', '', $view_submission['phone']); ?>" 
                       target="_blank" class="btn-action btn-view" style="padding: 10px 20px;">
                        <i class="fab fa-whatsapp"></i> Contactar por WhatsApp
                    </a>
                    
                    <a href="mailto:<?php echo htmlspecialchars($view_submission['email']); ?>" 
                       class="btn-action btn-view" style="padding: 10px 20px;">
                        <i class="fas fa-envelope"></i> Enviar Email
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
    function closeModal() {
        window.location.href = 'clients.php?status=<?php echo $current_status; ?>';
    }
    
    // Cerrar modal con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    
    // Cerrar modal al hacer clic fuera
    document.getElementById('detailsModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    </script>
</body>
</html>