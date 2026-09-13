<?php
// ============================================================
// Navbar compartido. Requiere que $whatsapp ya esté definido
// por el archivo que hace el include.
// Los links siempre apuntan a index.php#seccion para que
// funcionen igual desde la home y desde cualquier página de
// servicio.
// ============================================================
?>
<nav id="navbar">
  <!-- Zona 1: Logo -->
  <a href="index.php#inicio" class="nav-logo">
    <img src="home-nuevo/assets/logo.png" alt="Servibienes"
      onerror="this.outerHTML='<span style=\'font-size:1.4rem;font-weight:900;color:#1B2E6B\'>SB</span>'">
  </a>

  <!-- Zona 2: Links centrados -->
  <ul class="nav-links">
    <li><a href="index.php#inicio" class="activo">Inicio</a></li>
    <li><a href="index.php#proyectos" class="normal">Proyectos</a></li>
    <li><a href="index.php#contactos" class="activo">Contactos</a></li>
    <li><a href="index.php#nosotros" class="normal">Nosotros</a></li>
  </ul>

  <!-- Zona 3: CTA separado -->
  <a href="https://wa.me/<?= $whatsapp ?>" target="_blank" class="nav-cta">
    Contactar<br>con ventas
  </a>
</nav>
