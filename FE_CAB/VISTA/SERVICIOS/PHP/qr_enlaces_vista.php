/**ESTE HTML SE ENCUENTRA DENTRO DEL IF DE LA LINEA 58 */

<!doctype html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>No autorizado</title>
            <style>
             </style>
        </head>
        <body>
        <div class="auth-modal" aria-hidden="false">
            <div class="auth-card" role="dialog" aria-modal="true" aria-labelledby="authT">
                <div class="auth-hdr">
                    <h3 id="authT">Acceso no autorizado</h3>
                </div>
                <p>Debes iniciar sesión con el rol <strong>orientador</strong> para usar esta herramienta.</p>
                <p class="muted">Te redirigiremos al inicio en <strong id="count">5</strong> segundos.</p>
                <div class="auth-actions">
                    <a class="btn btn-secondary" href="<?php echo htmlspecialchars($homeUrl, ENT_QUOTES); ?>">Ir al inicio</a>
                    <button type="button" class="btn btn-primary" id="goNow">Ir ahora</button>
                </div>
            </div>
        </div>
        <script>
        
        </script>
        </body>
        </html>


/**SEGUNDO HTML LINEA //925*/

<!doctype html> 
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Enlaces y códigos QR para orientadores</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../componentes/estilo_qr_enlaces.css">
<link rel="shortcut icon" href="../../componentes/img/favicon.ico" type="image/x-icon">
</head>
<body>
  <div class="wrap">
    <header class="page-head">
      <div class="volver">
        <a href="panel_orientador">
          <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M14.1085 9.28033C14.4013 8.98744 14.4013 8.51256 14.1085 8.21967C13.8156 7.92678 13.3407 7.92678 13.0478 8.21967L9.79779 11.4697C9.5049 11.7626 9.5049 12.2374 9.79779 12.5303L13.0478 15.7803C13.3407 16.0732 13.8156 16.0732 14.1085 15.7803C14.4013 15.4874 14.4013 15.0126 14.1085 14.7197L11.3888 12L14.1085 9.28033Z" fill="currentColor"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M12.3281 2C6.80528 2 2.32812 6.47715 2.32812 12C2.32812 17.5228 6.80528 22 12.3281 22C17.851 22 22.3281 17.5228 22.3281 12C22.3281 6.47715 17.851 2 12.3281 2ZM3.82812 12C3.82812 7.30558 7.6337 3.5 12.3281 3.5C17.0225 3.5 20.8281 7.30558 20.8281 12C20.8281 16.6944 17.0225 20.5 12.3281 20.5C7.6337 20.5 3.82812 16.6944 3.82812 12Z" fill="currentColor"/>
          </svg>
          <span class="txt">Panel</span>
        </a>
      </div>
      <div class="page-title">
        Enlaces y códigos QR para orientadores
        <span class="page-title-badge">Herramienta interna</span>
      </div>
      <p class="page-subtitle">
        Elige un orientador, genera su enlace personalizado y compártelo con confianza.
      </p>
    </header>

    <!-- <?php if ($msg): ?>
      <div class="err">
        <div class="err-title">No pudimos completar la acción</div>
        <small><?= e($msg) ?></small>
      </div>
    <?php endif; ?> -->

    <div class="grid">
      <!-- Generar -->
      <form class="card" method="post">
        <input type="hidden" name="ui_action" value="gen">
        <h2>1. Generar enlace y código QR</h2>
        <p class="card-desc">
          Selecciona al orientador. El sistema completa los datos por ti y crea un enlace seguro para que sus emprendedores se registren.
        </p>

        <label>
          Orientador<span class="required">*</span>
        </label>
        <select name="select_oid" required>
          <option value="">Selecciona un orientador…</option>
          <?php foreach ($listaOrient as $o): ?>
            <option value="<?= (int)$o['id'] ?>" <?= (isset($_POST['select_oid']) && (int)$_POST['select_oid']===(int)$o['id'])?'selected':'' ?>>
              <?= e(fix_utf8($o['nombres'].' '.$o['apellidos'])) ?>
              <?= $o['centro']   ? ' — '.e(fix_utf8($o['centro'])) : '' ?>
              <?= $o['regional'] ? ' ('.e(fix_utf8($o['regional'])).')' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>

        <p class="mut">
          No necesitas editar nada más. El enlace se genera leyendo la información directamente de la base de datos.
        </p>
        <button class="btn" type="submit">
          <span>●</span> Generar enlace y QR
        </button>

        <?php if ($gen): ?>
          <hr>
          <p><strong style="font-size:12px;">Enlace generado</strong></p>
          <p>
            <a class="link" href="<?= e($gen['url']) ?>" target="_blank"><?= e($gen['url']) ?></a>
          </p>

          <p style="margin-top:10px;"><strong style="font-size:12px;">Token asociado</strong></p>
          <pre><code><?= e($gen['token']) ?></code></pre>

          <?php if ($qrDataUrl): ?>
            <hr>
            <p><strong style="font-size:12px;">Código QR</strong></p>
            <img class="qr" src="<?= $qrDataUrl ?>" alt="QR del enlace del orientador">
            <p class="qr-note">
              Puedes descargarlo haciendo clic derecho → <em>“Guardar imagen como…”</em> para compartirlo con tu grupo.
            </p>
          <?php endif; ?>
