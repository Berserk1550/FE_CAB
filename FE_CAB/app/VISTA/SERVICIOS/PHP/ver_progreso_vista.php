<!DOCTYPE html>
<html lang="es">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../componentes/ver_progreso.css">
    <link rel="icon" href="../../componentes/img/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Progreso de <?= htmlspecialchars($usuario['nombres']) ?></title>
</head>

<body>
    <!-- Botón volver flotante -->
    <a href="lista_emprendedores" class="btn-volver" title="Volver a la lista">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        <!-- <span>Volver</span> -->
    </a>

    <div class="contenedor">
        <!-- Header con información del usuario -->
        <div class="header-progreso">
            <div class="user-info">
                <div class="user-avatar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div class="user-details">
                    <h1 class="user-name"><?= htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']) ?></h1>
                    <p class="user-subtitle">Progreso de herramientas de ideación</p>
                </div>
            </div>

            <!-- Barra de progreso general -->
            <div class="progress-summary">
                <div class="progress-stats">
                    <span class="stat-label">Progreso general</span>
                    <span class="stat-value"><?= $completadas_count ?> / <?= $total_fases ?> completadas</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?= $porcentaje ?>%">
                        <span class="progress-percentage"><?= $porcentaje ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de fases -->
        <div class="fases-container">
            <?php foreach ($fases_totales as $num => $info): 
                $completada = in_array($num, $fases_completadas);
            ?>
                <div class="fase-card <?= $completada ? 'completada' : 'pendiente' ?>">
                    <div class="fase-header">
                        <div class="fase-numero">
                            <span><?= $num ?></span>
                        </div>
                        <div class="fase-info">
                            <h3 class="fase-titulo"><?= $info['nombre'] ?></h3>
                            <span class="fase-estado">
                                <?php if ($completada): ?>
                                    <svg class="icon-check" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    Completado
                                <?php else: ?>
                                    <svg class="icon-pending" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    Pendiente
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($completada): ?>
                        <div class="fase-actions">
                            <a href="ver_respuestas?id=<?= $usuario_id ?>&herramienta=<?= $info['tabla'] ?>&numero_id=<?= urlencode($numero_id) ?>"
                                class="btn-ver-respuestas">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="12" y1="18" x2="12" y2="12"/>
                                    <line x1="9" y1="15" x2="15" y2="15"/>
                                </svg>
                                <span>Ver respuestas / Dejar feedback</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>