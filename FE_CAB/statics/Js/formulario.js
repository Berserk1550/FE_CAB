function normalizeUTF8(str="") {
  try {
    return decodeURIComponent(escape(str));
  } catch(e) { return str; }
}


let faseActual = 0;
const fases = document.querySelectorAll(".fase");

function toggleMenu() {
  const menu = document.getElementById("navMenu");
  menu.classList.toggle("show");
}

function mostrarFase(index) {
  fases.forEach((fase, i) => {
    fase.style.display = i === index ? "block" : "none";
  });
  actualizarBarra(); // Actualiza la barra de progreso al mostrar una fase
}

// Validación de la fase actual, incluyendo restricciones personalizadas
function validarFaseActual() {
  const fase = fases[faseActual];
  let valid = true;
  let primerNoValido = null;

  const campos = fase.querySelectorAll(
    "input[required], select[required], textarea[required]"
  );
  campos.forEach((campo) => {
    // Campo 20: ficha
    if (campo.id === "ficha") {
      let valor = campo.value.trim();
      if (
        valor !== "" &&
        !/^[0-9]+$/.test(valor) &&
        valor.toLowerCase() !== "no aplica"
      ) {
        valid = false;
        campo.classList.add("campo-error");
        campo.setCustomValidity(
          "Solo se permite ingresar números o 'no aplica'."
        );
        if (!primerNoValido) primerNoValido = campo;
      } else {
        campo.setCustomValidity("");
        campo.classList.remove("campo-error");
      }
    }
    // Campo 21: programa_formacion
    else if (campo.id === "programa_formacion") {
      let valor = campo.value.trim();
      if (
        valor !== "" &&
        !/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+$/.test(valor) &&
        valor.toLowerCase() !== "no aplica"
      ) {
        valid = false;
        campo.classList.add("campo-error");
        campo.setCustomValidity("Solo se permite texto o 'no aplica'.");
        if (!primerNoValido) primerNoValido = campo;
      } else {
        campo.setCustomValidity("");
        campo.classList.remove("campo-error");
      }
    }
    // Radios: al menos uno seleccionado del grupo
    else if (campo.type === "radio") {
      const radios = fase.querySelectorAll(`input[name="${campo.name}"]`);
      const algunoMarcado = Array.from(radios).some((r) => r.checked);
      if (!algunoMarcado) {
        valid = false;
        radios.forEach((r) => r.classList.add("campo-error"));
        if (!primerNoValido) primerNoValido = radios[0];
      } else {
        radios.forEach((r) => r.classList.remove("campo-error"));
      }
    }
    // Teléfono celular
    else if (campo.type === "tel" && campo.id === "celular") {
      const soloNumeros = campo.value.replace(/\D/g, "");
      if (soloNumeros.length !== 10) {
        valid = false;
        campo.classList.add("campo-error");
        campo.setCustomValidity(
          "El celular debe tener exactamente 10 dígitos numéricos."
        );
        if (!primerNoValido) primerNoValido = campo;
      } else {
        campo.setCustomValidity("");
        campo.classList.remove("campo-error");
      }
    }
    // Otros campos
    else {
      if (!campo.checkValidity()) {
        valid = false;
        campo.classList.add("campo-error");
        if (!primerNoValido) primerNoValido = campo;
      } else {
        campo.classList.remove("campo-error");
      }
    }
  });

  if (!valid && primerNoValido) {
    primerNoValido.scrollIntoView({ behavior: "smooth", block: "center" });
    alert(
      primerNoValido.validationMessage ||
        "Por favor, completa correctamente todos los campos."
    );
  }

  // Validamos el campo #17: ficha
  const ficha = fase.querySelector("#ficha");
  if (ficha && !ficha.value.trim()) {
    ficha.classList.add("campo-error"); // lo pinta de rojo
    ficha.scrollIntoView({ behavior: "smooth" }); // lo lleva arriba
    ficha.reportValidity(); // muestra el tooltip del navegador
    return false; // bloquea el avance
  }
  return valid;
}

// Botones multipaso
function crearBotones() {
  fases.forEach((fase, i) => {
    const contenedor = document.createElement("div");
    contenedor.className = "navegacion-botones";

    if (i > 0) {
      const btnAtras = document.createElement("button");
      btnAtras.type = "button";
      btnAtras.className = "btn-atras";
      btnAtras.innerHTML = `<svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M15.2335 5.21967C15.5263 5.51256 15.5263 5.98744 15.2335 6.28033L9.51379 12L15.2335 17.7197C15.5263 18.0126 15.5263 18.4874 15.2335 18.7803C14.9406 19.0732 14.4657 19.0732 14.1728 18.7803L7.92279 12.5303C7.6299 12.2374 7.6299 11.7626 7.92279 11.4697L14.1728 5.21967C14.4657 4.92678 14.9406 4.92678 15.2335 5.21967Z" fill="#ffffffff"/>
</svg> `;
      // btnAtras.textContent = "Atrás";
      btnAtras.onclick = () => {
        faseActual--;
        mostrarFase(faseActual);
      };
      contenedor.appendChild(btnAtras);
    }

    if (i < fases.length - 1) {
      const btnSiguiente = document.createElement("button");
      btnSiguiente.type = "button";
      btnSiguiente.className = "btn-verde";
      btnSiguiente.textContent = "Siguiente";
      btnSiguiente.onclick = () => {
        if (validarFaseActual()) {
          faseActual++;
          mostrarFase(faseActual);
          actualizarBarra(); // Actualiza la barra de progreso al cambiar de fase
        }
      };
      contenedor.appendChild(btnSiguiente);
    }

    fase.appendChild(contenedor);
  });
}

crearBotones();
mostrarFase(faseActual);

document.querySelectorAll("input, select, textarea").forEach((campo) => {
  campo.addEventListener("blur", () => {
    campo.classList.add("tocado");
  });
  campo.addEventListener("change", () => {
    campo.classList.add("tocado");
  });
});

//Función genérica para mostrar el campo "otro" asociado
function setupCampoOtro(selectId, inputId) {
  const select = document.getElementById(selectId);
  const input = document.getElementById(inputId);

  if (select && input) {
    const toggle = () => {
      const show = select.value === "Otro";
      input.style.display = show ? "block" : "none";
      input.required = show; // ← obligatorio cuando es “Otro”
      if (!show) input.value = ""; // limpiar si se oculta
    };
    select.addEventListener("change", toggle);
    toggle(); // estado inicial
  }
}

