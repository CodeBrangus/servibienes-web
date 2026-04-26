<?php
require_once 'config.php';
require_once 'functions.php';

// Verificar si hay mensajes de éxito o error
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Obtener datos de la base de datos - ORDENAR POR id EN VEZ DE created_at
$gallery = dbSelect('gallery', '*', 'is_active = 1', [], 'display_order, id DESC', '130');
$projectsAll = dbSelect('projects', '*', 'is_active = 1', [], 'display_order, id DESC', '500');
$featuredProjects = dbSelect('featured_projects', '*', 'is_active = 1', [], 'display_order, id DESC', '50');
$videos = dbSelect('videos', '*', 'is_active = 1', [], 'display_order, id DESC', '20');
$events = dbSelect('events', '*', 'is_active = 1', [], 'display_order ASC, id DESC');
$agents = dbSelect('agents', '*', 'is_active = 1', [], 'display_order, name ASC');
$properties_venta = dbSelect('properties', '*', 'category = "venta" AND is_active = 1', [], 'id DESC', '8');
$properties_alquiler = dbSelect('properties', '*', 'category = "alquiler" AND is_active = 1', [], 'id DESC', '8');
$properties_anticretico = dbSelect('properties', '*', 'category = "anticretico" AND is_active = 1', [], 'id DESC', '8');

// Contadores para estadísticas
$total_properties = count($properties_venta) + count($properties_alquiler) + count($properties_anticretico);

// Obtener formularios de clientes para admin
$client_submissions = [];
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    $client_submissions = dbSelect('client_submissions', '*', '', [], 'id DESC', '50');
}

