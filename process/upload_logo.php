<?php
// process/upload_logo.php
// Sube el logo con compatibilidad:
// - assets/images/logo.png (principal)
// - imagenes/portada/logotipo.png y imagenes/portada/logo.png (si existen)
// Compatible con hostings donde mime_content_type() no existe.

session_start();

// Solo admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    exit('Acceso denegado');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit('No se recibió el archivo o hubo un error al subirlo.');
}

$tmpPath = $_FILES['logo']['tmp_name'];
$originalName = $_FILES['logo']['name'] ?? 'logo';
$fileSize = (int)($_FILES['logo']['size'] ?? 0);

if ($fileSize <= 0) {
    http_response_code(400);
    exit('Archivo vacío.');
}

// Máximo 10MB para logo
$maxBytes = 10 * 1024 * 1024;
if ($fileSize > $maxBytes) {
    http_response_code(400);
    exit('El archivo es muy grande. Máximo 10MB.');
}

$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowedExt = ['png', 'jpg', 'jpeg', 'webp'];
if (!in_array($ext, $allowedExt, true)) {
    http_response_code(400);
    exit('Formato no permitido. Usa PNG, JPG, JPEG o WEBP.');
}

// Verifica que sea una imagen real
$imgInfo = @getimagesize($tmpPath);
if ($imgInfo === false) {
    http_response_code(400);
    exit('El archivo no parece ser una imagen válida.');
}

$destDir = __DIR__ . '/../assets/images';
if (!is_dir($destDir)) {
    @mkdir($destDir, 0755, true);
}

if (!is_dir($destDir) || !is_writable($destDir)) {
    http_response_code(500);
    exit('No se puede escribir en assets/images. Revisa permisos.');
}

$destPath = $destDir . '/logo.png';

if (!@move_uploaded_file($tmpPath, $destPath)) {
    http_response_code(500);
    exit('No se pudo guardar el logo. Revisa permisos de assets/images.');
}

@chmod($destPath, 0644);

// Copia a /imagenes/portada si existe
$destDirB = __DIR__ . '/../imagenes/portada';
if (!is_dir($destDirB)) { @mkdir($destDirB, 0755, true); }
if (is_dir($destDirB) && is_writable($destDirB)) {
    @copy($destPath, $destDirB . '/logotipo.png');
    @copy($destPath, $destDirB . '/logo.png');
    @chmod($destDirB . '/logotipo.png', 0644);
    @chmod($destDirB . '/logo.png', 0644);
}

$back = $_SERVER['HTTP_REFERER'] ?? '/admin/settings.php';
header('Location: ' . $back);
exit;
