
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Identidad</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../componentes/verificar_identidad.css">
    <link rel="shortcut icon" href="../../componentes/img/favicon.ico" type="image/x-icon">
</head>

<body>

    <div class="card-auth">
        <div class="auth-header">
            <img src="../../componentes/img/logoFondoEmprender.svg" alt="SENA">
            <h1>Verificar identidad</h1>
        </div>
        
        <?php if ($viene_de_confirmacion): ?>
            <div style="background: linear-gradient(135deg, rgba(57, 169, 0, 0.1) 0%, rgba(255, 255, 255, 0.5) 100%); 
                        border-left: 4px solid #39A900; 
                        border-radius: 8px; 
                        padding: 15px 20px; 
                        margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <svg style="color: #39A900; width: 24px; height: 24px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <h3 style="color: #39A900; font-size: 1.1rem; font-weight: 700; margin: 0;">¡Participación confirmada!</h3>
                </div>
                <p style="color: #374151; margin: 0; line-height: 1.5;">
                    <?= htmlspecialchars($mensaje_bienvenida) ?>
                </p>
            </div>
        <?php endif; ?>
        
        <p class="lead">Ingresa tu número de documento para validar tu registro.</p>

        <form method="POST" id="form-id">
            <label for="numero_id" class="form-label">Documento de identidad</label>
            <input
                id="numero_id" name="numero_id" class="form-control" type="text"
                inputmode="numeric" pattern="[0-9]{5,15}" maxlength="15"
                placeholder="Ej: 1032456789" required autofocus value="<?= htmlspecialchars($numero_id) ?>">
            <div class="form-text">Solo números, sin puntos ni guiones.</div>

            <div style="height:12px"></div>
            <a type="submit" class="btn-sena" id="btnLookup" role="button">Revisar</a>
            <a class="volver" href="../../index"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left-dashed"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12h6m3 0h1.5m3 0h.5" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg></svg> Volver al inicio</a>
            <div id="alertZona"></div>
        </form>

    </div>

    <div id="usuarioModal" class="modal" role="dialog" aria-modal="true"
        aria-labelledby="modalTitle" aria-describedby="modalDesc">
        <div class="modal-card" tabindex="-1">
            <div class="modal-header">
                <h2 id="modalTitle" class="modal-title"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-progress-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 20.777a8.942 8.942 0 0 1 -2.48 -.969" /><path d="M14 3.223a9.003 9.003 0 0 1 0 17.554" /><path d="M4.579 17.093a8.961 8.961 0 0 1 -1.227 -2.592" /><path d="M3.124 10.5c.16 -.95 .468 -1.85 .9 -2.675l.169 -.305" /><path d="M6.907 4.579a8.954 8.954 0 0 1 3.093 -1.356" /><path d="M9 12l2 2l4 -4" /></svg> Usuario encontrado</h2>
                <button type="button" class="btn-close" data-close aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalDesc">
                <p>Verifica si tus datos son correctos:</p>
                <ul>
                    <li><b>Nombres:</b> <?= htmlspecialchars($usuario['nombres']) ?></li>
                    <li><b>Apellidos:</b> <?= htmlspecialchars($usuario['apellidos']) ?></li>
                    <li><b>Documento:</b> <?= htmlspecialchars($usuario['numero_id']) ?></li>
                    <li><b>Celular:</b> <?= htmlspecialchars($usuario['celular']) ?></li>
                    <li><b>Correo:</b> <?= htmlspecialchars($usuario['correo']) ?></li>
                </ul>
            </div>
            <div class="modal-footer">
                <a id="btnRedir" class="btn btn-sena" href="#">Actualizar mis datos</a>
                <button type="button" class="btn" data-close>Cancelar</button>
            </div>
        </div>
    </div>
    <div class="modal-backdrop" id="usuarioBackdrop"></div>
    <script>
    
    </script>

</body>

</html>