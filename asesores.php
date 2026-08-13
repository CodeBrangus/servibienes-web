<?php
// ============================================================
// Servibienes — Datos de asesores
// Cada clave es el slug que se usa en la URL: servibienessrl.com/mauricio-cespedes
// Para agregar un asesor nuevo: copiar un bloque, cambiar la clave (slug) y los datos.
// No requiere tocar asesor.php ni ningún otro archivo de lógica.
// ============================================================

return [

  "mauricio-cespedes" => [
    "nombre"     => "Mauricio Céspedes Justiniano",
    "cargo"      => "CEO de Servibienes S.R.L.",
    "subtitulo"  => "Empresario del rubro inmobiliario y de la construcción",
    "whatsapp"   => "59177657257",
    // ruta relativa dentro de home-nuevo/assets/equipo/asesores/
    "foto"       => "mauricio-cespedes.png",
    "bio"        => "Con más de 10 años de trayectoria en el sector inmobiliario, mi carrera se ha construido sobre tres pilares fundamentales: la ética profesional, el liderazgo y la confianza plena en los procesos. Como CEO de Servibienes S.R.L., he dirigido el crecimiento de la empresa consolidándola como un referente en Santa Cruz de la Sierra, tanto en la comercialización inmobiliaria como en el desarrollo de proyectos de construcción.",

    "redes" => [
      ["tipo" => "tiktok",    "url" => "https://www.tiktok.com/@mauriciocespedesj",                              "label" => "Mauricio Cespedes Justiniano"],
      ["tipo" => "facebook",  "url" => "https://www.facebook.com/Mauricio-Cespedes-Servibienes-61555702106386/", "label" => "Mauricio Cespedes Servibienes"],
      ["tipo" => "instagram", "url" => "https://www.instagram.com/mauricio_cespedes_consultoria/",               "label" => "Mauricio Cespedes"],
      ["tipo" => "web",       "url" => "https://servibienessrl.com/",                                            "label" => "Servibienessrl.com"],
    ],

    // iconos disponibles hoy en includes/iconos.php: trofeo, balanza, foco
    "badges" => [
      ["icono" => "trofeo",  "texto" => "+10 años de experiencia"],
      ["icono" => "balanza", "texto" => "Ética y transparencia"],
      ["icono" => "foco",    "texto" => "Estrategias de marketing de alto impacto"],
    ],

    // ASUNCIÓN A CONFIRMAR: propiedades propias del asesor, distintas del carrusel
    // global "calientes". Rutas relativas a home-nuevo/assets/equipo/asesores/
    "destacados" => [
      "mauricio-cespedes/destacado-1.jpg",
      "mauricio-cespedes/destacado-2.jpg",
      "mauricio-cespedes/destacado-3.jpg",
    ],
  ],

  // "otro-asesor-slug" => [ ... copiar bloque de arriba ... ],

];
