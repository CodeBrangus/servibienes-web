<?php
// process/upload_hero.php
// Sube la imagen principal (Hero) con compatibilidad de rutas:
// - assets/images/hero.jpg y assets/images/portada.jpg
// - imagenes/portada/heroe.jpg y imagenes/portada/portada.jpg

session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    exit('Acceso denegado');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

if (!isset($_FILES['hero']) || $_FILES['hero']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit('No se recibió el archivo o hubo un error al subirlo.');
}

$tmpPath = $_FILES['hero']['tmp_name'];
$originalName = $_FILES['hero']['name'] ?? 'hero';
$fileSize = (int)($_FILES['hero']['size'] ?? 0);

if ($fileSize <= 0) {
    http_response_code(400);
    exit('Archivo vacío.');
}

// Máximo 20MB (imagen principal)
$maxBytes = 20 * 1024 * 1024;
if ($fileSize > $maxBytes) {
    http_response_code(400);
    exit('El archivo es muy grande. Máximo 20MB.');
}

$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowedExt = ['jpg', 'jpeg'];
if (!in_array($ext, $allowedExt, true)) {
    http_response_code(400);
    exit('Formato no permitido. Usa JPG/JPEG (recomendado para web).');
}

$imgInfo = @getimagesize($tmpPath);
if ($imgInfo === false) {
    http_response_code(400);
    exit('El archivo no parece ser una imagen válida.');
}

// Guardar en assets/images
$destDirA = __DIR__ . '/../assets/images';
if (!is_dir($destDirA)) { @mkdir($destDirA, 0755, true); }
if (!is_dir($destDirA) || !is_writable($destDirA)) {
    http_response_code(500);
    exit('No se puede escribir en assets/images. Revisa permisos.');
}

$destA1 = $destDirA . '/hero.jpg';
$destA2 = $destDirA . '/portada.jpg';

if (!@move_uploaded_file($tmpPath, $destA1)) {
    http_response_code(500);
    exit('No se pudo guardar la imagen principal. Revisa permisos de assets/images.');
}
@chmod($destA1, 0644);
// Copia de compatibilidad
@copy($destA1, $destA2);
@chmod($destA2, 0644);

// Guardar copia en /imagenes/portada si existe
$destDirB = __DIR__ . '/../imagenes/portada';
if (!is_dir($destDirB)) { @mkdir($destDirB, 0755, true); }
if (is_dir($destDirB) && is_writable($destDirB)) {
    $destB1 = $destDirB . '/heroe.jpg';
    $destB2 = $destDirB . '/portada.jpg';
    @copy($destA1, $destB1);
    @copy($destA1, $destB2);
    @chmod($destB1, 0644);
    @chmod($destB2, 0644);
}

$back = $_SERVER['HTTP_REFERER'] ?? '/admin/settings.php';
header('Location: ' . $back);
exit;
