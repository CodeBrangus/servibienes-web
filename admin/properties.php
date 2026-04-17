<?php
require_once '../config.php';
requireAdmin();

$message = '';
$error = '';
$current_category = $_GET['category'] ?? 'venta';

// Mensajes por redirect (evita reenvío del formulario al refrescar)
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') $message = 'Propiedad agregada correctamente';
    if ($_GET['msg'] === 'deleted') $message = 'Propiedad eliminada correctamente';
}

// Categorías disponibles
$categories = ['venta', 'alquiler', 'anticretico'];

// Procesar formulario de nueva propiedad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_property'])) {
    // Asegurar que la pestaña activa se mantenga incluso si el POST viene sin querystring.
    if (!empty($_POST['category'])) {
        $current_category = $_POST['category'];
    }
    $data = [
        'category' => $_POST['category'],
        'subcategory' => $_POST['subcategory'],
        'title' => trim($_POST['title']),
        'description' => trim($_POST['description']),
        'price' => trim($_POST['price']),
        'departamento' => $_POST['departamento'],
        'provincia' => $_POST['provincia'],
        'location' => trim($_POST['location']),
        'is_active' => 1
    ];
    
    // Subir imagen si existe
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $result = uploadFile($_FILES['image'], 'image', 'properties');
        if ($result['success']) {
            $data['image_url'] = $result['path'];
        }
    }
    
    try {
        $id = dbInsert('properties', $data);
        // Redirect: asegura que la nueva propiedad se vea al recargar y evita duplicados.
        header('Location: properties.php?category=' . urlencode($data['category']) . '&msg=added');
        exit;
    } catch (Exception $e) {
        $error = "Error al agregar propiedad: " . $e->getMessage();
    }
}

