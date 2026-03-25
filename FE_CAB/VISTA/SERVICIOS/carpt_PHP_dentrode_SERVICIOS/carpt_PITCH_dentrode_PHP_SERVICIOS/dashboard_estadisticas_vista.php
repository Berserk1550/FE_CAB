<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Estadísticas de Pitches</title>
    <style>
        
    </style>
</head>
<body>
    <div class="container">
        <a href="orientador_pitches_v2.php" class="nav-back">
            ← Volver a Evaluaciones
        </a>

        <div class="header">
            <h1>📊 Dashboard de Estadísticas</h1>
            <p class="subtitle">Análisis general de las evaluaciones de pitches</p>
        </div>

        <!-- Estadísticas Generales -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">📝</div>
                <div class="label">Total Pitches</div>
                <div class="value"><?= number_format($stats['total_pitches']) ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">✅</div>
                <div class="label">Aprobados</div>
                <div class="value"><?= number_format($stats['aprobados']) ?></div>
                <div class="percentage">
                    <?= $stats['total_pitches'] > 0 ? round(($stats['aprobados'] / $stats['total_pitches']) * 100, 1) : 0 ?>% del total
                </div>
            </div>

            <div class="stat-card">
                <div class="icon">❌</div>
                <div class="label">No Aprobados</div>
                <div class="value"><?= number_format($stats['no_aprobados']) ?></div>
                <div class="percentage">
                    <?= $stats['total_pitches'] > 0 ? round(($stats['no_aprobados'] / $stats['total_pitches']) * 100, 1) : 0 ?>% del total
                </div>
            </div>

            <div class="stat-card">
                <div class="icon">⏳</div>
                <div class="label">Pendientes</div>
                <div class="value"><?= number_format($stats['pendientes']) ?></div>
                <div class="percentage">
                    <?= $stats['total_pitches'] > 0 ? round(($stats['pendientes'] / $stats['total_pitches']) * 100, 1) : 0 ?>% del total
                </div>
            </div>

            <div class="stat-card">
                <div class="icon">⭐</div>
                <div class="label">Promedio General</div>
                <div class="value">
                    <?= $stats['promedio_general'] !== null ? number_format($stats['promedio_general'], 2) : 'N/A' ?>
                </div>
                <div class="percentage">Calificación promedio</div>
            </div>
        </div>

        <!-- Top 10 Mejores Ponderados -->
        <div class="card">
            <h2 class="card-title">🏆 Top 10 Mejores Evaluaciones</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Emprendedor</th>
                        <th>Centro</th>
                        <th>Ponderado</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_pitches as $index => $pitch): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <strong><?= htmlspecialchars($pitch['nombres'] . ' ' . $pitch['apellidos']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($pitch['centro_desarrollo'] ?? 'N/A') ?></td>
                            <td>
                                <strong style="color: var(--sena-green); font-size: 16px;">
                                    <?= number_format($pitch['ponderado_final'], 2) ?>
                                </strong>
                            </td>
                            <td><?= date('d/m/Y', strtotime($pitch['fecha_evaluacion'])) ?></td>
                            <td>
                                <span class="badge badge-aprobado">✓ Aprobado</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Estadísticas por Centro -->
        <div class="card">
            <h2 class="card-title">🏢 Rendimiento por Centro de Desarrollo</h2>
            <div class="bar-chart">
                <?php 
                $max_promedio = max(array_column($centros, 'promedio'));
                foreach ($centros as $centro): 
                    $porcentaje_barra = ($centro['promedio'] / 10) * 100;
                    $tasa_aprobacion = $centro['total'] > 0 ? ($centro['aprobados'] / $centro['total']) * 100 : 0;
                ?>
                    <div class="bar-item">
                        <div class="bar-label"><?= htmlspecialchars($centro['centro_desarrollo']) ?></div>
                        <div style="flex: 1;">
                            <div class="bar" style="width: <?= $porcentaje_barra ?>%;">
                                <?= number_format($centro['promedio'], 2) ?> / 10
                            </div>
                            <div style="font-size: 11px; color: var(--text-light); margin-top: 4px;">
                                <?= $centro['total'] ?> evaluaciones • 
                                <?= round($tasa_aprobacion, 1) ?>% aprobación
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Distribución de Calificaciones -->
        <div class="card">
            <h2 class="card-title">📈 Distribución de Calificaciones</h2>
            <div class="bar-chart">
                <?php 
                $max_cantidad = max(array_column($distribucion, 'cantidad'));
                foreach ($distribucion as $rango): 
                    $porcentaje_barra = ($rango['cantidad'] / $max_cantidad) * 100;
                    $label_rango = $rango['rango'] . ' - ' . ($rango['rango'] + 0.9);
                ?>
                    <div class="bar-item">
                        <div class="bar-label"><?= $label_rango ?></div>
                        <div style="flex: 1;">
                            <div class="bar" style="width: <?= $porcentaje_barra ?>%;">
                                <?= $rango['cantidad'] ?> pitches
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>