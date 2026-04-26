<?php
require_once '../config.php';
require_once '../functions.php';

// Este endpoint recibe el formulario público de clientes (index.php)
// y guarda la información + archivos (imágenes/videos) en la tabla client_submissions.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

// Recoger datos del formulario
$data = [
    'name' => sanitize($_POST['name'] ?? ''),
    'phone' => sanitize($_POST['phone'] ?? ''),
    'email' => sanitize($_POST['email'] ?? ''),
    'operation' => sanitize($_POST['operation'] ?? ''),
    'property_type' => sanitize($_POST['property_type'] ?? ''),
    'location' => sanitize($_POST['location'] ?? ''),
    'price' => isset($_POST['price']) ? floatval($_POST['price']) : 0,
    'description' => sanitize($_POST['description'] ?? ''),
    'status' => 'pendiente'
];

// Validación mínima
if ($data['name'] === '' || $data['phone'] === '' || $data['operation'] === '' || $data['property_type'] === '') {
    header('Location: ../index.php?error=1#client-form-section');
    exit();
}

// Procesar imágenes subidas
$images = [];
if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
            continue;
        }

        $file = [
            'name' => $_FILES['images']['name'][$key],
            'type' => $_FILES['images']['type'][$key],
            'tmp_name' => $tmp_name,
            'error' => $_FILES['images']['error'][$key],
            'size' => $_FILES['images']['size'][$key]
        ];

        $result = uploadFile($file, 'image', 'clients');
        if (!empty($result['success'])) {
            $images[] = $result['path'];
        }
    }
}

// Procesar videos subidos
$videos = [];
if (isset($_FILES['videos']) && !empty($_FILES['videos']['name'][0])) {
    foreach ($_FILES['videos']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['videos']['error'][$key] !== UPLOAD_ERR_OK) {
            continue;
        }

        $file = [
            'name' => $_FILES['videos']['name'][$key],
            'type' => $_FILES['videos']['type'][$key],
            'tmp_name' => $tmp_name,
            'error' => $_FILES['videos']['error'][$key],
            'size' => $_FILES['videos']['size'][$key]
        ];

        $result = uploadFile($file, 'video', 'clients');
        if (!empty($result['success'])) {
            $videos[] = $result['path'];
        }
    }
}

$data['images'] = json_encode($images, JSON_UNESCAPED_SLASHES);
$data['videos'] = json_encode($videos, JSON_UNESCAPED_SLASHES);

try {
    dbInsert('client_submissions', $data);
    header('Location: ../index.php?success=1#client-form-section');
    exit();
} catch (Throwable $e) {
    // En producción no mostramos el error al público, solo redirigimos.
    header('Location: ../index.php?error=1#client-form-section');
    exit();
}
