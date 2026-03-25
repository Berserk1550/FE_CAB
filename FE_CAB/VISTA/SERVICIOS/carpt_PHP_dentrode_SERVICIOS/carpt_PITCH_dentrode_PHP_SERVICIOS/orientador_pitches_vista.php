<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación de Pitches - SENA Fondo Emprender</title>
    <style>
        
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Evaluación de Pitches - Fondo Emprender</h1>
            <p class="subtitle">Orientador: <strong><?= htmlspecialchars($nombre_orientador) ?></strong></p>
            <p class="subtitle" style="margin-top: 5px;">Descarga el pitch, revísalo cuidadosamente y completa la evaluación detallada.</p>
        </div>

        <?php if ($mensaje_ok): ?>
            <div class="message message-success">
                ✓ <?= htmlspecialchars($mensaje_ok) ?>
            </div>
        <?php endif; ?>

        <?php if ($mensaje_err): ?>
            <div class="message message-error">
                ✗ <?= htmlspecialchars($mensaje_err) ?>
            </div>
        <?php endif; ?>

        <div class="pitches-grid">
            <?php foreach ($pitches as $index => $pitch): ?>
                <div class="pitch-card">
                    <div class="pitch-header">
                        <div class="pitch-info">
                            <h3><?= htmlspecialchars($pitch['nombre_emprendedor'] . ' ' . $pitch['apellido_emprendedor']) ?></h3>
                            <div class="pitch-meta">
                                <div><strong>📄 Proyecto:</strong> <?= htmlspecialchars($pitch['nombre_proyecto'] ?? 'No especificado') ?></div>
                                <div><strong>🆔 Identificación:</strong> <?= htmlspecialchars($pitch['numero_identificacion'] ?? 'N/A') ?></div>
                                <div><strong>📧 Correo:</strong> <?= htmlspecialchars($pitch['correo_electronico'] ?? 'N/A') ?></div>
                                <div><strong>📅 Fecha de subida:</strong> <?= date('d/m/Y H:i', strtotime($pitch['fecha_subida'])) ?></div>
                            </div>
                            <div style="margin-top: 15px;">
                                <a href="https://arcano.digital/emprender/<?= htmlspecialchars($pitch['url_archivo']) ?>" 
                                   class="download-link" 
                                   target="_blank" 
                                   download>
                                    📥 Descargar Pitch (<?= htmlspecialchars($pitch['nombre_archivo_original']) ?>)
                                </a>
                            </div>
                        </div>
                        <div class="pitch-status">
                            <?php
                            $estado = $pitch['estado_aprobacion'] ?? 'pendiente';
                            $badge_class = 'badge-' . str_replace('_', '-', $estado);
                            ?>
                            <span class="badge <?= $badge_class ?>">
                                <?php
                                if ($estado === 'aprobado') echo '✓ Aprobado';
                                elseif ($estado === 'no_aprobado') echo '✗ No Aprobado';
                                else echo '⏳ Pendiente';
                                ?>
                            </span>
                            <?php if ($pitch['ponderado_final'] !== null): ?>
                                <div class="ponderado">
                                    <?= number_format($pitch['ponderado_final'], 2) ?>/10
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <form method="POST" class="eval-form" onsubmit="return validateForm(this);">
                        <input type="hidden" name="pitch_id" value="<?= (int)$pitch['id'] ?>">

                        <!-- Sección 1: Información del Evaluador -->
                        <div class="form-section">
                            <div class="section-title">📋 Información del Evaluador</div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    Nombre del Evaluador <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       name="nombre_evaluador" 
                                       class="form-input" 
                                       value="<?= htmlspecialchars($pitch['nombre_evaluador'] ?? $nombre_orientador) ?>"
                                       required
                                       placeholder="Escriba su nombre completo">
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    Centro de Desarrollo Empresarial <span class="required">*</span>
                                </label>
                                <select name="centro_desarrollo" class="form-select" required>
                                    <option value="">Seleccione un centro...</option>
                                    <?php foreach ($centros_desarrollo as $key => $value): ?>
                                        <option value="<?= htmlspecialchars($key) ?>" 
                                                <?= ($pitch['centro_desarrollo'] ?? '') === $key ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($value) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Sección 2: Criterios de Evaluación -->
                        <div class="form-section">
                            <div class="section-title">⭐ Criterios de Evaluación (Escala 1-10)</div>
                            
                            <?php
                            $preguntas = [
                                ['calif_conocimiento_modelo', '¿El emprendedor demuestra conocimiento y experiencia en el Modelo de Negocio?'],
                                ['calif_identifica_problema', '¿El emprendedor identifica una problemática o necesidad precisa y está enfocado(a) en una población caracterizada?'],
                                ['calif_reconoce_protagonista', '¿El emprendedor reconoce el protagonista de su modelo de negocio?'],
                                ['calif_solucion_innovadora', '¿El emprendedor presenta una solución específica e innovadora al problema identificado materializado en un producto o servicio validado?'],
                                ['calif_tendencias_cifras', '¿El emprendedor identifica las tendencias y presenta cifras alrededor de su modelo de negocio a nivel local, nacional e internacional soportada en fuentes confiables?'],
                                ['calif_traccion_ventas', '¿El emprendedor cuenta con clientes y está realizando ventas de su Modelo de Negocio (Tracción)?'],
                                ['calif_ventaja_competencia', '¿El emprendedor identifica su competencia y demuestra una ventaja frente a ella?'],
                                ['calif_canales_coherentes', '¿El emprendedor reconoce los canales (Canales de comunicación, comercialización y distribución) coherente con el segmento de mercado?'],
                                ['calif_valor_proyecto', '¿El emprendedor presenta el valor total de su proyecto, el aporte del emprendedor y una estimación de sus ventas para los primeros 3 años?'],
                                ['calif_equipo_interdisciplinario', '¿El emprendedor demuestra conocimiento y experiencia en el Modelo de Negocio y un equipo interdisciplinario?']
                            ];
                            
                            foreach ($preguntas as $i => $pregunta):
                                $field_name = $pregunta[0];
                                $question_text = $pregunta[1];
                                $current_value = $pitch[$field_name] ?? '';
                            ?>
                                <div class="form-group">
                                    <label class="form-label">
                                        <?= ($i + 1) ?>. <?= htmlspecialchars($question_text) ?> <span class="required">*</span>
                                    </label>
                                    <div class="rating-scale">
                                        <?php for ($j = 1; $j <= 10; $j++): ?>
                                            <div class="rating-option">
                                                <input type="radio" 
                                                       name="<?= $field_name ?>" 
                                                       value="<?= $j ?>" 
                                                       id="<?= $field_name ?>_<?= $j ?>_<?= $pitch['id'] ?>"
                                                       <?= $current_value == $j ? 'checked' : '' ?>
                                                       class="rating-input"
                                                       data-pitch-id="<?= $pitch['id'] ?>"
                                                       required>
                                                <label class="rating-label" for="<?= $field_name ?>_<?= $j ?>_<?= $pitch['id'] ?>">
                                                    <?= $j ?>
                                                </label>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Sección 3: Observaciones -->
                        <div class="form-section">
                            <div class="section-title">💬 Observaciones y Retroalimentación</div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    Observaciones y retroalimentación final <span class="required">*</span>
                                </label>
                                <textarea name="observaciones" 
                                          class="form-textarea" 
                                          required
                                          placeholder="Escriba sus observaciones y recomendaciones para el emprendedor..."><?= htmlspecialchars($pitch['observaciones'] ?? '') ?></textarea>
                            </div>

                            <!-- Ponderado Calculado -->
                            <div class="ponderado-calculated" id="ponderado_<?= $pitch['id'] ?>" style="display: none;">
                                <div class="label">Ponderado Final</div>
                                <div class="value" id="ponderado_value_<?= $pitch['id'] ?>">0.00</div>
                                <div class="status" id="ponderado_status_<?= $pitch['id'] ?>"></div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                💾 Guardar Evaluación
                            </button>
                        </div>
                    </form>

                    <!-- Formulario de Eliminación (separado) -->
                    <form method="POST" 
                          onsubmit="return confirm('⚠️ ¿Está seguro que desea eliminar este pitch? Esta acción no se puede deshacer.');" 
                          style="margin-top: 15px;">
                        <input type="hidden" name="eliminar_pitch_id" value="<?= (int)$pitch['id'] ?>">
                        <button type="submit" class="btn btn-danger">
                            🗑️ Eliminar Pitch
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>

            <?php if (empty($pitches)): ?>
                <div class="pitch-card" style="text-align: center; padding: 60px 20px;">
                    <div style="font-size: 48px; margin-bottom: 15px;">📭</div>
                    <h3 style="color: var(--text-light); margin-bottom: 10px;">No hay pitches disponibles</h3>
                    <p style="color: var(--text-light);">Cuando los emprendedores suban sus pitches, aparecerán aquí para evaluación.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Calcular ponderado en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar event listeners a todos los radio buttons
            const ratingInputs = document.querySelectorAll('.rating-input');
            
            ratingInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const pitchId = this.getAttribute('data-pitch-id');
                    calcularPonderado(pitchId);
                });
            });

            // Calcular ponderado inicial si ya hay valores
            <?php foreach ($pitches as $pitch): ?>
                calcularPonderado(<?= $pitch['id'] ?>);
            <?php endforeach; ?>
        });

        function calcularPonderado(pitchId) {
            const form = document.querySelector(`input[name="pitch_id"][value="${pitchId}"]`).closest('form');
            const ratings = form.querySelectorAll('.rating-input:checked');
            
            if (ratings.length === 10) {
                let suma = 0;
                ratings.forEach(rating => {
                    suma += parseFloat(rating.value);
                });
                
                const ponderado = suma / 10;
                const ponderadoDiv = document.getElementById(`ponderado_${pitchId}`);
                const ponderadoValue = document.getElementById(`ponderado_value_${pitchId}`);
                const ponderadoStatus = document.getElementById(`ponderado_status_${pitchId}`);
                
                ponderadoDiv.style.display = 'block';
                ponderadoValue.textContent = ponderado.toFixed(2);
                
                if (ponderado >= 6.5) {
                    ponderadoDiv.classList.remove('no-aprobado');
                    ponderadoDiv.classList.add('aprobado');
                    ponderadoStatus.textContent = '✓ APROBADO - Cumple con el mínimo requerido (6.5)';
                } else {
                    ponderadoDiv.classList.remove('aprobado');
                    ponderadoDiv.classList.add('no-aprobado');
                    ponderadoStatus.textContent = '✗ NO APROBADO - No alcanza el mínimo requerido (6.5)';
                }
            }
        }

        function validateForm(form) {
            const ratings = form.querySelectorAll('.rating-input:checked');
            
            if (ratings.length !== 10) {
                alert('⚠️ Por favor, califique todos los criterios de evaluación (10 preguntas).');
                return false;
            }
            
            const nombreEvaluador = form.querySelector('input[name="nombre_evaluador"]').value.trim();
            if (nombreEvaluador === '') {
                alert('⚠️ Por favor, ingrese su nombre como evaluador.');
                return false;
            }
            
            const centroDesa = form.querySelector('select[name="centro_desarrollo"]').value;
            if (centroDesa === '') {
                alert('⚠️ Por favor, seleccione el Centro de Desarrollo Empresarial.');
                return false;
            }
            
            const observaciones = form.querySelector('textarea[name="observaciones"]').value.trim();
            if (observaciones === '') {
                alert('⚠️ Por favor, escriba sus observaciones y retroalimentación.');
                return false;
            }
            
            return confirm('¿Está seguro de guardar esta evaluación?');
        }
    </script>
</body>
</html>