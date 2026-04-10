<?php header('Content-Type: application/javascript; charset=utf-8'); ?>

window.PREFILL = <?= json_encode([
    'ok'     => $prefill_ok,
    'center' => $center,
    'name'   => $name,
    'oid'    => $oid_resuelto
], JSON_UNESCAPED_UNICODE) ?>;

window.SERVER_NOW = {
  ymd: "<?= date('Y-m-d'); ?>",
  ts: "<?= date('Y-m-d H:i:s'); ?>",
  tz: "America/Bogota"
};