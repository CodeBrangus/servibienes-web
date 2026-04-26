<?php
require_once '../config.php';

// Destruir sesión
session_start();
session_unset();
session_destroy();

// Eliminar cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirigir al login
header('Location: index.php');
exit();
?>