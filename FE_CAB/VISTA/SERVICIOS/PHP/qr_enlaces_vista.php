/**ESTE HTML SE ENCUENTRA DENTRO DEL IF DE LA LINEA 58 */

<!doctype html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>No autorizado</title>
            <style>
             </style>
        </head>
        <body>
        <div class="auth-modal" aria-hidden="false">
            <div class="auth-card" role="dialog" aria-modal="true" aria-labelledby="authT">
                <div class="auth-hdr">
                    <h3 id="authT">Acceso no autorizado</h3>
                </div>
                <p>Debes iniciar sesión con el rol <strong>orientador</strong> para usar esta herramienta.</p>
                <p class="muted">Te redirigiremos al inicio en <strong id="count">5</strong> segundos.</p>
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