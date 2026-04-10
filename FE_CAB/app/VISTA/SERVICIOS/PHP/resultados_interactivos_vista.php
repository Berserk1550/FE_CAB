<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Acceso No Autorizado</title>
    <link rel="icon" href="../../componentes/img/favicon.ico">
    <meta http-equiv="refresh" content="5;url=<?= htmlspecialchars($HOME, ENT_QUOTES) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        
    </style>
</head>
<body>
    <div class="unauthorized-modal" aria-hidden="false">
        <div class="unauthorized-card" role="dialog" aria-modal="true" aria-labelledby="authTitle">
            <div class="unauthorized-header">
                <div class="unauthorized-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        <circle cx="12" cy="16" r="1"/>
                    </svg>
                </div>
                <h3 class="unauthorized-title" id="authTitle">Acceso No Autorizado</h3>
            </div>
            <div class="unauthorized-body">
                <p>No tienes una sesión activa para ver esta sección.</p>
                <p>Debes <strong>iniciar sesión</strong> para acceder a los resultados interactivos.</p>
                <p class="countdown">Serás redirigido al inicio en <strong id="secs">5</strong> segundos.</p>
            </div>
            <div class="unauthorized-actions">
                <a class="btn btn-secondary" href="javascript:history.back()">Volver</a>
                <a class="btn btn-primary" id="goNow" href="<?= htmlspecialchars($HOME, ENT_QUOTES) ?>">Ir al inicio</a>
            </div>
        </div>
    </div>
    <script>
        
</body>
</html>


/**2DO HTML */

<!DOCTYPE html> 324
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Resultados Interactivos — Global</title>
  <link rel="icon" href="../../componentes/img/favicon.ico">
  <link rel="stylesheet" href="../../componentes/tabla_emprendedores.css">
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <style>
    
  </style>
</head>

