<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Evaluación de Pitches</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {background:#f5f5f5;font-family:system-ui, sans-serif;padding:2rem;}
    h1 {margin-bottom:.25rem;}
    .sub {color:#666;margin-bottom:1rem;}
    table {width:100%;border-collapse:collapse;background:#fff;}
    th, td {border:1px solid #ddd;padding:.5rem .75rem;font-size:.9rem;vertical-align:top;}
    th {background:#eef3f7;}
    form {display:flex;flex-direction:column;gap:.25rem;}
    textarea {min-height:50px;}
    .badge-pendiente {background:#fef9c3;color:#854d0e;padding:2px 6px;border-radius:4px;font-size:0.7rem;}
    .badge-aprobado {background:#dcfce7;color:#166534;padding:2px 6px;border-radius:4px;font-size:0.7rem;}
    .badge-no {background:#fee2e2;color:#b91c1c;padding:2px 6px;border-radius:4px;font-size:0.7rem;}
    .btn-guardar {margin-top:.4rem;}
    .msg-ok {background:#dcfce7;color:#166534;padding:.5rem .75rem;border-radius:4px;margin-bottom:1rem;font-size:.9rem;}
    .msg-err {background:#fee2e2;color:#b91c1c;padding:.5rem .75rem;border-radius:4px;margin-bottom:1rem;font-size:.9rem;}
    .btn-delete {
      margin-top:.3rem;
      background:#fee2e2;
      color:#b91c1c;
      border:1px solid #fecaca;
      border-radius:4px;
      padding:.25rem .5rem;
      font-size:.75rem;
      cursor:pointer;
    }
    .btn-delete:hover {
      background:#fecaca;
    }
  </style>
</head>
<body>
  <h1>Evaluación de Pitches</h1>
  <p class="sub">Orientador: <strong><?= htmlspecialchars($nombre_orientador) ?></strong></p>
  <p>Descarga el archivo, revísalo y registra tu evaluación.</p>

  <?php if ($mensaje_ok): ?>
    <div class="msg-ok"><?= htmlspecialchars($mensaje_ok) ?></div>
  <?php endif; ?>
  <?php if ($mensaje_err): ?>
    <div class="msg-err"><?= htmlspecialchars($mensaje_err) ?></div>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Emprendedor</th>
        <th>Archivo</th>
        <th>Subido</th>
        <th>Estado</th>
        <th>Evaluar</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($pitches as $i => $p): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td>
            <?php
              $nom = trim(($p['nombre_emprendedor'] ?? '') . ' ' . ($p['apellido_emprendedor'] ?? ''));
              echo $nom !== '' ? htmlspecialchars($nom) : 'ID ' . (int)$p['emprendedor_id'];
            ?>
          </td>
          <td>
            <?= htmlspecialchars($p['nombre_archivo_original']) ?><br>
            <a href="https://arcano.digital/emprender/<?= htmlspecialchars($p['url_archivo']) ?>" target="_blank" download>
              Descargar / Ver
            </a>
          </td>
          <td><?= htmlspecialchars($p['fecha_subida']) ?></td>
          <td>
            <?php if ($p['estado_eval'] === 'aprobado'): ?>
              <span class="badge-aprobado">Aprobado</span>
            <?php elseif ($p['estado_eval'] === 'no_aprobado'): ?>
              <span class="badge-no">No aprobado</span>
            <?php else: ?>
              <span class="badge-pendiente">Pendiente</span>
            <?php endif; ?>
            <?php if ($p['calificacion'] !== null): ?>
              <br>Cal: <?= htmlspecialchars($p['calificacion']) ?>
            <?php endif; ?>
          </td>
          <td>
            <form method="post">
              <input type="hidden" name="pitch_id" value="<?= (int)$p['id'] ?>">

              <label>Calificación (0-10)</label>
              <input
                type="number"
                name="calificacion"
                min="0"
                max="10"
                step="0.1"
                inputmode="numeric"
                required
                value="<?= htmlspecialchars($p['calificacion'] ?? '') ?>"
              >

              <label>Estado</label>
              <select name="estado_eval">
                <option value="pendiente"   <?= $p['estado_eval'] === 'pendiente'   ? 'selected' : '' ?>>Pendiente</option>
                <option value="aprobado"    <?= $p['estado_eval'] === 'aprobado'    ? 'selected' : '' ?>>Aprobado</option>
                <option value="no_aprobado" <?= $p['estado_eval'] === 'no_aprobado' ? 'selected' : '' ?>>No aprobado</option>
              </select>

              <label>Observaciones</label>
              <textarea name="observaciones"><?= htmlspecialchars($p['observaciones'] ?? '') ?></textarea>

              <button type="submit" class="btn-guardar">Guardar evaluación</button>
            </form>
          </td>
          <td>
            <!-- Formulario separado para eliminar con confirmación -->
            <form method="post" onsubmit="return confirm('¿Seguro que deseas eliminar este pitch? Esta acción no se puede deshacer.');">
              <input type="hidden" name="eliminar_pitch_id" value="<?= (int)$p['id'] ?>">
              <button type="submit" class="btn-delete">Eliminar pitch</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>