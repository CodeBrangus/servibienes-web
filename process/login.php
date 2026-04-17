<?php
require_once '../config.php';

// Redirigir si ya está logueado
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Verificar credenciales
    if ($username === ADMIN_USER && password_verify($password, ADMIN_PASS_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_token'] = bin2hex(random_bytes(32));
        
        // Redirigir al dashboard
        header('Location: ../admin/dashboard.php');
        exit();
    } else {
        $error = 'Usuario o contraseña incorrectos';
        header('Location: ../admin/index.php?error=' . urlencode($error));
        exit();
    }
}

// Si no es POST, redirigir al login
header('Location: ../admin/index.php');
exit();
?>