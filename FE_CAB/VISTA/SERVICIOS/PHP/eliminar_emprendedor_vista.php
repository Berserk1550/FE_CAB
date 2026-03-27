<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Eliminar Emprendedor — Panel Orientadores</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>



    </style>
</head>

<body>
    <div class="wrap">
        <div class="header">
            <div class="header-left">
                <a href="panel_orientador" class="btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver al Panel
                </a>
                <h1 class="title">
                    <span class="title-icon">🗑️</span>
                    Eliminar Emprendedor
                    <span class="badge">orientador</span>
                </h1>
            </div>
            <div class="legend">
                Auto-prueba DB: <strong style="color: var(--success)">OK</strong>. Total: <strong><?= number_format($total) ?></strong>
            </div>
        </div>

        <div class="card">
            <div class="toolbar">
                <div class="search">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="search" id="q" placeholder="Buscar por nombre, documento, correo..." autocomplete="off">
                </div>
                <select id="orden">
                    <option value="id_desc">ID mayor a menor</option>
                    <option value="id_asc">ID menor a mayor</option>
                    <option value="fecha_desc" selected>Fecha reciente</option>
                    <option value="fecha_asc">Fecha antigua</option>
                </select>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 14px;">
                <div class="legend">
                    Registros cargados: <strong id="count">0</strong> 
                    <span id="total-info" style="color: var(--ink-muted)"></span>
                </div>
                <div class="legend" id="loading-indicator" style="display:none; align-items: center; gap: 8px;">
                    <span class="loading-spinner"></span> Cargando...
                </div>
            </div>

            <div class="table-wrap" id="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NOMBRE COMPLETO</th>
                            <th>DOCUMENTO</th>
                            <th>CELULAR</th>
                            <th>CORREO</th>
                            <th>FECHA ORIENTACIÓN</th>
                            <th style="text-align:right">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="tb"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal" id="md">
        <div class="modal-card" style="max-width: 750px;">
            <div class="modal-head">
                <h3 class="modal-title" id="md-title">Detalle del Emprendedor</h3>
                <button class="x" id="md-x">✕</button>
            </div>
            <div class="modal-body" id="md-body">
                <div class="legend" style="text-align: center; padding: 50px;">
                    <span class="loading-spinner"></span> Cargando detalle...
                </div>
            </div>
            <div class="modal-foot">
                <span id="md-status" class="legend" style="margin-right:auto;display:none"></span>
                <button class="btn btn-primary" id="md-guardar" title="Ctrl+S">Guardar</button>
                <button class="btn btn-line" id="md-cerrar">Cerrar</button>
            </div>
        </div>
    </div>

    <div class="modal" id="modal">
        <div class="modal-card">
            <div class="modal-head">
                <h3 class="modal-title" id="modal-title">Eliminar Emprendedor</h3>
                <button class="x" id="modal-x">✕</button>
            </div>
            <div class="modal-body">
                <p id="modal-text" style="margin-bottom: 18px; font-size: 15px; color: var(--ink); font-weight: 500;"></p>
                <label style="display: block; margin-bottom: 10px; font-weight: 700; color: var(--accent); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Motivo de eliminación (opcional):
                </label>
                <textarea id="motivo" rows="3" placeholder="Escribe el motivo de la eliminación..."></textarea>
            </div>
            <div class="modal-foot">
                <button class="btn btn-line" id="cancelar">Cancelar</button>
                <button class="btn btn-danger" id="confirmar">Eliminar definitivamente</button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script>
    
    </script>

    <script>
    
    </script>
</body>

</html>