// Reglas por tipo de documento (ajústalas si tus rangos reales difieren)
const REGLAS_ID = {
  TI: { min: 6, max: 10, soloNumeros: true, etiqueta: "Tarjeta de Identidad" },
  CC: { min: 6, max: 10, soloNumeros: true, etiqueta: "Cédula de Ciudadanía" }, // amplié max a 12 por casos largos
  CE: {
    min: 6,
    max: 15,
    soloNumeros: true,
    etiqueta: "Cédula de Extranjería",
  },
  PEP: {
    min: 6,
    max: 15,
    soloNumeros: false,
    etiqueta: "Permiso Especial de Permanencia",
  },
  PPT: {
    min: 6,
    max: 15,
    soloNumeros: false,
    etiqueta: "Permiso por Protección Temporal",
  },
  PAS: { min: 6, max: 15, soloNumeros: false, etiqueta: "Pasaporte" },
};

function actualizarReglasNumeroId() {
  const tipo = document.getElementById("tipo_id");
  const input = document.getElementById("numero_id");
  const hint = document.getElementById("numero_id_hint");
  if (!tipo || !input) return;

  const regla = REGLAS_ID[tipo.value];

  if (!regla) {
    input.removeAttribute("maxlength");
    input.removeAttribute("minlength");
    input.removeAttribute("pattern");
    input.placeholder = "";
    if (hint) hint.textContent = "";
    return;
  }

  input.maxLength = regla.max;
  input.minLength = regla.min;

  if (regla.soloNumeros) {
    input.setAttribute("pattern", `\\d{${regla.min},${regla.max}}`);
    input.setAttribute("inputmode", "numeric");
    input.placeholder = `Solo números (${regla.min}-${regla.max} dígitos)`;
  } else {
    input.setAttribute("pattern", `[A-Za-z0-9]{${regla.min},${regla.max}}`);
    input.setAttribute("inputmode", "text");
    input.placeholder = `Letras y/o números (${regla.min}-${regla.max} caracteres)`;
  }

  if (hint) {
    const tipoTxt = regla.etiqueta || tipo.value;
    hint.textContent = `${tipoTxt}: ${regla.min}-${regla.max} ${
      regla.soloNumeros ? "dígitos (solo números)" : "caracteres alfanuméricos"
    }.`;
  }

  input.oninvalid = () => {
    input.setCustomValidity(
      regla.soloNumeros
        ? `Ingresa de ${regla.min} a ${regla.max} dígitos numéricos.`
        : `Ingresa de ${regla.min} a ${regla.max} caracteres alfanuméricos (sin espacios).`
    );
  };
  input.oninput = () => input.setCustomValidity("");
}

function filtroNumeroIdEnVivo() {
  const tipo = document.getElementById("tipo_id");
  const input = document.getElementById("numero_id");
  if (!tipo || !input) return;

  const regla = REGLAS_ID[tipo.value];
  if (!regla) return;

  if (regla.soloNumeros) {
    const limpio = input.value.replace(/\D+/g, "");
    if (limpio !== input.value) input.value = limpio;
  } else {
    // Quita todo lo que no sea letra o número
    const limpio = input.value.replace(/[^A-Za-z0-9]+/g, "");
    if (limpio !== input.value) input.value = limpio;
  }
}