// Procesar eliminación
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $property = dbSelect('properties', '*', 'id = ?', [$id])[0] ?? null;
    
    if ($property) {
        // Eliminar imagen si existe
        if (!empty($property['image_url'])) {
            $filepath = BASE_PATH . '/' . $property['image_url'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        
        dbDelete('properties', 'id = ?', [$id]);
        header('Location: properties.php?category=' . urlencode($current_category) . '&msg=deleted');
        exit;
    }
}

// Obtener propiedades por categoría
$properties = dbSelect('properties', '*', 'category = ?', [$current_category], 'created_at DESC');

// Estadísticas
$total_properties = dbSelect('properties', 'COUNT(*) as total')[0]['total'];
$active_properties = dbSelect('properties', 'COUNT(*) as total', 'is_active = 1')[0]['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propiedades - Panel Admin</title>
    <style>
        <?php include '../assets/css/style.css'; ?>
        
        .admin-content {
            padding: 20px;
        }
        
        .category-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }
        
        .category-tab {
            padding: 10px 20px;
            background: #f5f5f5;
            border: none;
            border-radius: 5px 5px 0 0;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .category-tab.active {
            background: #0f6fb1;
            color: white;
        }
        
        .property-form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .property-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
        }
        
        .property-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .property-info {
            padding: 15px;
        }
        
        .property-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .property-price {
            color: #0f6fb1;
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 10px;
        }
        
        .property-meta {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .property-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-action {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            flex: 1;
            text-align: center;
            text-decoration: none;
            color: white;
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
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-content">
        <h1>Gestión de Propiedades</h1>
        
        <?php if ($message): ?>
        <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Tabs de categorías -->
        <div class="category-tabs">
            <?php foreach ($categories as $cat): ?>
            <button class="category-tab <?php echo $cat === $current_category ? 'active' : ''; ?>"
                    onclick="window.location.href='?category=<?php echo $cat; ?>'">
                Propiedades en <?php echo ucfirst($cat); ?>
            </button>
            <?php endforeach; ?>
        </div>
        
        <!-- Formulario para agregar propiedad -->
        <div class="property-form">
            <h2>Agregar Nueva Propiedad (<?php echo ucfirst($current_category); ?>)</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="category" value="<?php echo $current_category; ?>">
                <input type="hidden" name="add_property" value="1">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Título *</label>
                        <input type="text" name="title" required placeholder="Ej: Casa moderna en zona residencial">
                    </div>
                    
                    <div class="form-group">
                        <label>Precio *</label>
                        <input type="text" name="price" required 
                               placeholder="<?php echo $current_category === 'venta' ? 'USD 150,000' : 
                                             ($current_category === 'alquiler' ? 'USD 500/mes' : 'USD 10,000 garantía'); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Departamento *</label>
                        <select name="departamento" required onchange="updateProvincias(this.value, 'provincia')">
                            <option value="">Seleccione departamento</option>
                            <option value="santacruz">Santa Cruz</option>
                            <option value="lapaz">La Paz</option>
                            <option value="cochabamba">Cochabamba</option>
                            <option value="oruro">Oruro</option>
                            <option value="potosi">Potosí</option>
                            <option value="tarija">Tarija</option>
                            <option value="chuquisaca">Chuquisaca</option>
                            <option value="beni">Beni</option>
                            <option value="pando">Pando</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Provincia *</label>
                        <select name="provincia" id="provincia" required>
                            <option value="">Seleccione provincia</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Tipo de propiedad *</label>
                        <select name="subcategory" required>
                            <option value="">Seleccione tipo</option>
                            <option value="casa">Casa</option>
                            <option value="departamento">Departamento</option>
                            <option value="terreno">Terreno</option>
                            <option value="local">Local Comercial</option>
                            <option value="oficina">Oficina</option>
                            <option value="monoambiente">Monoambiente</option>
                            <option value="habitacion">Habitación</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label>Ubicación *</label>
                        <input type="text" name="location" id="property_location" required
                               autocomplete="off" spellcheck="false"
                               placeholder="Ej: Barrio Equipetrol, Calle lugones">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label>Descripción *</label>
                        <textarea name="description" required rows="4" 
                                  placeholder="Describa la propiedad, características, amenities..."></textarea>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Imagen de la propiedad</label>
                        <input type="file" name="image" accept="image/*">
                    </div>
                </div>
                
                <button type="submit" class="btn">Agregar Propiedad</button>
            </form>
        </div>
        
        <!-- Lista de propiedades -->
        <h2>Propiedades en <?php echo ucfirst($current_category); ?> (<?php echo count($properties); ?>)</h2>
        
        <?php if (empty($properties)): ?>
        <div class="message">No hay propiedades en esta categoría</div>
        <?php else: ?>
        <div class="properties-grid">
            <?php foreach ($properties as $property): ?>
            <div class="property-card">
                <?php if (!empty($property['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($property['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($property['title']); ?>" 
                     class="property-image">
                <?php else: ?>
                <div class="property-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-home" style="font-size: 50px; color: #ccc;"></i>
                </div>
                <?php endif; ?>
                
                <div class="property-info">
                    <div class="property-title"><?php echo htmlspecialchars($property['title']); ?></div>
                    <div class="property-price"><?php echo htmlspecialchars($property['price']); ?></div>
                    
                    <div class="property-meta">
                        <span><?php echo htmlspecialchars($property['subcategory']); ?></span>
                        <span><?php echo htmlspecialchars($property['departamento']); ?></span>
                    </div>
                    
                    <p style="font-size: 14px; color: #666; margin-bottom: 10px;">
                        <?php echo htmlspecialchars($property['location']); ?>
                    </p>
                    
                    <p style="font-size: 13px; color: #888; line-height: 1.4;">
                        <?php echo substr(htmlspecialchars($property['description']), 0, 100); ?>...
                    </p>
                    
                    <div class="property-actions">
                        <a href="?category=<?php echo $current_category; ?>&edit=<?php echo $property['id']; ?>" 
                           class="btn-action btn-edit">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="?category=<?php echo $current_category; ?>&delete=<?php echo $property['id']; ?>" 
                           class="btn-action btn-delete"
                           onclick="return confirm('¿Eliminar esta propiedad?')">
                            <i class="fas fa-trash"></i> Eliminar
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    // Datos de provincias por departamento
    const provinciasData = {
        santacruz: ["Andrés Ibáñez", "Warnes", "Sara", "Ichilo", "Chiquitos", "Santiesteban"],
        lapaz: ["Murillo", "Omasuyos", "Pacajes", "Camacho", "Muñecas", "Larecaja"],
        cochabamba: ["Cercado", "Campero", "Ayopaya", "Esteban Arce", "Arani", "Arque"],
        oruro: ["Cercado", "Carangas", "Sajama", "Litoral", "Poopó", "Pantaleón Dalence"],
        potosi: ["Tomas Frías", "Rafael Bustillo", "Cornelio Saavedra", "Chayanta", "Charcas"],
        tarija: ["Cercado", "Arce", "Gran Chaco", "Aviles", "Mendez", "Burdet O'Connor"],
        chuquisaca: ["Oropeza", "Azurduy", "Zudáñez", "Tomina", "Hernando Siles", "Yamparáez"],
        beni: ["Cercado", "Vaca Díez", "Yacuma", "Moxos", "Marbán", "Mamoré"],
        pando: ["Nicolás Suárez", "Manuripi", "Madre de Dios", "Abuná", "Federico Román"]
    };
    
    function updateProvincias(departamento, selectId) {
        const select = document.getElementById(selectId);
        select.innerHTML = '<option value="">Seleccione provincia</option>';
        
        if (departamento && provinciasData[departamento]) {
            provinciasData[departamento].forEach(provincia => {
                const option = document.createElement('option');
                option.value = provincia.toLowerCase().replace(/ /g, '-');
                option.textContent = provincia;
                select.appendChild(option);
            });
        }
    }
    </script>
</body>
</html>