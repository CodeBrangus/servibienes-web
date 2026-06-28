<?php
require_once 'config.php';
require_once 'functions.php';

$success = isset($_GET['success']) ? intval($_GET['success']) : 0;
$error   = isset($_GET['error'])   ? intval($_GET['error'])   : 0;
$tab     = isset($_GET['tab'])     ? $_GET['tab']             : 'vender';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Contacto – Servibienes S.R.L.</title>
<meta name="description" content="Contáctanos para vender, alquilar o encontrar tu propiedad ideal en Bolivia."/>
<link rel="stylesheet" href="/home-nuevo/css/styles.css"/>
<style>
/* ── Variables consistentes con la home ── */
:root{
  --gold:#C9A84C;
  --gold-dark:#a8862f;
  --dark:#1a1a2e;
  --dark2:#16213e;
  --card-bg:rgba(255,255,255,0.04);
  --border:rgba(201,168,76,0.25);
  --text-muted:rgba(255,255,255,0.6);
  --radius:12px;
  --transition:0.3s ease;
}

*{box-sizing:border-box;margin:0;padding:0}

body{
  font-family:'Segoe UI',system-ui,sans-serif;
  background:var(--dark);
  color:#fff;
  min-height:100vh;
}

/* ── HEADER simple ── */
.page-header{
  background:linear-gradient(135deg,var(--dark2) 0%,var(--dark) 100%);
  border-bottom:1px solid var(--border);
  padding:16px 24px;
  display:flex;
  align-items:center;
  justify-content:space-between;
}
.page-header a.logo{
  font-size:1.2rem;
  font-weight:700;
  color:var(--gold);
  text-decoration:none;
  letter-spacing:1px;
}
.page-header a.back-home{
  color:var(--text-muted);
  text-decoration:none;
  font-size:.9rem;
  display:flex;
  align-items:center;
  gap:6px;
  transition:color var(--transition);
}
.page-header a.back-home:hover{color:#fff}

/* ── HERO ── */
.contact-hero{
  background:linear-gradient(135deg,var(--dark2) 0%,#0f3460 100%);
  padding:60px 24px 40px;
  text-align:center;
  position:relative;
  overflow:hidden;
}
.contact-hero::before{
  content:'';
  position:absolute;inset:0;
  background:radial-gradient(ellipse at center,rgba(201,168,76,.12) 0%,transparent 70%);
}
.contact-hero h1{
  font-size:clamp(1.8rem,4vw,2.8rem);
  font-weight:800;
  position:relative;
}
.contact-hero h1 span{color:var(--gold)}
.contact-hero p{
  color:var(--text-muted);
  margin-top:12px;
  font-size:1.05rem;
  position:relative;
}

/* ── TABS ── */
.tabs-wrapper{
  max-width:700px;
  margin:32px auto 0;
  padding:0 16px;
  position:relative;
}
.tabs{
  display:grid;
  grid-template-columns:1fr 1fr;
  background:rgba(255,255,255,.05);
  border:1px solid var(--border);
  border-radius:50px;
  padding:4px;
  gap:4px;
}
.tab-btn{
  padding:13px 20px;
  border:none;
  border-radius:50px;
  cursor:pointer;
  font-size:.95rem;
  font-weight:600;
  transition:all var(--transition);
  background:transparent;
  color:var(--text-muted);
}
.tab-btn.active{
  background:linear-gradient(135deg,var(--gold),var(--gold-dark));
  color:#fff;
  box-shadow:0 4px 15px rgba(201,168,76,.35);
}
.tab-btn:hover:not(.active){color:#fff}

/* ── MAIN CONTENT ── */
.contact-main{
  max-width:860px;
  margin:0 auto;
  padding:32px 16px 60px;
}

/* ── ALERT MESSAGES ── */
.alert-box{
  border-radius:var(--radius);
  padding:18px 22px;
  margin-bottom:28px;
  display:flex;
  align-items:flex-start;
  gap:14px;
  animation:slideDown .4s ease;
}
@keyframes slideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
.alert-box.success{
  background:rgba(34,197,94,.12);
  border:1px solid rgba(34,197,94,.35);
}
.alert-box.error{
  background:rgba(239,68,68,.12);
  border:1px solid rgba(239,68,68,.35);
}
.alert-icon{font-size:1.5rem}
.alert-title{font-weight:700;margin-bottom:4px}
.alert-box.success .alert-title{color:#4ade80}
.alert-box.error .alert-title{color:#f87171}
.alert-text{color:var(--text-muted);font-size:.92rem;line-height:1.5}

/* ── FORM CARD ── */
.form-card{
  background:var(--card-bg);
  border:1px solid var(--border);
  border-radius:16px;
  padding:36px;
  backdrop-filter:blur(10px);
}
@media(max-width:600px){.form-card{padding:22px 16px}}

.form-section-title{
  font-size:1.1rem;
  font-weight:700;
  color:var(--gold);
  margin:28px 0 16px;
  padding-bottom:8px;
  border-bottom:1px solid var(--border);
  display:flex;
  align-items:center;
  gap:8px;
}
.form-section-title:first-of-type{margin-top:0}

/* ── GRID ── */
.form-grid{display:grid;gap:18px}
.form-grid.cols-2{grid-template-columns:1fr 1fr}
@media(max-width:560px){.form-grid.cols-2{grid-template-columns:1fr}}

/* ── FIELD ── */
.field label{
  display:block;
  font-size:.85rem;
  font-weight:600;
  color:var(--text-muted);
  margin-bottom:7px;
  letter-spacing:.5px;
  text-transform:uppercase;
}
.field label span.req{color:var(--gold)}

.field input,
.field select,
.field textarea{
  width:100%;
  background:rgba(255,255,255,.06);
  border:1px solid var(--border);
  border-radius:8px;
  color:#fff;
  padding:12px 14px;
  font-size:.95rem;
  font-family:inherit;
  transition:border-color var(--transition),box-shadow var(--transition);
  outline:none;
}
.field input:focus,
.field select:focus,
.field textarea:focus{
  border-color:var(--gold);
  box-shadow:0 0 0 3px rgba(201,168,76,.15);
}
.field input::placeholder,
.field textarea::placeholder{color:rgba(255,255,255,.3)}
.field select option{background:var(--dark2);color:#fff}
.field textarea{resize:vertical;min-height:110px}

/* ── BUTTON GROUP (opciones visuales) ── */
.btn-group{
  display:flex;
  flex-wrap:wrap;
  gap:8px;
}
.btn-opt{
  padding:9px 18px;
  border-radius:50px;
  border:1px solid var(--border);
  background:transparent;
  color:var(--text-muted);
  font-size:.88rem;
  font-weight:600;
  cursor:pointer;
  transition:all var(--transition);
}
.btn-opt:hover{border-color:var(--gold);color:#fff}
.btn-opt.selected{
  background:linear-gradient(135deg,var(--gold),var(--gold-dark));
  border-color:transparent;
  color:#fff;
  box-shadow:0 3px 10px rgba(201,168,76,.3);
}
/* hidden radio/checkbox behind btn-opt */
.btn-group input[type=radio],
.btn-group input[type=checkbox]{display:none}

/* ── FILE UPLOAD ── */
.upload-zone{
  border:2px dashed var(--border);
  border-radius:var(--radius);
  padding:28px;
  text-align:center;
  cursor:pointer;
  transition:all var(--transition);
  position:relative;
}
.upload-zone:hover,.upload-zone.dragover{
  border-color:var(--gold);
  background:rgba(201,168,76,.06);
}
.upload-zone input[type=file]{
  position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;
}
.upload-icon{font-size:2rem;margin-bottom:8px}
.upload-label{font-weight:600;color:#fff;font-size:.95rem}
.upload-hint{font-size:.8rem;color:var(--text-muted);margin-top:4px}

.preview-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(90px,1fr));
  gap:8px;
  margin-top:12px;
}
.preview-item{
  position:relative;
  border-radius:8px;
  overflow:hidden;
  aspect-ratio:1;
  border:1px solid var(--border);
}
.preview-item img,.preview-item video{
  width:100%;height:100%;object-fit:cover;display:block;
}
.preview-item .remove-file{
  position:absolute;top:4px;right:4px;
  background:rgba(0,0,0,.7);
  border:none;border-radius:50%;
  color:#fff;font-size:.7rem;
  width:20px;height:20px;
  cursor:pointer;display:flex;align-items:center;justify-content:center;
}

.video-preview-item{
  border-radius:8px;
  overflow:hidden;
  border:1px solid var(--border);
  position:relative;
  margin-top:8px;
}
.video-preview-item video{width:100%;max-height:200px;display:block}
.video-preview-item .remove-file{
  position:absolute;top:8px;right:8px;
  background:rgba(0,0,0,.75);
  border:none;border-radius:50%;
  color:#fff;font-size:.8rem;
  width:26px;height:26px;
  cursor:pointer;display:flex;align-items:center;justify-content:center;
}

/* ── SUBMIT BTN ── */
.btn-submit{
  width:100%;
  padding:16px;
  background:linear-gradient(135deg,var(--gold),var(--gold-dark));
  border:none;
  border-radius:50px;
  color:#fff;
  font-size:1.05rem;
  font-weight:700;
  cursor:pointer;
  transition:all var(--transition);
  margin-top:28px;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:10px;
  letter-spacing:.5px;
}
.btn-submit:hover{
  transform:translateY(-2px);
  box-shadow:0 8px 25px rgba(201,168,76,.4);
}
.btn-submit:disabled{opacity:.6;cursor:not-allowed;transform:none}

/* ── PROGRESS BAR ── */
.upload-progress{
  display:none;
  margin-top:16px;
  background:rgba(255,255,255,.08);
  border-radius:50px;
  height:6px;
  overflow:hidden;
}
.upload-progress-bar{
  height:100%;
  background:linear-gradient(90deg,var(--gold),var(--gold-dark));
  border-radius:50px;
  width:0;
  transition:width .3s ease;
}

/* ── TABS PANEL ── */
.tab-panel{display:none}
.tab-panel.active{display:block}

/* ── INFO ASIDE ── */
.contact-aside{
  margin-top:36px;
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
  gap:16px;
}
.aside-card{
  background:var(--card-bg);
  border:1px solid var(--border);
  border-radius:var(--radius);
  padding:20px;
  display:flex;
  gap:14px;
  align-items:flex-start;
}
.aside-icon{font-size:1.5rem;flex-shrink:0}
.aside-title{font-weight:700;font-size:.95rem;margin-bottom:4px}
.aside-text{color:var(--text-muted);font-size:.85rem;line-height:1.5}
.aside-text a{color:var(--gold);text-decoration:none}
.aside-text a:hover{text-decoration:underline}

/* ── FILE SIZE WARNING ── */
.file-warning{
  font-size:.8rem;color:#f87171;margin-top:6px;display:none;
}
</style>
</head>
<body>

<!-- HEADER -->
<header class="page-header">
  <a href="/" class="logo">SERVIBIENES</a>
  <a href="/" class="back-home">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M19 12H5M12 5l-7 7 7 7"/>
    </svg>
    Volver al inicio
  </a>
</header>

<!-- HERO -->
<section class="contact-hero">
  <h1>Estamos para <span>ayudarte</span></h1>
  <p>Cuéntanos qué necesitas y te respondemos a la brevedad</p>
</section>

<!-- TABS -->
<div class="tabs-wrapper">
  <div class="tabs" role="tablist">
    <button class="tab-btn <?= $tab === 'comprar' ? 'active' : '' ?>"
            onclick="switchTab('comprar')" role="tab" id="tab-comprar">
      🔍 Quiero comprar / alquilar
    </button>
    <button class="tab-btn <?= $tab === 'vender' ? 'active' : '' ?>"
            onclick="switchTab('vender')" role="tab" id="tab-vender">
      🏠 Quiero ofrecer mi propiedad
    </button>
  </div>
</div>

<!-- MAIN -->
<main class="contact-main">

  <!-- ALERTAS -->
  <?php if ($success === 1): ?>
  <div class="alert-box success" role="alert">
    <span class="alert-icon">✅</span>
    <div>
      <div class="alert-title">¡Mensaje recibido!</div>
      <div class="alert-text">
        Gracias por contactarnos. Un asesor se comunicará contigo en las próximas horas.<br>
        <?php if(!empty($_GET['email'])): ?>
          Enviamos una confirmación a <strong><?= htmlspecialchars($_GET['email']) ?></strong>.
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php elseif ($error === 1): ?>
  <div class="alert-box error" role="alert">
    <span class="alert-icon">❌</span>
    <div>
      <div class="alert-title">Algo salió mal</div>
      <div class="alert-text">
        <?php
        $errMsg = [
          2 => 'Por favor completá los campos obligatorios (nombre, teléfono, operación y tipo de propiedad).',
          3 => 'Uno o más archivos superan el tamaño permitido.',
          4 => 'Formato de archivo no permitido.',
        ];
        echo $errMsg[$_GET['error'] ?? 1] ?? 'Ocurrió un error. Por favor intentá nuevamente.';
        ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ═══════════════════════════════════════
       TAB: COMPRAR / ALQUILAR
  ═══════════════════════════════════════ -->
  <div class="tab-panel <?= $tab === 'comprar' ? 'active' : '' ?>" id="panel-comprar">
    <div class="form-card">
      <form method="POST" action="/process/submit_client_form.php" id="form-comprar">
        <input type="hidden" name="contact_type" value="comprar"/>
        <input type="hidden" name="operation_hidden" id="operation-comprar-hidden"/>
        <input type="hidden" name="property_type_hidden" id="ptype-comprar-hidden"/>
        <input type="hidden" name="budget_range_hidden" id="budget-hidden"/>
        <input type="hidden" name="timeline_hidden" id="timeline-hidden"/>

        <!-- DATOS PERSONALES -->
        <p class="form-section-title">👤 Tus datos</p>
        <div class="form-grid cols-2">
          <div class="field">
            <label>Nombre completo <span class="req">*</span></label>
            <input type="text" name="name" placeholder="Juan Pérez" required maxlength="100"/>
          </div>
          <div class="field">
            <label>WhatsApp / Teléfono <span class="req">*</span></label>
            <input type="tel" name="phone" placeholder="70000000" required maxlength="20"/>
          </div>
        </div>
        <div class="form-grid" style="margin-top:18px">
          <div class="field">
            <label>Correo electrónico</label>
            <input type="email" name="email" placeholder="tucorreo@email.com" maxlength="100"/>
          </div>
        </div>

        <!-- QUÉ BUSCA -->
        <p class="form-section-title">🔍 ¿Qué estás buscando?</p>

        <div class="field">
          <label>Tipo de operación <span class="req">*</span></label>
          <div class="btn-group" id="op-comprar-group">
            <button type="button" class="btn-opt" data-value="venta" onclick="selectOpt(this,'operation-comprar-hidden','op-comprar-group')">Compra</button>
            <button type="button" class="btn-opt" data-value="alquiler" onclick="selectOpt(this,'operation-comprar-hidden','op-comprar-group')">Alquiler</button>
            <button type="button" class="btn-opt" data-value="anticretico" onclick="selectOpt(this,'operation-comprar-hidden','op-comprar-group')">Anticrético</button>
          </div>
        </div>

        <div class="field" style="margin-top:18px">
          <label>Tipo de propiedad <span class="req">*</span></label>
          <div class="btn-group" id="ptype-comprar-group">
            <button type="button" class="btn-opt" data-value="casa" onclick="selectOpt(this,'ptype-comprar-hidden','ptype-comprar-group')">🏡 Casa</button>
            <button type="button" class="btn-opt" data-value="departamento" onclick="selectOpt(this,'ptype-comprar-hidden','ptype-comprar-group')">🏢 Departamento</button>
            <button type="button" class="btn-opt" data-value="terreno" onclick="selectOpt(this,'ptype-comprar-hidden','ptype-comprar-group')">🌿 Terreno</button>
            <button type="button" class="btn-opt" data-value="local_comercial" onclick="selectOpt(this,'ptype-comprar-hidden','ptype-comprar-group')">🏪 Local Comercial</button>
            <button type="button" class="btn-opt" data-value="oficina" onclick="selectOpt(this,'ptype-comprar-hidden','ptype-comprar-group')">💼 Oficina</button>
            <button type="button" class="btn-opt" data-value="otro" onclick="selectOpt(this,'ptype-comprar-hidden','ptype-comprar-group')">Otro</button>
          </div>
        </div>

        <div class="form-grid cols-2" style="margin-top:18px">
          <div class="field">
            <label>Zona / Barrio preferido</label>
            <input type="text" name="location" placeholder="Ej: Plan 3000, Urbarí, Norte..." maxlength="200"/>
          </div>
          <div class="field">
            <label>Presupuesto aproximado (Bs)</label>
            <input type="number" name="price" placeholder="Ej: 150000" min="0" step="1000"/>
          </div>
        </div>

        <div class="field" style="margin-top:18px">
          <label>Rango de presupuesto</label>
          <div class="btn-group" id="budget-group">
            <button type="button" class="btn-opt" data-value="menos-50k" onclick="selectOpt(this,'budget-hidden','budget-group')">Menos de $50K</button>
            <button type="button" class="btn-opt" data-value="50k-100k" onclick="selectOpt(this,'budget-hidden','budget-group')">$50K – $100K</button>
            <button type="button" class="btn-opt" data-value="100k-200k" onclick="selectOpt(this,'budget-hidden','budget-group')">$100K – $200K</button>
            <button type="button" class="btn-opt" data-value="mas-200k" onclick="selectOpt(this,'budget-hidden','budget-group')">Más de $200K</button>
            <button type="button" class="btn-opt" data-value="a-consultar" onclick="selectOpt(this,'budget-hidden','budget-group')">A consultar</button>
          </div>
        </div>

        <div class="field" style="margin-top:18px">
          <label>¿Cuándo planeas concretar?</label>
          <div class="btn-group" id="timeline-group">
            <button type="button" class="btn-opt" data-value="inmediato" onclick="selectOpt(this,'timeline-hidden','timeline-group')">Lo antes posible</button>
            <button type="button" class="btn-opt" data-value="1-3meses" onclick="selectOpt(this,'timeline-hidden','timeline-group')">1–3 meses</button>
            <button type="button" class="btn-opt" data-value="3-6meses" onclick="selectOpt(this,'timeline-hidden','timeline-group')">3–6 meses</button>
            <button type="button" class="btn-opt" data-value="explorando" onclick="selectOpt(this,'timeline-hidden','timeline-group')">Solo explorando</button>
          </div>
        </div>

        <div class="field" style="margin-top:18px">
          <label>Cuéntanos más sobre lo que buscas</label>
          <textarea name="description" placeholder="Ej: Busco casa con 3 dormitorios, garage, cerca de colegio, con patio..."></textarea>
        </div>

        <button type="submit" class="btn-submit" id="submit-comprar">
          <span>📩 Enviar consulta</span>
        </button>
      </form>
    </div>
  </div>

  <!-- ═══════════════════════════════════════
       TAB: OFRECER PROPIEDAD
  ═══════════════════════════════════════ -->
  <div class="tab-panel <?= $tab === 'vender' ? 'active' : '' ?>" id="panel-vender">
    <div class="form-card">
      <form method="POST" action="/process/submit_client_form.php"
            enctype="multipart/form-data" id="form-vender">
        <input type="hidden" name="contact_type" value="vender"/>
        <input type="hidden" name="operation_hidden" id="operation-vender-hidden"/>
        <input type="hidden" name="property_type_hidden" id="ptype-vender-hidden"/>

        <!-- DATOS PERSONALES -->
        <p class="form-section-title">👤 Tus datos</p>
        <div class="form-grid cols-2">
          <div class="field">
            <label>Nombre completo <span class="req">*</span></label>
            <input type="text" name="name" placeholder="Juan Pérez" required maxlength="100"/>
          </div>
          <div class="field">
            <label>WhatsApp / Teléfono <span class="req">*</span></label>
            <input type="tel" name="phone" placeholder="70000000" required maxlength="20"/>
          </div>
        </div>
        <div class="form-grid" style="margin-top:18px">
          <div class="field">
            <label>Correo electrónico</label>
            <input type="email" name="email" placeholder="tucorreo@email.com" maxlength="100"/>
          </div>
        </div>

        <!-- PROPIEDAD -->
        <p class="form-section-title">🏠 Sobre tu propiedad</p>

        <div class="field">
          <label>¿Qué quieres hacer? <span class="req">*</span></label>
          <div class="btn-group" id="op-vender-group">
            <button type="button" class="btn-opt" data-value="venta" onclick="selectOpt(this,'operation-vender-hidden','op-vender-group')">Vender</button>
            <button type="button" class="btn-opt" data-value="alquiler" onclick="selectOpt(this,'operation-vender-hidden','op-vender-group')">Alquilar</button>
            <button type="button" class="btn-opt" data-value="anticretico" onclick="selectOpt(this,'operation-vender-hidden','op-vender-group')">Anticrético</button>
          </div>
        </div>

        <div class="field" style="margin-top:18px">
          <label>Tipo de propiedad <span class="req">*</span></label>
          <div class="btn-group" id="ptype-vender-group">
            <button type="button" class="btn-opt" data-value="casa" onclick="selectOpt(this,'ptype-vender-hidden','ptype-vender-group')">🏡 Casa</button>
            <button type="button" class="btn-opt" data-value="departamento" onclick="selectOpt(this,'ptype-vender-hidden','ptype-vender-group')">🏢 Departamento</button>
            <button type="button" class="btn-opt" data-value="terreno" onclick="selectOpt(this,'ptype-vender-hidden','ptype-vender-group')">🌿 Terreno</button>
            <button type="button" class="btn-opt" data-value="local_comercial" onclick="selectOpt(this,'ptype-vender-hidden','ptype-vender-group')">🏪 Local Comercial</button>
            <button type="button" class="btn-opt" data-value="oficina" onclick="selectOpt(this,'ptype-vender-hidden','ptype-vender-group')">💼 Oficina</button>
            <button type="button" class="btn-opt" data-value="otro" onclick="selectOpt(this,'ptype-vender-hidden','ptype-vender-group')">Otro</button>
          </div>
        </div>

        <div class="form-grid cols-2" style="margin-top:18px">
          <div class="field">
            <label>Ubicación / Dirección <span class="req">*</span></label>
            <input type="text" name="location" placeholder="Barrio, zona, referencia..." required maxlength="300"/>
          </div>
          <div class="field">
            <label>Precio solicitado (Bs)</label>
            <input type="number" name="price" placeholder="Ej: 250000" min="0" step="500"/>
          </div>
        </div>

        <div class="field" style="margin-top:18px">
          <label>Descripción de la propiedad</label>
          <textarea name="description" placeholder="Superficie, dormitorios, baños, características especiales, estado..."></textarea>
        </div>

        <!-- ARCHIVOS -->
        <p class="form-section-title">📸 Fotos de la propiedad</p>
        <div class="upload-zone" id="image-zone">
          <input type="file" name="images[]" id="image-input"
                 accept="image/jpeg,image/png,image/webp,image/gif"
                 multiple/>
          <div class="upload-icon">🖼️</div>
          <div class="upload-label">Arrastrá o hacé clic para subir fotos</div>
          <div class="upload-hint">JPG, PNG, WEBP · Máx 5 MB por foto · Hasta 10 fotos</div>
        </div>
        <div class="file-warning" id="img-warning">⚠️ Alguna imagen supera los 5 MB y no será enviada.</div>
        <div class="preview-grid" id="image-preview"></div>

        <p class="form-section-title">🎬 Video de la propiedad</p>
        <div class="upload-zone" id="video-zone">
          <input type="file" name="videos[]" id="video-input"
                 accept="video/mp4,video/webm,video/quicktime,video/x-msvideo"
                 multiple/>
          <div class="upload-icon">📹</div>
          <div class="upload-label">Arrastrá o hacé clic para subir video(s)</div>
          <div class="upload-hint">MP4, MOV, WEBM · Máx 50 MB por archivo</div>
        </div>
        <div class="file-warning" id="vid-warning">⚠️ Algún video supera los 50 MB y no será enviado.</div>
        <div id="video-preview"></div>

        <div class="field" style="margin-top:18px">
          <label>O pegá un link de video (YouTube, Drive, WhatsApp...)</label>
          <input type="url" name="video_link" placeholder="https://youtube.com/..." maxlength="500"/>
        </div>

        <div class="upload-progress" id="upload-progress">
          <div class="upload-progress-bar" id="upload-bar"></div>
        </div>

        <button type="submit" class="btn-submit" id="submit-vender">
          <span>📬 Enviar mi propiedad</span>
        </button>
      </form>
    </div>
  </div>

  <!-- INFO ASIDE -->
  <div class="contact-aside">
    <div class="aside-card">
      <span class="aside-icon">📞</span>
      <div>
        <div class="aside-title">Llamanos</div>
        <div class="aside-text">
          <a href="tel:+59177072010">77072010</a><br/>
          Lic. Mauricio Céspedes
        </div>
      </div>
    </div>
    <div class="aside-card">
      <span class="aside-icon">💬</span>
      <div>
        <div class="aside-title">WhatsApp</div>
        <div class="aside-text">
          <a href="https://wa.me/59177072010" target="_blank" rel="noopener">Escribinos ahora</a><br/>
          Respuesta rápida
        </div>
      </div>
    </div>
    <div class="aside-card">
      <span class="aside-icon">📧</span>
      <div>
        <div class="aside-title">Correo</div>
        <div class="aside-text">
          <a href="mailto:info@servibienessrl.com">info@servibienessrl.com</a>
        </div>
      </div>
    </div>
    <div class="aside-card">
      <span class="aside-icon">🕐</span>
      <div>
        <div class="aside-title">Horario de atención</div>
        <div class="aside-text">Lun–Vie: 8:00–18:00<br/>Sáb: 8:00–13:00</div>
      </div>
    </div>
  </div>

</main>

<script>
// ── Tab switch ──
function switchTab(tab) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('tab-' + tab).classList.add('active');
  document.getElementById('panel-' + tab).classList.add('active');
}

// ── Opción visual (btn-opt) ──
function selectOpt(btn, hiddenId, groupId) {
  document.querySelectorAll('#' + groupId + ' .btn-opt').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
  document.getElementById(hiddenId).value = btn.dataset.value;
}

// ── Validación antes de submit ──
function validateForm(formId, opHiddenId, ptypeHiddenId) {
  const op = document.getElementById(opHiddenId).value;
  const pt = document.getElementById(ptypeHiddenId).value;
  if (!op || !pt) {
    alert('Por favor seleccioná el tipo de operación y el tipo de propiedad.');
    return false;
  }
  return true;
}

document.getElementById('form-comprar').addEventListener('submit', function(e) {
  if (!validateForm('form-comprar','operation-comprar-hidden','ptype-comprar-hidden')) {
    e.preventDefault();
  }
});
document.getElementById('form-vender').addEventListener('submit', function(e) {
  if (!validateForm('form-vender','operation-vender-hidden','ptype-vender-hidden')) {
    e.preventDefault();
  }
});

// ── Preview imágenes ──
const imageInput  = document.getElementById('image-input');
const imagePreview = document.getElementById('image-preview');
const imgWarning  = document.getElementById('img-warning');
const MAX_IMG     = 5 * 1024 * 1024;
const MAX_VID     = 50 * 1024 * 1024;

let selectedImages = [];
let selectedVideos = [];

imageInput.addEventListener('change', function() {
  const newFiles = Array.from(this.files);
  let hasOversized = false;

  newFiles.forEach(f => {
    if (f.size > MAX_IMG) { hasOversized = true; return; }
    if (selectedImages.length >= 10) return;
    selectedImages.push(f);
  });

  imgWarning.style.display = hasOversized ? 'block' : 'none';
  renderImagePreviews();
  syncFileInput();
});

function renderImagePreviews() {
  imagePreview.innerHTML = '';
  selectedImages.forEach((f, i) => {
    const reader = new FileReader();
    reader.onload = e => {
      const div = document.createElement('div');
      div.className = 'preview-item';
      div.innerHTML = `<img src="${e.target.result}" alt="preview"/>
        <button type="button" class="remove-file" onclick="removeImage(${i})">✕</button>`;
      imagePreview.appendChild(div);
    };
    reader.readAsDataURL(f);
  });
}

function removeImage(i) {
  selectedImages.splice(i, 1);
  renderImagePreviews();
  syncFileInput();
}

// ── Preview videos ──
const videoInput   = document.getElementById('video-input');
const videoPreview = document.getElementById('video-preview');
const vidWarning   = document.getElementById('vid-warning');

videoInput.addEventListener('change', function() {
  const newFiles = Array.from(this.files);
  let hasOversized = false;

  newFiles.forEach(f => {
    if (f.size > MAX_VID) { hasOversized = true; return; }
    selectedVideos.push(f);
  });

  vidWarning.style.display = hasOversized ? 'block' : 'none';
  renderVideoPreviews();
  syncVideoInput();
});

function renderVideoPreviews() {
  videoPreview.innerHTML = '';
  selectedVideos.forEach((f, i) => {
    const url = URL.createObjectURL(f);
    const div = document.createElement('div');
    div.className = 'video-preview-item';
    div.innerHTML = `<video src="${url}" controls></video>
      <button type="button" class="remove-file" onclick="removeVideo(${i})">✕</button>`;
    videoPreview.appendChild(div);
  });
}

function removeVideo(i) {
  selectedVideos.splice(i, 1);
  renderVideoPreviews();
  syncVideoInput();
}

// ── Sincronizar FileList real con los arrays ──
function syncFileInput() {
  const dt = new DataTransfer();
  selectedImages.forEach(f => dt.items.add(f));
  imageInput.files = dt.files;
}

function syncVideoInput() {
  const dt = new DataTransfer();
  selectedVideos.forEach(f => dt.items.add(f));
  videoInput.files = dt.files;
}

// ── Drag & drop ──
['image-zone','video-zone'].forEach(id => {
  const zone = document.getElementById(id);
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
  zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('dragover');
    const inp = zone.querySelector('input[type=file]');
    inp.files = e.dataTransfer.files;
    inp.dispatchEvent(new Event('change'));
  });
});

// ── Progress bar simulado ──
document.getElementById('form-vender').addEventListener('submit', function() {
  const bar = document.getElementById('upload-progress');
  const fill = document.getElementById('upload-bar');
  bar.style.display = 'block';
  let w = 0;
  const iv = setInterval(() => {
    w = Math.min(w + Math.random() * 12, 90);
    fill.style.width = w + '%';
    if (w >= 90) clearInterval(iv);
  }, 300);
  document.getElementById('submit-vender').disabled = true;
});
</script>

</body>
</html>
