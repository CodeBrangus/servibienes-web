<?php
require_once '../config.php';
requireAdmin();

// Obtener estadísticas
$stats = [
    'submissions' => dbSelect('client_submissions', 'COUNT(*) as total')[0]['total'] ?? 0,
    'properties' => dbSelect('properties', 'COUNT(*) as total', 'is_active = 1')[0]['total'] ?? 0,
    'gallery' => dbSelect('gallery', 'COUNT(*) as total', 'is_active = 1')[0]['total'] ?? 0,
    'videos' => dbSelect('videos', 'COUNT(*) as total', 'is_active = 1')[0]['total'] ?? 0,
    'agents' => dbSelect('agents', 'COUNT(*) as total', 'is_active = 1')[0]['total'] ?? 0,
    'projects' => dbSelect('projects', 'COUNT(*) as total', 'is_active = 1')[0]['total'] ?? 0,
    'featured_projects' => dbSelect('featured_projects', 'COUNT(*) as total', 'is_active = 1')[0]['total'] ?? 0
];

// Obtener formularios recientes
$recent_submissions = dbSelect('client_submissions', '*', '', [], 'submitted_at DESC', '5');

// Obtener propiedades recientes
$recent_properties = dbSelect('properties', '*', 'is_active = 1', [], 'created_at DESC', '3');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Servibienes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #0b2340 0%, #082034 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .admin-header h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .dashboard-container {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .welcome-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .welcome-section h2 {
            color: #0b2340;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .welcome-section p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            border-left: 5px solid #0f6fb1;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-card i {
            font-size: 40px;
            color: #0f6fb1;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #0b2340;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 16px;
        }
        
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .module-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
            border: 2px solid transparent;
        }
        
        .module-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border-color: #0f6fb1;
            color: #0f6fb1;
        }
        
        .module-card i {
            font-size: 50px;
            color: #0f6fb1;
            margin-bottom: 20px;
        }
        
        .module-card h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }
        
        .module-card p {
            color: #777;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .recent-activity {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .recent-activity h2 {
            color: #0b2340;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #0f6fb1;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            background: #0f6fb1;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 18px;
        }
        
        .activity-info {
            flex: 1;
        }
        
        .activity-info strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .activity-time {
            color: #888;
            font-size: 13px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #888;
        }
        
        .empty-state i {
            font-size: 50px;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .property-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
        }
        
        .property-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .property-info {
            padding: 20px;
        }
        
        .property-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .property-price {
            color: #0f6fb1;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .property-location {
            color: #666;
            font-size: 14px;
        }
        
        .section-title {
            color: #0b2340;
            margin: 40px 0 20px 0;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .view-all {
            text-align: center;
            margin-top: 20px;
        }
        
        .view-all-btn {
            display: inline-block;
            padding: 10px 25px;
            background: #0f6fb1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .view-all-btn:hover {
            background: #0d5a96;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 20px;
            }
            
            .admin-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .modules-grid {
                grid-template-columns: 1fr;
            }
            
            .properties-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="admin-header">
        <h1><i class="fas fa-home"></i> Panel de Administración - Servibienes</h1>
        <div class="user-info">
            <span>Bienvenido, <?php echo $_SESSION['admin_username'] ?? 'Administrador'; ?></span>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>
    </div>
    
    <!-- Contenido Principal -->
    <div class="dashboard-container">
        <!-- Mensaje de Bienvenida -->
        <div class="welcome-section">
            <h2>👋 ¡Bienvenido al Panel de Control!</h2>
            <p>Desde aquí puedes gestionar todo el contenido de tu sitio web: propiedades, galerías, agentes y formularios de clientes.</p>
        </div>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-clipboard-list"></i>
                <div class="stat-number"><?php echo $stats['submissions']; ?></div>
                <div class="stat-label">Formularios Recibidos</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-building"></i>
                <div class="stat-number"><?php echo $stats['properties']; ?></div>
                <div class="stat-label">Propiedades Activas</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-images"></i>
                <div class="stat-number"><?php echo $stats['gallery']; ?></div>
                <div class="stat-label">Imágenes en Galería</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-video"></i>
                <div class="stat-number"><?php echo $stats['videos']; ?></div>
                <div class="stat-label">Videos Subidos</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-number"><?php echo $stats['agents']; ?></div>
                <div class="stat-label">Agentes Registrados</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-project-diagram"></i>
                <div class="stat-number"><?php echo $stats['projects']; ?></div>
                <div class="stat-label">Proyectos</div>
            </div>

            <div class="stat-card">
                <i class="fas fa-star"></i>
                <div class="stat-number"><?php echo $stats['featured_projects']; ?></div>
                <div class="stat-label">Proyectos Destacados (Sección)</div>
            </div>
        </div>
        
        <!-- Módulos del Sistema -->
        <div class="modules-grid">
            <a href="properties.php" class="module-card">
                <i class="fas fa-home"></i>
                <h3>Gestión de Propiedades</h3>
                <p>Administra propiedades en venta, alquiler y anticrético</p>
            </a>
            
            <a href="settings.php" class="module-card">
                <div class="module-icon" style="background: rgba(52, 152, 219, 0.12); color: #3498db;">
                    <i class="fa-solid fa-gear"></i>
                </div>
                <div class="module-info">
                    <h3>Configuración</h3>
                    <p>Cambiar logo y foto del representante</p>
                </div>
            </a>

            <a href="settings.php#hero" class="module-card">
                <i class="fa-solid fa-image"></i>
                <h3>Portada (Inicio)</h3>
                <p>Cambia la imagen principal de portada sin afectar el contraste</p>
            </a>

            
            <a href="gallery.php" class="module-card">
                <i class="fas fa-images"></i>
                <h3>Galería de Imágenes</h3>
                <p>Sube y gestiona imágenes para la galería del sitio</p>
            </a>
            
            <a href="videos.php" class="module-card">
                <i class="fas fa-video"></i>
                <h3>Videos de Proyectos</h3>
                <p>Administra videos de propiedades y proyectos</p>
            </a>
            
            <a href="agents.php" class="module-card">
                <i class="fas fa-users"></i>
                <h3>Agentes Inmobiliarios</h3>
                <p>Gestiona el equipo de agentes y sus perfiles</p>
            </a>
            
            <a href="clients.php" class="module-card">
                <i class="fas fa-user-friends"></i>
                <h3>Formularios de Clientes</h3>
                <p>Revisa y gestiona solicitudes de clientes</p>
            </a>
            
            <a href="featured_projects.php" class="module-card">
                <i class="fas fa-star"></i>
                <h3>Proyectos Destacados (Sección)</h3>
                <p>Sube imágenes independientes para “Nuestros Proyectos Destacados” (sin límite)</p>
            </a>

            <a href="projects.php" class="module-card">
                <i class="fas fa-project-diagram"></i>
                <h3>Imágenes de Proyectos</h3>
                <p>Sube imágenes por proyecto para el carrusel interno de cada proyecto</p>
            </a>

            <a href="project_names.php" class="module-card">
                <i class="fas fa-pen-to-square"></i>
                <h3>Editar nombres de proyectos</h3>
                <p>Agrega nuevos proyectos o renombra los existentes</p>
            </a>

            
            <a href="sections.php" class="module-card">
                <i class="fas fa-list"></i>
                <h3>Secciones del menú</h3>
                <p>Edita los nombres del menú y agrega nuevas secciones (IDs)</p>
            </a>

<a href="events.php" class="module-card">
                <i class="fas fa-calendar-star"></i>
                <h3>Nuevos Eventos (Galería)</h3>
                <p>Agrega y edita eventos debajo de “Ofertas por Categoría”</p>
            </a>

<a href="offers.php" class="module-card">
                <i class="fas fa-tags"></i>
                <h3>Ofertas por Categoría</h3>
                <p>Sube imágenes independientes para Ventas, Anticréticos y Alquileres</p>
            </a>
        </div>
        
        <!-- Propiedades Recientes -->
        <?php if (!empty($recent_properties)): ?>
        <h2 class="section-title"><i class="fas fa-star"></i> Propiedades Recientes</h2>
        
        <div class="properties-grid">
            <?php foreach ($recent_properties as $property): ?>
            <div class="property-card">
                <?php if (!empty($property['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($property['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($property['title']); ?>" 
                     class="property-image">
                <?php else: ?>
                <div class="property-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-home" style="font-size: 60px; color: #ccc;"></i>
                </div>
                <?php endif; ?>
                
                <div class="property-info">
                    <div class="property-title"><?php echo htmlspecialchars($property['title']); ?></div>
                    <div class="property-price"><?php echo htmlspecialchars($property['price']); ?></div>
                    <div class="property-location">
                        <?php echo htmlspecialchars($property['departamento']) . ' - ' . htmlspecialchars($property['provincia']); ?>
                    </div>
                    <small style="color: #888; display: block; margin-top: 10px;">
                        <?php echo date('d/m/Y', strtotime($property['created_at'])); ?>
                    </small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="view-all">
            <a href="properties.php" class="view-all-btn">Ver Todas las Propiedades</a>
        </div>
        <?php endif; ?>
        
        <!-- Actividad Reciente -->
        <div class="recent-activity">
            <h2><i class="fas fa-history"></i> Formularios Recientes</h2>
            
            <div class="activity-list">
                <?php if (empty($recent_submissions)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No hay formularios recibidos recientemente</p>
                </div>
                <?php else: ?>
                <?php foreach ($recent_submissions as $submission): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="activity-info">
                        <strong><?php echo htmlspecialchars($submission['name']); ?></strong>
                        <span>Nuevo formulario de <?php echo $submission['operation']; ?> - <?php echo $submission['property_type']; ?></span>
                        <div class="activity-time">
                            <?php echo date('d/m/Y H:i', strtotime($submission['submitted_at'])); ?> - 
                            <span style="color: <?php 
                                echo $submission['status'] === 'pendiente' ? '#e74c3c' : 
                                    ($submission['status'] === 'contactado' ? '#3498db' : '#2ecc71'); 
                            ?>;">
                                <?php echo ucfirst($submission['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($recent_submissions)): ?>
            <div class="view-all" style="margin-top: 20px;">
                <a href="clients.php" class="view-all-btn">Ver Todos los Formularios</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    // Actualizar la página cada 60 segundos para ver nueva actividad
    setTimeout(function() {
        window.location.reload();
    }, 60000);
    
    // Mostrar notificación de bienvenida
    window.onload = function() {
        console.log('Panel de administración cargado correctamente');
    };
    </script>
</body>
</html>