// Configurar constantes para traducciones
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inmobiliaria Servibienes S.R.L. - Tu Hogar Ideal en Bolivia</title>
    <meta name="description" content="Servibienes - Encuentra tu propiedad ideal en Bolivia. Especialistas en venta, alquiler y anticrético de casas, departamentos y terrenos. Asesoría legal garantizada.">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

    <style>
        <?php include 'assets/css/style.css'; ?>

        /* Botón / icono "Ir a inicio" en cada sección */
        section[id] {
            position: relative;
        }

        .to-top-btn {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: rgba(11, 35, 64, .92);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .25);
            border: 1px solid rgba(255, 255, 255, .12);
            z-index: 5;
        }

        .to-top-btn:hover {
            transform: translateY(-1px);
        }

        /* Eventos (galería) */
        .events-section {
            margin-top: 28px;
        }

        .events-grid-front {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 18px;
            margin-top: 18px;
        }

        .event-card-front {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .20);
        }

        .event-card-img img {
            width: 100%;
            height: 180px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.06);
            display: block;
        }

        .event-card-body {
            padding: 14px 14px 18px;
        }

        .event-card-title {
            font-weight: 900;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .event-card-desc {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }

        @media (max-width: 560px) {
            .event-card-img img {
                height: 150px;
            }
        }

        /* Fix móvil: padding del contenedor principal para que NO se recorte el representante */
        @media (max-width: 560px) {
            .site {
                padding: 16px;
                margin: 10px auto;
            }
        }

        /* Foto representante */
        .rep-photo {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 18px;
            display: block;
            border: 3px solid rgba(255, 255, 255, 0.12);
        }

        .rep-photo-placeholder {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            border: 3px dashed rgba(255, 255, 255, 0.12);
        }

        .rep-photo-placeholder i {
            font-size: 52px;
            color: var(--muted);
        }

        /* Ocultar barra superior de Google Translate */
        .goog-te-banner-frame.skiptranslate {
            display: none !important;
        }

        body {
            top: 0 !important;
        }


        :root {
            --navy: #0b2340;
            --sky: #cfeefc;
            --accent: #0f6fb1;
            --gold: #ffd700;
            --card-bg: rgba(255, 255, 255, 0.04);
            --glass: rgba(255, 255, 255, 0.06);
            --text: #e9f2fb;
            --muted: #bcd6ee;
            --radius: 16px;
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            font-family: 'Roboto', Arial, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            background: linear-gradient(135deg, var(--navy) 0%, #082034 50%, #041826 100%);
            color: var(--text);
            scroll-behavior: smooth;
            overflow-x: hidden;
        }

        .site {
            max-width: 1400px;
            margin: 20px auto;
            padding: 30px;
            border-radius: 24px;
            background: linear-gradient(165deg, rgba(255, 255, 255, 0.02) 0%, rgba(255, 255, 255, 0.01) 100%);
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }

        /* ========== PANEL DE ADMINISTRADOR ========== */
        .admin-panel {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 10000;
        }

        .admin-btn {
            background: var(--gold);
            color: var(--navy);
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .admin-btn:hover {
            background: #ffed4e;
            transform: scale(1.05);
        }

        .admin-login {
            background: rgba(11, 35, 64, 0.95);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10001;
            display: none;
        }

        .admin-login.active {
            display: flex;
        }

        .login-box {
            background: var(--navy);
            padding: 40px;
            border-radius: 20px;
            width: 400px;
            max-width: 90%;
            border: 2px solid var(--accent);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }

        .login-box h3 {
            text-align: center;
            margin-bottom: 25px;
            color: var(--sky);
        }

        .login-box input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid var(--accent);
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .login-box button {
            width: 100%;
            padding: 12px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 8px;
            margin-top: 15px;
            cursor: pointer;
            font-weight: bold;
        }

        .admin-content {
            background: rgba(11, 35, 64, 0.98);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow-y: auto;
            z-index: 10002;
            padding: 20px;
            display: none;
        }

        .admin-content.active {
            display: block;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--accent);
        }

        .admin-section {
            background: rgba(255, 255, 255, 0.05);
            padding: 25px;
            border-radius: 16px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-section h3 {
            color: var(--sky);
            margin-bottom: 20px;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .submission-card {
            background: rgba(255, 255, 255, 0.03);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .submission-card h4 {
            color: var(--text);
            margin-bottom: 10px;
        }

        .submission-card p {
            color: var(--muted);
            margin: 5px 0;
            font-size: 14px;
        }

        .submission-images {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .submission-images img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .admin-controls {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-admin {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-view {
            background: var(--accent);
            color: white;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        /* ========== FORMULARIO CLIENTES ========== */
        .client-form-section {
            margin-top: 40px;
            padding: 30px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .client-form-section h3 {
            color: var(--sky);
            margin-top: 0;
            font-size: 24px;
            margin-bottom: 25px;
            text-align: center;
        }

        .form-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .form-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-field {
            display: flex;
            flex-direction: column;
        }

        .form-field label {
            margin-bottom: 8px;
            color: var(--muted);
            font-size: 14px;
        }

        .form-field input,
        .form-field select,
        .form-field textarea {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
            font-size: 15px;
        }

        .form-field textarea {
            min-height: 120px;
            resize: vertical;
        }

        .upload-area {
            border: 2px dashed rgba(255, 255, 255, 0.2);
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-area:hover {
            border-color: var(--accent);
            background: rgba(255, 255, 255, 0.02);
        }

        .upload-area i {
            font-size: 40px;
            margin-bottom: 15px;
            color: var(--muted);
        }

        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .preview-item {
            position: relative;
            width: 100px;
            height: 100px;
        }

        .preview-item img,
        .preview-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .remove-preview {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 12px;
        }

        .submit-btn {
            background: var(--accent);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            width: 100%;
        }

        .submit-btn:hover {
            background: var(--sky);
            color: var(--navy);
            transform: translateY(-2px);
        }

        /* Header Mejorado */
        header {
            display: flex;
            align-items: center;
            gap: 25px;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .brand {
            display: flex;
            gap: 20px;
            align-items: center;
            flex: 1;
        }

        .logo-container {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--sky), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            box-shadow: 0 8px 25px rgba(11, 172, 255, 0.3);
            transition: transform 0.3s ease;
        }

        .logo-container:hover {
            transform: scale(1.05);
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 10px;
        }

        .brand-text h1 {
            margin: 0;
            font-size: 28px;
            background: linear-gradient(135deg, var(--sky), var(--text));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }

        .brand-text p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 15px;
            font-weight: 300;
        }

        .contact-header {
            text-align: right;
        }

        .contact-header .muted {
            display: block;
            margin-bottom: 5px;
            font-size: 13px;
        }

        .contact-header div {
            font-size: 15px;
            font-weight: 500;
        }

        /* Language Selector */
        .language-selector {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 100;
        }

        .language-selector select {
            background: var(--accent);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .language-selector select option {
            background: var(--navy);
            color: white;
        }

        .translator-label {
            display: block;
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 4px;
            text-align: center;
        }

        /* Hero Section Mejorada */

        /* PORTADA (según diseño) */
        .hero-banner {
            position: relative;
            width: 100%;
            min-height: 100vh;
            background-size: cover;
            background-position: center;
            overflow: hidden;
            margin-top: 0;
            border-radius: 0;
            display: flex;
            flex-direction: column;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            /* Imagen visible + oscurecido solo hacia abajo */
            background: linear-gradient(180deg, rgba(0, 0, 0, .05), rgba(4, 25, 45, .86));
            pointer-events: none;
        }

        .hero-topbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 18px;
            background: rgba(255, 255, 255, .92);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(0, 0, 0, .06);
        }

        /* Asegura que al navegar con #hash el header fijo no tape el título */
        section[id] {
            scroll-margin-top: 96px;
        }

        .hero-menu a,
        .hero-dropbtn {
            color: #102a43;
            font-weight: 900;
        }

        /* Lineas de encabezado (menu) */
        .hero-menu a,
        .hero-dropbtn {
            position: relative;
            padding: 10px 10px;
            border-radius: 10px;
        }

        .hero-menu a::after,
        .hero-dropbtn::after {
            content: '';
            position: absolute;
            left: 10px;
            right: 10px;
            bottom: 4px;
            height: 3px;
            background: #0b2340;
            border-radius: 3px;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .18s ease;
            opacity: .9;
        }

        .hero-menu a:hover::after,
        .hero-menu a.active::after,
        .hero-dropbtn:hover::after,
        .hero-dropdown.open .hero-dropbtn::after {
            transform: scaleX(1);
        }

        .hero-btn {
            background: #0b2340;
            border-radius: 12px;
            padding: 10px 14px;
            font-weight: 900;
        }

        .hero-btn-secondary {
            background: transparent;
            color: #0b2340;
            border: 2px solid rgba(11, 35, 64, .35);
        }

        .hero-cta {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .hero-overlay {
            position: relative;
            flex: 1;
            display: flex;
            align-items: flex-end;
            padding: 60px 18px;
            z-index: 2;
        }

        .hero-overlay-inner h2 {
            margin: 0;
            font-size: clamp(34px, 4vw, 58px);
            line-height: 1.05;
            text-shadow: 0 10px 30px rgba(0, 0, 0, .55);
            font-weight: 950;
        }

        .hero-overlay-inner p {
            max-width: 760px;
            margin-top: 10px;
            text-shadow: 0 10px 30px rgba(0, 0, 0, .55);
            font-size: clamp(14px, 1.6vw, 18px);
            opacity: .95;
        }

        @media (max-width: 980px) {
            .hero-overlay {
                padding: 52px 18px;
            }
        }

        @media (max-width: 860px) {
            .hero-topbar {
                flex-wrap: wrap;
                justify-content: space-between;
            }

            .hero-cta {
                width: auto;
            }
        }


        /* Dropdown Proyectos (funciona en móvil y escritorio) */
        .hero-menu {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .hero-dropdown {
            position: relative;
        }

        .hero-dropbtn {
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 8px 10px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 16px;
        }

        .hero-dropmenu {
            position: absolute;
            top: calc(100%);
            left: 0;
            min-width: 260px;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 14px;
            padding: 8px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, .15);
            max-height: 60vh;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            display: none;
            z-index: 2000;
        }

        .hero-dropmenu a {
            display: block;
            padding: 10px 12px;
            border-radius: 10px;
            color: #102a43;
            text-decoration: none;
            font-weight: 800;
        }

        .hero-dropmenu a:hover {
            background: rgba(11, 35, 64, .06);
        }

        .hero-dropdown.open .hero-dropmenu {
            display: block;
        }

        @media (max-width: 860px) {
            .hero-dropmenu {
                position: static;
                box-shadow: none;
                border: none;
                padding: 0;
                margin-top: 6px;
                background: transparent;
                display: none;
                min-width: auto;
            }

            .hero-dropdown.open .hero-dropmenu {
                display: block;
            }

            .hero-dropmenu a {
                background: rgba(255, 255, 255, .9);
                border: 1px solid rgba(0, 0, 0, .06);
                margin: 6px 0;
            }
        }

        /* Mini-carrusel dentro de cada proyecto (thumbnails) */
        .project-block-thumbs {
            overflow: hidden;
        }

        .thumb-track {
            display: flex;
            gap: 10px;
            transition: transform .6s ease;
            will-change: transform;
        }

        .thumb-track img {
            width: 140px;
            height: 100px;
            object-fit: cover;
            border-radius: 14px;
            flex-shrink: 0;
        }

        @media (max-width: 560px) {
            .thumb-track img {
                width: 120px;
                height: 88px;
            }
        }

        /* Representante legal: ancho controlado y centrado (sin romper CSS) */
        .rep {
            box-sizing: border-box;
        }

        @media (max-width: 980px) {

            /* cuando pasa a 1 columna, centramos items */
            .below-hero {
                justify-items: center;
            }

            .rep {
                max-width: 520px;
                width: 100%;
                margin-left: auto;
                margin-right: auto;
            }
        }

        @media (max-width: 560px) {
            .rep {
                padding: 16px;
                border-radius: 18px;
                max-width: 420px;
                width: calc(100% - 24px);
                margin-left: auto;
                margin-right: auto;
            }

            /* En móvil mostrar foto completa (sin recorte) */
            .rep-photo {
                width: 100%;
                max-width: 340px;
                height: auto;
                border-radius: 14px;
                object-fit: contain;
                background: rgba(0, 0, 0, 0.25);
                border: 3px solid rgba(255, 255, 255, 0.12);
                margin: 0 auto 14px;
                display: block;
            }

            .rep-photo-placeholder {
                width: 100%;
                max-width: 340px;
                height: 220px;
                border-radius: 14px;
                background: rgba(255, 255, 255, 0.06);
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 14px;
                border: 3px dashed rgba(255, 255, 255, 0.12);
            }
        }

        @media (max-width: 420px) {
            .rep {
                padding: 14px;
                border-radius: 16px;
                max-width: 380px;
                width: calc(100% - 20px);
                margin-left: auto;
                margin-right: auto;
            }

            .rep-photo {
                max-width: 300px;
            }

            .rep-photo-placeholder {
                max-width: 300px;
                height: 200px;
            }

            .rep h3 {
                font-size: 18px;
            }

            .rep p {
                font-size: 13px;
            }
        }



        /* Ocultar botón Admin en sitio público */
        .admin-panel {
            display: none !important;
        }

        /* Ajuste extra: representante centrado siempre */
        .rep {
            margin-left: auto;
            margin-right: auto;
        }

        /* DEBAJO DE LA PORTADA: carrusel + representante
           Fix definitivo: por defecto 1 columna (móvil). En pantallas grandes se muestra 2 columnas.
           Esto evita recortes si el navegador móvil usa viewport “desktop”. */
        .below-hero {
            margin-top: 18px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
            align-items: stretch;
            width: 100%;
            max-width: 100%;
        }

        .below-left {
            min-width: 0;
            max-width: 100%;
        }

        @media (min-width: 981px) {
            .below-hero {
                grid-template-columns: 1fr 400px;
            }
        }

        /* Anti-desborde horizontal en móviles */
        html,
        body {
            max-width: 100%;
            overflow-x: hidden;
        }

        .rep {
            justify-self: center;
            width: 100%;
            max-width: 520px;
        }


        .hero {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
            margin-top: 40px;
            align-items: start;
            position: relative;
            padding: 10px;
            border-radius: 24px;
            background-size: cover;
            background-position: center;
            overflow: hidden;
        }

        /* Marca de agua desactivada */
        .hero>* {
            position: relative;
            z-index: 1;
        }

        .intro {
            padding: 35px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03) 0%, rgba(255, 255, 255, 0.01) 100%);
            border: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
            overflow: hidden;
        }

        .intro::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--sky));
        }

        .intro h2 {
            margin: 0 0 15px 0;
            font-size: 36px;
            font-weight: 600;
            line-height: 1.2;
            background: linear-gradient(135deg, var(--text), var(--sky));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .intro p {
            color: var(--muted);
            line-height: 1.7;
            font-size: 16px;
            margin-bottom: 25px;
        }

        /* Gallery Slider Mejorado - 250x250px */
        .slider-container {
            position: relative;
            overflow: hidden;
            width: 100%;
            max-width: 520px;
            margin-left: auto;
            margin-right: auto;
            margin-top: 25px;
            border-radius: 16px;
            height: 270px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.02);
        }

        .slider-track {
            display: flex;
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            align-items: center;
            height: 100%;
        }

        .gallery-item {
            position: relative;
            width: 100% !important;
            height: 100% !important;
            flex-shrink: 0;
            margin-right: 0;
        }

        .slider-track img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 12px;
            transition: all 0.4s ease;
            border: 2px solid transparent;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            background-color: white;
        }

        .slider-track img:hover {
            transform: scale(1.05);
            border-color: var(--accent);
            box-shadow: 0 8px 30px rgba(11, 172, 255, 0.3);
        }

        .gallery-delete-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 3;
        }

        .gallery-delete-btn:hover {
            background: rgba(255, 0, 0, 0.9);
            transform: scale(1.1);
        }

        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: var(--accent);
            border: none;
            color: #fff;
            font-size: 20px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            opacity: 0.9;
            z-index: 2;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .slider-btn:hover {
            opacity: 1;
            transform: translateY(-50%) scale(1.1);
            background: var(--sky);
            color: var(--navy);
        }

        .slider-prev {
            left: 15px;
        }

        .slider-next {
            right: 15px;
        }

        /* GALERÍA DE PROYECTOS */
        .projects-gallery-section {
            margin-top: 40px;
            padding: 30px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .projects-gallery-section h3 {
            color: var(--sky);
            margin-top: 0;
            font-size: 24px;
            margin-bottom: 25px;
            text-align: center;
        }

        .projects-gallery-container {
            position: relative;
            overflow: hidden;
            margin-top: 25px;
            border-radius: 16px;
            height: 270px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.02);
        }

        .projects-track {
            display: flex;
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            align-items: center;
            height: 100%;
        }

        .project-item {
            position: relative;
            width: 250px !important;
            height: 250px !important;
            flex-shrink: 0;
            margin-right: 10px;
        }

        .projects-track img {
            width: 100%;
            height: 100%;
            /* Mostrar la imagen completa (sin recorte) */
            object-fit: contain;
            border-radius: 12px;
            transition: all 0.4s ease;
            border: 2px solid transparent;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.9);
        }

        .projects-track img:hover {
            transform: scale(1.05);
            border-color: var(--gold);
            box-shadow: 0 8px 30px rgba(255, 215, 0, 0.3);
        }

        .project-delete-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 3;
        }

        .project-delete-btn:hover {
            background: rgba(255, 0, 0, 0.9);
            transform: scale(1.1);
        }

        .projects-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: var(--gold);
            border: none;
            color: var(--navy);
            font-size: 20px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            opacity: 0.9;
            z-index: 2;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .projects-btn:hover {
            opacity: 1;
            transform: translateY(-50%) scale(1.1);
            background: #ffed4e;
            color: var(--navy);
        }

        .projects-prev {
            left: 15px;
        }

        .projects-next {
            right: 15px;
        }

        /* SUBIDOR DE VIDEOS */
        .videos-section {
            margin-top: 40px;
            padding: 30px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .videos-section h3 {
            color: var(--sky);
            margin-top: 0;
            font-size: 24px;
            margin-bottom: 25px;
            text-align: center;
        }

        .videos-section p {
            color: var(--muted);
            text-align: center;
            margin-bottom: 20px;
        }

        .dropzone {
            border: 2px dashed rgba(255, 255, 255, 0.2);
            padding: 30px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .dropzone:hover {
            border-color: var(--accent);
            background: rgba(255, 255, 255, 0.04);
        }

        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .video-card {
            background: rgba(255, 255, 255, 0.02);
            padding: 10px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        video {
            width: 100%;
            border-radius: 10px;
        }

        .remove {
            background: rgba(255, 0, 0, 0.7);
            color: white;
            padding: 4px 8px;
            font-size: 12px;
            border: none;
            cursor: pointer;
            float: right;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .remove:hover {
            background: rgba(255, 0, 0, 0.9);
            transform: scale(1.1);
        }

        /* Stats Section */
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
            padding: 25px;
            border-radius: 16px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            background: linear-gradient(135deg, var(--sky), var(--accent));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
        }

        /* Representante Mejorado */
        .rep {
            padding: 30px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
            border-radius: 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
        }

        .rep::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), #ff6b00);
        }

        .rep img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
        }

        .rep-photo {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .rep-photo-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            margin-right: auto;
        }

        .rep-photo-placeholder i {
            font-size: 52px;
            color: var(--muted);
        }

        .rep h3 {
            margin: 10px 0 5px;
            font-size: 22px;
            font-weight: 600;
        }

        .rep p {
            margin: 8px 0;
            color: var(--muted);
            font-size: 15px;
        }

        .rep-badge {
            background: linear-gradient(135deg, var(--gold), #ff6b00);
            color: var(--navy);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            margin-top: 10px;
        }

        /* ✅ FIX DEFINITIVO (móvil): mostrar representante completo y evitar que la tarjeta se vaya fuera de pantalla
           Nota: estos estilos deben ir DESPUÉS de los estilos base para sobreescribirlos en móvil. */
        @media (max-width: 980px) {
            .rep {
                max-width: 520px;
                width: 100%;
                margin-left: auto;
                margin-right: auto;
            }
        }

        @media (max-width: 560px) {
            .rep {
                padding: 16px;
                border-radius: 18px;
                max-width: 420px;
                width: calc(100% - 24px);
                margin-left: auto;
                margin-right: auto;
            }

            .rep-photo,
            .rep img {
                width: 100% !important;
                max-width: 340px !important;
                height: auto !important;
                border-radius: 14px !important;
                object-fit: contain !important;
                background: rgba(0, 0, 0, 0.25);
                border: 3px solid rgba(255, 255, 255, 0.12) !important;
                margin: 0 auto 14px !important;
                display: block;
            }

            .rep-photo-placeholder {
                width: 100% !important;
                max-width: 340px !important;
                height: 220px !important;
                border-radius: 14px !important;
                margin: 0 auto 14px !important;
            }
        }

        @media (max-width: 420px) {
            .rep {
                padding: 14px;
                border-radius: 16px;
                max-width: 380px;
                width: calc(100% - 20px);
            }

            .rep-photo,
            .rep img {
                max-width: 300px !important;
            }

            .rep-photo-placeholder {
                max-width: 300px !important;
                height: 200px !important;
            }

            .rep h3 {
                font-size: 18px;
            }

            .rep p {
                font-size: 13px;
            }
        }

        /* Mission, Vision, Values */
        .mission-vision {
            margin-top: 40px;
            padding: 30px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .mission-vision h3 {
            color: var(--sky);
            margin-top: 0;
            font-size: 24px;
            margin-bottom: 25px;
            text-align: center;
        }

        .mv-item {
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.02);
        }

        .mv-item h4 {
            color: var(--text);
            margin-bottom: 12px;
            font-size: 20px;
            font-weight: 600;
        }

        .mv-item p {
            color: var(--muted);
            line-height: 1.7;
        }

        /* Property Categories - MEJORADO */
        .categories {
            margin-top: 40px;
            padding: 30px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .categories h3 {
            color: var(--sky);
            margin-top: 0;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .search-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            background: var(--card-bg);
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
        }

        .search-icon {
            background: var(--accent);
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-icon:hover {
            background: var(--sky);
            color: var(--navy);
        }

        .category-selectors {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .category-selector {
            background: var(--card-bg);
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .category-selector.active {
            background: var(--accent);
            color: white;
        }

        .category-content {
            display: none;
        }

        .category-content.active {
            display: block;
        }

        .property-form {
            background: rgba(255, 255, 255, 0.02);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--muted);
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            background: var(--card-bg);
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }

        .location-selectors {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .location-selectors select {
            background: var(--card-bg);
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px 14px;
            border-radius: 10px;
            min-width: 180px;
        }

        .subcategory-selectors {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .category-properties {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .category-property {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 16px;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .category-property:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .category-property img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .category-property h5 {
            margin: 12px 0 6px;
            font-size: 18px;
        }

        .category-property p {
            margin: 4px 0;
            color: var(--muted);
            font-size: 14px;
        }

        .property-description {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 13px;
            color: var(--muted);
            line-height: 1.5;
        }

        /* Agents Section Mejorada */
        .agents-section {
            margin-top: 50px;
        }

        .section-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-header h3 {
            font-size: 2.2em;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--text), var(--sky));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 600;
        }

        .section-header p {
            color: var(--muted);
            font-size: 1.1em;
            max-width: 600px;
            margin: 0 auto;
        }

        .agents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .agent-card {
            background: linear-gradient(135deg, var(--card-bg), rgba(255, 255, 255, 0.02));
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            overflow: hidden;
        }

        .agent-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--sky));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .agent-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .agent-card:hover::before {
            transform: scaleX(1);
        }

        .agent-card img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .agent-card:hover img {
            transform: scale(1.1);
            border-color: var(--accent);
        }

        .agent-card h4 {
            margin: 10px 0 5px;
            font-size: 18px;
            font-weight: 600;
        }

        .agent-card p {
            margin: 5px 0;
            color: var(--muted);
            font-size: 14px;
        }

        .agent-contact {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Botones de agentes */
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .delete-btn:hover {
            background: rgba(255, 0, 0, 0.9);
            transform: scale(1.1);
        }

        .edit-btn {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(52, 152, 219, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .edit-btn:hover {
            background: rgba(41, 128, 185, 1);
            transform: scale(1.1);
        }

        /* Controls */
        .controls {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .btn {
            background: var(--accent);
            border: none;
            padding: 12px 18px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: var(--sky);
            color: var(--navy);
            transform: translateY(-2px);
        }

        .btn.ghost {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        /* File Upload */
        input[type=file] {
            display: none;
        }

        .upload-label {
            display: inline-block;
            padding: 10px 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.03);
            cursor: pointer;
            border: 1px dashed rgba(255, 255, 255, 0.04);
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .upload-label:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .muted {
            color: var(--muted);
        }

        /* Status messages */
        .status-message {
            padding: 12px;
            margin: 10px 0;
            border-radius: 10px;
            text-align: center;
            font-size: 14px;
        }

        .status-success {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        .status-error {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        /* Mapa Section */
        .map-section {
            margin-top: 60px;
            padding: 30px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .map-container {
            height: 400px;
            border-radius: 16px;
            overflow: hidden;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Welcome Overlay */
        .welcome-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(11, 35, 64, 0.95);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 1s ease;
            pointer-events: auto;
        }

        .welcome-logo {
            width: 200px;
            height: 200px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--sky), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            animation: logoGlow 2s ease-in-out infinite, logoPulse 2s ease-in-out infinite;
        }

        .welcome-logo img {
            width: 80%;
            height: 80%;
            object-fit: contain;
        }

        .welcome-message {
            text-align: center;
            color: var(--text);
            font-size: 24px;
            margin-bottom: 20px;
        }

        .welcome-submessage {
            text-align: center;
            color: var(--muted);
            font-size: 16px;
            margin-bottom: 30px;
        }

        .enter-button {
            background: var(--accent);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .enter-button:hover {
            background: var(--sky);
            color: var(--navy);
            transform: scale(1.05);
        }

        /* Logo Animation */
        @keyframes logoGlow {
            0% {
                box-shadow: 0 0 5px var(--sky), 0 0 10px var(--sky), 0 0 15px var(--sky);
            }

            50% {
                box-shadow: 0 0 20px var(--accent), 0 0 30px var(--accent), 0 0 40px var(--accent);
            }

            100% {
                box-shadow: 0 0 5px var(--sky), 0 0 10px var(--sky), 0 0 15px var(--sky);
            }
        }

        @keyframes logoPulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes logoSpin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .logo-animation {
            animation: logoGlow 2s ease-in-out infinite, logoPulse 3s ease-in-out infinite;
        }

        .logo-spin {
            animation: logoSpin 1.5s ease-in-out;
        }

        /* Floating buttons - COLUMNA VERTICAL */
        .whatsapp-float,
        .facebook-float,
        .tiktok-float {
            position: fixed;
            right: 20px;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s ease;
            border: none;
        }

        /* POSICIÓN VERTICAL - WhatsApp abajo */
        .whatsapp-float {
            bottom: 20px;
            background: linear-gradient(135deg, #25D366, #128C7E);
        }

        /* POSICIÓN VERTICAL - Facebook en medio */
        .facebook-float {
            bottom: 90px;
            background: linear-gradient(135deg, #1877F2, #0D5FD7);
        }

        /* POSICIÓN VERTICAL - TikTok arriba */
        .tiktok-float {
            bottom: 160px;
            background: linear-gradient(135deg, #000000, #333333);
            border: 2px solid #ffffff;
        }

        .whatsapp-float:hover,
        .facebook-float:hover,
        .tiktok-float:hover {
            transform: scale(1.15);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
        }

        .whatsapp-float:hover {
            background: linear-gradient(135deg, #128C7E, #25D366);
        }

        .facebook-float:hover {
            background: linear-gradient(135deg, #0D5FD7, #1877F2);
        }

        .tiktok-float:hover {
            background: linear-gradient(135deg, #333333, #000000);
        }

        /* Mensajes del sistema */
        .system-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 30px;
            border-radius: 10px;
            z-index: 10000;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.5s ease;
        }

        .system-message.success {
            background: #2ecc71;
            color: white;
            border: 1px solid #27ae60;
        }

        .system-message.error {
            background: #e74c3c;
            color: white;
            border: 1px solid #c0392b;
        }

        @keyframes slideDown {
            from {
                top: -100px;
            }

            to {
                top: 20px;
            }
        }

        .fade-out {
            animation: fadeOut 1s ease forwards;
            animation-delay: 3s;
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
            }
        }

        /* Responsive para móviles */
        @media (max-width: 768px) {
            .site {
                margin: 10px;
                padding: 20px;
            }

            header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .hero {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .agents-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }

            .slider-container {
                height: 280px;
            }

            .whatsapp-float,
            .facebook-float,
            .tiktok-float {
                width: 50px;
                height: 50px;
                right: 15px;
            }

            .whatsapp-float {
                bottom: 15px;
            }

            .facebook-float {
                bottom: 80px;
            }

            .tiktok-float {
                bottom: 145px;
            }

            .language-selector {
                position: relative;
                top: 0;
                right: 0;
                margin-bottom: 20px;
                text-align: center;
            }

            .location-selectors {
                flex-direction: column;
            }

            .category-selectors {
                flex-direction: column;
            }

            .subcategory-selectors {
                flex-direction: column;
            }

            .projects-gallery-container {
                height: 280px;
            }

            .form-row {
                flex-direction: column;
            }

            .search-container {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: 100%;
            }

            .videos-grid {
                grid-template-columns: 1fr;
            }

            .form-container {
                grid-template-columns: 1fr;
            }

            .admin-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ====== NUEVAS SECCIONES: ofertas por categoria ====== */
        .quick-properties {
            padding: 38px 0 10px;
        }

        .quick-columns {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .quick-col {
            padding: 18px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .quick-col-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }

        .quick-col-head h4 {
            margin: 0;
            color: var(--sky);
            font-size: 18px;
        }

        .prop-mini-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .prop-mini {
            display: block;
            text-decoration: none;
            color: inherit;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(255, 255, 255, 0.02);
            transition: transform .15s ease, border-color .15s ease;
        }

        .prop-mini:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 215, 0, 0.45);
        }

        .prop-mini-img {
            height: 110px;
            background: rgba(255, 255, 255, 0.03);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .prop-mini-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.06);
        }

        .prop-mini-img.placeholder {
            color: rgba(255, 255, 255, 0.55);
            font-size: 28px;
        }

        .prop-mini-body {
            padding: 10px;
        }

        .prop-mini-title {
            font-weight: 700;
            font-size: 13px;
            line-height: 1.2;
            margin-bottom: 6px;
        }

        .prop-mini-meta {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.75);
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .prop-mini-price {
            margin-top: 6px;
            font-weight: 700;
            color: var(--gold);
            font-size: 13px;
        }

        /* ====== NUEVAS SECCIONES: lista de proyectos ====== */
        .projects-list-section {
            margin-top: 26px;
            padding: 30px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .projects-list-grid {
            display: flex;
            flex-direction: column;
            gap: 18px;
            margin-top: 18px;
        }

        .project-block {
            padding: 16px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .project-block-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .project-block-header h4 {
            margin: 0;
            color: var(--sky);
            font-size: 16px;
        }

        .project-block-thumbs {
            position: relative;
        }

        .project-block-carousel-wrap {
            position: relative;
            border-radius: 16px;
            height: 270px;
            border: 1px solid rgba(255, 255, 255, 0.10);
            background: rgba(255, 255, 255, 0.02);
            overflow: hidden;
        }

        .project-block-carousel {
            height: 100%;
            overflow-x: auto;
        }

        .project-block-carousel::-webkit-scrollbar {
            display: none;
        }

        .project-block-track {
            display: flex;
            align-items: center;
            height: 100%;
        }

        .project-block-item {
            width: 250px;
            height: 250px;
            flex: 0 0 auto;
            margin-right: 10px;
        }

        .project-block-item img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 12px;
            border: 2px solid transparent;
            background: rgba(255, 255, 255, 0.90);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.20);
        }

        .project-block-item img:hover {
            transform: scale(1.03);
            border-color: var(--gold);
            box-shadow: 0 8px 30px rgba(255, 215, 0, 0.25);
        }

        .pbc-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            z-index: 2;
            opacity: 0.92;
            background: var(--accent);
            color: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.30);
        }

        .pbc-btn:hover {
            opacity: 1;
            background: var(--sky);
            color: var(--navy);
        }

        .pbc-prev {
            left: 10px;
        }

        .pbc-next {
            right: 10px;
        }

        .project-block-carousel {
            scroll-snap-type: x mandatory;
            padding: 10px 60px 10px 10px;
        }

        .project-block-item {
            scroll-snap-align: start;
        }

        /* Boton pequenio */
        .btn.small {
            padding: 8px 12px;
            font-size: 12px;
            border-radius: 999px;
        }

        /* Responsive */
        @media (max-width: 980px) {
            .quick-columns {
                grid-template-columns: 1fr;
            }
        }


        /* Videos: separar profesional vs celular */
        .videos-subtitle {
            font-weight: 700;
            margin-top: 6px;
            opacity: .9;
        }

        .video-media {
            background: #000;
            border-radius: 14px;
            overflow: hidden;
        }

        .video-player {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: contain;
        }

        .video-card.landscape .video-media {
            aspect-ratio: 16/9;
        }

        .video-card.portrait .video-media {
            max-height: 70vh;
        }

        .video-card.portrait .video-player {
            height: auto;
            max-height: 70vh;
        }
    </style>
</head>

<body>
    <?php if ($success): ?>
        <div class="system-message success fade-out" id="successMessage">
            <i class="fas fa-check-circle"></i>
            <?php
            switch ($success) {
                case '1':
                    echo 'Formulario enviado exitosamente. Nos pondremos en contacto pronto.';
                    break;
                case '2':
                    echo 'Operación realizada exitosamente.';
                    break;
                default:
                    echo 'Operación exitosa.';
            }
            ?>
        </div>
        <script>
            setTimeout(() => {
                document.getElementById('successMessage').remove();
            }, 4000);
        </script>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="system-message error fade-out" id="errorMessage">
            <i class="fas fa-exclamation-circle"></i>
            <?php
            switch ($error) {
                case '1':
                    echo 'Error al enviar el formulario. Por favor intente nuevamente.';
                    break;
                case '2':
                    echo 'Error en la operación.';
                    break;
                default:
                    echo 'Ha ocurrido un error.';
            }
            ?>
        </div>
        <script>
            setTimeout(() => {
                document.getElementById('errorMessage').remove();
            }, 4000);
        </script>
    <?php endif; ?>


    <!-- Welcome Overlay -->
    <div class="welcome-overlay" id="welcomeOverlay">
        <div class="welcome-logo">
            <?php
            $welcomeLogo = 'assets/images/logo.png';
            if (file_exists($welcomeLogo)) {
                echo '<img src="' . $welcomeLogo . '" alt="Logo Servibienes" style="width:100%;height:100%;object-fit:contain;">';
            } else {
                echo '<svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                        <rect width="200" height="200" fill="#0b2340"/>
                        <text x="100" y="50" text-anchor="middle" fill="#cfeefc" font-family="Arial" font-size="32" font-weight="bold">S</text>
                        <text x="100" y="85" text-anchor="middle" fill="#cfeefc" font-family="Arial" font-size="32" font-weight="bold">B</text>
                        <text x="100" y="125" text-anchor="middle" fill="#ffffff" font-family="Arial" font-size="18" font-weight="bold">SERVIBIENES</text>
                        <text x="100" y="155" text-anchor="middle" fill="#bcd6ee" font-family="Arial" font-size="10">El servicio que tu inversion merece</text>
                    </svg>';
            }
            ?>
        </div>
        <div class="welcome-message">Bienvenido a Servibienes</div>
        <div class="welcome-submessage">Tu socio de confianza en bienes raíces</div>
        <button class="enter-button" id="enterButton">Ingresar al Sitio</button>
    </div>

    <!-- Language Selector -->
    <div class="language-selector">
        <span class="translator-label">Traductor de idiomas</span>
        <select id="languageSelect">
            <option value="es">Español</option>
            <option value="en">English</option>
            <option value="fr">Français</option>
            <option value="de">Deutsch</option>
            <option value="it">Italiano</option>
            <option value="pt">Português</option>
            <option value="ja">日本語</option>
            <option value="zh">中文</option>
            <option value="ru">Русский</option>
            <option value="ar">العربية</option>
        </select>
    </div>

    <div id="google_translate_element" style="display:none"></div>

    <div class="site">



        <?php
        // Imagen de portada (compatibilidad con distintas estructuras):
        // 1) imagenes/portada/portada.jpg (o heroe.jpg)
        // 2) assets/images/portada.jpg (fallback)
        $heroCandidates = [
            ['rel' => 'imagenes/portada/portada.jpg', 'abs' => BASE_PATH . '/imagenes/portada/portada.jpg'],
            ['rel' => 'imagenes/portada/heroe.jpg',   'abs' => BASE_PATH . '/imagenes/portada/heroe.jpg'],
            ['rel' => 'assets/images/portada.jpg',    'abs' => BASE_PATH . '/assets/images/portada.jpg'],
        ];

        $heroRel = null;
        foreach ($heroCandidates as $c) {
            if (file_exists($c['abs'])) {
                $heroRel = $c['rel'];
                break;
            }
        }

        $heroStyle = $heroRel
            ? "background-image: url('{$heroRel}');"
            : "background-image: linear-gradient(135deg, rgba(11,35,64,.92), rgba(4,24,38,.88));";
        ?>
        <section id="inicio" class="hero-banner" style="<?php echo $heroStyle; ?>
">
            <div class="hero-topbar">
                <div class="hero-brand">
                    <a href="#inicio" class="hero-brand-link" aria-label="Ir a inicio">
                        <?php
                        // Logo (compatibilidad)
                        $logoRel = file_exists(BASE_PATH . '/imagenes/portada/logotipo.png')
                            ? 'imagenes/portada/logotipo.png'
                            : (file_exists(BASE_PATH . '/imagenes/portada/logo.png')
                                ? 'imagenes/portada/logo.png'
                                : 'assets/images/logo.png');
                        ?>
                        <img src="<?php echo $logoRel; ?>" alt="Servibienes" class="hero-logo">
                    </a>
                </div>
                <button class="hero-burger" id="heroBurger" type="button" aria-label="Abrir menú"><i class="fas fa-bars"></i></button>
                <nav class="hero-menu" id="heroNav">
                    <a href="#inicio">Inicio</a>

                    <div class="hero-dropdown">
                        <button class="hero-dropbtn" type="button" aria-haspopup="true" aria-expanded="false">
                            Proyectos <span class="hero-caret">▾</span>
                        </button>
                        <div class="hero-dropmenu" role="menu">
                            <?php
                            $sectionsPath = BASE_PATH . '/assets/data/sections.json';
                            $sections = [];
                            if (file_exists($sectionsPath)) {
                                $decoded = json_decode(file_get_contents($sectionsPath), true);
                                if (is_array($decoded)) $sections = $decoded;
                            }
                            if (empty($sections)) {
                                $sections = [
                                    ['id' => 'ofertas', 'label' => 'Ofertas por Categoría'],
                                    ['id' => 'formulario', 'label' => 'Desea vender o alquilar su propiedad'],
                                    ['id' => 'destacados', 'label' => 'Nuestros Proyectos Destacados'],
                                    ['id' => 'proyectos', 'label' => 'Proyectos'],
                                    ['id' => 'videos', 'label' => 'Videos de Nuestros Proyectos'],
                                    ['id' => 'catalogo', 'label' => 'Catálogo de Propiedades'],
                                    ['id' => 'ubicacion', 'label' => 'Nuestra Ubicación'],
                                ];
                            }
                            foreach ($sections as $sec) {
                                if (!is_array($sec)) continue;
                                $id = sanitize($sec['id'] ?? '');
                                $label = trim((string)($sec['label'] ?? ''));
                                if ($id === '' || $label === '') continue;
                                echo '<a href="#' . htmlspecialchars($id) . '" role="menuitem">' . htmlspecialchars($label) . '</a>';
                            }
                            ?>
                        </div>
                    </div>

                    <a href="#agentes">Contáctanos</a>
                    <a href="#identidad">Nosotros</a>
                </nav>
                <div class="hero-cta">
                    <a class="hero-btn" href="https://wa.me/59177657257" target="_blank" rel="noopener">contactar con ventas</a>
                </div>
            </div>


            <div class="hero-overlay">
                <div class="hero-overlay-inner">
                    <h2>Hacemos realidad tus sueños<br>Construimos futuros</h2>
                    <p>Transparencia, compromiso y profesionalismo en bienes raíces.</p>
                </div>
            </div>
        </section>
        <section class="below-hero">
            <div class="below-left">
                <div class="intro">
                    <div class="slider-container">
                        <div class="slider-track" id="galleryTrack">
                            <?php if (!empty($gallery)): ?>
                                <?php foreach ($gallery as $g): ?>
                                    <div class="gallery-item">
                                        <img src="<?php echo hurl($g['image_url']); ?>" alt="Galería" loading="lazy">
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="gallery-item">
                                    <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80" alt="Imagen" loading="lazy">
                                </div>
                                <div class="gallery-item">
                                    <img src="https://images.unsplash.com/photo-1570129477492-45c003edd2be?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80" alt="Imagen" loading="lazy">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="slider-controls">
                            <button class="slider-btn slider-prev" id="prevBtn" type="button"><i class="fas fa-chevron-left"></i></button>
                            <button class="slider-btn slider-next" id="nextBtn" type="button"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>

                    <div class="controls" style="margin-top:18px">
                        <button class="btn" onclick="window.open('https://wa.me/59177657257', '_blank')">
                            <i class="fab fa-whatsapp"></i> Contactar ahora
                        </button>
                    </div>

                    <div id="galleryStatus"></div>
                </div>
            </div>
            <aside class="rep">
                <?php
                // Foto representante (compatibilidad con distintas carpetas/nombres)
                $repCandidates = [
                    ['rel' => 'imagenes/agentes/representante.jpg', 'abs' => BASE_PATH . '/imagenes/agentes/representante.jpg'],
                    ['rel' => 'imagenes/agentes/representante-legal.png', 'abs' => BASE_PATH . '/imagenes/agentes/representante-legal.png'],
                    ['rel' => 'imagenes/representante/REPRESENTANTE LEGAL.png', 'abs' => BASE_PATH . '/imagenes/representante/REPRESENTANTE LEGAL.png'],
                    ['rel' => 'assets/uploads/agents/representante.jpg', 'abs' => BASE_PATH . '/assets/uploads/agents/representante.jpg'],
                ];
                $repPhotoRel = null;
                foreach ($repCandidates as $c) {
                    if (file_exists($c['abs'])) {
                        $repPhotoRel = $c['rel'];
                        break;
                    }
                }
                $hasRepPhoto = (bool)$repPhotoRel;
                ?>

                <div class="rep-photo-wrap">
                    <?php if ($hasRepPhoto): ?>
                        <img src="<?php echo $repPhotoRel; ?>" alt="Lic. Mauricio Céspedes Justiniano" class="rep-photo">
                    <?php else: ?>
                        <div class="rep-photo-placeholder" aria-label="Sin foto">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    <?php endif; ?>

                    <?php if (isAdminLoggedIn()): ?>
                        <form action="process/upload_representante.php" method="post" enctype="multipart/form-data" style="margin-top:12px;">
                            <label class="upload-label" style="display:inline-block;">
                                <i class="fas fa-camera"></i> Cambiar foto
                                <input type="file" name="photo" accept="image/*" onchange="this.form.submit()" style="display:none">
                            </label>
                        </form>
                    <?php endif; ?>
                </div>

                <h3>Lic. Mauricio Céspedes Justiniano</h3>
                <p class="muted">Representante legal · Inmobiliaria Servibienes S.R.L.</p>
                <p class="muted" style="font-size:13px">Cel: 77657257 · mcespedes@servibienessrl.com</p>
                <div class="rep-badge">Representante Legal</div>
            </aside>
        </section>


        <!-- SECCIONES RAPIDAS DE PROPIEDADES (debajo de la galeria) -->
        <section class="quick-properties" id="ofertas">
            <div class="section-header">
                <h3>Ofertas por Categoria</h3>
                <p>Explora rapidamente nuestras opciones en venta, alquiler y anticretico</p>
            </div>

            <?php
            function renderPropertyMiniCard($p)
            {
                $img = $p['image_url'] ?? '';
                $title = $p['title'] ?? 'Propiedad';
                $loc = $p['location'] ?? '';
                $price = $p['price'] ?? '';
                $cat = $p['category'] ?? '';
                $sub = $p['subcategory'] ?? '';
                $href = 'propiedades.php?tipo=' . urlencode((string)$cat);
                if ($sub !== '') {
                    $href .= '&sub=' . urlencode((string)$sub);
                }
                if (!empty($title)) {
                    $href .= '&q=' . urlencode((string)$title);
                }
                echo '<a class="prop-mini" href="' . $href . '">';
                if (!empty($img)) {
                    echo '<div class="prop-mini-img"><img src="' . htmlspecialchars($img) . '" alt="' . htmlspecialchars($title) . '" loading="lazy"></div>';
                } else {
                    echo '<div class="prop-mini-img placeholder"><i class="fas fa-home"></i></div>';
                }
                echo '<div class="prop-mini-body">';
                echo '<div class="prop-mini-title">' . htmlspecialchars($title) . '</div>';
                if (!empty($loc)) echo '<div class="prop-mini-meta"><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($loc) . '</div>';
                if (!empty($price)) echo '<div class="prop-mini-price">' . htmlspecialchars($price) . '</div>';
                echo '</div></a>';
            }
            ?>


            <?php
            $banners = [];
            $bannerFile = __DIR__ . '/assets/data/category_banners.json';
            if (file_exists($bannerFile)) {
                $banners = json_decode(file_get_contents($bannerFile), true);
                if (!is_array($banners)) $banners = [];
            }
            function bannerFor($cat, $banners)
            {
                return !empty($banners[$cat]) ? $banners[$cat] : '';
            }
            ?>

            <div class="quick-columns">
                <div class="quick-col">
                    <div class="quick-col-head">
                        <h4>Ventas de Casas</h4>
                        <a class="btn small" href="propiedades.php?tipo=venta">Ver todas</a>
                        <?php $b = bannerFor("venta", $banners);
                        if ($b): ?>
                            <div class="cat-banner"><img src="<?php echo htmlspecialchars($b); ?>" alt="banner Ventas de Casas" loading="lazy"></div>
                        <?php endif; ?>
                    </div>
                    <div class="prop-mini-grid">
                        <?php foreach ($properties_venta as $p) renderPropertyMiniCard($p); ?>
                        <?php if (empty($properties_venta)): ?><div class="muted" style="padding:10px;">Aun no hay propiedades en venta.</div><?php endif; ?>
                    </div>
                </div>

                <div class="quick-col">
                    <div class="quick-col-head">
                        <h4>Anticreticos</h4>
                        <a class="btn small" href="propiedades.php?tipo=anticretico">Ver todas</a>
                        <?php $b = bannerFor("anticretico", $banners);
                        if ($b): ?>
                            <div class="cat-banner"><img src="<?php echo htmlspecialchars($b); ?>" alt="banner Anticreticos" loading="lazy"></div>
                        <?php endif; ?>
                    </div>
                    <div class="prop-mini-grid">
                        <?php foreach ($properties_anticretico as $p) renderPropertyMiniCard($p); ?>
                        <?php if (empty($properties_anticretico)): ?><div class="muted" style="padding:10px;">Aun no hay anticreticos registrados.</div><?php endif; ?>
                    </div>
                </div>

                <div class="quick-col">
                    <div class="quick-col-head">
                        <h4>Alquileres</h4>
                        <a class="btn small" href="propiedades.php?tipo=alquiler">Ver todas</a>
                        <?php $b = bannerFor("alquiler", $banners);
                        if ($b): ?>
                            <div class="cat-banner"><img src="<?php echo htmlspecialchars($b); ?>" alt="banner Alquileres" loading="lazy"></div>
                        <?php endif; ?>
                    </div>
                    <div class="prop-mini-grid">
                        <?php foreach ($properties_alquiler as $p) renderPropertyMiniCard($p); ?>
                        <?php if (empty($properties_alquiler)): ?><div class="muted" style="padding:10px;">Aun no hay propiedades en alquiler.</div><?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Nuevos Eventos (galería) -->
        <section id="eventos" class="events-section">
            <div class="section-header">
                <h3>Nuevos Eventos</h3>
                <p>Galería de eventos y novedades</p>
            </div>

            <div class="events-grid-front">
                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $ev): ?>
                        <div class="event-card-front">
                            <div class="event-card-img">
                                <img src="<?php echo hurl($ev['image_url']); ?>" alt="<?php echo htmlspecialchars($ev['title']); ?>" loading="lazy">
                            </div>
                            <div class="event-card-body">
                                <div class="event-card-title"><?php echo htmlspecialchars($ev['title']); ?></div>
                                <?php if (!empty($ev['description'])): ?>
                                    <div class="event-card-desc"><?php echo nl2br(htmlspecialchars($ev['description'])); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="event-card-front" style="padding:18px;">
                        <div class="event-card-title">Aún no hay eventos publicados</div>
                        <div class="event-card-desc">Puedes agregarlos desde Admin → Nuevos Eventos (Galería).</div>
                    </div>
                <?php endif; ?>
            </div>
        </section>


        <!-- ========== FORMULARIO PARA CLIENTES ========== -->
        <section id="formulario" class="client-form-section">
            <h3>¿Desea vender o alquilar su propiedad?</h3>
            <p class="muted" style="text-align: center; margin-bottom: 30px;">
                Complete el formulario y nos pondremos en contacto con usted para una evaluación gratuita
            </p>

            <form action="process/submit_client_form.php" method="post" enctype="multipart/form-data"
                id="clientForm" onsubmit="return validateClientForm()">
                <div class="form-container">
                    <div class="form-column">
                        <div class="form-field">
                            <label>Nombre completo *</label>
                            <input type="text" name="name" required
                                placeholder="Ingrese su nombre completo">
                        </div>

                        <div class="form-field">
                            <label>Teléfono *</label>
                            <input type="tel" name="phone" required
                                placeholder="Ej: 77657257"
                                pattern="[0-9+]{7,15}">
                        </div>

                        <div class="form-field">
                            <label>Email *</label>
                            <input type="email" name="email" required
                                placeholder="correo@ejemplo.com">
                        </div>

                        <div class="form-field">
                            <label>Tipo de operación *</label>
                            <select name="operation" required>
                                <option value="">Seleccione una opción</option>
                                <option value="venta">Venta</option>
                                <option value="alquiler">Alquiler</option>
                                <option value="anticretico">Anticrético</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-column">
                        <div class="form-field">
                            <label>Tipo de propiedad *</label>
                            <select name="property_type" required>
                                <option value="">Seleccione tipo</option>
                                <option value="casa">Casa</option>
                                <option value="departamento">Departamento</option>
                                <option value="terreno">Terreno</option>
                                <option value="local">Local Comercial</option>
                                <option value="oficina">Oficina</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label>Ubicación *</label>
                            <input type="text" name="location" required
                                placeholder="Ej: Barrio Equipetrol, Calle lugones">
                        </div>

                        <div class="form-field">
                            <label>Precio estimado (USD) *</label>
                            <input type="number" name="price" required
                                placeholder="Ej: 150000" min="0" step="0.01">
                        </div>

                        <div class="form-field">
                            <label>Descripción de la propiedad *</label>
                            <textarea name="description" required
                                placeholder="Describa las características de la propiedad..."
                                rows="4"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Subida de imágenes -->
                <div class="form-field" style="margin-top: 20px;">
                    <label>Imágenes de la propiedad (Máximo 10, opcional)</label>
                    <div class="upload-area" onclick="document.getElementById('clientImages').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Haga clic o arrastre imágenes aquí</p>
                        <p class="muted" style="font-size: 12px;">Formatos: JPG, PNG (Tamaño máximo: 5MB cada una)</p>
                        <input type="file" id="clientImages" name="images[]"
                            accept="image/*" multiple style="display: none;">
                    </div>
                    <div class="preview-container" id="imagePreview"></div>
                </div>

                <!-- Subida de videos -->
                <div class="form-field" style="margin-top: 20px;">
                    <label>Videos de la propiedad (Opcional)</label>
                    <div class="upload-area" onclick="document.getElementById('clientVideos').click()">
                        <i class="fas fa-video"></i>
                        <p>Haga clic o arrastre videos aquí</p>
                        <p class="muted" style="font-size: 12px;">Formato recomendado: MP4 (el tamaño máximo depende del hosting)</p>
                        <input type="file" id="clientVideos" name="videos[]"
                            accept="video/*" multiple style="display: none;">
                    </div>
                    <div class="preview-container" id="videoPreview"></div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Enviar Formulario
                </button>
            </form>

            <div id="clientFormStatus" style="margin-top: 20px;"></div>
        </section>

        <!-- GALERÍA DE PROYECTOS DESTACADOS -->
        <section class="projects-gallery-section" id="destacados">
            <div class="section-header">
                <h3>Nuestros Proyectos Destacados</h3>
                <p>Conoce algunos de los proyectos más importantes que hemos desarrollado</p>
            </div>

            <div class="projects-gallery-container">
                <button class="projects-btn projects-prev">&#10094;</button>
                <div class="projects-track" id="projectsGallery">
                    <?php if (!empty($featuredProjects)): ?>
                        <?php foreach ($featuredProjects as $project): ?>
                            <div class="project-item">
                                <!-- admin-tools-removed -->
                                <img src="<?php echo hurl($project['image_url']); ?>"
                                    alt="<?php echo htmlspecialchars($project['title']); ?>"
                                    loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Proyectos por defecto -->
                        <div class="project-item">
                            <img src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?ixlib=rb-1.2.1&auto=format&fit=crop&w=250&h=250&q=80"
                                alt="Proyecto 1">
                        </div>
                        <div class="project-item">
                            <img src="https://images.unsplash.com/photo-1518780664697-55e3ad937233?ixlib=rb-1.2.1&auto=format&fit=crop&w=250&h=250&q=80"
                                alt="Proyecto 2">
                        </div>
                    <?php endif; ?>
                </div>
                <button class="projects-btn projects-next">&#10095;</button>
            </div>

            <div class="controls" style="margin-top:20px; justify-content: center;">
                <!-- admin-tools-removed -->
            </div>

            <div id="projectsStatus"></div>
        </section>

        <!-- LISTA DE PROYECTOS (SECCIONES) -->
        <section class="projects-list-section" id="proyectos">
            <div class="section-header">
                <h3>Proyectos</h3>
                <p>Selecciona un proyecto para ver sus imágenes y avances</p>
            </div>

            <?php
            // Mapa de proyectos solicitados (se recomienda que los archivos subidos lleven el nombre del proyecto en el archivo)
            $namesPath = BASE_PATH . '/assets/data/project_names.json';
            $projectNames = [];
            if (file_exists($namesPath)) {
                $decoded = json_decode(file_get_contents($namesPath), true);
                if (is_array($decoded)) {
                    $projectNames = array_values(array_filter(array_map('strval', $decoded)));
                }
            }
            if (empty($projectNames)) {
                // Fallback por si el archivo no existe
                $projectNames = [
                    'ICARO',
                    'PALMERAS GARDEN',
                    'OBLOX ONE',
                    'RAIZANT BLEND',
                    'ERGO',
                    'GIARDINO',
                    'SINAI URUBO',
                    'BERCHATTI HOME',
                    'BERCHATTI BN2',
                    'VERTICAL TERRA',
                    'CONDOMINIO REZE II',
                    'MATT - 51'
                ];
            }

            function projectMatches($title, $needle)
            {
                return mb_stripos((string)$title, (string)$needle) !== false;
            }
            ?>

            <div class="projects-list-grid">
                <?php foreach ($projectNames as $pname): ?>
                    <?php
                    $matches = [];
                    foreach ($projectsAll as $pr) {
                        if (projectMatches($pr['title'] ?? '', $pname)) {
                            $matches[] = $pr;
                        }
                    }
                    ?>
                    <div class="project-block">
                        <div class="project-block-header">
                            <h4><?php echo htmlspecialchars('PROYECTO: ' . $pname); ?></h4>
                            <a class="btn small" href="proyecto.php?proyecto=<?php echo urlencode($pname); ?>">Ver ofertas</a>
                        </div>
                        <div class="project-block-thumbs">
                            <?php if (!empty($matches)): ?>
                                <?php $cid = 'pbc_' . md5($pname); ?>
                                <div class="project-block-carousel-wrap">
                                    <button class="pbc-btn pbc-prev" type="button" aria-label="Anterior" data-target="<?php echo $cid; ?>">&#10094;</button>
                                    <div class="project-block-carousel" id="<?php echo $cid; ?>">
                                        <div class="project-block-track">
                                            <?php foreach ($matches as $m): ?>
                                                <div class="project-block-item">
                                                    <img src="<?php echo hurl($m['image_url']); ?>" alt="<?php echo htmlspecialchars($pname); ?>" loading="lazy">
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <button class="pbc-btn pbc-next" type="button" aria-label="Siguiente" data-target="<?php echo $cid; ?>">&#10095;</button>
                                </div>
                            <?php else: ?>
                                <div class="muted" style="padding:10px; font-size:13px;">
                                    Aun no hay imagenes para este proyecto.
                                    <?php if (isAdminLoggedIn()): ?>
                                        <br>Tip: sube imagenes en <strong>Admin → Proyectos</strong> y nombra el archivo incluyendo “<?php echo htmlspecialchars($pname); ?>”.
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- SUBIDOR DE VIDEOS -->
        <section id="videos" class="videos-section">
            <div class="section-header">
                <h3>Videos de Nuestros Proyectos</h3>
                <p>Descubre nuestros proyectos en video</p>
            </div>
            <div id="videosGridMobile" class="videos-grid"></div>

            <!-- admin-tools-removed -->

            <div id="videosGridPro" class="videos-grid">
                <?php if (!empty($videos)): ?>
                    <?php foreach ($videos as $video): ?>
                        <div class="video-card">
                            <!-- admin-tools-removed -->
                            <div class="video-media">
                                <video class="video-player" controls playsinline webkit-playsinline preload="metadata">
                                    <source data-src="<?php echo hurl($video['video_url']); ?>" type="video/mp4">
                                    Tu navegador no soporta el elemento de video.
                                </video>
                            </div>
                            <?php if (!empty($video['title'])): ?>
                                <p style="margin-top:10px;text-align:center;"><?php echo htmlspecialchars($video['title']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="video-card" style="text-align:center;padding:20px;">
                        <i class="fas fa-video" style="font-size:50px;color:#ccc;margin-bottom:15px;"></i>
                        <p>No hay videos disponibles</p>
                    </div>
                <?php endif; ?>
            </div>

            <div id="videosStatus"></div>
        </section>

        <!-- ========== CONSTRUCTORA ========== -->
        <section id="constructora" class="constructora-section">
            <a href="#inicio" class="to-top-btn" aria-label="Ir a inicio">
                <i class="fas fa-arrow-up"></i>
            </a>
            <div class="section-header">
                <h3>Servibienes S.R.L. Constructora</h3>
                <p>De planos a hogares, de sueños a espacios llenos de vida</p>
            </div>
            <div class="constructora-inner" style="display:grid; grid-template-columns:1fr 1.2fr; gap:40px; align-items:start; margin-top:28px;">

                <!-- COLUMNA IZQUIERDA: Arte principal -->
                <div class="constructora-art">
                    <?php
                    $arteConstructora = BASE_PATH . '/imagenes/constructora/constructora-arte.jpg.jpeg';
                    if (file_exists($arteConstructora)):
                    ?>
                        <img src="imagenes/constructora/constructora-arte.jpg.jpeg"
                            alt="Servibienes Constructora - Diseñamos tu proyecto ideal"
                            class="constructora-arte-img"
                            style="width:100%; height:auto; object-fit:contain; border-radius:18px; display:block; background:transparent;">
                    <?php else: ?>
                        <div class="constructora-art-placeholder">
                            <i class="fas fa-hard-hat"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- COLUMNA DERECHA: Logo + texto -->
                <div class="constructora-info" style="display:flex; flex-direction:column; gap:18px; align-items:flex-start;">
                    <?php
                    $logoConstructora = BASE_PATH . '/imagenes/constructora/logo-constructora-master.jpg.jpeg';
                    if (file_exists($logoConstructora)):
                    ?>
                        <img src="imagenes/constructora/logo-constructora-master.jpg.jpeg"
                            alt="Logo Servibienes Constructora"
                            class="constructora-logo"
                            style="width:170px !important; max-width:170px !important; height:auto; display:block; background:#fff; border-radius:14px; padding:10px 14px; box-shadow:0 4px 18px rgba(0,0,0,0.2);">
                    <?php else: ?>
                        <div class="constructora-logo-placeholder">
                            <i class="fas fa-building"></i>
                        </div>
                    <?php endif; ?>

                    <p class="constructora-desc">
                        En <strong>SERVIBIENES S.R.L. CONSTRUCTORA</strong>, diseñamos tu proyecto ideal
                        y lo convertimos en realidad.
                    </p>
                    <div class="constructora-servicios">
                        <div class="constructora-serv-item">
                            <i class="fas fa-check-circle"></i> Viviendas modernas
                        </div>
                        <div class="constructora-serv-item">
                            <i class="fas fa-check-circle"></i> Edificios seguros
                        </div>
                        <div class="constructora-serv-item">
                            <i class="fas fa-check-circle"></i> Locales comerciales funcionales
                        </div>
                        <div class="constructora-serv-item">
                            <i class="fas fa-check-circle"></i> Interiores
                        </div>
                        <div class="constructora-serv-item">
                            <i class="fas fa-check-circle"></i> Avalúos y presupuestos
                        </div>
                        <div class="constructora-serv-item">
                            <i class="fas fa-check-circle"></i> Supervisión de obra
                        </div>
                    </div>
                    <p class="constructora-slogan">
                        SERVIBIENES S.R.L. – <em>Construimos confianza, edificamos futuro.</em>
                    </p>
                    <a href="https://wa.me/59177657257" target="_blank" rel="noopener" class="constructora-cta"
                        style="display:inline-flex; align-items:center; gap:10px; background:linear-gradient(135deg,#25D366,#128C7E); border-radius:12px; padding:13px 22px; font-weight:700; font-size:15px; text-decoration:none; color:#fff; margin-top:10px;">
                        <i class="fab fa-whatsapp"></i> Consultar proyecto
                    </a>
                </div>

            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats">
            <div class="stat-card">
                <div class="stat-number">500+</div>
                <div class="stat-label">Propiedades Vendidas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">15+</div>
                <div class="stat-label">Años de Experiencia</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">98%</div>
                <div class="stat-label">Clientes Satisfechos</div>
            </div>
        </section>

        <!-- MISSION, VISION, VALUES -->
        <section id="identidad" class="mission-vision">
            <h3>Nuestra Identidad</h3>

            <div class="mv-item">
                <h4>MISIÓN</h4>
                <p>
                    En <strong>Servibienes</strong> tenemos la misión de transformar sueños en realidades tangibles,
                    conectando a las personas con el hogar perfecto que merecen. Nos dedicamos a brindar soluciones
                    inmobiliarias integrales con honestidad, transparencia y profesionalismo, garantizando que cada
                    inversión esté respaldada por la seguridad jurídica necesaria. Nos apasiona crear experiencias
                    únicas que superen expectativas y construyan relaciones duraderas basadas en la confianza mutua.
                </p>
            </div>

            <div class="mv-item">
                <h4>VISIÓN</h4>
                <p>
                    Visualizamos un futuro donde <strong>Servibienes</strong> sea sinónimo de excelencia inmobiliaria
                    en toda Bolivia, reconocida por nuestra capacidad de anticiparnos a las necesidades del mercado
                    y ofrecer propiedades que no solo son espacios físicos, sino proyectos de vida. Aspiramos a ser
                    el puente que conecta a las familias bolivianas con oportunidades de crecimiento patrimonial,
                    contribuyendo al desarrollo urbano sostenible y al progreso económico de nuestra querida Santa Cruz
                    y todo el país. Soñamos con ser la primera opción para quienes buscan no solo una propiedad,
                    sino un legado para las futuras generaciones.
                </p>
            </div>

            <div class="mv-item">
                <h4>VALORES</h4>
                <p>
                    <strong>Integridad:</strong> Actuamos con honestidad y transparencia en cada transacción,
                    priorizando siempre los intereses de nuestros clientes.<br><br>

                    <strong>Compromiso:</strong> Nos entregamos al 100% en cada proyecto, entendiendo que
                    detrás de cada propiedad hay sueños, esfuerzo y esperanzas.<br><br>

                    <strong>Excelencia:</strong> Buscamos la perfección en cada detalle, desde el asesoramiento
                    inicial hasta la entregafinal, porque sabemos que tu inversión merece lo mejor.<br><br>

                    <strong>Innovación:</strong> Adoptamos las mejores prácticas y tecnologías para ofrecer
                    soluciones vanguardistas que agreguen valor real a tu patrimonio.<br><br>

                    <strong>Pasión por servir:</strong> Amamos lo que hacemos y eso se refleja en la calidad
                    de nuestro servicio, siempre con calidez humana y disposición para escuchar.
                </p>
            </div>
        </section>

        <!-- PROPERTY CATEGORIES - MEJORADO -->
        <section id="catalogo" class="categories">
            <h3>Catálogo de Propiedades</h3>
            <p class="muted">Explora nuestras propiedades organizadas por categoría y ubicación</p>

            <!-- Barra de búsqueda -->
            <div class="search-container">
                <input type="text" id="propertySearch" class="search-box"
                    placeholder="Buscar propiedades por nombre, ubicación, tipo..."
                    onkeyup="searchProperties()">
                <button class="search-icon" onclick="searchProperties()">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <div class="search-actions" style="display:flex;justify-content:flex-end;margin-top:10px;">
                <a id="openCatalogBtn" class="btn small" href="propiedades.php?tipo=venta" style="text-decoration:none;">Ver propiedades</a>
            </div>


            <div class="category-selectors">
                <div class="category-selector active" data-category="venta">Propiedades en Venta</div>
                <div class="category-selector" data-category="alquiler">Propiedades en Alquiler</div>
                <div class="category-selector" data-category="anticretico">Propiedades en Anticrético</div>
            </div>

            <!-- VENTA CATEGORY -->
            <div class="category-content active" id="venta-content">
                <!-- admin-tools-removed -->

                <div class="category-properties" id="venta-properties">
                    <?php if (!empty($properties_venta)): ?>
                        <?php foreach ($properties_venta as $property): ?>
                            <div class="category-property">
                                <?php if (!empty($property['image_url'])): ?>
                                    <img src="<?php echo hurl($property['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($property['title']); ?>"
                                        loading="lazy">
                                <?php endif; ?>
                                <h5><?php echo htmlspecialchars($property['title']); ?></h5>
                                <p class="muted"><?php echo htmlspecialchars($property['location']); ?> · <?php echo htmlspecialchars($property['subcategory']); ?></p>
                                <p class="muted" style="color:var(--accent);font-weight:bold;">
                                    <?php echo htmlspecialchars($property['price']); ?>
                                </p>
                                <div class="property-description">
                                    <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                                </div>
                                <!-- admin-tools-removed -->
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="category-property" style="text-align:center;padding:30px;">
                            <i class="fas fa-home" style="font-size:50px;color:#ccc;margin-bottom:15px;"></i>
                            <p>No hay propiedades en venta disponibles</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="ventaStatus"></div>
            </div>

            <!-- ALQUILER CATEGORY -->
            <div class="category-content" id="alquiler-content" style="display:none;">
                <!-- admin-tools-removed -->

                <div class="category-properties" id="alquiler-properties">
                    <?php if (!empty($properties_alquiler)): ?>
                        <?php foreach ($properties_alquiler as $property): ?>
                            <div class="category-property">
                                <?php if (!empty($property['image_url'])): ?>
                                    <img src="<?php echo hurl($property['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($property['title']); ?>"
                                        loading="lazy">
                                <?php endif; ?>
                                <h5><?php echo htmlspecialchars($property['title']); ?></h5>
                                <p class="muted"><?php echo htmlspecialchars($property['location']); ?> · <?php echo htmlspecialchars($property['subcategory']); ?></p>
                                <p class="muted" style="color:var(--accent);font-weight:bold;">
                                    <?php echo htmlspecialchars($property['price']); ?>
                                </p>
                                <div class="property-description">
                                    <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="category-property" style="text-align:center;padding:30px;">
                            <p>No hay propiedades en alquiler disponibles</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="alquilerStatus"></div>
            </div>

            <!-- ANTICRETICO CATEGORY -->
            <div class="category-content" id="anticretico-content" style="display:none;">
                <!-- admin-tools-removed -->

                <div class="category-properties" id="anticretico-properties">
                    <?php if (!empty($properties_anticretico)): ?>
                        <?php foreach ($properties_anticretico as $property): ?>
                            <div class="category-property">
                                <?php if (!empty($property['image_url'])): ?>
                                    <img src="<?php echo hurl($property['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($property['title']); ?>"
                                        loading="lazy">
                                <?php endif; ?>
                                <h5><?php echo htmlspecialchars($property['title']); ?></h5>
                                <p class="muted"><?php echo htmlspecialchars($property['location']); ?> · <?php echo htmlspecialchars($property['subcategory']); ?></p>
                                <p class="muted" style="color:var(--accent);font-weight:bold;">
                                    <?php echo htmlspecialchars($property['price']); ?>
                                </p>
                                <div class="property-description">
                                    <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="category-property" style="text-align:center;padding:30px;">
                            <p>No hay propiedades en anticrético disponibles</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="anticreticoStatus"></div>
            </div>
        </section>

        <!-- Agents Section -->
        <section id="agentes" class="agents-section">
            <div class="section-header">
                <h3>Nuestro Equipo de Especialistas</h3>
                <p>Profesionales responsables y serios a tu servicio</p>
            </div>

            <div class="agents-grid" id="agentsGrid">
                <?php if (!empty($agents)): ?>
                    <?php foreach ($agents as $agent): ?>
                        <div class="agent-card">
                            <!-- admin-tools-removed -->

                            <?php if (!empty($agent['photo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($agent['photo_url']); ?>"
                                    alt="<?php echo htmlspecialchars($agent['name']); ?>"
                                    style="width:120px;height:120px;border-radius:50%;object-fit:cover;">
                            <?php else: ?>
                                <div style="width:120px;height:120px;border-radius:50%;background:var(--card-bg);
                                   display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                                    <i class="fas fa-user-tie" style="font-size:40px;color:var(--muted);"></i>
                                </div>
                            <?php endif; ?>
                            <h4><?php echo htmlspecialchars($agent['name']); ?></h4>
                            <p class="muted"><?php echo htmlspecialchars($agent['position']); ?></p>
                            <p class="muted" style="font-size:13px"><?php echo htmlspecialchars($agent['phone']); ?></p>
                            <?php
                            $wa = preg_replace('/\D+/', '', (string)($agent['phone'] ?? ''));
                            if ($wa !== '') {
                                if (strlen($wa) === 8) $wa = '591' . $wa; // Bolivia
                                if (strpos($wa, '591') !== 0 && strlen($wa) <= 9) $wa = '591' . $wa;
                            }
                            ?>
                            <?php if (!empty($wa)): ?>
                                <a class="wa-btn" href="https://wa.me/<?php echo htmlspecialchars($wa); ?>" target="_blank" rel="noopener">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($agent['email'])): ?>
                                <p class="muted" style="font-size:12px"><?php echo htmlspecialchars($agent['email']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="agent-card" style="text-align:center;padding:20px;grid-column:1/-1;">
                        <p>No hay agentes registrados</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- admin-tools-removed -->

            <div id="agentsStatus"></div>
        </section>

        <!-- Mapa Section -->
        <section id="ubicacion" class="map-section">
            <div class="section-header">
                <h3>Nuestra Ubicación</h3>
                <p>Visítanos en nuestra oficina principal en Santa Cruz</p>
            </div>

            <div class="map-container" id="officeMap">
                <!-- El mapa se cargará con JavaScript -->
            </div>

            <div class="contact-block">
                <div class="contact-line"><i class="fas fa-map-marker-alt"></i><span>Av. San Martín y Calle Lugones, Equipetrol, Santa Cruz de la Sierra, Bolivia</span></div>
                <div class="contact-sep"></div>
                <div class="contact-line"><i class="fas fa-phone"></i><span>+591 77657257</span></div>
                <div class="contact-sep"></div>
                <div class="contact-line"><i class="fas fa-envelope"></i><span>info@servibienessr.com</span></div>
            </div>
        </section>
    </div>

    <!-- Floating buttons - COLUMNA VERTICAL -->
    <a href="https://wa.me/59177657257" target="_blank" class="whatsapp-float" title="Contáctanos por WhatsApp">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="white">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893c0-3.18-1.24-6.169-3.495-8.424" />
        </svg>
    </a>

    <a href="https://www.facebook.com/share/1Ge6ahQxk2/" target="_blank" class="facebook-float" title="Síguenos en Facebook">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="white">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
        </svg>
    </a>

    <a href="https://www.tiktok.com/@servibienes.srl" target="_blank" class="tiktok-float" title="Síguenos en TikTok">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="white">
            <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" />
        </svg>
    </a>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // ========== TRADUCTOR (Google Translate) ==========
        function googleTranslateElementInit() {
            // Crea el traductor (oculto) para traducir toda la página
            new google.translate.TranslateElement({
                    pageLanguage: 'es',
                    autoDisplay: false
                },
                'google_translate_element'
            );
        }

        function setSiteLanguage(lang) {
            localStorage.setItem('servibienes_language', lang);
            const select = document.getElementById('languageSelect');
            if (select) select.value = lang;

            // Español = quitar cookie
            if (lang === 'es') {
                document.cookie = 'googtrans=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/';
                document.cookie = 'googtrans=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;domain=' + window.location.hostname;
                window.location.reload();
                return;
            }

            // Google translate usa cookie googtrans=/origen/destino
            const value = '/es/' + lang;
            document.cookie = 'googtrans=' + value + ';path=/';
            document.cookie = 'googtrans=' + value + ';path=/;domain=' + window.location.hostname;
            window.location.reload();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const saved = localStorage.getItem('servibienes_language') || 'es';
            const select = document.getElementById('languageSelect');
            if (select) {
                select.value = saved;
                select.addEventListener('change', (e) => setSiteLanguage(e.target.value));
            }
        });

        // ========== FUNCIONES DEL PANEL DE ADMINISTRADOR ==========
        function openAdminLogin() {
            document.getElementById('adminLogin').classList.add('active');
        }

        function closeAdminLogin() {
            document.getElementById('adminLogin').classList.remove('active');
            document.getElementById('adminPassword').value = '';
        }

        function checkAdminPassword() {
            const password = document.getElementById('adminPassword').value;
            if (password === '') {
                closeAdminLogin();
                openAdminPanel();
            } else {
                alert('Contraseña incorrecta');
            }
        }

        function openAdminPanel() {
            document.getElementById('adminContent').classList.add('active');
        }

        function closeAdmin() {
            document.getElementById('adminContent').classList.remove('active');
        }

        // ========== GESTIÓN DE GALERÍA ==========
        function deleteGalleryImage(id) {
            if (confirm('¿Eliminar esta imagen?')) {
                window.location.href = 'process/delete_gallery.php?id=' + id;
            }
        }

        function deleteAllGallery() {
            if (confirm('¿Eliminar TODAS las imágenes de galería?')) {
                window.location.href = 'process/delete_all_gallery.php';
            }
        }

        function deleteProject(id) {
            if (confirm('¿Eliminar este proyecto?')) {
                window.location.href = 'process/delete_project.php?id=' + id;
            }
        }

        function deleteAllProjects() {
            if (confirm('¿Eliminar TODOS los proyectos?')) {
                window.location.href = 'process/delete_all_projects.php';
            }
        }

        function deleteVideo(id) {
            if (confirm('¿Eliminar este video?')) {
                window.location.href = 'process/delete_video.php?id=' + id;
            }
        }

        // ========== FUNCIONES DEL FORMULARIO CLIENTE ==========
        function validateClientForm() {
            const required = document.querySelectorAll('#clientForm [required]');

            for (let field of required) {
                if (!field.value.trim()) {
                    field.style.borderColor = '#e74c3c';
                    alert('Complete todos los campos obligatorios');
                    field.focus();
                    return false;
                } else {
                    field.style.borderColor = '';
                }
            }

            return true;
        }

        function previewImages(input) {
            const container = document.getElementById('imagePreview');
            container.innerHTML = '';

            Array.from(input.files).slice(0, 10).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}">
                        <button type="button" class="remove-preview" onclick="this.parentElement.remove()">×</button>
                    `;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }

        // ========== INICIALIZACIÓN ==========
        document.addEventListener('DOMContentLoaded', function() {
            // Welcome overlay
            const welcomeOverlay = document.getElementById('welcomeOverlay');
            const enterButton = document.getElementById('enterButton');

            if (welcomeOverlay && enterButton) {
                const hideWelcome = () => {
                    // Evita que bloquee clics aunque esté transparente
                    welcomeOverlay.style.pointerEvents = 'none';
                    welcomeOverlay.style.opacity = '0';
                    setTimeout(() => {
                        welcomeOverlay.style.display = 'none';
                    }, 500);
                };

                // Auto-cierre
                setTimeout(hideWelcome, 3000);

                // Cierre manual
                enterButton.addEventListener('click', hideWelcome);
            }

            // Initialize map (Leaflet) - seguro
            try {
                const mapEl = document.getElementById('officeMap');
                if (mapEl && window.L && typeof L.map === 'function') {
                    // Av. San Martín y Calle Lugones (Equipetrol) - coordenadas aproximadas
                    const officeCoords = [-17.768243, -63.193480];
                    const map = L.map('officeMap', {
                        scrollWheelZoom: false
                    }).setView(officeCoords, 13);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    L.marker(officeCoords).addTo(map)
                        .bindPopup('<b>Servibienes S.R.L.</b><br>Av. San Martín y Calle Lugones, Equipetrol')
                        .openPopup();
                }
            } catch (e) {
                console.warn('Mapa no disponible:', e);
            }
        });

        (function() {

            function qs(sel, root) {
                return (root || document).querySelector(sel);
            }

            function qsa(sel, root) {
                return Array.from((root || document).querySelectorAll(sel));
            }

            // Dropdown "Proyectos" (click)
            function initDropdown() {
                const dd = qs('.hero-dropdown');
                if (!dd) return;
                const btn = qs('.hero-dropbtn', dd);
                const menu = qs('.hero-dropmenu', dd);
                if (!btn || !menu) return;

                function setOpen(v) {
                    dd.classList.toggle('open', v);
                    btn.setAttribute('aria-expanded', v ? 'true' : 'false');
                }
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    setOpen(!dd.classList.contains('open'));
                });
                document.addEventListener('click', (e) => {
                    if (!dd.contains(e.target)) setOpen(false);
                });
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') setOpen(false);
                });
            }

            // Generic horizontal carousel (auto + botones). 
            // En móviles usamos scroll horizontal nativo para que el auto-slide también funcione.
            function makeCarousel(opts) {
                const container = opts.container;
                const track = opts.track;
                const prevBtn = opts.prevBtn;
                const nextBtn = opts.nextBtn;
                const itemSelector = opts.itemSelector;
                const intervalMs = opts.intervalMs || 3500;
                if (!container || !track) return;

                const items = qsa(itemSelector, track);
                if (items.length === 0) return;

                // Marcar como inicializado (evita doble inicialización por otros scripts)
                try {
                    track.dataset.carouselInit = '1';
                    container.dataset.carouselInit = '1';
                } catch (_) {}

                const isTouch = (('ontouchstart' in window) || (navigator.maxTouchPoints > 0));
                const isSmallScreen = () => {
                    if (window.matchMedia) return window.matchMedia('(max-width: 768px)').matches;
                    return (window.innerWidth || 0) <= 768;
                };

                let idx = 0;
                let timer = null;
                let scrollEl = null;

                function stepSize() {
                    const it = items[0];
                    const cs = getComputedStyle(it);
                    const mr = parseFloat(cs.marginRight || '0') || 0;
                    const ml = parseFloat(cs.marginLeft || '0') || 0;
                    return it.getBoundingClientRect().width + mr + ml;
                }

                function visibleCount() {
                    const s = stepSize();
                    if (s <= 0) return 1;
                    return Math.max(1, Math.floor(container.getBoundingClientRect().width / s));
                }

                function maxIdx() {
                    return Math.max(0, items.length - visibleCount());
                }

                function pickScrollEl() {
                    // Preferir el elemento realmente desplazable (track o container)
                    const candidates = [track, container];
                    for (const el of candidates) {
                        if (!el) continue;
                        const cs = getComputedStyle(el);
                        const ox = String(cs.overflowX || '').toLowerCase();
                        const scrollable = (ox === 'auto' || ox === 'scroll');
                        if (scrollable && el.scrollWidth > el.clientWidth + 2) {
                            scrollEl = el;
                            return;
                        }
                    }
                    // Fallback por tamaño
                    if (track.scrollWidth > track.clientWidth + 2) scrollEl = track;
                    else if (container.scrollWidth > container.clientWidth + 2) scrollEl = container;
                    else scrollEl = null;
                }

                function useScrollMode() {
                    return isTouch || isSmallScreen();
                }

                function update() {
                    const s = stepSize();
                    idx = Math.max(0, Math.min(idx, maxIdx()));

                    if (useScrollMode()) {
                        pickScrollEl();
                        if (scrollEl) {
                            const left = idx * s;
                            try {
                                scrollEl.scrollTo({
                                    left,
                                    behavior: 'smooth'
                                });
                            } catch (e) {
                                scrollEl.scrollLeft = left;
                            }
                            return;
                        }
                    }

                    // Desktop: transform
                    track.style.transform = 'translateX(' + (-idx * s) + 'px)';
                }

                function next() {
                    const m = maxIdx();
                    idx = (idx >= m) ? 0 : idx + 1;
                    update();
                }

                function prev() {
                    const m = maxIdx();
                    idx = (idx <= 0) ? m : idx - 1;
                    update();
                }

                function restart() {
                    if (timer) clearInterval(timer);
                    if (items.length > visibleCount()) timer = setInterval(next, intervalMs);
                }

                // Botones
                nextBtn && nextBtn.addEventListener('click', () => {
                    next();
                    restart();
                });
                prevBtn && prevBtn.addEventListener('click', () => {
                    prev();
                    restart();
                });

                // Pausar al pasar mouse (desktop)
                container.addEventListener('mouseenter', () => {
                    if (timer) clearInterval(timer);
                });
                container.addEventListener('mouseleave', () => {
                    restart();
                });

                // Pausar al tocar (móvil)
                container.addEventListener('touchstart', () => {
                    if (timer) clearInterval(timer);
                }, {
                    passive: true
                });
                container.addEventListener('touchend', () => {
                    restart();
                }, {
                    passive: true
                });

                // Recalcular
                window.addEventListener('resize', () => {
                    pickScrollEl();
                    update();
                    restart();
                });

                // Si el usuario hace scroll manual, intentamos mantener idx sincronizado (móvil)
                const syncFromScroll = () => {
                    if (!useScrollMode()) return;
                    pickScrollEl();
                    if (!scrollEl) return;
                    const s = stepSize();
                    if (s <= 0) return;
                    idx = Math.round(scrollEl.scrollLeft / s);
                    idx = Math.max(0, Math.min(idx, maxIdx()));
                };
                track.addEventListener('scroll', () => {
                    syncFromScroll();
                }, {
                    passive: true
                });
                container.addEventListener('scroll', () => {
                    syncFromScroll();
                }, {
                    passive: true
                });

                // Si las imágenes cargan después, actualizar de nuevo
                setTimeout(() => {
                    pickScrollEl();
                    update();
                    restart();
                }, 300);

                pickScrollEl();
                update();
                restart();
            }


            // Catálogo: selector de categorías (venta/alquiler/anticretico)
            function initCatalogTabs() {
                const selectors = qsa('.category-selector');
                if (selectors.length === 0) return;

                function show(cat) {
                    selectors.forEach(s => s.classList.toggle('active', s.dataset.category === cat));
                    ['venta', 'alquiler', 'anticretico'].forEach(c => {
                        const el = qs('#' + c + '-content');
                        if (!el) return;
                        const active = (c === cat);
                        el.classList.toggle('active', active);
                        el.style.display = active ? '' : 'none';
                    });

                    // actualizar botón "Ver propiedades"
                    const q = (qs('#propertySearch')?.value || '').trim();
                    const btn = qs('#openCatalogBtn');
                    if (btn) {
                        const url = new URL(btn.getAttribute('href') || 'propiedades.php', window.location.href);
                        url.pathname = url.pathname.replace(/.*\/([^\/]+)$/, '$1'); // deja solo el archivo
                        url.searchParams.set('tipo', cat);
                        if (q) url.searchParams.set('q', q);
                        btn.setAttribute('href', url.pathname + '?' + url.searchParams.toString());
                    }
                }

                selectors.forEach(s => {
                    s.addEventListener('click', () => {
                        show(s.dataset.category);
                        // si hay búsqueda, re-filtrar
                        if (typeof window.searchProperties === 'function') window.searchProperties();
                    });
                });

                // init with the active selector, fallback 'venta'
                const active = selectors.find(s => s.classList.contains('active'))?.dataset.category || 'venta';
                show(active);
            }

            // Catálogo: búsqueda simple (filtra tarjetas)
            window.searchProperties = function() {
                const input = qs('#propertySearch');
                const query = (input?.value || '').trim().toLowerCase();

                const activeContent = qs('.category-content.active') || qs('#venta-content');
                if (!activeContent) return;

                const cards = qsa('.category-property', activeContent);
                let visible = 0;

                cards.forEach(card => {
                    const txt = card.innerText.toLowerCase();
                    const ok = query === '' || txt.includes(query);
                    card.style.display = ok ? '' : 'none';
                    if (ok) visible++;
                });

                // actualizar botón "Ver propiedades" según query + pestaña activa
                const activeCat = qs('.category-selector.active')?.dataset.category || 'venta';
                const btn = qs('#openCatalogBtn');
                if (btn) {
                    const params = new URLSearchParams();
                    params.set('tipo', activeCat);
                    if (query) params.set('q', query);
                    btn.setAttribute('href', 'propiedades.php?' + params.toString());
                }

                // mensaje "no hay resultados"
                let empty = qs('.catalog-empty', activeContent);
                if (!empty) {
                    empty = document.createElement('div');
                    empty.className = 'catalog-empty muted';
                    empty.style.padding = '16px';
                    empty.style.marginTop = '10px';
                    empty.style.textAlign = 'center';
                    empty.textContent = 'No se encontraron propiedades con esa búsqueda.';
                    activeContent.appendChild(empty);
                }
                empty.style.display = (query !== '' && visible === 0) ? '' : 'none';
            };

            // Init all
            document.addEventListener('DOMContentLoaded', function() {
                // Dropdown se gestiona en el bloque "Navegación" (más robusto para móviles)

                // Galería superior (250x250)
                makeCarousel({
                    container: qs('.slider-container'),
                    track: qs('#galleryTrack'),
                    prevBtn: qs('#prevBtn'),
                    nextBtn: qs('#nextBtn'),
                    itemSelector: '.gallery-item',
                    intervalMs: 3200
                });

                // Carrusel "Proyectos Destacados"
                makeCarousel({
                    container: qs('.projects-gallery-container'),
                    track: qs('#projectsGallery'),
                    prevBtn: qs('.projects-prev'),
                    nextBtn: qs('.projects-next'),
                    itemSelector: '.project-item',
                    intervalMs: 3600
                });
                // Mini-galería por proyecto (móvil): usamos scroll horizontal nativo para mostrar TODAS las imágenes.

                initCatalogTabs();
                // primer filtro
                if (typeof window.searchProperties === 'function') window.searchProperties();
            });
        })();
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script src="assets/js/main.js"></script>


    <script>
        // ===== Navegación: Dropdown "Proyectos" y botones "Ir a inicio" =====
        (function() {
            function closeAllDropdowns() {
                document.querySelectorAll('.hero-dropdown.open').forEach(function(d) {
                    d.classList.remove('open');
                    var btn = d.querySelector('.hero-dropbtn');
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                });
            }

            // Dropdown "Proyectos" (robusto en móvil):
            // - Permite desplazarse (scroll) dentro del menú sin que se cierre.
            // - Cierra solo en "tap" real fuera, o al elegir una opción.
            var lastTouchMoveAt = 0;
            var TOUCH_MOVE_GRACE = 450; // ms

            // Detectar si el usuario está deslizando
            document.addEventListener('touchmove', function() {
                lastTouchMoveAt = Date.now();
            }, {
                passive: true
            });

            // Evitar que el menú se cierre por "click" fantasma tras un scroll
            document.addEventListener('click', function(e) {
                if (Date.now() - lastTouchMoveAt < TOUCH_MOVE_GRACE) {
                    // Si el usuario estaba deslizando, no procesar cierre/apertura por click
                    return;
                }

                var drop = e.target.closest('.hero-dropdown');
                var btn = e.target.closest('.hero-dropbtn');

                // Click fuera: cerrar
                if (!drop) {
                    closeAllDropdowns();
                    return;
                }

                // Click en botón: abrir/cerrar
                if (btn) {
                    e.preventDefault();
                    var isOpen = drop.classList.contains('open');
                    closeAllDropdowns();
                    if (!isOpen) {
                        drop.classList.add('open');
                        btn.setAttribute('aria-expanded', 'true');
                    }
                }
            });

            // Dentro del menú: evitar que un toque cierre el dropdown (especialmente en iOS)
            document.addEventListener('touchstart', function(e) {
                var menu = e.target.closest('.hero-dropmenu');
                if (menu) {
                    // No detener el scroll; solo evitar propagación a handlers de cierre
                    e.stopPropagation();
                }
            }, {
                passive: true
            });

            // Cuando el público elige una sección del menú: cerrar dropdown y hacer scroll suave
            document.addEventListener('click', function(e) {
                var a = e.target.closest('.hero-dropmenu a[href^="#"]');
                if (!a) return;
                var href = a.getAttribute('href');
                if (!href) return;
                closeAllDropdowns();
            });

            // Botón "Ir a inicio" en cada sección indicada
            function injectToTopButtons() {
                var ids = ['ofertas', 'eventos', 'formulario', 'destacados', 'proyectos', 'videos', 'catalogo', 'agentes', 'identidad', 'ubicacion'];
                ids.forEach(function(id) {
                    var el = document.getElementById(id);
                    if (!el) return;
                    if (el.querySelector('.to-top-btn')) return;
                    var a = document.createElement('a');
                    a.href = '#inicio';
                    a.className = 'to-top-btn';
                    a.title = 'Ir a inicio';
                    a.setAttribute('aria-label', 'Ir a inicio');
                    a.innerHTML = '<i class="fas fa-arrow-up"></i>';
                    el.appendChild(a);
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', injectToTopButtons);
            } else {
                injectToTopButtons();
            }
        })();
    </script>


    <script>
        (function() {
            // Project-block carousels (horizontal scroll-snap)
            // - Buttons scroll left/right
            // - Autoplay moves to next image automatically
            const AUTOPLAY_MS = 3500;
            const USER_PAUSE_MS = 6000; // pause autoplay for a while after user interaction

            function byId(id) {
                return document.getElementById(id);
            }

            function getStep(el) {
                try {
                    const first = el.querySelector('.project-block-item');
                    if (!first) return Math.max(240, Math.floor(el.clientWidth * 0.65));
                    const rect = first.getBoundingClientRect();
                    const cs = window.getComputedStyle(first);
                    const mr = parseFloat(cs.marginRight || '0') || 0;
                    const ml = parseFloat(cs.marginLeft || '0') || 0;
                    // the item itself contains padding; use full width + margins
                    return Math.max(180, Math.floor(rect.width + mr + ml));
                } catch (e) {
                    return 260;
                }
            }

            function atEnd(el) {
                return (el.scrollLeft + el.clientWidth) >= (el.scrollWidth - 8);
            }

            function scrollNext(el) {
                const step = getStep(el);
                if (atEnd(el)) {
                    el.scrollTo({
                        left: 0,
                        behavior: 'smooth'
                    });
                } else {
                    el.scrollBy({
                        left: step,
                        behavior: 'smooth'
                    });
                }
            }

            // Button controls
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.pbc-btn');
                if (!btn) return;
                const id = btn.getAttribute('data-target');
                const el = byId(id);
                if (!el) return;
                const dir = btn.classList.contains('pbc-prev') ? -1 : 1;
                const step = getStep(el);
                el.dataset.userInteractedAt = String(Date.now());
                el.scrollBy({
                    left: dir * step,
                    behavior: 'smooth'
                });
            });

            // Autoplay for every carousel
            function initAutoplay() {
                const carousels = Array.from(document.querySelectorAll('.project-block-carousel'));
                carousels.forEach((el) => {
                    // don't stack multiple timers
                    if (el._pbcTimer) return;

                    // mark user interactions to pause autoplay
                    const markInteract = () => {
                        el.dataset.userInteractedAt = String(Date.now());
                    };
                    el.addEventListener('pointerdown', markInteract, {
                        passive: true
                    });
                    el.addEventListener('touchstart', markInteract, {
                        passive: true
                    });
                    el.addEventListener('wheel', markInteract, {
                        passive: true
                    });
                    el.addEventListener('scroll', () => {
                        // scrolling also counts as interaction
                        markInteract();
                    }, {
                        passive: true
                    });

                    // pause on hover (desktop)
                    let hover = false;
                    el.addEventListener('mouseenter', () => {
                        hover = true;
                    });
                    el.addEventListener('mouseleave', () => {
                        hover = false;
                    });

                    el._pbcTimer = setInterval(() => {
                        if (hover) return;
                        const last = parseInt(el.dataset.userInteractedAt || '0', 10) || 0;
                        if (last && (Date.now() - last) < USER_PAUSE_MS) return;
                        // autoplay only when there is overflow
                        if (el.scrollWidth <= el.clientWidth + 10) return;
                        scrollNext(el);
                    }, AUTOPLAY_MS);
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAutoplay);
            } else {
                initAutoplay();
            }
        })();
    </script>

</body>

</html>