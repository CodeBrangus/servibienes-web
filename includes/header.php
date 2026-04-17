<?php
// Verificar si estamos en el panel admin
$is_admin_page = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;

if ($is_admin_page && (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Panel Admin - Servibienes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos específicos para el header del panel admin */
        .admin-header {
            background: linear-gradient(135deg, #0b2340 0%, #082034 100%);
            color: white;
            padding: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .admin-header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-logo-img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: linear-gradient(135deg, #cfeefc, #0f6fb1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0b2340;
            font-weight: bold;
        }
        
        .admin-nav {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .admin-nav a {
            color: #bcd6ee;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .admin-nav a:hover, .admin-nav a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.1);
            padding: 8px 15px;
            border-radius: 20px;
        }
        
        .admin-user i {
            font-size: 14px;
        }
        
        .notification-badge {
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            margin-left: 5px;
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
            
            .admin-nav {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #0b2340;
                flex-direction: column;
                padding: 20px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }
            
            .admin-nav.active {
                display: flex;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="admin-header-content">
            <div class="admin-logo">
                <div class="admin-logo-img">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 18px;">Servibienes Admin</h3>
                    <small style="color: #bcd6ee;">Panel de Administración</small>
                </div>
            </div>
            
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            
            <nav class="admin-nav" id="adminNav">
                <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="clients.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'clients.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Clientes
                    <?php
                    $pending_count = dbSelect('client_submissions', 'COUNT(*) as total', "status = 'pendiente'")[0]['total'] ?? 0;
                    if ($pending_count > 0): ?>
                    <span class="notification-badge"><?php echo $pending_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="properties.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'properties.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Propiedades
                </a>
                <a href="gallery.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'gallery.php' ? 'active' : ''; ?>">
                    <i class="fas fa-images"></i> Galería
                </a>
                <a href="events.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'events.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-star"></i> Eventos
                </a>
                <a href="../index.php" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Ver Sitio
                </a>
            </nav>
            
            <div class="admin-user">
                <i class="fas fa-user-circle"></i>
                <span><?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></span>
                <a href="logout.php" style="color: #e74c3c; margin-left: 10px;" 
                   onclick="return confirm('¿Cerrar sesión?')">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </header>
    
    <script>
    // Toggle menu móvil
    document.getElementById('mobileMenuBtn').addEventListener('click', function() {
        document.getElementById('adminNav').classList.toggle('active');
    });
    
    // Cerrar menu al hacer clic fuera
    document.addEventListener('click', function(event) {
        const nav = document.getElementById('adminNav');
        const btn = document.getElementById('mobileMenuBtn');
        
        if (!nav.contains(event.target) && !btn.contains(event.target)) {
            nav.classList.remove('active');
        }
    });
    </script>
</body>
</html>