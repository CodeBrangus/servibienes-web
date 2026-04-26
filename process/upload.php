<?php
require_once '../config.php';
requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit();
}

$type = $_POST['type'] ?? 'gallery';

function normalizeFileArray($files, $i) {
    return [
        'name' => $files['name'][$i] ?? '',
        'type' => $files['type'][$i] ?? '',
        'tmp_name' => $files['tmp_name'][$i] ?? '',
        'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
        'size' => $files['size'][$i] ?? 0,
    ];
}

function uploadMany($files, $kind, $subfolder) {
    $uploaded = [];
    $errors = [];

    if (!isset($files['tmp_name']) || !is_array($files['tmp_name'])) {
        return ['uploaded' => [], 'errors' => ['No se recibieron archivos']];
    }

    foreach ($files['tmp_name'] as $i => $_) {
        $file = normalizeFileArray($files, $i);
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            continue;
        }

        $result = uploadFile($file, $kind, $subfolder);
        if (!empty($result['success'])) {
            $uploaded[] = $result['path'];
        } else {
            $errors[] = $result['error'] ?? 'Error al subir archivo';
        }
    }

    return ['uploaded' => $uploaded, 'errors' => $errors];
}

try {
    switch ($type) {
        case 'gallery':
        case 'projects':
        case 'events': {
            $result = uploadMany($_FILES['files'] ?? [], 'image', $type);
            foreach ($result['uploaded'] as $path) {
                $table = $type === 'events' ? 'events' : $type;
                dbInsert($table, [
                    'image_url' => $path,
                    'title' => pathinfo($path, PATHINFO_FILENAME),
                    'is_active' => 1,
                ]);
            }
            echo json_encode([
                'success' => count($result['uploaded']) > 0,
                'count' => count($result['uploaded']),
                'files' => $result['uploaded'],
                'errors' => $result['errors'],
            ]);
            break;
        }

        case 'agents': {
            $result = uploadMany($_FILES['files'] ?? [], 'image', 'agents');
            foreach ($result['uploaded'] as $path) {
                dbInsert('agents', [
                    'photo_url' => $path,
                    'name' => 'Agente ' . date('YmdHis'),
                    'position' => 'Especialista',
                    'is_active' => 1,
                ]);
            }
            echo json_encode([
                'success' => count($result['uploaded']) > 0,
                'count' => count($result['uploaded']),
                'files' => $result['uploaded'],
                'errors' => $result['errors'],
            ]);
            break;
        }

        case 'videos': {
            $result = uploadMany($_FILES['files'] ?? [], 'video', 'videos');
            foreach ($result['uploaded'] as $path) {
                dbInsert('videos', [
                    'video_url' => $path,
                    'title' => pathinfo($path, PATHINFO_FILENAME),
                    'is_active' => 1,
                ]);
            }
            echo json_encode([
                'success' => count($result['uploaded']) > 0,
                'count' => count($result['uploaded']),
                'files' => $result['uploaded'],
                'errors' => $result['errors'],
            ]);
            break;
        }

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Tipo no válido']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
}
