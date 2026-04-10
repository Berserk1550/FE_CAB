<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuestas - <?= htmlspecialchars($nombres_herramientas[$herramienta]) ?></title>
    <link rel="icon" href="../../componentes/img/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../componentes/ver_respuestas.css">
</head>

<body>
    <!-- Botón volver flotante -->
    <a href="ver_progreso?numero_id=<?= urlencode($numero_id ?? '') ?>" class="btn-volver fade-in">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        <!-- Volver -->
    </a>

    <div class="container">

        <!-- Título principal -->
        <div class="page-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M12 2l.117 .007a1 1 0 0 1 .876 .876l.007 .117v4l.005 .15a2 2 0 0 0 1.838 1.844l.157 .006h4l.117 .007a1 1 0 0 1 .876 .876l.007 .117v9a3 3 0 0 1 -2.824 2.995l-.176 .005h-10a3 3 0 0 1 -2.995 -2.824l-.005 -.176v-14a3 3 0 0 1 2.824 -2.995l.176 -.005zm0 12h-1a1 1 0 0 0 0 2v3a1 1 0 0 0 1 1h1a1 1 0 0 0 1 -1l-.007 -.117a1 1 0 0 0 -.876 -.876l-.117 -.007v-3a1 1 0 0 0 -1 -1m.01 -3h-.01a1 1 0 0 0 -.117 1.993l.127 .007a1 1 0 0 0 0 -2" />
                <path d="M19 7h-4l-.001 -4.001z" />
            </svg>
            <h1>Respuestas: <?= htmlspecialchars($nombres_herramientas[$herramienta]) ?></h1>
        </div>

        <?php if ($respuestas->num_rows > 0): ?>
            <?php while ($r = $respuestas->fetch_assoc()): ?>
                <div class="respuesta-card">
                    <div class="card-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                        </svg>
                        <h3>Detalles de la respuesta</h3>
                    </div>
                    
                    <div class="campos-grid">
                        <?php foreach ($r as $campo => $valor): ?>
                            <?php
                            // Ocultar campos internos y fecha_registro
                            if (in_array($campo, ['id', 'usuario_id', 'fecha_registro'])) continue;
                            
                            // Formatear nombre del campo
                            $campo_legible = ucwords(str_replace('_', ' ', $campo));
                            ?>
                            <div class="campo-item">
                                <span class="campo-label"><?= htmlspecialchars($campo_legible) ?></span>
                                <div class="campo-valor"><?= htmlspecialchars($valor ?: 'No especificado') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="fecha-registro">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" />
                            <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                            <path d="M15 3v4" />
                            <path d="M7 3v4" />
                            <path d="M3 11h16" />
                            <path d="M18 16.496v1.504l1 1" />
                        </svg>
                        Fecha de registro: <?= htmlspecialchars($r['fecha_registro'] ?? 'No registrada') ?>
                    </div>
                </div>
            <?php endwhile; ?>

            <!-- Sección de feedback -->
            <div class="feedback-section">
                <div class="feedback-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12.4 2a6.33 6.33 0 0 1 5.491 3.176l.09 .162l.126 .027a6.335 6.335 0 0 1 4.889 5.934l.004 .234a6.333 6.333 0 0 1 -6.333 6.334l-.035 -.002l-.035 .05a5.26 5.26 0 0 1 -3.958 2.08l-.239 .005q -.722 0 -1.404 -.19l-.047 -.014l-3.434 2.061a1 1 0 0 1 -1.509 -.743l-.006 -.114v-2.434l-.121 -.06a3.67 3.67 0 0 1 -1.94 -3.042l-.006 -.197q 0 -.365 .07 -.717l.013 -.058l-.113 -.09a5.8 5.8 0 0 1 -2.098 -4.218l-.005 -.25a5.8 5.8 0 0 1 5.8 -5.8l.058 .001l.15 -.163a6.32 6.32 0 0 1 4.328 -1.967z" />
                    </svg>
                    <h3>Dejar Feedback</h3>
                </div>
                
                <form method="POST" action="guardar_feedback" class="feedback-form">
                    <input type="hidden" name="usuario_id" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>">
                    <input type="hidden" name="herramienta" value="<?= htmlspecialchars($_GET['herramienta'] ?? '') ?>">
                    
                    <textarea name="comentario" class="feedback-textarea" placeholder="Escribe tu feedback aquí..." required></textarea>
                    
                    <button type="submit" class="btn-guardar-feedback">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                            <path d="M14 4l0 4l-6 0l0 -4" />
                        </svg>
                        Guardar Feedback
                    </button>
                </form>

                <?php if ($feedback): ?>
                    <div class="ultimo-feedback">
                        <p><strong>Último feedback:</strong> <?= htmlspecialchars($feedback['comentario']) ?></p>
                        <small>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" />
                                <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                <path d="M15 3v4" />
                                <path d="M7 3v4" />
                                <path d="M3 11h16" />
                                <path d="M18 16.496v1.504l1 1" />
                            </svg>
                            <?= htmlspecialchars($feedback['fecha']) ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="no-respuestas">
                No hay respuestas registradas para esta herramienta.
            </div>
        <?php endif; ?>
    </div>

    <script>
    </script>
</body>

</html>