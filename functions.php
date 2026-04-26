<?php
require_once 'config.php';

// Escapa URL y corrige espacios en rutas (archivos subidos con espacios desde cPanel)
function hurl($url) {
    $url = (string)$url;
    $url = str_replace(' ', '%20', $url);
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}

// ============================================
// FUNCIONES PARA OBTENER DATOS
// ============================================

// Función para obtener galería
function getGallery($limit = null) {
    $sql = "SELECT * FROM gallery WHERE is_active = 1 ORDER BY display_order, created_at DESC";
    
    if ($limit !== null) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    return dbSelect('gallery', '*', 'is_active = 1', [], 'display_order, created_at DESC', $limit);
}

// Función para obtener proyectos
function getProjects($limit = null) {
    return dbSelect('projects', '*', 'is_active = 1', [], 'display_order, created_at DESC', $limit);
}

// Sección independiente: Nuestros Proyectos Destacados
function getFeaturedProjects($limit = null) {
    return dbSelect('featured_projects', '*', 'is_active = 1', [], 'display_order, created_at DESC', $limit);
}

// Función para obtener videos
function getVideos($limit = null) {
    return dbSelect('videos', '*', 'is_active = 1', [], 'display_order, created_at DESC', $limit);
}

// Función para obtener agentes
function getAgents($limit = null) {
    return dbSelect('agents', '*', 'is_active = 1', [], 'display_order, created_at ASC', $limit);
}

// Función para obtener propiedades por categoría
function getPropertiesByCategory($category, $limit = null) {
    return dbSelect('properties', '*', 'category = ? AND is_active = 1', [$category], 'created_at DESC', $limit);
}

// Función para obtener todas las propiedades
function getAllProperties($limit = null) {
    return dbSelect('properties', '*', 'is_active = 1', [], 'created_at DESC', $limit);
}

// Función para obtener formularios de clientes
function getClientSubmissions($limit = null, $status = null) {
    $where = '';
    $params = [];
    
    if ($status !== null) {
        $where = 'status = ?';
        $params[] = $status;
    }
    
    $results = dbSelect('client_submissions', '*', $where, $params, 'submitted_at DESC', $limit);
    
    // Decodificar JSON de imágenes y videos
    foreach ($results as &$row) {
        $row['images'] = json_decode($row['images'], true) ?: [];
        $row['videos'] = json_decode($row['videos'], true) ?: [];
    }
    
    return $results;
}

// Función para obtener un solo elemento por ID
function getItemById($table, $id) {
    $results = dbSelect($table, '*', 'id = ?', [$id], '', 1);
    return !empty($results) ? $results[0] : null;
}

// ============================================
// FUNCIONES CRUD (Crear, Leer, Actualizar, Eliminar)
// ============================================

// Función para agregar contenido (alias de dbInsert)
function addContent($table, $data) {
    return dbInsert($table, $data);
}

// Función para eliminar contenido (alias de dbDelete)
function deleteContent($table, $id) {
    return dbDelete($table, 'id = ?', [$id]);
}

// Función para actualizar contenido (alias de dbUpdate)
function updateContent($table, $id, $data) {
    return dbUpdate($table, $data, 'id = ?', [$id]);
}

// Función para contar elementos
function countItems($table, $where = '', $params = []) {
    $sql = "SELECT COUNT(*) as total FROM $table";
    
    if (!empty($where)) {
        $sql .= " WHERE $where";
    }
    
    $stmt = dbQuery($sql, $params);
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['total'] ?? 0;
}

// ============================================
// FUNCIONES PARA FORMULARIOS Y VISTAS
// ============================================

