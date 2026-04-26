<?php
require_once '../config.php';
requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit();
}

$table = $_POST['table'] ?? '';
$id = intval($_POST['id'] ?? 0);

if (empty($table) || $id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit();
}

// Obtener información del elemento
$item = dbSelect($table, '*', 'id = ?', [$id])[0] ?? null;

if (!$item) {
    echo json_encode(['success' => false, 'error' => 'Elemento no encontrado']);
    exit();
}

// Eliminar archivos físicos si existen
$file_fields = ['image_url', 'photo_url', 'video_url'];
foreach ($file_fields as $field) {
    if (!empty($item[$field])) {
        $filepath = BASE_PATH . '/' . $item[$field];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
}

// Eliminar imágenes/videos de clientes
if ($table === 'client_submissions') {
    $images = json_decode($item['images'] ?? '[]', true);
    foreach ($images as $image) {
        $filepath = BASE_PATH . '/' . $image;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
    
    $videos = json_decode($item['videos'] ?? '[]', true);
    foreach ($videos as $video) {
        $filepath = BASE_PATH . '/' . $video;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
}

// Eliminar de la base de datos
$result = dbDelete($table, 'id = ?', [$id]);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al eliminar']);
}
?>