// departamento
document.addEventListener("DOMContentLoaded", () => {
  const depSel = document.getElementById("departamento");
  const depOtro = document.getElementById("dpto_otro");
  if (!depSel) return;

  // si no hay valor (o el navegador no restauró), fuerza Valle del Cauca
  if (!depSel.value) depSel.value = "Valle del Cauca";

  // sincroniza campo “Otro”
  if (depSel.value === "Otro") {
    depOtro.style.display = "block";
    depOtro.required = true;
  } else {
    if (depOtro) {
      depOtro.style.display = "none";
      depOtro.required = false;
      if (depSel.value !== "Otro") depOtro.value = "";
    }
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const tipo = document.getElementById("tipo_id");
  const input = document.getElementById("numero_id");
  if (tipo) {
    tipo.addEventListener("change", actualizarReglasNumeroId);
    actualizarReglasNumeroId();
  }
  if (input) input.addEventListener("input", filtroNumeroIdEnVivo);
});

// Configurar todos los campos que usan "Otro"
setupCampoOtro("departamento", "dpto_otro");
setupCampoOtro("programa", "programa_otro");
setupCampoOtro("situacion_negocio", "negocio_otro");

// Restricción de fecha para los campos de nacimiento, expedición y orientación
document.addEventListener("DOMContentLoaded", function () {
  const hoy = new Date();
  const minFecha = "1900-01-01";
  const fecha18 = new Date(
    hoy.getFullYear() - 14,
    hoy.getMonth(),
    hoy.getDate()
  )
    .toISOString()
    .split("T")[0];

  const campoNacimiento = document.getElementById("fecha_nacimiento");
  const campoOrientacion = document.getElementById("fecha_orientacion");

  if (campoNacimiento) {
    campoNacimiento.setAttribute("max", fecha18);
    campoNacimiento.setAttribute("min", minFecha);
    campoNacimiento.addEventListener("input", () => {
      const seleccionada = campoNacimiento.value;
      if (seleccionada > fecha18) {
        campoNacimiento.setCustomValidity("Debes tener al menos 16 años.");
      } else {
        campoNacimiento.setCustomValidity("");
      }
    });
  }

  if (campoOrientacion) {
    const maxOrientacion = hoy.toISOString().split("T")[0];
    campoOrientacion.setAttribute("max", maxOrientacion);
    campoOrientacion.setAttribute("min", "2010-01-01");
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const d = new Date();

  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const dd = String(d.getDate()).padStart(2, "0");
  const hh = String(d.getHours()).padStart(2, "0");
  const mi = String(d.getMinutes()).padStart(2, "0");
  const ss = String(d.getSeconds()).padStart(2, "0");

  const soloFecha = `${yyyy}-${mm}-${dd}`;
  const fechaHoraInicio = `${yyyy}-${mm}-${dd} ${hh}:${mi}:${ss}`;

  // visible y no editable
  const display = document.getElementById("fecha_orientacion_display");
  if (display) display.value = soloFecha;

  // ocultos que se envían
  const hiddenFecha = document.getElementById("fecha_orientacion");
  if (hiddenFecha) hiddenFecha.value = soloFecha;

  const hiddenTs = document.getElementById("ts_inicio");
  if (hiddenTs) hiddenTs.value = fechaHoraInicio;
});

const form = document.querySelector("#MIformulario"); // <form id="formEmprendedores">
const nivel_formacion = document.querySelector("#nivel_formacion");

// Mapa: nivel -> id del select de carrera
const mapaCarreras = {
  Tecnólogo: "#carrera_tecnologo",
  Técnico: "#carrera_tecnico",
  Operario: "#carrera_operario",
  Auxiliar: "#carrera_auxiliar",
  Profesional: "#carrera_profesional",
  Especialización: "#posgrado_especializacion",
  Maestría: "#posgrado_maestria",
  Doctorado: "#posgrado_doctorado",
};

const todosCarrera = Object.values(mapaCarreras)
  .map((sel) => document.querySelector(sel))
  .filter(Boolean);

function resetCarreras() {
  todosCarrera.forEach((s) => {
    s.style.display = "none"; // oculto
    s.required = false; // que no sea obligatorio si está oculto
    s.disabled = true; // no se envía al backend
    s.value = ""; // reset
  });
}

function syncCarreraConNivel() {
  resetCarreras();
  const val = nivel_formacion.value;
  if (mapaCarreras[val]) {
    const s = document.querySelector(mapaCarreras[val]);
    s.style.display = ""; // mostrar (block/inline según tu CSS)
    s.disabled = false; // habilitar envío
    s.required = true; // obligatorio
  }
  // Si es "Sin título" o vacío: no se muestra ninguna carrera (no requerida)
}

// Cambios en el nivel
nivel_formacion.addEventListener("change", syncCarreraConNivel);

// Estado inicial al cargar la página
document.addEventListener("DOMContentLoaded", syncCarreraConNivel);

// Extra: validación por si acaso (usa los mensajes nativos del navegador)
form?.addEventListener("submit", (e) => {
  // El atributo "required" en #nivel_formacion ya fuerza la elección
  const sel = mapaCarreras[nivel.value]
    ? document.querySelector(mapaCarreras[nivel.value])
    : null;
  if (sel && !sel.value) {
    sel.reportValidity(); // muestra el aviso nativo en el select de carrera
    e.preventDefault();
  }

  // Enfoca el primer campo inválido para que se vea el foco rojo al intentar enviar
  // document
  //   .querySelector("#formEmprendedores")
  //   ?.addEventListener("submit", (e) => {
  //     const form = e.currentTarget;
  //     if (!form.checkValidity()) {
  //       e.preventDefault();
  //       const firstInvalid = form.querySelector(":invalid");
  //       firstInvalid?.focus(); // al enfocarlo, tomará el estilo rojo del CSS
  //     }
  //   });
});

// "Otro" para tipo_emprendedor
const selectTipoEmp = document.getElementById("tipo_emprendedor");
const inputTipoEmpOtro = document.getElementById("tipo_emprendedor_otro");

if (selectTipoEmp && inputTipoEmpOtro) {
  selectTipoEmp.addEventListener("change", function () {
    if (this.value === "Otro") {
      inputTipoEmpOtro.style.display = "block";
      inputTipoEmpOtro.setAttribute("required", "required");
    } else {
      inputTipoEmpOtro.style.display = "none";
      inputTipoEmpOtro.removeAttribute("required");
      inputTipoEmpOtro.value = "";
    }
  });
}

// Restricción dinámica para campo ficha (solo números o 'no aplica')
const inputFicha = document.getElementById("ficha");
if (inputFicha) {
  inputFicha.addEventListener("input", function () {
    let valor = inputFicha.value.trim();
    if (
      valor === "" ||
      /^[0-9]+$/.test(valor) ||
      valor.toLowerCase() === "no aplica"
    ) {
      inputFicha.setCustomValidity("");
      inputFicha.classList.remove("campo-error");
    } else {
      inputFicha.setCustomValidity(
        "Solo se permite ingresar números o 'no aplica'."
      );
      inputFicha.classList.add("campo-error");
    }
  });
}

// Restricción dinámica para campo programa_formacion (solo texto o 'no aplica')
const inputPrograma = document.getElementById("programa_formacion");
if (inputPrograma) {
  inputPrograma.addEventListener("input", function () {
    let valor = inputPrograma.value.trim();
    if (
      valor === "" ||
      /^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+$/.test(valor) ||
      valor.toLowerCase() === "no aplica"
    ) {
      inputPrograma.setCustomValidity("");
      inputPrograma.classList.remove("campo-error");
    } else {
      inputPrograma.setCustomValidity("Solo se permite texto o 'no aplica'.");
      inputPrograma.classList.add("campo-error");
    }
  });
}

function ahoraTimestamp() {
  const d = new Date();
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const dd = String(d.getDate()).padStart(2, "0");
  const hh = String(d.getHours()).padStart(2, "0");
  const mi = String(d.getMinutes()).padStart(2, "0");
  const ss = String(d.getSeconds()).padStart(2, "0");
  return `${yyyy}-${mm}-${dd} ${hh}:${mi}:${ss}`;
}

document.addEventListener("DOMContentLoaded", () => {
  syncOrientadorId();

  const d = new Date();

  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const dd = String(d.getDate()).padStart(2, "0");
  const hh = String(d.getHours()).padStart(2, "0");
  const mi = String(d.getMinutes()).padStart(2, "0");
  const ss = String(d.getSeconds()).padStart(2, "0");

  const soloFecha = `${yyyy}-${mm}-${dd}`;
  const tsInicio = `${yyyy}-${mm}-${dd} ${hh}:${mi}:${ss}`;

  const display = document.getElementById("fecha_orientacion_display");
  if (display) display.value = soloFecha;

  const hiddenFecha = document.getElementById("fecha_orientacion");
  if (hiddenFecha) hiddenFecha.value = soloFecha;

  const hiddenTs = document.getElementById("ts_inicio");
  if (hiddenTs) hiddenTs.value = tsInicio;
});

// ==== Tipo de emprendedor: mostrar input y enviar su valor ====
const selTipo = document.getElementById("tipo_emprendedor");
const inpOtro = document.getElementById("tipo_emprendedor_otro");
const formMain = document.getElementById("MIformulario");

function toggleTipoEmprendedor() {
  if (selTipo.value === "Otro") {
    inpOtro.style.display = "block";
    inpOtro.required = true;
    inpOtro.focus();
  } else {
    inpOtro.required = false;
    inpOtro.value = "";
    inpOtro.style.display = "none";
  }
}

selTipo.addEventListener("change", toggleTipoEmprendedor);

function enforceOtro(selectId, inputId) {
  const sel = document.getElementById(selectId);
  const inp = document.getElementById(inputId);
  if (!sel || !inp) return;

  if (sel.value === "Otro") {
      if (inp.value.trim().length < 3) {
          alert("Debes especificar correctamente la opción 'Otro'.");
          inp.focus();
          throw new Error("Valor 'Otro' inválido");
      }
      sel.value = normalizeUTF8(inp.value.trim());
  }
}

// Antes de enviar, si está “Otro”, reemplazamos la opción seleccionada
formMain.addEventListener("submit", function (e) {
  if (selTipo.value === "Otro") {
    const otroVal = (inpOtro.value || "").trim();
    if (otroVal.length < 3) {
      e.preventDefault();
      inpOtro.focus();
      return;
    }
    // Cambiamos value y label de la opción seleccionada
    const opt = selTipo.options[selTipo.selectedIndex];
    opt.value = otroVal;
    opt.text = otroVal;
  }
});

// ORIENTADORES POR CENTRO
const orientadoresPorCentro = {
  CAB: [
    "Celiced Castaño Barco",
    "Jose Julian Angulo Hernandez",
    "Lina Maria Varela",
    "Harby Arce",
    "Carlos Andrés Matallana",
    "Albeth Martinez Valencia",
  ],
  CBI: [
    "Hector James Serrano Ramírez",
    "Javier Duvan Cano León",
    "Sandra Patricia Reinel Piedrahita",
    "Julian Adolfo Manzano Gutierrez",
  ],
  CDTI: [
    "Diana Lorena Bedoya Vásquez",
    "Jacqueline Mafla Vargas",
    "Juan Manuel Oyola",
    "Gloria Betancourth",
  ],
  CEAI: [
    "Carolina Gálvez Noreña",
    "Cerbulo Andres Cifuentes Garcia",
    "Clara Ines Campo chaparro",
  ],
  CGTS: [
    "Francia Velasquez",
    "Julio Andres Pabon Arboleda",
    "Andres Felipe Betancourt Hernandez",
  ],
  ASTIN: [
    "Pablo Andres Cardona Echeverri",
    "Juan Carlos Bernal Bernal",
    "Pablo Diaz",
    "Marlen Erazo",
  ],
  CTA: [
    "Angela Rendon Marin",
    "Juan Manuel Marmolejo Escobar",
    "Liliana Fernandez Angulo",
    "Luz Adriana Loaiza",
  ],
  CLEM: [
    "Adalgisa Palacio Santa",
    "Eiider Cardona",
    "Manuela Jimenez",
    "William Bedoya Gomez",
  ],
  CNP: [
    "LEIDDY DIANA MOLANO CAICEDO",
    "PEDRO ANDRÉS ARCE MONTAÑO",
    "DIANA MORENO FERRÍN",
  ],
  CC: [
    "Franklin Ivan Marin Gomez",
    "Jorge Iván Valencia Vanegas",
    "Deider Arboleda Riascos",
  ],
};

// ID por nombre y centro (completa los que te falten)
const orientadorIdPorCentro = {
  CAB: {
    "Celiced Castaño Barco": 1,
    "Jose Julian Angulo Hernandez": 2,
    "Lina Maria Varela": 3,
    "Harby Arce": 4,
    "Carlos Andrés Matallana": 5, // <-- pon su id real cuando lo tengas
    "Albeth Martinez Valencia": 6,
  },
  CBI: {},
  CDTI: {},
  CEAI: {},
  CGTS: {},
  ASTIN: {},
  CTA: {},
  CLEM: {},
  CNP: {},
  CC: {},
};

function actualizarOrientadores() {
  const centroSeleccionado =
    document.getElementById("centro_orientacion").value;
  const selectOrientador = document.getElementById("orientador");

  selectOrientador.innerHTML =
    '<option value="">-- Selecciona un orientador --</option>';

  if (orientadoresPorCentro[centroSeleccionado]) {
    orientadoresPorCentro[centroSeleccionado].forEach((nombre) => {
      const option = document.createElement("option");
      option.value = nombre; // sigues enviando nombre
      option.textContent = nombre;
      selectOrientador.appendChild(option);
    });
  }

  // tras repoblar opciones, resetea y sincroniza
  selectOrientador.selectedIndex = 0;
  syncOrientadorId();
}

document
  .getElementById("orientador")
  ?.addEventListener("change", syncOrientadorId);

function getOrientadorId(centro, nombre) {
  const mapaCentro = orientadorIdPorCentro[centro] || {};
  return mapaCentro[nombre] || 0; // 0 si no está mapeado
}

function syncOrientadorId() {
  const centro = document.getElementById("centro_orientacion")?.value || "";
  const sel = document.getElementById("orientador");
  const hid = document.getElementById("orientador_id");
  if (!sel || !hid) return;

  const nombre = sel.value || ""; // tu select usa value = nombre
  let id = getOrientadorId(centro, nombre);

  // Si el select está deshabilitado por QR, usa el id del QR si es válido
  const pre = window.PREFILL || {};
  if ((!id || id === 0) && pre.ok && +pre.oid > 0) {
    id = +pre.oid;
  }

  hid.value = id > 0 ? String(id) : ""; // vacío si no hay id
}

// Mapa simple de nacionalidades
const paisNacionalidad = {
  Afganistán: "Afgano/a",
  Albania: "Albanés/a",
  Alemania: "Alemán/a",
  Andorra: "Andorrano/a",
  Angola: "Angoleño/a",
  "Antigua y Barbuda": "Antiguano/a",
  "Arabia Saudita": "Saudí/a",
  Argelia: "Argelino/a",
  Argentina: "Argentino/a",
  Armenia: "Armenio/a",
  Australia: "Australiano/a",
  Austria: "Austriaco/a",
  Azerbaiyán: "Azerí/a",
  Bahamas: "Bahamés/a",
  Bangladés: "Bangladesí/a",
  Barbados: "Barbadense/a",
  Baréin: "Bareiní/a",
  Bélgica: "Belga/a",
  Belice: "Beliceño/a",
  Benín: "Beninés/a",
  Bielorrusia: "Bielorruso/a",
  Birmania: "Birmano/a",
  Bolivia: "Boliviano/a",
  "Bosnia y Herzegovina": "Bosnio/a",
  Botsuana: "Botsuano/a",
  Brasil: "Brasileño/a",
  Brunéi: "Bruneano/a",
  Bulgaria: "Búlgaro/a",
  "Burkina Faso": "Burkinés/a",
  Burundi: "Burundés/a",
  Bután: "Butanés/a",
  "Cabo Verde": "Caboverdiano/a",
  Camboya: "Camboyano/a",
  Camerún: "Camerunés/a",
  Canadá: "Canadiense/a",
  Catar: "Catarí/a",
  Chile: "Chileno/a",
  China: "Chino/a",
  Chipre: "Chipriota/a",
  Colombia: "Colombiano/a",
  "Corea del Norte": "Norcoreano/a",
  "Corea del Sur": "Surcoreano/a",
  "Costa Rica": "Costarricense/a",
  Croacia: "Croata/a",
  Cuba: "Cubano/a",
  Dinamarca: "Danés/a",
  Ecuador: "Ecuatoriano/a",
  Egipto: "Egipcio/a",
  "El Salvador": "Salvadoreño/a",
  "Emiratos Árabes Unidos": "Emiratí/a",
  Eslovaquia: "Eslovaco/a",
  Eslovenia: "Esloveno/a",
  España: "Español/a",
  "Estados Unidos": "Estadounidense/a",
  Estonia: "Estonio/a",
  Etiopía: "Etíope/a",
  Filipinas: "Filipino/a",
  Finlandia: "Finlandés/a",
  Francia: "Francés/a",
  Gabón: "Gabonés/a",
  Gambia: "Gambiano/a",
  Georgia: "Georgiano/a",
  Ghana: "Ghanés/a",
  Grecia: "Griego/a",
  Guatemala: "Guatemalteco/a",
  Guinea: "Guineano/a",
  Guyana: "Guyanés/a",
  Haití: "Haitiano/a",
  Honduras: "Hondureño/a",
  Hungría: "Húngaro/a",
  India: "Indio/a",
  Indonesia: "Indonesio/a",
  Irak: "Iraquí/a",
  Irán: "Iraní/a",
  Irlanda: "Irlandés/a",
  Islandia: "Islandés/a",
  Israel: "Israelí/a",
  Italia: "Italiano/a",
  Jamaica: "Jamaicano/a",
  Japón: "Japonés/a",
  Jordania: "Jordano/a",
  Kazajistán: "Kazajo/a",
  Kenia: "Keniano/a",
  Kirguistán: "Kirguís/a",
  Kuwait: "Kuwaití/a",
  Laos: "Laosiano/a",
  Letonia: "Letón/a",
  Líbano: "Libanés/a",
  Liberia: "Liberiano/a",
  Libia: "Libio/a",
  Liechtenstein: "Liechtensteiniano/a",
  Lituania: "Lituano/a",
  Luxemburgo: "Luxemburgués/a",
  Madagascar: "Malgache/a",
  Malasia: "Malasio/a",
  Malawi: "Malauí/a",
  Maldivas: "Maldivo/a",
  Malta: "Maltés/a",
  Marruecos: "Marroquí/a",
  México: "Mexicano/a",
  Moldavia: "Moldavo/a",
  Mónaco: "Monegasco/a",
  Mongolia: "Mongol/a",
  Montenegro: "Montenegrino/a",
  Mozambique: "Mozambiqueño/a",
  Namibia: "Namibio/a",
  Nepal: "Nepalí/a",
  Nicaragua: "Nicaragüense/a",
  Níger: "Nigerino/a",
  Nigeria: "Nigeriano/a",
  Noruega: "Noruego/a",
  "Nueva Zelanda": "Neozelandés/a",
  Omán: "Omaní/a",
  "Países Bajos": "Neerlandés/a",
  Pakistán: "Pakistaní/a",
  Panamá: "Panameño/a",
  Paraguay: "Paraguayo/a",
  Perú: "Peruano/a",
  Polonia: "Polaco/a",
  Portugal: "Portugués/a",
  "Reino Unido": "Británico/a",
  "República Checa": "Checo/a",
  "República Dominicana": "Dominicano/a",
  Rumania: "Rumano/a",
  Rusia: "Ruso/a",
  "San Marino": "Sanmarinense/a",
  Senegal: "Senegalés/a",
  Serbia: "Serbio/a",
  Singapur: "Singapurense/a",
  Siria: "Sirio/a",
  Somalia: "Somalí/a",
  "Sri Lanka": "Ceilanés/a",
  Sudáfrica: "Sudafricano/a",
  Sudán: "Sudanés/a",
  Suecia: "Sueco/a",
  Suiza: "Suizo/a",
  Tailandia: "Tailandés/a",
  Tanzania: "Tanzano/a",
  Túnez: "Tunecino/a",
  Turquía: "Turco/a",
  Ucrania: "Ucraniano/a",
  Uganda: "Ugandés/a",
  Uruguay: "Uruguayo/a",
  Uzbekistán: "Uzbeko/a",
  Venezuela: "Venezolano/a",
  Vietnam: "Vietnamita/a",
  Yemen: "Yemení/a",
  Zambia: "Zambiano/a",
  Zimbabue: "Zimbabuense/a",
};

// Lista de países desde el mapa
const listaPaises = Object.keys(paisNacionalidad);

document.addEventListener("DOMContentLoaded", function () {
  const selectPais = document.getElementById("pais");
  const nacionalidadSpan = document.getElementById("nacionalidad");

  // Llenar el select de países
  selectPais.innerHTML =
    '<option value="" disabled selected>-- Selecciona un país --</option>';
  listaPaises.forEach((pais) => {
    const option = document.createElement("option");
    option.value = pais;
    option.textContent = pais;
    selectPais.appendChild(option);
  });

  // Al cambiar de país, mostrar la nacionalidad correspondiente
  selectPais.addEventListener("change", function () {
    const paisSeleccionado = this.value;
    const nacionalidad = paisNacionalidad[paisSeleccionado] || "";
    nacionalidadSpan.textContent = nacionalidad;

    // Crear o actualizar el campo oculto para enviar al backend
    let inputHidden = document.getElementById("nacionalidad_hidden");
    if (!inputHidden) {
      inputHidden = document.createElement("input");
      inputHidden.type = "hidden";
      inputHidden.name = "nacionalidad";
      inputHidden.id = "nacionalidad_hidden";
      selectPais.closest("form").appendChild(inputHidden);
    }
    inputHidden.value = nacionalidad;
  });

  /* --- PRESELECCIÓN POR DEFECTO --- */
  const DEFAULT_COUNTRY = "Colombia";
  if ([...selectPais.options].some((o) => o.value === DEFAULT_COUNTRY)) {
    selectPais.value = DEFAULT_COUNTRY; // selecciona Colombia
  } else {
    // (por si acaso) si no estuviera en la lista, lo agrega y selecciona
    const opt = document.createElement("option");
    opt.value = DEFAULT_COUNTRY;
    opt.textContent = DEFAULT_COUNTRY;
    selectPais.appendChild(opt);
    selectPais.value = DEFAULT_COUNTRY;
  }
  // Dispara el change para poblar el span y el hidden con "Colombiano/a"
  selectPais.dispatchEvent(new Event("change", { bubbles: true }));
});

// Envío del formulario con alert de éxito
document.querySelector("form").addEventListener("submit", function (event) {
  event.preventDefault();
  if (!validarFaseActual()) return; // Previene envío si hay error en la última fase
  syncOrientadorId(); // asegura que el hidden vaya correcto
  // Backfill final de fechas con valores del servidor (si están disponibles)
  const hiddenFecha = document.getElementById("fecha_orientacion");
  const hiddenTs = document.getElementById("ts_inicio");
  const server = window.SERVER_NOW || {};

  // yyyy-mm-dd “seguro”
  if (hiddenFecha && !hiddenFecha.value) {
    hiddenFecha.value = server.ymd || new Date().toISOString().slice(0, 10);
  }

  // yyyy-mm-dd hh:mm:ss “seguro”
  if (hiddenTs && !hiddenTs.value) {
    if (server.ts) hiddenTs.value = server.ts;
    else {
      const d = new Date();
      const pad = (n) => String(n).padStart(2, "0");
      hiddenTs.value = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(
        d.getDate()
      )} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
    }
  }


  syncOrientadorId(); // obliga el ID correcto

const obligatorioQR = ["orientador_id","centro_orientacion","fecha_orientacion"];
obligatorioQR.forEach(id=>{
  const el = document.getElementById(id);
  if (el && !el.value.trim()) {
      alert("Error: faltan datos del orientador. Recargue el enlace QR.");
      throw new Error("Bloqueado: datos QR incompletos");
  }

  enviarFormulario();
})});

const nivel = document.getElementById("nivel_formacion");
const tecno = document.getElementById("carrera_tecnologo");
const tecni = document.getElementById("carrera_tecnico");
const op = document.getElementById("carrera_operario");
const aux = document.getElementById("carrera_auxiliar");
const prof = document.getElementById("carrera_profesional");
const posEsp = document.getElementById("posgrado_especializacion");
const posMsc = document.getElementById("posgrado_maestria");
const posPhD = document.getElementById("posgrado_doctorado");

function mostrarSelect(valor) {
  // ocultar todo
  todosCarrera.forEach((s) => {
    s.style.display = "none";
    s.required = false;
    s.disabled = true;
  });

  const sel = mapaCarreras[valor]
    ? document.querySelector(mapaCarreras[valor])
    : null;
  if (sel) {
    sel.style.display = "block";
    sel.required = true;
    sel.disabled = false;
  }
}

nivel.addEventListener("change", (e) => mostrarSelect(e.target.value));

const selectPrograma = document.getElementById("programa");
const inputOtro = document.getElementById("programa_otro");

selectPrograma.addEventListener("change", function () {
  if (this.value === "Otro") {
    inputOtro.style.display = "block";
    inputOtro.setAttribute("required", "required");
  } else {
    inputOtro.style.display = "none";
    inputOtro.removeAttribute("required");
    inputOtro.value = ""; // limpia si se cambia
  }
});

function enviarFormulario() {
  // marca hora fin
  const tsFinInput = document.getElementById("ts_fin");
  if (tsFinInput) tsFinInput.value = ahoraTimestamp();

  // Normalización de campos tipo "Otro"
  enforceOtro("tipo_emprendedor","tipo_emprendedor_otro");
  enforceOtro("programa","programa_otro");

  // Normalizar UTF-8 en todos los campos
  const form = document.querySelector("form");
  [...new FormData(form).entries()].forEach(([k,v])=>{
     if (typeof v === "string") form.elements[k].value = normalizeUTF8(v.trim());
  });

  form.submit();
}


function actualizarBarra() {
  const pasos = document.querySelectorAll(".progress-steps .step");
  const barra = document.getElementById("progress-bar");

  pasos.forEach((paso, index) => {
    if (index <= faseActual) {
      paso.classList.add("active");
    } else {
      paso.classList.remove("active");
    }
  });

  const progreso = (faseActual / (pasos.length - 1)) * 100;
  barra.style.width = `${progreso}%`;
}

(function() {
      const pad = n => String(n).padStart(2, '0');
      const BOGOTA_TZ = (window.SERVER_NOW && window.SERVER_NOW.tz) || 'America/Bogota';

      function hoyBogotaYMD() {
        if (window.SERVER_NOW?.ymd) return window.SERVER_NOW.ymd;
        try {
          const fmt = new Intl.DateTimeFormat('es-CO', {
            timeZone: BOGOTA_TZ,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
          });
          const p = fmt.formatToParts(new Date());
          return `${p.find(x=>x.type==='year').value}-${p.find(x=>x.type==='month').value}-${p.find(x=>x.type==='day').value}`;
        } catch {
          const d = new Date();
          return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
        }
      }

      function ahoraBogotaTS() {
        if (window.SERVER_NOW?.ts) return window.SERVER_NOW.ts;
        const d = new Date();
        return `${hoyBogotaYMD()} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
      }

      function initFechas() {
        const fDisp = document.getElementById('fecha_orientacion_display');
        const fHidden = document.getElementById('fecha_orientacion'); // hidden Y-m-d
        const tsIni = document.getElementById('ts_inicio'); // hidden timestamp
        const ymd = hoyBogotaYMD();
        const ts = ahoraBogotaTS();
        if (fHidden && !fHidden.value) fHidden.value = ymd;
        if (fDisp) fDisp.value = ymd; // Si quieres “larga”, cámbialo por Intl largo
        if (tsIni && !tsIni.value) tsIni.value = ts;
      }

      function hookSubmit() {
        const form = document.getElementById('MIformulario') || document.querySelector('form');
        if (!form) return;
        form.addEventListener('submit', () => {
          // Asegura datos críticos ANTES de enviar
          window.syncOrientadorId?.();
          initFechas();
        }, true);
      }

      // Reglas de nacimiento coherentes (16 años)
      function initNacimiento() {
        const f = document.getElementById('fecha_nacimiento');
        if (!f) return;
        const d = new Date();
        const max = new Date(d.getFullYear() - 16, d.getMonth(), d.getDate());
        const pad = n => String(n).padStart(2, '0');
        const maxYMD = `${max.getFullYear()}-${pad(max.getMonth()+1)}-${pad(max.getDate())}`;
        f.setAttribute('min', '1900-01-01');
        f.setAttribute('max', maxYMD);
        f.addEventListener('input', () => {
          f.setCustomValidity(f.value > maxYMD ? 'Debes tener al menos 16 años.' : '');
        });
      }

      initFechas();
      initNacimiento();
      hookSubmit();
    })();

    (function() {
      const form = document.getElementById('MIformulario');
      const tipo = document.getElementById('tipo_id');
      const numero = document.getElementById('numero_id');
      const hint = document.getElementById('numero_id_hint');
      const submitBtn = document.querySelector('button[type="submit"]');

      const modal = document.getElementById('modal-actualizar-datos');
      const madIr = document.getElementById('mad-ir');
      const madTexto = document.getElementById('mad-texto');
      const madCerrar = document.getElementById('mad-cerrar'); // existe en tu HTML, lo ocultamos

      // estado de la verificación
      let docCheck = {
        pending: false,
        exists: null,
        lastQuery: {
          tipo: '',
          num: ''
        }
      };

      /* ---------------- MODAL: no se puede cerrar ---------------- */

      // bloquear scroll de fondo
      function lockPage() {
        document.body.style.overflow = 'hidden';
      }

      function unlockPage() {
        document.body.style.overflow = '';
      }

      // focus trap + bloqueo de ESC
      let focusables = [];
      let keyHandler = null;

      function buildFocusables() {
        focusables = Array.from(
          modal.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])')
        ).filter(el => el.offsetParent !== null);
      }

      function trapFocus(e) {
        if (e.key !== 'Tab' || !focusables.length) return;
        const first = focusables[0],
          last = focusables[focusables.length - 1];
        if (e.shiftKey && document.activeElement === first) {
          e.preventDefault();
          last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
          e.preventDefault();
          first.focus();
        }
      }

      function blockEsc(e) {
        if (e.key === 'Escape') {
          e.preventDefault();
          e.stopPropagation();
        }
      }

      function attachKeys() {
        keyHandler = (e) => {
          trapFocus(e);
          blockEsc(e);
        };
        document.addEventListener('keydown', keyHandler, true);
      }

      function detachKeys() {
        if (keyHandler) {
          document.removeEventListener('keydown', keyHandler, true);
          keyHandler = null;
        }
      }

      // NO cerrar por clic fuera
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          e.preventDefault();
          e.stopPropagation();
        }
      }, true);

      // NO permitir cerrar por el botón "Cerrar" si quedó en el DOM
      // madCerrar && madCerrar.addEventListener('click', (e) => {
      //   e.preventDefault();
      //   e.stopPropagation();
      // });

      function cerrarModal() {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        detachKeys(); // quita el trap de foco y bloqueo de ESC
        unlockPage(); // vuelve a habilitar el scroll del body

        // Devuelve el foco a un punto lógico (número de documento)
        const numero = document.getElementById('numero_id');
        if (numero) numero.focus();
      }

      // Permitir cerrar únicamente con la X
      if (madCerrar) {
        madCerrar.addEventListener('click', (e) => {
          e.preventDefault();
          cerrarModal();
        });
      }

      function abrirModal(data, t, n) {
        const nombre = data?.data ? `${data.data.nombres} ${data.data.apellidos}`.trim() : '';
        madTexto.textContent = nombre ?
          `El documento ${n} ya está registrado a nombre de ${nombre}. Por favor, actualiza tus datos.` :
          `Este documento ya se encuentra registrado. Para continuar, por favor actualiza tus datos.`;

        const urlActualizar = `../servicios/php_Login/actualizar_datos?tipo_id=${encodeURIComponent(t)}&numero_id=${encodeURIComponent(n)}`;
        madIr.setAttribute('href', urlActualizar);

        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        lockPage();

        buildFocusables();
        (focusables[0] || madIr).focus();
        attachKeys();
      }

      // ÚNICA salida válida: navegar a actualizar datos
      madIr.addEventListener('click', function() {
        detachKeys();
        unlockPage();
        // no prevenimos el default; sigue el enlace
      });

      /* ---------------- UI helper states ---------------- */

      function setUIChecking() {
        docCheck.pending = true;
        submitBtn && (submitBtn.disabled = true);
        hint && (hint.textContent = 'Verificando número de identificación…');
      }

      function setUIExists(data, t, n) {
        docCheck.pending = false;
        docCheck.exists = true;
        submitBtn && (submitBtn.disabled = true);
        const nombre = data?.data ? `${data.data.nombres} ${data.data.apellidos}`.trim() : '';
        hint && (hint.textContent = nombre ? `Ya registrado a nombre de ${nombre}.` : `Este documento ya está registrado.`);
        abrirModal(data, t, n);
      }

      function setUINotExists() {
        docCheck.pending = false;
        docCheck.exists = false;
        submitBtn && (submitBtn.disabled = false);
        hint && (hint.textContent = '');
        // por si la modal quedó abierta de antes (no debería)
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        detachKeys();
        unlockPage();
      }

      function setUIError(msg) {
        docCheck.pending = false;
        docCheck.exists = null;
        submitBtn && (submitBtn.disabled = false);
        hint && (hint.textContent = msg || 'No se pudo validar el documento. Intenta de nuevo.');
        // no abrimos modal en errores
      }

      /* ---------------- Validación AJAX ---------------- */

      async function validarDocumento() {
        const t = (tipo.value || '').trim();
        const n = (numero.value || '').trim();

        if (!t || !n || n.length < 6) {
          docCheck.exists = null;
          submitBtn && (submitBtn.disabled = false);
          hint && (hint.textContent = '');
          return;
        }

        if (docCheck.lastQuery.tipo === t && docCheck.lastQuery.num === n && docCheck.exists !== null) return;

        docCheck.lastQuery = {
          tipo: t,
          num: n
        };
        setUIChecking();

        try {
          const fd = new FormData();
          fd.append('action', 'check_doc');
          fd.append('tipo_id', t);
          fd.append('numero_id', n);

          const resp = await fetch(location.href, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
          });
          const raw = await resp.text();
          let data;
          try {
            data = JSON.parse(raw);
          } catch {
            setUIError('Error del servidor (respuesta inesperada).');
            return;
          }

          if (data.ok !== true) {
            setUIError(data.msg || 'Error al validar documento.');
            return;
          }

          if (data.exists === true) setUIExists(data, t, n);
          else if (data.exists === false) setUINotExists();
          else setUIError('Validación indeterminada.');
        } catch (err) {
          console.error(err);
          setUIError('Error de red al validar documento.');
        }
      }

      /* ---------------- Eventos ---------------- */

      numero.addEventListener('blur', validarDocumento);
      numero.addEventListener('input', () => {
        hint && (hint.textContent = '');
        submitBtn && (submitBtn.disabled = false);
      });
      tipo.addEventListener('change', () => {
        hint && (hint.textContent = '');
        validarDocumento();
      });

      form.addEventListener('submit', (ev) => {
        if (docCheck.pending) {
          ev.preventDefault();
          hint && (hint.textContent = 'Esperando validación del documento…');
          return;
        }
        if (docCheck.exists === true) {
          ev.preventDefault();
          abrirModal(null, (tipo.value || ''), (numero.value || ''));
        }
      });
    })();

    (function () {
    const medallaBtn = document.querySelector(".btn-medalla");
    const tooltip = document.querySelector(".tooltip-medalla");
    const textoGuardado = document.querySelector(".texto-guardado");

    if (!medallaBtn || !tooltip || !textoGuardado) return;

    // Inicializamos el contenido del tooltip con el texto guardado (por si cambia desde servidor)
    tooltip.textContent = textoGuardado.textContent.trim();

    medallaBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        tooltip.classList.toggle("visible");
        tooltip.setAttribute("aria-hidden", tooltip.classList.contains("visible") ? "false" : "true");
    });

    // Se cierra si se da click por fuera
    document.addEventListener("click", function (e) {
        if (!medallaBtn.contains(e.target) && !tooltip.contains(e.target)) {
            tooltip.classList.remove("visible");
            tooltip.setAttribute("aria-hidden", "true");
        }
    });
})();

document.addEventListener("DOMContentLoaded", () => {

  const pre = window.PREFILL || {};
  if (!pre.ok) return;

  const selCentro = document.getElementById("centro_orientacion");
  const selOri    = document.getElementById("orientador");
  const hidId     = document.getElementById("orientador_id");

  // 1) Fijar centro directamente
  selCentro.value = pre.center;
  selCentro.setAttribute("disabled", "disabled");

  // 2) Cargar orientadores del centro actual
  // actualiza la lista de orientadores y luego aplica el prellenado.
  if (typeof actualizarOrientadores === "function") {
      const maybe = actualizarOrientadores();
      // Si actualizarOrientadores devuelve una promesa, espera a que finalice.
      if (maybe && typeof maybe.then === 'function') {
          maybe.then(() => {
              // Esperamos un tick antes de aplicar el prefill para garantizar que
              // el DOM se actualice con las nuevas opciones.
              setTimeout(aplicarPrefill, 0);
          }).catch(() => setTimeout(aplicarPrefill, 0));
      } else {
          // No devuelve promesa, así que aplicamos prefill en el siguiente ciclo
          setTimeout(aplicarPrefill, 0);
      }
  } else {
      aplicarPrefill();
  }

  function aplicarPrefill() {
    // Siempre buscamos por nombre visible. Normalizamos para comparar
    const targetName = normalizar(pre.name);
    let match = [...selOri.options].find(o => normalizar(o.text) === targetName || normalizar(o.value) === targetName);

    if (match) {
        // Seleccionamos el orientador por su valor (nombre)
        selOri.value = match.value;
    } else {
        // Si no existe en el listado actual, inyectamos una opción con el nombre
        const opt = document.createElement("option");
        opt.value = pre.name;
        opt.text  = pre.name;
        selOri.appendChild(opt);
        selOri.value = opt.value;
    }
    // Establecemos el id oculto con el oid del token (si existe)
    if (pre.oid && +pre.oid > 0) {
        hidId.value = String(pre.oid);
    } else {
        // Si no hay id, intentamos resolverlo a través del mapeo JS
        const nombreSel = selOri.value || '';
        let idMap = 0;
        if (typeof getOrientadorId === 'function') {
            idMap = getOrientadorId(selCentro.value, nombreSel);
        }
        hidId.value = idMap > 0 ? String(idMap) : '';
    }
  }

  function normalizar(s){
      return (s||"")
        .trim()
        .toLowerCase()
        .normalize("NFD")
        .replace(/\p{Diacritic}/gu,"")
        .replace(/\s+/g," ");
  }
});
