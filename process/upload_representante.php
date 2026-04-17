<?php
require_once '../config.php';
requireAdmin();

// Redirigir de vuelta al inicio
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : (SITE_URL . '/');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect);
    exit();
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    header('Location: ' . $redirect . (strpos($redirect, '?') !== false ? '&' : '?') . 'error=1');
    exit();
}

$file = $_FILES['photo'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ALLOWED_IMAGE_TYPES, true)) {
    header('Location: ' . $redirect . (strpos($redirect, '?') !== false ? '&' : '?') . 'error=1');
    exit();
}

if ($file['size'] > MAX_IMAGE_SIZE) {
    header('Location: ' . $redirect . (strpos($redirect, '?') !== false ? '&' : '?') . 'error=1');
    exit();
}

// Asegurar directorio
$dir = UPLOAD_BASE . 'agents/';
if (!is_dir($dir)) {
    @mkdir($dir, 0775, true);
}

$targetAbs = $dir . 'representante.jpg';
$targetRel = 'assets/uploads/agents/representante.jpg';

// Guardar como JPG fijo para que siempre se encuentre
$ok = false;

// Si ya es jpg/jpeg, mover directo
if (in_array($ext, ['jpg','jpeg'], true)) {
    $ok = move_uploaded_file($file['tmp_name'], $targetAbs);
} else {
    // Convertir a JPG (png/webp/gif)
    $img = null;
    if ($ext === 'png') {
        $img = @imagecreatefrompng($file['tmp_name']);
    } elseif ($ext === 'gif') {
        $img = @imagecreatefromgif($file['tmp_name']);
    } elseif ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
        $img = @imagecreatefromwebp($file['tmp_name']);
    }

    if ($img) {
        // Fondo blanco por si hay transparencia
        $w = imagesx($img); $h = imagesy($img);
        $bg = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($bg, 255, 255, 255);
        imagefill($bg, 0, 0, $white);
        imagecopy($bg, $img, 0, 0, 0, 0, $w, $h);
        $ok = imagejpeg($bg, $targetAbs, 90);
        imagedestroy($bg);
        imagedestroy($img);
    }
}

if ($ok) {
    // Evitar cache agresivo
    @chmod($targetAbs, 0644);

    // Copia de compatibilidad para estructura /imagenes
    $destDirB = BASE_PATH . '/imagenes/agentes';
    if (!is_dir($destDirB)) { @mkdir($destDirB, 0775, true); }
    if (is_dir($destDirB) && is_writable($destDirB)) {
        @copy($targetAbs, $destDirB . '/representante.jpg');
        @chmod($destDirB . '/representante.jpg', 0644);
    }

    header('Location: ' . $redirect . (strpos($redirect, '?') !== false ? '&' : '?') . 'success=2');
} else {
    header('Location: ' . $redirect . (strpos($redirect, '?') !== false ? '&' : '?') . 'error=1');
}

exit();