// Función para generar select de provincias
function getProvinciasOptions($departamento) {
    $provincias = [
        'santacruz' => [
            "Andrés Ibáñez", "Warnes", "Sara", "Ichilo", "Chiquitos", 
            "Santiesteban", "Ñuflo de Chávez", "Velasco", "Guarayos", 
            "Cordillera", "Vallegrande", "Florida", "Ángel Sandoval", 
            "Germán Busch", "Manuel María Caballero"
        ],
        'lapaz' => [
            "Murillo", "Omasuyos", "Pacajes", "Camacho", "Muñecas", 
            "Larecaja", "Franz Tamayo", "Ingavi", "Loayza", "Inquisivi", 
            "Sud Yungas", "Los Andes", "Aroma", "Nor Yungas", "Abel Iturralde", 
            "Bautista Saavedra", "Manco Kapac", "Gualberto Villarroel", "José Manuel Pando", 
            "Caranavi"
        ],
        'cochabamba' => [
            "Cercado", "Campero", "Ayopaya", "Esteban Arce", "Arani", 
            "Arque", "Capinota", "Germán Jordán", "Quillacollo", "Chapare", 
            "Tapacarí", "Carrasco", "Mizque", "Punata", "Bolívar", 
            "Tiraque"
        ],
        'oruro' => [
            "Cercado", "Carangas", "Sajama", "Litoral", "Poopó", 
            "Pantaleón Dalence", "Ladislao Cabrera", "Saucarí", "Tomas Barrón", 
            "Sur Carangas", "San Pedro de Totora", "Sebastián Pagador", "Eduardo Avaroa", 
            "Mejillones", "Nor Carangas", "Challapata"
        ],
        'potosi' => [
            "Tomas Frías", "Rafael Bustillo", "Cornelio Saavedra", "Chayanta", 
            "Charcas", "Nor Chichas", "Alonso de Ibáñez", "Sur Chichas", 
            "Nor Lípez", "Sur Lípez", "José María Linares", "Antonio Quijarro", 
            "General Bilbao", "Daniel Campos", "Modesto Omiste", "Enrique Baldivieso"
        ],
        'tarija' => [
            "Cercado", "Arce", "Gran Chaco", "Aviles", "Mendez", "Burdet O'Connor"
        ],
        'chuquisaca' => [
            "Oropeza", "Azurduy", "Zudáñez", "Tomina", "Hernando Siles", 
            "Yamparáez", "Nor Cinti", "Sud Cinti", "Belisario Boeto", "Luis Calvo"
        ],
        'beni' => [
            "Cercado", "Vaca Díez", "Yacuma", "Moxos", "Marbán", 
            "Mamoré", "Iténez", "Ballivián"
        ],
        'pando' => [
            "Nicolás Suárez", "Manuripi", "Madre de Dios", "Abuná", "Federico Román"
        ]
    ];
    
    if (isset($provincias[$departamento])) {
        $options = '';
        foreach ($provincias[$departamento] as $provincia) {
            $value = strtolower(str_replace(' ', '-', $provincia));
            $options .= "<option value=\"$value\">$provincia</option>";
        }
        return $options;
    }
    
    return '<option value="">Seleccione provincia</option>';
}

// Función para obtener categorías de propiedades
function getPropertyCategories() {
    return [
        'venta' => 'Venta',
        'alquiler' => 'Alquiler',
        'anticretico' => 'Anticrético'
    ];
}

// Función para mostrar alertas
function showAlert($type, $message) {
    $classes = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    
    $class = $classes[$type] ?? 'alert-info';
    
    return "
    <div class=\"alert $class alert-dismissible fade show\" role=\"alert\">
        $message
        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
    </div>
    ";
}

// Función para formatear precio
function formatPrice($price) {
    if (empty($price)) return 'Consultar';
    
    if (is_numeric($price)) {
        return 'Bs. ' . number_format($price, 2, ',', '.');
    }
    
    return $price;
}

// Función para truncar texto
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    return $text . '...';
}

// Función para validar email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Función para sanitizar entrada
function cleanInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Función para redireccionar
function redirect($url) {
    header("Location: $url");
    exit();
}

// Función para verificar si es admin
function checkAdminAccess() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        redirect('/admin/login.php');
    }
}

// Función para generar token CSRF
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función para verificar token CSRF
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>