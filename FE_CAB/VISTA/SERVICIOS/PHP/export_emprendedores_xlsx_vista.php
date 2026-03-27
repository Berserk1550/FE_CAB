/**ESTE HTML SE ENCONTRABA DENTRO DEL ARCHIVO EXPORT_EMPRENDEDORES_XLSX.PHP DENTRO DE UN IF EN LA LINEA 323 DE CODIGO */

<!doctype html>
    <html lang="es">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Acceso No Autorizado</title>

        <link rel="stylesheet" href="../../componentes/acceso_noautorizado.css">
    </head>

    <body>
        <div class="auth-modal" aria-hidden="false">
            <div class="auth-card" role="dialog" aria-modal="true" aria-labelledby="authTitle">
                <div class="auth-hdr">
                    <div class="auth-ico">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            <line x1="12" y1="15" x2="12" y2="18" />
                        </svg>
                    </div>
                    <h3 id="authTitle">Acceso No Autorizado</h3>
                </div>
                <div class="auth-body">
                    <p>No tienes acceso a esta funcionalidad.</p>
                    <p>Debes iniciar sesión con el <strong>rol autorizado</strong> para ver esta página.</p>
                    <p class="muted">Te redirigiremos al inicio en <strong id="count">5</strong> segundos.</p>
                </div>
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

/**ESTE SEGUNDO HTML SE ENCUENTRA DENTRO DEL IF DE LA LINEA 423 */
<!doctype html>
    <html lang="es">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title>Exportar (GEE-F-010 o Base completa)</title>
        <link rel="icon" href="../../componentes/img/favicon.ico">
        <link rel="stylesheet" href="../../componentes/export_emprendedores.css">

    </head>

    <body>
        <div class="card">
            <div class="hdr"><span class="dot"></span>
                <h1>Exportar — <strong>GEE-F-010</strong> o <strong>Base completa</strong></h1>
            </div>
            <form method="post" id="frmExport" novalidate>
                <fieldset>
                    <legend>Tipo de exportación</legend>
                    <div class="grid">
                        <div class="col-8">
                            <label>¿Qué deseas exportar?</label>
                            <select name="export_mode" id="export_mode" required>
                                <option value="gee" selected>Formato GEE-F-010 (lista de asistencia para orientadores)</option>
                                <option value="db">Base de datos completa (Emprendedores)</option>
                            </select>
                            <div class="muted">El formato GEE-F-010 requiere los datos del evento; la base completa no.</div>
                        </div>
                    </div>
                </fieldset>

                <fieldset id="fs-fechas">
                    <legend>Rango de fechas (filtra por <em>fecha_orientacion</em>)</legend>
                    <div class="grid">
                        <div class="col-4"><label>Desde</label><input type="date" name="desde" value="<?= h($today) ?>" required></div>
                        <div class="col-4"><label>Hasta</label><input type="date" name="hasta" value="<?= h($today) ?>" required></div>
                        <div class="col-4"><label>Hora (opcional)</label><input type="time" name="hora" placeholder="HH:MM"></div>
                    </div>
                    <div class="muted">Si “Desde” y “Hasta” son iguales, se exporta solo esa fecha.</div>
                </fieldset>

                <fieldset id="fs-evento">
                    <legend>Datos del evento</legend>
                    <div class="grid">
                        <div class="col-7">
                            <label>Título de la charla</label>
                            <input type="text" name="titulo" placeholder="Introducción a Fondo Emprender" required>
                        </div>
                        <div class="col-3">
                            <label>Tipo de sesión</label>
                            <select name="tipo_sesion" required>
                                <option value="ORIENTACION">ORIENTACIÓN</option>
                                <option value="ENTRENAMIENTO">ENTRENAMIENTO</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label>Modalidad</label>
                            <select name="modalidad" required>
                                <option value="PRESENCIAL">PRESENCIAL</option>
                                <option value="VIRTUAL">VIRTUAL</option>
                            </select>
                        </div>

                        <div class="col-5">
                            <label>Expositor</label>
                            <div class="row">
                                <input type="text" id="expositor" name="expositor" placeholder="Nombre del expositor" style="flex:1">
                                <label class="na"><input type="checkbox" id="expo_na" name="expositor_na" value="1"> No aplica</label>
                            </div>
                        </div>

                        <div class="col-7">
                            <label>Orientador responsable</label>
                            <select name="orientador_sel" id="orientador_sel" required>
                                <option value="" <?= $preSelect === '' ? 'selected' : '' ?> disabled>— Selecciona orientador —</option>
                                <?php foreach ($ORIENTADORES as $centro => $lista): ?>
                                    <optgroup label="<?= h($centro) ?>">
                                        <?php foreach ($lista as $nom): ?>
                                            <option value="<?= h($nom) ?>" <?= $preSelect === $nom ? 'selected' : '' ?>><?= h($nom) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                                <option value="OTRO" <?= $preSelect === 'OTRO' ? 'selected' : '' ?>>Otro (escribir manualmente)</option>
                            </select>
                            <input type="text" name="orientador_otro" id="orientador_otro" placeholder="Nombre y apellido"
                                value="<?= h($preOtro) ?>" style="margin-top:8px;display:<?= $preSelect === 'OTRO' ? 'block' : 'none' ?>;">
                        </div>
                    </div>
                </fieldset>

                <fieldset id="fs-exclusivo">
                    <legend>Evento exclusivo (opcional)</legend>
                    <div class="exclusive-top">
                        <div class="muted">Marca si el evento fue exclusivo para alguno de estos grupos. Si no aplica, déjalo sin marcar o activa “No aplica”.</div>
                        <label class="na"><input type="checkbox" id="excl_na" name="exclusivo_na" value="1"> No aplica</label>
                    </div>
                    <div class="exclusive-grid" id="choices" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px 16px;align-items:start">
                        <?php $labels = ['Jóvenes', 'Población ARN', 'Líderes y/o Lideresas', 'Jóvenes en Paz', 'Cuidadores', 'Veteranos', 'Negritudes', 'Raizales', 'Indígenas', 'Municipios PDET', 'Mujeres', 'Economía Popular', 'Campesinos', 'Indígena Amazónico', 'Parques Nacionales', 'ICBF', 'Afrocolombianos', 'Palenqueros'];
                        foreach ($labels as $lb): ?>
                            <label class="option" style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px solid var(--border);border-radius:12px;background:#fff;">
                                <input type="checkbox" name="exclusivo[]" value="<?= h($lb) ?>"> <?= h($lb) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <div class="actions">
                    <a href="panel_orientador" class="btn btn-back">← Volver al panel</a>
                    <button type="submit" class="btn btn-primary">Exportar XLSX</button>
                    <button type="reset" class="btn btn-secondary">Limpiar</button>
                </div>
            </form>
        </div>

    
        </script>
    </body>

    </html>