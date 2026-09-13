<?php
// ============================================================
// Lista fija de los 16 servicios de Servibienes Constructora.
// slug => [titulo, descripcion]
//
// Este array es la ÚNICA fuente de verdad: lo usan tanto index.php
// (para pintar el grid) como servicio.php (para validar el slug
// de la URL y mostrar el detalle). Si agregan un servicio 17,
// solo se agrega aquí una línea más y se crea su carpeta en
// home-nuevo/assets/servicios/<slug>/{icono.png, material/}
// ============================================================
return [
  'casas-habitacionales'        => ['titulo' => 'Casas Habitacionales',          'descripcion' => 'Diseño y construcción de viviendas unifamiliares y multifamiliares.'],
  'locales-comerciales'         => ['titulo' => 'Locales Comerciales',           'descripcion' => 'Construcción de tiendas, oficinas y espacios comerciales.'],
  'edificios-torres'            => ['titulo' => 'Edificios y Torres',            'descripcion' => 'Edificaciones en altura para uso residencial y corporativo.'],
  'diseno-interiores'           => ['titulo' => 'Diseño de Interiores',          'descripcion' => 'Planificación, acabados y ambientación de espacios interiores.'],
  'puentes'                     => ['titulo' => 'Puentes',                       'descripcion' => 'Diseño y construcción de puentes vehiculares y peatonales.'],
  'estructuras-metalicas'       => ['titulo' => 'Estructuras Metálicas',         'descripcion' => 'Fabricación y montaje de estructuras y galpones en acero.'],
  'obras-civiles'               => ['titulo' => 'Obras Civiles',                 'descripcion' => 'Ejecución de infraestructura civil en general.'],
  'baterias-compresion'         => ['titulo' => 'Baterías de Compresión',        'descripcion' => 'Instalación de plantas de compresión para gas.'],
  'urbanizaciones-loteamientos' => ['titulo' => 'Urbanizaciones y Loteamientos', 'descripcion' => 'Habilitación de terrenos y desarrollo urbano.'],
  'pavimentacion-asfaltado'     => ['titulo' => 'Pavimentación y Asfaltado',     'descripcion' => 'Construcción de vías, accesos y superficies asfaltadas.'],
  'movimiento-tierras'          => ['titulo' => 'Movimiento de Tierras',         'descripcion' => 'Excavación, nivelación y relleno de terrenos.'],
  'instalaciones-electricas'    => ['titulo' => 'Instalaciones Eléctricas',      'descripcion' => 'Diseño e instalación de sistemas eléctricos.'],
  'instalaciones-sanitarias'    => ['titulo' => 'Instalaciones Sanitarias',      'descripcion' => 'Redes de agua potable y alcantarillado.'],
  'naves-industriales-galpones' => ['titulo' => 'Naves Industriales y Galpones', 'descripcion' => 'Construcción de plantas y depósitos industriales.'],
  'remodelaciones-refacciones'  => ['titulo' => 'Remodelaciones y Refacciones',  'descripcion' => 'Renovación y mejora de espacios existentes.'],
  'supervision-fiscalizacion'   => ['titulo' => 'Supervisión y Fiscalización',   'descripcion' => 'Control técnico y seguimiento de proyectos de obra.'],
];