<?php if (!empty($debug_payload)): ?>
<div style="
    margin-top:15px;
    background:#072;
    color:#dfffdf;
    padding:12px 14px;
    border-radius:10px;
    font-family:monospace;
    white-space:pre-wrap;
">
<b>DEBUG (Generador → Token incluido en URL)</b>
<?php echo htmlspecialchars(json_encode($debug_payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)); ?>
</div>
<?php endif; ?>

          <div class="pill">
            <span class="pill-dot"></span>
            El token tiene una vigencia aproximada de N días.
          </div>
        <?php endif; ?>
      </form>

      <!-- Verificar -->
      <form class="card" method="post">
        <input type="hidden" name="ui_action" value="verify">
        <h2>2. Verificar un token</h2>
        <p class="card-desc">
          Si alguien te comparte un token y quieres comprobarlo, pégalo aquí para validar que el enlace sigue siendo válido.
        </p>

        <label>Token a verificar</label>
        <textarea name="token" rows="4" style="font-size:11px;resize:vertical;min-height:90px;"><?= e($_POST['token'] ?? '') ?></textarea>

        <button class="btn" type="submit">
          <span>✓</span> Verificar token
        </button>

        <?php if ($ver): ?>
          <?php if (!empty($ver['ok'])): ?>
            <hr>
            <div class="token-valid">
              <span>●</span> Token válido
            </div>
            <p class="token-data"><strong>Nombre del orientador:</strong> <?= e(fix_utf8($ver['nombres'])) ?></p>
            <p class="token-data"><strong>Centro:</strong> <?= e(fix_utf8($ver['centro'])) ?></p>
            <p class="token-data"><strong>Regional:</strong> <?= e(fix_utf8($ver['regional'])) ?></p>
            <!-- <p class="hint">
              Recuerda: internamente siempre se usa el ID guardado en la sesión
              (<code>$_SESSION['token_orientador']['oid']</code>) para registrar a los emprendedores.
            </p> -->
          <?php else: ?>
            <hr>
            <div class="err">
              <div class="err-title">El token no es válido</div>
              <small><?= e($ver['error'] ?? 'Token inválido') ?></small>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </form>
    </div>

    <footer class="page-foot">
      <span>
        Esta herramienta está pensada para uso interno de Fondo Emprender CAB.
        Si algo no funciona como esperas, puedes cerrar esta ventana y volver a intentarlo con calma.
      </span>
    </footer>
  </div>
  <script>

  </script>
</body>
</html>