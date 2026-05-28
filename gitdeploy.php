<?php
$token = 'servibienes2026deploy';

if (!isset($_GET['token']) || $_GET['token'] !== $token) {
    http_response_code(403);
    die('Acceso denegado');
}

$output = shell_exec('cd /home/servibie/public_html && git fetch --all && git reset --hard origin/main 2>&1');
echo '<pre>' . $output . '</pre>';
