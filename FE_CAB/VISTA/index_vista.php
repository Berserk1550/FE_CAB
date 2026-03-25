<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Inicio SENA CAB - Fondo Emprender</title>
    <link rel="icon" href="componentes/img/favicon.ico">
    <link rel="stylesheet" href="../statics/css/login.css" />
    <link rel="icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGMAAQAABQABDQottQAAAABJRU5ErkJggg==">
</head>

<body>
    <!-- ====== Encabezado institucional (blanco, línea delgada) ====== -->
    <header class="site-header" role="banner">
        <div class="site-header__inner">
            <a href="index" class="brand brand--sena" aria-label="SENA" tabindex="8">
                <img src="componentes/img/logoFondoEmprender.svg" alt="SENA" width="80" height="80" loading="eager">
            </a>
            <nav class="site-header__nav" aria-label="Acciones">
                <div class="dropdown-medalla">
                    <button class="btn-medalla" type="button" aria-label="Mostrar nombre del programa" tabindex="7">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#39a900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-medal-2">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M9 3h6l3 7l-6 2l-6 -2z" />
                            <path d="M12 12l-3 -9" />
                            <path d="M15 11l-3 -8" />
                            <path d="M12 19.5l-3 1.5l.5 -3.5l-2 -2l3 -.5l1.5 -3l1.5 3l3 .5l-2 2l.5 3.5z" />
                        </svg>
                    </button>
                    <span class="texto-guardado" style="display: none;">ANÁLISIS Y DESARROLLO EN SOFTWARE | 2825817</span>
                    <div class="tooltip-medalla" id="tooltip-medalla" aria-hidden="true"></div>
                </div>
                <a class="btn-cta" href="formulario_emprendedores/registro_emprendedores" tabindex="6">Registrarse</a>
            </nav>
        </div>
    </header>

    
    <!-- ====== Contenido principal (según boceto) ====== -->
    <main class="layout" id="contenido" role="main">
        <section class="home-grid" aria-label="Inicio Fondo Emprender">
            <article class="card card-registro" aria-labelledby="texto-carlos">
                <h3 id="texto-carlos" class="card__title">Regístrate</h3>
                <p>
                    Regístrate y da el primer paso para convertir tu proyecto en realidad.
                </p>
                <p><strong>Crea tu cuenta y comencemos juntos.</strong></p>
                <a class="chip" href="formulario_emprendedores/registro_emprendedores" aria-label="Ir al formulario de registro" tabindex="1">
                    Regístrate
                </a>
                <p class="card__note" style="margin-top:10px">
                    <small>
                        (Al registrarte autorizas el tratamiento de tus datos conforme a la Ley 1581 de 2012 y normas concordantes de Habeas Data).
                    </small>
                </p>
            </article>
            <div class="home-pair" aria-label="Usuario y Convocatoria">
            <section class="panel card-login">
                <header class="panel__header">
                    <h1 class="panel__title">Usuario Registrado</h1>
                </header>

                <form action="servicios/php_Login/autenticador" method="post" class="form" novalidate>
                    <div class="form__group">
                        <label for="numeroDocumento">Número de documento</label>
                        <input
                            type="text"
                            id="numeroDocumento"
                            name="numeroDocumento"
                            pattern="[A-Z0-9]{1,20}"
                            maxlength="20"
                            title="Ingrese un número de documento válido (letras mayúsculas y números, hasta 20)"
                            tabindex="2"
                            value="<?= h($docParam) ?>"
                            required />
                    </div>

                    <div class="form__group">
                        <label for="contrasena">Contraseña</label>
                        <div class="form__password">
                            <input
                                type="password"
                                id="contrasena"
                                name="contrasena"
                                title="Solo números, mínimo 6 y máximo 10"
                                pattern="[0-9]{6,10}"
                                minlength="6"
                                tabindex="3"
                                maxlength="10" 
                                required/>
                            <button type="button" id="mostrarConstrasena" class="form__toggle" aria-label="Mostrar u ocultar contraseña">
                                <svg id="ojoAbierto" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#19985B">
                                    <ellipse cx="12" cy="12" rx="8" ry="6" stroke-width="2" />
                                    <circle cx="12" cy="12" r="2" fill="#19985B" />
                                </svg>
                                <svg id="ojoCerrado" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#A0A0A0" style="display:none">
                                    <ellipse cx="12" cy="12" rx="8" ry="6" stroke-width="2" />
                                    <line x1="5" y1="19" x2="19" y2="5" stroke="#A0A0A0" stroke-width="2" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <?php if ($errorParam !== ''): ?>
                        <div class="mensaje-error" id="errorMensaje"><?= h($errorParam) ?></div>
                    <?php endif; ?>

                    <button type="submit" class="btn-primary" tabindex="3">Ingresar</button>

                    <div class="links">
                        <a href="servicios/php_Login/verificar_identidad" class="link">Verificar identidad (si no tienes contraseña, entra aquí)</a>
                    </div>
                </form>
            </section>
            <article class="card card-convocatoria">
                <h3 id="convocatoria" class="card__title">Convocatoria</h3>
                <p>
                    Conoce las convocatorias vigentes del Fondo Emprender. Aquí encontrarás
                    requisitos, beneficios y cronogramas para transformar tu idea en una
                    empresa sostenible con el acompañamiento del SENA.
                </p>
                <p><strong>Explora las convocatorias y descubre la ruta que mejor se adapta a ti.</strong></p>
                <p class="card__note">
                    <small>
                        <a href="https://www.fondoemprender.com/SitePages/FondoEmprenderConvocatorias2020.aspx" target="_blank" rel="noopener" tabindex="4">
                            Ver convocatorias →
                        </a>
                    </small>
                </p>
            </article>
            </div>
            <section class="stats-container stats-grid">

                <article class="card card-stat card-orientados-modern">

                    <div class="header">
                        <h3>Emprendedores orientados</h3>
                        <p class="total-number count" data-key="totalReg">
                            <?= number_format((int)$totalReg, 0, ',', '.') ?>
                        </p>
                    </div>

                    <div class="divider"></div>

                    <div class="year-row">
                        <span class="year">2023</span>
                        <span class="value count" data-key="anio2023"><?= number_format((int)$anio2023, 0, ',', '.') ?></span>
                    </div>

                    <div class="year-row">
                        <span class="year">2024</span>
                        <span class="value count" data-key="anio2024"><?= number_format((int)$anio2024, 0, ',', '.') ?></span>
                    </div>

                    <div class="year-row">
                        <span class="year">2025</span>
                        <span class="value count" data-key="anio2025"><?= number_format((int)$anio2025, 0, ',', '.') ?></span>
                    </div>

                </article>



                <article class="card card-stat">
                    <h3 class="card__title">Top 3 Municipios alcanzados</h3>
                    <ul class="stat-list" data-key="municipios">
                        <?php foreach ($municipios as $m): ?>
                            <li>
                                <span class="stat-label"><?= h($m['municipio']) ?></span>
                                <span class="stat-value count">
                                    <?= number_format((int)$m['total'], 0, ',', '.') ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </article>

                <article class="card card-stat">
                    <h3 class="card__title">Top 3 niveles de estudios de nuestros emprendedores</h3>
                    <ul class="stat-list" data-key="estudios">
                        <?php foreach ($estudios as $e): ?>
                            <li>
                                <span class="stat-label"><?= h($e['nivel_formacion']) ?></span>
                                <span class="stat-value count">
                                    <?= number_format((int)$e['total'], 0, ',', '.') ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </article>

                <article class="card card-stat">
                    <h3 class="card__title">Top 3 tipificaciones de nuestros emprendedores</h3>
                    <ul class="stat-list" data-key="clasificaciones">
                        <?php foreach ($clasificaciones as $c): ?>
                            <li>
                                <span class="stat-label"><?= h($c['clasificacion']) ?></span>
                                <span class="stat-value count">
                                    <?= number_format((int)$c['total'], 0, ',', '.') ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            </section>
        </section>
    </main>


    <footer class="site-footer" role="contentinfo">
        <div class="site-footer__inner">
            <img src="componentes/img/logocolombiaporlavidatrabajo.png" alt="Colombia potencia de la vida" width="50" height="50">
            <img src="componentes/img/mintrabajo.png" alt="Ministerio del Trabajo" width="50" height="50">
        </div>
    </footer>

    <script src="../statics/Js/login.js"></script>

</body>

</html>