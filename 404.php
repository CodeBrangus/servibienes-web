<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada - Servibienes</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #0b2340 0%, #082034 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
        }
        
        .error-container {
            padding: 40px;
            max-width: 600px;
        }
        
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #cfeefc;
            margin-bottom: 20px;
        }
        
        .error-message {
            font-size: 24px;
            margin-bottom: 30px;
            color: #bcd6ee;
        }
        
        .btn-home {
            background: #0f6fb1;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-home:hover {
            background: #0d5d9a;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <div class="error-message">Página no encontrada</div>
        <p>La página que estás buscando no existe o ha sido movida.</p>
        <a href="/" class="btn-home">Volver al inicio</a>
    </div>
</body>
</html>