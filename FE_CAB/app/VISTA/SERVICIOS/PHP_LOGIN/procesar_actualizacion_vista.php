<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Actualización exitosa</title>
    
</head>

<body>
    <div class="overlay" role="dialog" aria-modal="true" aria-labelledby="okT">
        <div class="card">
            <div class="hdr">
                <div class="ico" aria-hidden="true">✓</div>
                <h3 id="okT">Datos guardados correctamente</h3>
            </div>
            <p>Documento <strong><?= h($numero_id) ?></strong> — <spa class="badge">
                    <!-- Modo: <?= h($modo) ?></span></p> -->
                    Serás rediridigo al inicio</p>
            <?php if ($nuevo_orientador_id > 0): ?>
                <p class="muted">Asignado a <strong><?= h($orientador) ?></strong></p>
            <?php endif; ?>

            <?php if ($SHOW_DEBUG): ?>
                <details open>
                    <summary>🧾 Meta e Identificación</summary>
                    <pre><?= h(json_encode($DEBUG_OUT['meta'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                </details>

                <details>
                    <summary>📥 POST recibido (enmascarado + verificación de llegada)</summary>
                    <pre><?= h(json_encode($DEBUG_OUT['incoming'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                </details>

                <details <?= (!$MASK_SENSITIVE ? 'open' : '') ?>>
                    <summary>📥 POST crudo <?= $MASK_SENSITIVE ? '(oculto — usa ?debug=full)' : '(visible)' ?></summary>
                    <pre><?= h(is_array($DEBUG_OUT['incoming_raw']) ? json_encode($DEBUG_OUT['incoming_raw'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string)$DEBUG_OUT['incoming_raw']) ?></pre>
                </details>

                <details>
                    <summary>👤 Resolución de orientador</summary>
                    <pre><?= h(json_encode($DEBUG_OUT['resolved_orientador'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                </details>

                <details>
                    <summary>🧩 Normalizado → columnas a actualizar (enmascarado)</summary>
                    <pre><?= h(json_encode($DEBUG_OUT['normalized_to_update_masked'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                </details>

                <details>
                    <summary>🕰️ BEFORE (fila actual, enmascarado)</summary>
                    <pre><?= h(json_encode($DEBUG_OUT['before_row_masked'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                </details>

                <?php if ($dupInfo['attempted'] ?? false): ?>
                    <details open>
                        <summary>➕ Copia (INSERT … SELECT)</summary>
                        <pre><?= h(json_encode($DEBUG_OUT['dup'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                    </details>
                <?php endif; ?>

                <details open>
                    <summary>✏️ UPDATE ejecutado</summary>
                    <pre><?= h(json_encode($DEBUG_OUT['update'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                </details>

                <details open>
                    <summary>🔎 AFTER + Diff (cambios reales)</summary>
                    <pre><?= h(json_encode(['after_row_masked' => $DEBUG_OUT['after_row_masked'], 'diff' => $DEBUG_OUT['diff']], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                </details>
            <?php endif; ?>

            <div class="actions"><a class="btn btn-ghost" href="../../index">Inicio</a></div>
        </div>
    </div>

    <?php if ($AUTO_REDIRECT): ?>
        <script>
            setTimeout(() => location.assign(<?= json_encode($redirect) ?>), 5000);
        </script>
    <?php endif; ?>
</body>

</html>