<!doctype html>
  <html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Acceso restringido</title>
    <style>
    </style>
  </head>
  <body>
    <div class="wrap" role="dialog" aria-modal="true" aria-labelledby="t">
      <div class="head"><h3 id="t">Acceso restringido</h3></div>
      <p>Introduce la contraseña para ver los <strong>QR pre-llenados</strong>.</p>
      <p class="muted counter">Intentos restantes: <strong id="rem"><?= (int)$remaining ?></strong> / <?= MAX_TRIES ?></p>
      <?php if ($message !== ''): ?><div class="err"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <form method="post" autocomplete="off">
        <input type="password" name="passcode" id="passcode" placeholder="Contraseña" required autofocus>
        <div class="row">
          <a class="btn btn-secondary" href="<?= htmlspecialchars(HOME_URL, ENT_QUOTES) ?>">Ir al inicio</a>
          <button class="btn btn-primary" type="submit">Ingresar</button>
        </div>
      </form>
    </div>
    <script>document.getElementById('passcode')?.focus();</script>
  </body>
  </html>

  /** ESTE ES EL SEGUNDO HTML */

  <!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>QR pre-llenados para el formulario</title>
<style>
</style>
</head>
<body>
  <h1>QR pre-llenados para el formulario</h1>
  <p class="lead">Escanea el QR para abrir el formulario con Centro y Orientador ya seleccionados.</p>

  <div class="grid">
    <?php while($row=$rs->fetch_assoc()):
      $oid      = (int)$row['id_orientador'];
      $nombre   = $row['nombre'];
      $centro   = $row['centro'];
      $regional = $row['regional'];

      $url = prefill_link($nombre, $centro, $regional);
      $qr  = 'https://quickchart.io/qr?size=500&margin=2&text='.urlencode($url);
    ?>

    <div class="card">
      <div class="topline">
        <span class="badge">ID <?= $oid ?></span>
        <h3 class="name"><?= htmlspecialchars($nombre) ?></h3>
      </div>
      <div class="meta">Centro: <b><?= htmlspecialchars($centro) ?></b> · Regional: <b><?= htmlspecialchars($regional) ?></b></div>

      <div class="qr-wrap">
        <img class="qr" src="<?= $qr ?>" alt="QR de <?= htmlspecialchars($nombre) ?>">
      </div>

      <div class="link-row">
        <span class="link" title="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($url) ?></span>
      </div>
      <div class="link-row">
        <button class="btn" data-copy="<?= htmlspecialchars($url) ?>">Copiar</button>
        <a class="btn" href="<?= $qr ?>" download="QR_<?= $oid ?>.png">Ver</a>
      </div>
    </div>
    <?php endwhile; ?>
  </div>

<script>

</script>
</body>
</html>