<body>
  <div class="board">
    <div class="hero">
      <a href="panel_orientador" class="back">⬅️ Volver al panel</a>
      <h2>📊 Resultados Interactivos — Global</h2>
      <div class="badge" id="badgeRango">Rango activo</div>
    </div>

    <div id="err" class="error-banner"></div>

    <div class="filtros-top">
      <div class="field"><label>Desde</label><input type="date" id="fDesde" value="<?= htmlspecialchars(date('Y-m-d', strtotime('-29 days'))) ?>"></div>
      <div class="field"><label>Hasta</label><input type="date" id="fHasta" value="<?= htmlspecialchars(date('Y-m-d')) ?>"></div>
      <button class="btn-ghost" id="btn7">Últimos 7 días</button>
      <button class="btn-ghost" id="btn30">Últimos 30 días</button>
      <button class="btn-ghost" id="btn90">Últimos 90 días</button>
      <div class="field" style="align-items:center;gap:8px;flex-direction:row">
        <input type="checkbox" id="chkAll" />
        <label for="chkAll" style="margin:0">Todo (ignorar rango)</label>
      </div>

      <button class="btn-primary" id="btnAplicar">Aplicar</button>
      <button class="btn-ghost" id="btnExport">Exportar Gráficos</button>
    </div>

    <div class="cards">
      <div class="kpi-mini" style="--accent:#34d399">
        <div class="label">Total en rango</div>
        <div class="val" id="kTotal">0</div><small id="kFirstLast"></small>
      </div>
      <div class="kpi-mini" style="--accent:#a78bfa">
        <div class="label">Promedio diario</div>
        <div class="val" id="kProm">0</div>
      </div>
      <div class="kpi-mini" style="--accent:#f59e0b">
        <div class="label">Máximo en un día</div>
        <div class="val" id="kMax">0</div>
      </div>
      <div class="kpi-mini" style="--accent:#22d3ee">
        <div class="label">Acceso habilitado</div>
        <div class="val" id="kAccOk">0</div>
      </div>
      <div class="kpi-mini" style="--accent:#38bdf8">
        <div class="label">% Habilitado</div>
        <div class="val" id="kAccPct">0%</div>
      </div>
      <div class="kpi-mini" style="--accent:#f472b6">
        <div class="label">Variación vs prev.</div>
        <div class="val" id="kVar">0</div><small id="kVarPct"></small>
      </div>
    </div>

    <div class="grid-charts">
      <div class="panel">
        <h3>Evolución</h3>
        <div class="legend" id="rangoText"></div>
        <div class="chart-wrap chart--large"><canvas id="chartLine"></canvas></div>
      </div>
      <div class="panel">
        <h3>Acceso</h3>
        <div class="legend">Habilitado vs No habilitado</div>
        <div class="chart-wrap chart--medium"><canvas id="chartAccess"></canvas></div>
        <h3 style="margin-top:14px">Sexo</h3>
        <div class="legend">Distribución</div>
        <div class="chart-wrap chart--medium"><canvas id="chartSexo"></canvas></div>
      </div>
    </div>

    <div class="grid-charts" style="margin-top:16px">
      <div class="panel">
        <h3>Actividad por hora</h3>
        <div class="legend">Frecuencia por hora del día (0–23)</div>
        <div class="chart-wrap chart--medium"><canvas id="chartHoras"></canvas></div>
      </div>
      <div class="panel">
        <h3>Días de la semana</h3>
        <div class="legend">Dom → Sáb</div>
        <div class="chart-wrap chart--medium"><canvas id="chartDOW"></canvas></div>
      </div>
    </div>

    <div class="panel" style="margin-top:16px;">
      <h3>Tendencia — Crecimiento acumulado</h3>
      <div class="legend">Suma acumulada de registros en el rango seleccionado</div>
      <div class="chart-wrap chart--large"><canvas id="chartAcum"></canvas></div>
    </div>

    <div class="panel" style="margin-top:16px;">
      <h3>Programas — Formación y Carrera (TOP-N)</h3>
      <div style="display:grid;gap:16px;grid-template-columns:1fr 1fr">
        <div>
          <div class="legend">Formación</div>
          <div class="chart-wrap"><canvas id="chartFormacion"></canvas></div>
        </div>
        <div>
          <div class="legend">Carrera</div>
          <div class="chart-wrap"><canvas id="chartCarrera"></canvas></div>
        </div>
      </div>
      <div style="margin-top:16px">
        <div class="legend">Programa</div>
        <div class="chart-wrap"><canvas id="chartPrograma"></canvas></div>
      </div>
    </div>

    <div class="panel" style="margin-top:16px;">
      <h3>Dominios de correo — TOP-N</h3>
      <div class="legend">Extraído desde la columna de correo</div>
      <div class="chart-wrap"><canvas id="chartDominios"></canvas></div>
    </div>

    <div class="panel" style="margin-top:16px;">
      <h3>Ubicación (TOP-N)</h3>
      <div style="display:grid;gap:16px;grid-template-columns:1fr 1fr">
        <div>
          <div class="legend">Municipio</div>
          <div class="chart-wrap"><canvas id="chartMuni"></canvas></div>
        </div>
        <div>
          <div class="legend">Departamentos</div>
          <div class="chart-wrap"><canvas id="chartDepto"></canvas></div>
        </div>
      </div>
      <div class="legend" style="margin-top:10px">Países</div>
      <div class="chart-wrap"><canvas id="chartPais"></canvas></div>
    </div>

    <div class="grid-charts" style="margin-top:16px">
      <div class="panel">
        <h3>Clasificación de población</h3>
        <div class="legend">Distribución por clasificación</div>
        <div class="chart-wrap"><canvas id="chartPoblacion"></canvas></div>

        <h3 style="margin-top:14px">Discapacidad</h3>
        <div class="legend">Distribución por condición</div>
        <div class="chart-wrap"><canvas id="chartDiscapacidad"></canvas></div>
      </div>

      <div class="panel">
        <h3 style="margin-top:14px">Empresa formalizada</h3>
        <div class="legend">Registro en cámara de comercio</div>
        <div class="chart-wrap chart--medium"><canvas id="chartFormalEmpresa"></canvas></div>

        <h3 style="margin-top:14px">Ejerce actividad</h3>
        <div class="legend">Actividad económica</div>
        <div class="chart-wrap chart--medium"><canvas id="chartActividad"></canvas></div>

        <h3 style="margin-top:14px">Situación del negocio</h3>
        <div class="legend">Distribución</div>
        <div class="chart-wrap chart--medium"><canvas id="chartSitNeg"></canvas></div>
      </div>

      <div class="panel">
        <h3>Orientadores (TOP-N)</h3>
        <div class="legend">Registros por orientador</div>
        <div class="chart-wrap"><canvas id="chartOrientadores"></canvas></div>

        <h3 style="margin-top:14px">Centro de formación (TOP-N)</h3>
        <div class="legend">Centros de formación</div>
        <div class="chart-wrap chart--medium"><canvas id="chartCentro"></canvas></div>
      </div>
    </div>
  </div>

  <script>

  </script>
</body>

</html>