<?php
// ============================================================
// Iconos SVG inline para los badges de asesor.php
// Agregar uno nuevo: sumar una clave más al array $iconos.
// ============================================================
function iconoSvg(string $nombre): string {
  $iconos = [
    'trofeo' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4zM7 4H4a3 3 0 0 0 3 5M17 4h3a3 3 0 0 1-3 5"/></svg>',
    'balanza' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18M5 8l-3 6a3 3 0 0 0 6 0l-3-6zM19 8l-3 6a3 3 0 0 0 6 0l-3-6zM5 8h14M9 21h6"/></svg>',
    'foco'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="9" r="6"/><path d="M9 21h6M10 17v4M14 17v4M12 3v0"/></svg>',
  ];
  return $iconos[$nombre] ?? '';
}
