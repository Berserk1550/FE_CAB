<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Actualizar mis datos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  

</head>

<body>
  <div class="wrap">
    <div class="cardx">
      <div class="header">
        <div class="blob" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.5 7.5l-7 7-3 1 1-3 7-7 2 2z"></path>
            <path d="M15 6a5 5 0 11-8.9 3.1"></path>
            <path d="M3 21c.5-3.3 3.3-6 7-6h1"></path>
          </svg>
        </div>
        <div class="flex-grow-1">
          <h1>Actualizar mis datos</h1>
          <div class="mt-1"><span class="badge-id" title="Identificación"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="5" width="18" height="14" rx="3"></rect>
                <path d="M7 9h8"></path>
                <path d="M7 13h5"></path>
              </svg><?= V('tipo_id') ?: 'ID'; ?> • <?= V('numero_id'); ?></span></div>
        </div>
      </div>

      <div class="stepper" role="tablist" aria-label="Progreso de actualización">
        <div class="step" data-step="1" data-active="true"><span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Z" />
              <path d="M3 21a9 9 0 0 1 18 0" />
            </svg></span><span class="txt">1. Información personal</span></div>
        <div class="step" data-step="2"><span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M12 21s-6-4.4-6-10a6 6 0 1 1 12 0c0 5.6-6 10-6 10Z" />
              <circle cx="12" cy="11" r="2.5" />
            </svg></span><span class="txt">2. Nacionalidad</span></div>
        <div class="step" data-step="3"><span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M3 4h18M8 8h8M6 12h12M4 16h16" />
            </svg></span><span class="txt">3. Información adicional</span></div>
        <div class="step" data-step="4"><span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <rect x="3" y="4" width="18" height="16" rx="2" />
              <path d="M7 8h10M7 12h10M7 16h10" />
            </svg></span><span class="txt">4. Caracterización</span></div>
        <div class="step" data-step="5"><span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M22 10L12 6 2 10l10 4 10-4z" />
              <path d="M6 12v5c2 1 4 1 6 1s4 0 6-1v-5" />
            </svg></span><span class="txt">5. Educativa</span></div>
        <div class="step" data-step="6"><span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M3 12h18" />
              <path d="M7 12v7a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-7" />
              <path d="M7 7h10v5H7z" />
            </svg></span><span class="txt">6. Complementaria</span></div>
        <div class="step" data-step="7"><span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M3 21V7a2 2 0 0 1 2-2h3v16" />
              <path d="M9 21h6" />
            </svg></span><span class="txt">7. Centro y orientador</span></div>
      </div>
      <div class="prog">
        <div class="bar" id="bar" style="width:0%"></div>
      </div>

      <form id="form" class="body needs-validation" action="procesar_actualizacion" method="POST" novalidate>
        <input type="hidden" name="numero_id" autocomplete="off" value="<?= V('numero_id'); ?>">
        <input type="hidden" id="prev_centro" value="<?= NORM('centro_orientacion'); ?>">
        <input type="hidden" id="prev_orientador" value="<?= V('orientador'); ?>">
        <input type="hidden" name="reasignacion_modo" id="reasignacion_modo" value="actualizar">

        <!-- ===== FASE 1 ===== -->
        <section class="phase active" data-step="1" aria-labelledby="ph1">
          <h2 id="ph1" class="h5 mb-2">1) Información Personal</h2>
          <p class="hint mb-3">Actualiza tus datos básicos. <span class="req">*</span> Campos obligatorios.</p>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="nombres" class="form-label">Nombres <span class="req">*</span></label>
              <input type="text" class="form-control" name="nombres" id="nombres" required pattern="[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]{2,25}" minlength="2" maxlength="25" value="<?= V('nombres'); ?>">
              <div class="invalid-feedback">Por favor ingresa tus nombres (solo letras).</div>
            </div>
            <div class="col-md-6">
              <label for="apellidos" class="form-label">Apellidos <span class="req">*</span></label>
              <input type="text" class="form-control" name="apellidos" id="apellidos" required pattern="[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]{2,40}" minlength="2" maxlength="40" value="<?= V('apellidos'); ?>">
              <div class="invalid-feedback">Por favor ingresa tus apellidos (solo letras).</div>
            </div>
            <div class="col-md-6">
              <label for="tipo_id" class="form-label">Tipo de Identificación <span class="req">*</span></label>
              <?php $tipos = ["TI" => "Tarjeta de Identidad (TI)", "CC" => "Cédula de Ciudadanía (CC)", "CE" => "Cédula de Extranjería (CE)", "PEP" => "Permiso Especial de Permanencia (PEP)", "PAS" => "Pasaporte (PAS)", "PPT" => "Permiso Temporal de Protección (PPT)"]; ?>
              <select id="tipo_id" name="tipo_id" class="form-select" required>
                <option value="" disabled <?= ($usuario_db['tipo_id'] === '') ? 'selected' : ''; ?>>-- Selecciona una opción --</option>
                <?php foreach ($tipos as $val => $label): $sel = ($usuarioN['tipo_id'] === $val) ? 'selected' : ''; ?>
                  <option value="<?= h($val) ?>" <?= $sel ?>><?= h($label) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Selecciona un tipo de identificación.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Número de Identificación</label>
              <input type="text" class="form-control" value="<?= V('numero_id'); ?>" readonly>
            </div>
            <div class="col-md-6">
              <label for="correo" class="form-label">Correo <span class="req">*</span></label>
              <input type="email" class="form-control" name="correo" id="correo" required value="<?= V('correo'); ?>">
              <div class="invalid-feedback">Ingresa un correo válido.</div>
            </div>
            <div class="col-md-6">
              <label for="celular" class="form-label">Celular <span class="req">*</span></label>
              <input type="tel" class="form-control" name="celular" id="celular" required pattern="[0-9]{10}" minlength="10" maxlength="10" title="10 dígitos" value="<?= V('celular'); ?>">
              <div class="invalid-feedback">Ingresa tu número (10 dígitos).</div>
            </div>
            <div class="col-md-6">
              <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento <span class="req">*</span></label>
              <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control" value="<?= V('fecha_nacimiento'); ?>" required>
              <div class="invalid-feedback">Indica tu fecha de nacimiento.</div>
            </div>
          </div>
        </section>

        <!-- ===== FASE 2 ===== -->
        <section class="phase" data-step="2" aria-labelledby="ph2">
          <h2 id="ph2" class="h5 mb-2">2) Nacionalidad</h2>
          <p class="hint mb-3">País, nacionalidad y ubicación de residencia.</p>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="pais_origen" class="form-label">País <span class="req">*</span></label>
              <select id="pais_origen" name="pais_origen" class="form-select" required data-value="<?= V('pais_origen') ?>">
                <option value="" disabled <?= ($usuario_db['pais_origen'] === '') ? 'selected' : ''; ?>>-- Selecciona un país --</option>
              </select>
              <div class="invalid-feedback">Selecciona tu país.</div>
            </div>
            <div class="col-md-6">
              <label for="nacionalidad" class="form-label">Nacionalidad <span class="req">*</span></label>
              <input type="text" id="nacionalidad" name="nacionalidad" class="form-control" value="<?= V('nacionalidad'); ?>" disabled pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ .\/-]{3,40}" title="3–40 caracteres (letras, espacios, punto, / o -)">
              <div class="invalid-feedback">Escribe tu nacionalidad.</div>
            </div>
            <div class="col-md-6">
              <label for="departamento" class="form-label">Departamento <span class="req">*</span></label>
              <select id="departamento" name="departamento" class="form-select" required>
                <option value="" disabled <?= ($usuarioN['departamento'] === '') ? 'selected' : ''; ?>>-- Selecciona un departamento --</option>
                <?php $departamentos = ["Amazonas", "Antioquia", "Arauca", "Atlántico", "Bogotá D.C.", "Bolívar", "Boyacá", "Caldas", "Caquetá", "Casanare", "Cauca", "Cesar", "Chocó", "Córdoba", "Cundinamarca", "Guainía", "Guaviare", "Huila", "La Guajira", "Magdalena", "Meta", "Nariño", "Norte de Santander", "Putumayo", "Quindío", "Risaralda", "San Andrés y Providencia", "Santander", "Sucre", "Tolima", "Valle del Cauca", "Vaupés", "Vichada", "Otro"];
                foreach ($departamentos as $dpto) {
                  $selected = ($usuarioN['departamento'] == $dpto) ? "selected" : "";
                  echo "<option value='" . h($dpto) . "' $selected>" . h($dpto) . "</option>";
                } ?>
              </select>
              <div class="invalid-feedback">Selecciona un departamento.</div>
              <input type="text" id="dpto_otro" name="departamento_otro" placeholder="Especifique cuál" class="form-control mt-2" style="display:<?= ($usuarioN['departamento'] == "Otro") ? "block" : "none"; ?>;" value="<?= NORM('departamento_otro'); ?>">
            </div>
            <div class="col-md-6">
              <label for="municipio" class="form-label">Municipio <span class="req">*</span></label>
              <input type="text" class="form-control" name="municipio" id="municipio" required pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ .,'\-]{2,60}" title="Letras, espacios, coma, punto y guion (2–60)" value="<?= V('municipio'); ?>">
              <div class="invalid-feedback">Indica tu municipio.</div>
            </div>
        </section>

        <!-- ===== FASE 3 ===== -->
        <section class="phase" data-step="3" aria-labelledby="ph3">
          <h2 id="ph3" class="h5 mb-2">3) Información Adicional</h2>
          <p class="hint mb-3">Datos para seguimiento y sexo.</p>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="fecha_orientacion_display" class="form-label">Fecha de orientación</label>
              <input type="date" id="fecha_orientacion_display" class="form-control" value="<?= V('fecha_orientacion') ?: date('Y-m-d'); ?>" required>
              <input type="hidden" name="fecha_orientacion" id="fecha_orientacion" value="<?= V('fecha_orientacion') ?: date('Y-m-d'); ?>">
              <input type="hidden" name="ts_inicio" id="ts_inicio" value="<?= V('ts_inicio'); ?>">
            </div>
            <div class="col-md-6">
              <label for="sexo" class="form-label">Sexo <span class="req">*</span></label>
              <?php $gen = $usuarioN['sexo']; ?>
              <select id="sexo" name="sexo" class="form-select" required>
                <option value="" disabled <?= $gen === '' ? 'selected' : ''; ?>>-- Selecciona --</option>
                <option value="Mujer" <?= $gen === 'Mujer'  ? 'selected' : ''; ?>>Mujer</option>
                <option value="Hombre" <?= $gen === 'Hombre' ? 'selected' : ''; ?>>Hombre</option>
                <option value="No definido" <?= $gen === 'No definido' ? 'selected' : ''; ?>>No definido</option>
              </select>
              <div class="invalid-feedback">Selecciona tu sexo.</div>
            </div>
          </div>
        </section>

        <!-- ===== FASE 4 ===== -->
        <section class="phase" data-step="4" aria-labelledby="ph4">
          <h2 id="ph4" class="h5 mb-2">4) Caracterización</h2>
          <p class="hint mb-3">Población y condición de discapacidad.</p>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="clasificacion" class="form-label">Clasificación de población <span class="req">*</span></label>
              <select id="clasificacion" name="clasificacion" class="form-select" required>
                <option value="" disabled <?= ($usuarioN['clasificacion'] === '') ? 'selected' : ''; ?>>-- Selecciona una opción --</option>
                <?php $clasificaciones = ["Ninguno", "Adolescente trabajador", "Adolescente en conflicto con la ley penal", "Adolescentes y jóvenes vulnerables", "Afrocolombianos", "Campesinos", "Desplazado por fenómenos naturales", "Migrantes que retornan al país", "Mujer cabeza de hogar", "Negritudes", "Palenqueros", "Reintegrados (ARN)", "Personas en reincorporación", "Población con discapacidad", "Población indígena", "Población LGBTI", "Víctima de minas antipersona", "Pueblo ROM", "Raizales", "Remitidos por PAL", "Soldados campesinos", "Tercera edad", "Víctima de la violencia", "Víctima de otros hechos", "Sobrevivientes de agentes químicos"];
                foreach ($clasificaciones as $clas) {
                  $selected = ($usuarioN['clasificacion'] == $clas) ? "selected" : "";
                  echo "<option value='" . h($clas) . "' $selected>" . h($clas) . "</option>";
                } ?>
              </select>
              <div class="invalid-feedback">Selecciona tu clasificación.</div>
            </div>
            <div class="col-md-6">
              <label for="discapacidad" class="form-label">Condición de discapacidad <span class="req">*</span></label>
              <?php $disc = $usuarioN['discapacidad']; ?>
              <select id="discapacidad" name="discapacidad" class="form-select" required>
                <option value="" disabled <?= $disc === '' ? 'selected' : ''; ?>>-- Selecciona una opción --</option>
                <?php $opcDisc = ["Ninguna", "Auditiva", "Cognitiva", "Física", "Múltiple", "Psicosocial", "Sordoceguera", "Visual"];
                foreach ($opcDisc as $op) {
                  $sel = ($disc === $op) ? 'selected' : '';
                  echo "<option value='" . h($op) . "' $sel>" . h($op) . "</option>";
                } ?>
              </select>
              <div class="invalid-feedback">Selecciona una opción.</div>
            </div>
          </div>
        </section>

        <!-- ===== FASE 5 ===== -->
        <section class="phase" data-step="5" aria-labelledby="ph5">
          <h2 id="ph5" class="h5 mb-2">5) Caracterización Educativa</h2>
          <p class="hint mb-3">Tipo de emprendedor, formación y programa.</p>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="tipo_emprendedor" class="form-label">Tipo de Emprendedor <span class="req">*</span></label>
              <?php $tipos_emprendedor = ["Aprendiz", "Instructor", "Egresado de Otras Instituciones", "Egresado SENA Complementaria", "Egresado SENA Titulada", "No cuenta con formación", "Otro"];
              $tipo_db = trim((string)($usuario_db['tipo_emprendedor'] ?? ''));
              $hay_match_tipo = false;
              foreach ($tipos_emprendedor as $tipo) {
                if (eq_ci($tipo_db, $tipo)) {
                  $hay_match_tipo = true;
                  break;
                }
              } ?>
              <select id="tipo_emprendedor" name="tipo_emprendedor" class="form-select" required>
                <option value="" disabled <?= empty($tipo_db) ? 'selected' : '' ?>>-- Selecciona una opción --</option>
                <?php foreach ($tipos_emprendedor as $tipo): ?>
                  <option value="<?= h($tipo) ?>" <?= eq_ci($tipo_db, $tipo) ? 'selected' : '' ?>><?= h($tipo) ?></option>
                <?php endforeach; ?>
                <?php if ($tipo_db !== '' && !$hay_match_tipo): ?>
                  <option value="<?= h($tipo_db) ?>" selected><?= h($tipo_db) ?></option>
                <?php endif; ?>
              </select>
              <input type="text" id="tipo_emprendedor_otro" name="tipo_emprendedor_otro" class="form-control mt-2" placeholder="Escribe tu tipo de emprendedor" style="<?= ($usuarioN['tipo_emprendedor'] === 'Otro') ? '' : 'display:none;' ?>" value="<?= V('tipo_emprendedor_otro') ?>" pattern="[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]{3,60}" title="Solo letras, de 3 a 60 caracteres">
              <div class="small-muted">Solo letras (3–60 caracteres).</div>

              <?php
              $niveles = ["Técnico", "Técnico laboral", "Tecnólogo", "Operario", "Auxiliar", "Profesional", "Profesional con Posgrado", "Especialización", "Maestría", "Doctorado", "Sin título"];
              $nf_raw = (string)($usuario_db['nivel_formacion'] ?? '');
              $nf_raw = preg_replace('/[\x{00A0}\x{2000}-\x{200B}\x{2060}\x{FEFF}]+/u', ' ', $nf_raw);
              $nf_raw = trim(preg_replace('/\s+/u', ' ', $nf_raw));
              // ¿El crudo calza con alguna opción del catálogo?
              $tieneMatchCrudo = false;
              foreach ($niveles as $niv) {
                if (eq_ci($nf_raw, $niv)) {
                  $tieneMatchCrudo = true;
                  break;
                }
              }
              $nf_raw = (string)($usuario_db['nivel_formacion'] ?? '');
              $nf_raw = preg_replace('/[\x{00A0}\x{2000}-\x{200B}\x{2060}\x{FEFF}]+/u', ' ', $nf_raw);
              $nf_raw = trim(preg_replace('/\s+/u', ' ', $nf_raw));
              ?>

              <label for="nivel_formacion" class="form-label">Nivel de Formación <span class="req">*</span></label>
              <select id="nivel_formacion" name="nivel_formacion" class="form-select" required autocomplete="off" data-value="<?= h($nf_raw) ?>">
                <option value="" disabled <?= ($nf_raw === '') ? 'selected' : '' ?>>-- Selecciona tu nivel --</option>
                <?php foreach ($niveles as $niv): ?><option value="<?= h($niv) ?>" <?= eq_ci($nf_raw, $niv) ? 'selected' : '' ?>><?= h($niv) ?></option><?php endforeach; ?>
                <?php if ($nf_raw !== '' && !$tieneMatchCrudo): ?><option value="<?= h($nf_raw) ?>" selected><?= h($nf_raw) ?></option><?php endif; ?>
              </select>
              <div class="invalid-feedback">Selecciona tu nivel de formación.</div>

              <?php
              $tecnologos = ["Análisis y desarrollo de software", "Gestión de talento humano", "Gestión agroempresarial", "Gestión de recursos naturales", "Prevención y control ambiental", "Desarrollo multimedia y web", "Gestión contable y de información financiera", "Desarrollo publicitario", "Gestión de la seguridad y salud en el trabajo", "Gestión de redes de datos", "Mantenimiento electromecánico industrial", "Producción de multimedia", "Animación digital", "Gestión empresarial", "Gestión documental", "Actividad física y entrenamiento deportivo", "Regencia de farmacia", "Producción ganadera", "Gestión de empresas agropecuarias", "Supervisión de redes de distribución de energía eléctrica", "Procesamiento de alimentos", "Control de calidad de alimentos", "Gestión logística", "Mecanización agrícola y producción agrícola", "Otro"];
              $tecnicos = ["Asistencia administrativa", "Cocina", "Conservación de recursos naturales", "Contabilización de operaciones comerciales y financieras", "Ejecución de programas deportivos", "Enfermería", "Monitoreo ambiental", "Operación turística local", "Sistemas agropecuarios ecológicos", "Sistemas teleinformáticos", "Sistemas atención integral al cliente", "Cultivo de agrícolas", "Elaboración de productos alimenticios", "Instalación de sistemas eléctricos residenciales y comerciales", "Programación de software", "Proyectos agropecuarios", "Recursos humanos y comercialización de productos masivos", "Integración de operaciones logísticas", "Manejo de viveros", "Mecánica de maquinaria industrial", "Integración de contenidos digitales", "Electricista industrial", "Mantenimiento de motocicletas y motocarros", "Mantenimiento de vehículos livianos", "Soldadura de productos metalócios en platina", "Producción pecuario", "Operaciones de comercio exterior", "Servicios comerciales y financieros", "Servicios farmacéuticos", "Servicio de restaurante y bar", "Operaciones comerciales en retail", "Operaciones de maquinaria agrícola", "Procesamiento de carnes", "Técnico en operaciones forestales y producción ovino-caprina"];
              $operarios = ["Procesos de panadería", "Cuidado básico de personas con dependencia funcional", "Instalaciones eléctricas para viviendas", "Otro"];
              $auxiliares = ["Servicios de apoyo al cliente", "Otro"];
              $prof_grps = [
                "Ingenierías y Tecnología" => ["Ingeniería de Sistemas", "Ingeniería de Software", "Ingeniería Informática", "Ingeniería en Computación", "Ingeniería Electrónica", "Ingeniería Eléctrica", "Ingeniería en Telecomunicaciones", "Ingeniería Mecánica", "Ingeniería Mecatrónica", "Ingeniería Industrial", "Ingeniería Civil", "Ingeniería Ambiental", "Ingeniería Química", "Ingeniería Biomédica", "Ingeniería Aeroespacial", "Ingeniería Naval", "Ingeniería Geológica", "Ingeniería de Petróleos", "Ingeniería de Minas", "Ingeniería Agroindustrial", "Ingeniería de Alimentos", "Ingeniería en Energías Renovables", "Ingeniería en Materiales", "Ingeniería Topográfica", "Ingeniería de Transporte", "Ingeniería de Datos", "Ciencia de Datos", "Analítica de Negocios", "Inteligencia Artificial", "Ciberseguridad", "Robótica", "Geomática", "Logística e Ingeniería Logística"],
                "Ciencias de la Salud" => ["Medicina", "Enfermería", "Odontología", "Fisioterapia", "Terapia Ocupacional", "Fonoaudiología", "Nutrición y Dietética", "Instrumentación Quirúrgica", "Bacteriología", "Microbiología", "Química Farmacéutica (Farmacia)", "Optometría", "Terapia Respiratoria", "Salud Pública", "Radiología e Imágenes Diagnósticas"],
                "Ciencias Sociales y Humanas" => ["Psicología", "Sociología", "Antropología", "Trabajo Social", "Filosofía", "Historia", "Geografía", "Ciencia Política", "Relaciones Internacionales", "Arqueología", "Lingüística", "Literatura", "Estudios Culturales", "Teología", "Desarrollo Territorial"],
                "Economía, Negocios y Gestión" => ["Administración de Empresas", "Contaduría Pública", "Economía", "Finanzas", "Mercadeo", "Negocios Internacionales", "Comercio Exterior", "Administración Pública", "Gestión Empresarial", "Banca y Finanzas", "Dirección de Empresas", "Emprendimiento", "Gerencia Logística", "Gestión de Proyectos", "Gestión del Talento Humano", "Administración Turística y Hotelera"],
                "Educación (Licenciaturas)" => ["Licenciatura en Educación Preescolar", "Licenciatura en Educación Básica Primaria", "Licenciatura en Lengua Castellana", "Licenciatura en Matemáticas", "Licenciatura en Ciencias Naturales", "Licenciatura en Educación Física", "Licenciatura en Idiomas (Inglés)", "Licenciatura en Educación Especial", "Licenciatura en Artes", "Licenciatura en Música", "Licenciatura en Tecnología e Informática"],
                "Artes, Arquitectura y Diseño" => ["Arquitectura", "Diseño Gráfico", "Diseño Industrial", "Diseño de Modas", "Diseño de Interiores", "Artes Plásticas", "Artes Visuales", "Fotografía", "Cine y Televisión", "Animación Digital", "Música", "Danza", "Teatro", "Producción Multimedia"],
                "Ciencias Básicas y Naturales" => ["Matemáticas", "Estadística", "Física", "Química", "Biología", "Bioquímica", "Geología", "Ciencias de la Tierra", "Astronomía", "Nanociencia y Nanotecnología", "Ciencias del Mar"],
                "Agropecuarias y Ambiente" => ["Medicina Veterinaria", "Zootecnia", "Agronomía", "Ingeniería Agronómica", "Ingeniería Forestal", "Ingeniería Agroecológica", "Ingeniería Agrícola", "Ingeniería Pesquera", "Acuicultura", "Administración Ambiental", "Gestión Ambiental", "Ciencias Ambientales", "Hidrología", "Meteorología"],
                "Comunicación y Medios" => ["Comunicación Social", "Periodismo", "Publicidad", "Relaciones Públicas", "Comunicación Audiovisual", "Comunicación Digital", "Producción de Radio y TV", "Comunicación Organizacional"],
                "Derecho, Gobierno y Seguridad" => ["Derecho", "Criminología", "Criminalística", "Gobierno y Asuntos Públicos", "Gestión Pública", "Seguridad y Salud en el Trabajo", "Gestión de la Seguridad", "Investigación Criminal"],
                "Turismo, Gastronomía y Deporte" => ["Turismo", "Administración Turística y Hotelera", "Gastronomía", "Guianza Turística", "Gestión Deportiva", "Recreación y Deporte"]
              ];
              $post_espec = ["Especialización en Gerencia de Proyectos", "Especialización en Seguridad y Salud en el Trabajo", "Especialización en Gestión de la Calidad", "Especialización en Finanzas", "Especialización en Logística y Supply Chain", "Especialización en Talento Humano", "Especialización en Marketing Digital", "Especialización en Analítica de Datos", "Especialización en Ciberseguridad", "Especialización en Docencia Universitaria", "Especialización en Gerencia de Producción", "Especialización en Derecho Laboral", "Especialización en Derecho Administrativo", "Especialización en Tributación", "Especialización en Gerencia de la Innovación"];
              $post_maes = ["Maestría en Administración (MBA)", "Maestría en Gestión de Proyectos", "Maestría en Ingeniería de Sistemas", "Maestría en Ciencia de Datos", "Maestría en Inteligencia Artificial", "Maestría en Educación", "Maestría en Psicología", "Maestría en Derecho", "Maestría en Economía", "Maestría en Finanzas", "Maestría en Salud Pública", "Maestría en Epidemiología", "Maestría en Ingeniería Industrial", "Maestría en Ingeniería Ambiental", "Maestría en Ciberseguridad"];
              $post_doc = ["Doctorado en Ingeniería", "Doctorado en Ciencias - Física", "Doctorado en Ciencias - Química", "Doctorado en Ciencias - Biología", "Doctorado en Educación", "Doctorado en Psicología", "Doctorado en Derecho", "Doctorado en Economía", "Doctorado en Matemáticas", "Doctorado en Salud Pública", "Doctorado en Ciencia de Datos e IA"];

              /* INFERIR NIVEL DESDE CARRERA CUANDO NIVEL ESTÁ VACÍO */
              $nf_db = trim((string)($usuario_db['nivel_formacion'] ?? ''));
              $carrera_db = trim((string)($usuario_db['carrera'] ?? ''));
              if ($nf_db === '' && $carrera_db !== '') {
                $nivel_inferido = '';
                $in_ci = function (string $needle, array $list): bool {
                  $n = norm_ci($needle);
                  foreach ($list as $v) if (norm_ci($v) === $n) return true;
                  return false;
                };
                $in_group_ci = function (string $needle, array $groups) use ($in_ci): bool {
                  foreach ($groups as $items) if ($in_ci($needle, (array)$items)) return true;
                  return false;
                };
                if ($in_ci($carrera_db, $tecnologos))            $nivel_inferido = 'Tecnólogo';
                elseif ($in_ci($carrera_db, $tecnicos))          $nivel_inferido = 'Técnico';
                elseif ($in_ci($carrera_db, $operarios))         $nivel_inferido = 'Operario';
                elseif ($in_ci($carrera_db, $auxiliares))        $nivel_inferido = 'Auxiliar';
                elseif ($in_group_ci($carrera_db, $prof_grps))   $nivel_inferido = 'Profesional';
                elseif ($in_ci($carrera_db, $post_espec))        $nivel_inferido = 'Especialización';
                elseif ($in_ci($carrera_db, $post_maes))         $nivel_inferido = 'Maestría';
                elseif ($in_ci($carrera_db, $post_doc))          $nivel_inferido = 'Doctorado';
                if ($nivel_inferido !== '') {
                  $usuarioN['nivel_formacion'] = $nivel_inferido;
                  $usuario_db['nivel_formacion'] = $nivel_inferido;
                }
              }
              $carrera_db = $usuarioN['carrera'] ?? $carrera_db;

              if (!function_exists('sel')) {
                function sel(string $a, string $b): string
                {
                  return norm_ci($a) === norm_ci($b) ? 'selected' : '';
                }
              }
              function option_exists(array $opts, string $needle): bool
              {
                foreach ($opts as $opt) {
                  if (norm_ci($opt) === norm_ci($needle)) return true;
                }
                return false;
              }
              if (!function_exists('in_list_ci')) {
                function in_list_ci(string $needle, array $list): bool
                {
                  foreach ($list as $v) {
                    if (norm_ci($needle) === norm_ci($v)) return true;
                  }
                  return false;
                }
              }
              if (!function_exists('in_grouped_list_ci')) {
                function in_grouped_list_ci(string $needle, array $groups): bool
                {
                  foreach ($groups as $items) {
                    if (in_list_ci($needle, (array)$items)) return true;
                  }
                  return false;
                }
              }

              /* ¿El "nivel" realmente es una carrera? => corrige */
              $nf_db        = trim((string)($usuario_db['nivel_formacion'] ?? ''));
              $carrera_db   = trim((string)($usuario_db['carrera'] ?? ''));
              $nivel_detectado = '';
              if ($nf_db !== '' && !in_list_ci($nf_db, $niveles)) {
                if (in_list_ci($nf_db, $tecnologos))              $nivel_detectado = 'Tecnólogo';
                elseif (in_list_ci($nf_db, $tecnicos))            $nivel_detectado = 'Técnico';
                elseif (in_list_ci($nf_db, $operarios))           $nivel_detectado = 'Operario';
                elseif (in_list_ci($nf_db, $auxiliares))          $nivel_detectado = 'Auxiliar';
                elseif (in_grouped_list_ci($nf_db, $prof_grps))   $nivel_detectado = 'Profesional';
                elseif (in_list_ci($nf_db, $post_espec))          $nivel_detectado = 'Especialización';
                elseif (in_list_ci($nf_db, $post_maes))           $nivel_detectado = 'Maestría';
                elseif (in_list_ci($nf_db, $post_doc))            $nivel_detectado = 'Doctorado';
              }
              if ($nivel_detectado !== '') {
                if ($carrera_db === '') $carrera_db = $nf_db;
                $usuarioN['nivel_formacion'] = $nivel_detectado;
                $usuario_db['nivel_formacion'] = $nivel_detectado;
                $usuarioN['carrera'] = $carrera_db;
                $usuario_db['carrera'] = $carrera_db;
              }
              if (norm_ci($usuarioN['nivel_formacion'] ?? '') === norm_ci('Sin título') && ($usuarioN['carrera'] ?? '') === '') {
                $usuarioN['carrera'] = 'No aplica';
                $usuario_db['carrera'] = 'No aplica';
              }
              function professional_option_exists_multi(array $groups, array $espec, array $maes, array $doc, string $needle): bool
              {
                $n = norm_ci($needle);
                foreach ($groups as $items) foreach ($items as $opt) if (norm_ci($opt) === $n) return true;
                foreach ([$espec, $maes, $doc] as $arr) foreach ($arr as $opt) if (norm_ci($opt) === $n) return true;
                return $needle !== '' && norm_ci($needle) === norm_ci('Otro');
              }
              $carrera_db = $usuarioN['carrera'] ?? '';
              $hasProfMatch = professional_option_exists_multi($prof_grps, $post_espec, $post_maes, $post_doc, $carrera_db) || norm_ci($carrera_db) === norm_ci('Otro');
              ?>

              <!-- Selects de carrera por nivel -->
              <select id="carrera_tecnologo" name="carrera_tecnologo" class="form-select mt-1" style="display:none;" autocomplete="off" data-value="<?= h($carrera_db) ?>">
                <?php $hasTecnoMatch = option_exists($tecnologos, $carrera_db); ?>
                <option value="" disabled <?= $hasTecnoMatch ? '' : 'selected' ?>>-- Elige tu Tecnólogo --</option>
                <?php foreach ($tecnologos as $c): ?><option value="<?= h($c) ?>" <?= sel($carrera_db, $c) ?>><?= h($c) ?></option><?php endforeach; ?>
                <?php if (!$hasTecnoMatch && trim($carrera_db) !== ''): ?><option value="<?= h($carrera_db) ?>" selected><?= h($carrera_db) ?></option><?php endif; ?>
              </select>

              <select id="carrera_tecnico" name="carrera_tecnico" class="form-select mt-1" style="display:none;" autocomplete="off" data-value="<?= h($carrera_db) ?>">
                <?php $hasTecMatch = option_exists($tecnicos, $carrera_db); ?>
                <option value="" disabled <?= $hasTecMatch ? '' : 'selected' ?>>-- Elige tu Técnico --</option>
                <?php foreach ($tecnicos as $c): ?><option value="<?= h($c) ?>" <?= sel($carrera_db, $c) ?>><?= h($c) ?></option><?php endforeach; ?>
                <?php if (in_array(norm_ci($nf_db), [norm_ci('Técnico'), norm_ci('Técnico laboral')], true) && !$hasTecMatch && trim($carrera_db) !== ''): ?><option value="<?= h($carrera_db) ?>" selected><?= h($carrera_db) ?></option><?php endif; ?>
              </select>

              <select id="carrera_operario" name="carrera_operario" class="form-select mt-1" style="display:none;" autocomplete="off" data-value="<?= h($carrera_db) ?>">
                <?php $hasOperMatch = option_exists($operarios, $carrera_db); ?>
                <option value="" disabled <?= $hasOperMatch ? '' : 'selected' ?>>-- Elige tu Operario --</option>
                <?php foreach ($operarios as $c): ?><option value="<?= h($c) ?>" <?= sel($carrera_db, $c) ?>><?= h($c) ?></option><?php endforeach; ?>
                <?php if (norm_ci($nf_db) === norm_ci('Operario') && !$hasOperMatch && trim($carrera_db) !== ''): ?><option value="<?= h($carrera_db) ?>" selected><?= h($carrera_db) ?></option><?php endif; ?>
              </select>

              <select id="carrera_auxiliar" name="carrera_auxiliar" class="form-select mt-1" style="display:none;" autocomplete="off" data-value="<?= h($carrera_db) ?>">
                <?php $hasAuxMatch = option_exists($auxiliares, $carrera_db); ?>
                <option value="" disabled <?= $hasAuxMatch ? '' : 'selected' ?>>-- Elige tu Auxiliar --</option>
                <?php foreach ($auxiliares as $c): ?><option value="<?= h($c) ?>" <?= sel($carrera_db, $c) ?>><?= h($c) ?></option><?php endforeach; ?>
                <?php if (norm_ci($nf_db) === norm_ci('Auxiliar') && !$hasAuxMatch && trim($carrera_db) !== ''): ?><option value="<?= h($carrera_db) ?>" selected><?= h($carrera_db) ?></option><?php endif; ?>
              </select>

              <?php function professional_option_exists(array $groups, string $needle): bool
              {
                foreach ($groups as $items) {
                  foreach ($items as $opt) {
                    if (norm_ci($opt) === norm_ci($needle)) return true;
                  }
                }
                return false;
              } ?>

              <select id="carrera_profesional" name="carrera_profesional" class="form-select mt-1" style="display:none;" autocomplete="off" data-value="<?= h($carrera_db) ?>">
                <option value="" disabled <?= $hasProfMatch ? '' : 'selected' ?>>-- Selecciona tu carrera / posgrado --</option>
                <?php foreach ($prof_grps as $label => $items): ?>
                  <optgroup label="<?= h($label) ?>">
                    <?php foreach ($items as $c): ?><option value="<?= h($c) ?>" <?= sel($carrera_db, $c) ?>><?= h($c) ?></option><?php endforeach; ?>
                  </optgroup>
                <?php endforeach; ?>
                <optgroup label="Posgrado — Especializaciones"><?php foreach ($post_espec as $c): ?><option value="<?= h($c) ?>" <?= sel($carrera_db, $c) ?>><?= h($c) ?></option><?php endforeach; ?></optgroup>
                <optgroup label="Posgrado — Maestrías"><?php foreach ($post_maes as $c): ?><option value="<?= h($c) ?>" <?= sel($carrera_db, $c) ?>><?= h($c) ?></option><?php endforeach; ?></optgroup>
                <optgroup label="Posgrado — Doctorados"><?php foreach ($post_doc as $c): ?><option value="<?= h($c) ?>" <?= sel($carrera_db, $c) ?>><?= h($c) ?></option><?php endforeach; ?></optgroup>
                <option value="Otro" <?= sel($carrera_db, 'Otro') ?>>Otro</option>
                <?php if (in_array(norm_ci($nf_db), [norm_ci('Profesional'), norm_ci('Profesional con Posgrado'), norm_ci('Especialización'), norm_ci('Maestría'), norm_ci('Doctorado')], true) && !$hasProfMatch && trim($carrera_db) !== ''): ?>
                  <option value="<?= h($carrera_db) ?>" selected><?= h($carrera_db) ?></option>
                <?php endif; ?>
              </select>

              <input type="hidden" id="carrera_hidden" name="carrera" value="<?= h($carrera_db) ?>">
              <div class="small-muted mt-1">Si tu nivel es “Sin título”, no es obligatorio seleccionar programa.</div>
            </div>

            <div class="col-md-12">
              <label for="ficha" class="form-label">Ficha (si eres aprendiz/egresado SENA) <span class="req">*</span></label>
              <input type="text" id="ficha" name="ficha" class="form-control" required value="<?= V('ficha'); ?>">
              <div class="invalid-feedback">Indica tu ficha o escribe “no aplica”.</div>
            </div>
          </div>
        </section>

        <!-- ===== FASE 6 ===== -->
        <section class="phase" data-step="6" aria-labelledby="ph6">
          <h2 id="ph6" class="h5 mb-2">6) Información Complementaria</h2>
          <p class="hint mb-3">Situación del negocio y programas especiales.</p>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Eres un emprendedor que tiene… <span class="req">*</span></label>
              <select id="situacion_negocio" name="situacion_negocio" class="form-select" required>
                <option value="" disabled <?= ($usuarioN['situacion_negocio'] === '') ? 'selected' : '' ?>>-- Selecciona --</option>
                <?php foreach ($sitOps as $val => $lbl): ?><option value="<?= h($val) ?>" <?= eq_ci($usuarioN['situacion_negocio'], $val) ? 'selected' : '' ?>><?= h($lbl) ?></option><?php endforeach; ?>
                <?php if (!$matchSit && trim($sitDB) !== ''): ?><option value="<?= h($sitDB) ?>" selected><?= h($sitDB) ?></option><?php endif; ?>
              </select>
              <input type="text" id="negocio_otro" name="situacion_negocio_otro" placeholder="Especifique cuál" class="form-control mt-2" style="<?= (eq_ci($usuarioN['situacion_negocio'], 'Otro')) ? '' : 'display:none;' ?>" value="<?= h($usuario_db['situacion_negocio_otro'] ?? '') ?>" pattern="[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]{3,60}" title="Solo letras (3–60)">
            </div>

            <div class="col-md-6">
              <label class="form-label">¿Pertenece a programas especiales? <span class="req">*</span></label>
              <select id="programa" name="programa" class="form-select" required>
                <option value="" disabled <?= ($usuarioN['programa'] === '') ? 'selected' : '' ?>>-- Selecciona --</option>
                <?php foreach ($progOps as $p): ?><option value="<?= h($p) ?>" <?= eq_ci($usuarioN['programa'], $p) ? 'selected' : '' ?>><?= h($p) ?></option><?php endforeach; ?>
                <?php if (!$matchProg && trim($progDB) !== ''): ?><option value="<?= h($progDB) ?>" selected><?= h($progDB) ?></option><?php endif; ?>
              </select>
              <div class="invalid-feedback">Selecciona una opción.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">¿Ejerce la actividad del proyecto? <span class="req">*</span></label>
              <select id="ejercer_actividad_proyecto" name="ejercer_actividad_proyecto" class="form-select" required>
                <option value="" disabled <?= ($usuarioN['ejercer_actividad_proyecto'] === '') ? 'selected' : '' ?>>-- Selecciona --</option>
                <option value="SI" <?= ($usuarioN['ejercer_actividad_proyecto'] === 'SI') ? 'selected' : '' ?>>Sí</option>
                <option value="NO" <?= ($usuarioN['ejercer_actividad_proyecto'] === 'NO') ? 'selected' : '' ?>>No</option>
              </select>
              <?php if ($usuarioN['ejercer_actividad_proyecto'] === '' && trim($ejercer_raw) !== ''): ?><div class="small-muted mt-1">De BD: <?= h($ejercer_raw) ?></div><?php endif; ?>
            </div>

            <div class="col-md-6">
              <label for="empresa_formalizada" class="form-label">¿Tienes empresa formalizada con Cámara de Comercio? <span class="req">*</span></label>
              <select id="empresa_formalizada" name="empresa_formalizada" class="form-select" required>
                <option value="" disabled <?= ($usuarioN['empresa_formalizada'] === '') ? 'selected' : '' ?>>-- Selecciona --</option>
                <option value="SI" <?= ($usuarioN['empresa_formalizada'] === 'SI') ? 'selected' : '' ?>>Sí</option>
                <option value="NO" <?= ($usuarioN['empresa_formalizada'] === 'NO') ? 'selected' : '' ?>>No</option>
              </select>
              <?php if ($usuarioN['empresa_formalizada'] === '' && trim($formal_raw) !== ''): ?><div class="small-muted mt-1">De BD: <?= h($formal_raw) ?></div><?php endif; ?>
              <div class="invalid-feedback">Selecciona una opción.</div>
            </div>
          </div>
        </section>

        <!-- ===== FASE 7 ===== -->
        <section class="phase" data-step="7" aria-labelledby="ph7">
          <h2 id="ph7" class="h5 mb-2">7) Centro y Orientador</h2>
          <p class="hint mb-3">Datos del CDE que te orientó.</p>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="centro_orientacion" class="form-label">Centro de Desarrollo Empresarial <span class="req">*</span></label>
              <?php $co = $usuarioN['centro_orientacion'];
              $co_raw = trim((string)$usuario_db['centro_orientacion']);
              $huboMatchCentro = false; ?>
              <select id="centro_orientacion" name="centro_orientacion" class="form-select" required>
                <option value="" disabled <?= $co === '' && $co_raw === '' ? 'selected' : '' ?>>-- Selecciona un centro --</option>
                <?php foreach ($centros_map as $k => $v): $sel = ($co === $k) ? 'selected' : '';
                  if ($sel) $huboMatchCentro = true; ?>
                  <option value="<?= h($k) ?>" <?= $sel ?>><?= h($v) ?></option>
                <?php endforeach; ?>
                <?php if (!$huboMatchCentro && $co_raw !== ''): ?>
                  <option value="<?= h($co_raw) ?>" selected><?= h($co_raw) ?></option>
                <?php endif; ?>
              </select>
              <div class="invalid-feedback">Selecciona el centro.</div>
            </div>
            <div class="col-md-6">
              <label for="orientador" class="form-label">Orientador/a <span class="req">*</span></label>
              <select id="orientador" name="orientador" class="form-select" required>
                <option value="" disabled>-- Selecciona primero un centro --</option>
              </select>
              <div class="invalid-feedback">Selecciona el orientador.</div>
            </div>
            <div class="col-12">
              <div class="divider"></div>
              <div class="d-flex align-items-center gap-2"><span class="small-muted">Revisa que la información esté correcta antes de enviar.</span></div>
            </div>
          </div>
        </section>

        <div class="form-check" style="margin-top:8px; display:none;">
          <input class="form-check-input" type="checkbox" id="chkDuplicar">
          <label class="form-check-label" for="chkDuplicar">Crear una <strong>COPIA</strong> con el nuevo orientador (no modificar el registro original).</label>
        </div>

        <div class="footer">
          <button type="button" class="btn btn-out" id="btnPrev"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;">
              <path d="M12 19l-7-7 7-7"></path>
              <path d="M19 12H5"></path>
            </svg>Atrás</button>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-out" id="btnSaveDraft" title="Guardar borrador (local)">Guardar borrador</button>
            <button type="button" class="btn btn-sena" id="btnNext">Siguiente</button>
            <button type="submit" class="btn btn-sena" id="btnSubmit" style="display:none;">Guardar cambios</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</body>

</html>