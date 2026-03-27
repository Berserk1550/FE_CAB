document.addEventListener('DOMContentLoaded', function () {
    const ordenFiltro = document.getElementById('ordenFiltro');
    const estadoFiltro = document.getElementById('estadoFiltro');
    const tabla = document.getElementById('tablaEmprendedores');
    const tbody = document.getElementById('tbodyEmprendedores');
    let filasOriginal = Array.from(tbody.querySelectorAll('tr'));

    // Helper para saber si está completado (fase 4)
    function esCompletado(fila) {
        const estado = fila.querySelector('[data-label="Estado de avance"]').textContent.trim();
        return estado === 'Lean Canvas'; // Cambia si tu "completado" es otro texto
    }

    function filtrarYOrdenar() {
        let filas = [...filasOriginal];

        // FILTRO POR ESTADO
        const estadoVal = estadoFiltro.value;
        if (estadoVal === 'completados') {
            filas = filas.filter(esCompletado);
        } else if (estadoVal === 'no_completados') {
            filas = filas.filter(f => !esCompletado(f));
        }

        // ORDENAMIENTO
        const ordenVal = ordenFiltro.value;
        if (ordenVal === 'alfabetico') {
            filas.sort((a, b) => a.children[0].textContent.localeCompare(b.children[0].textContent));
        } else if (ordenVal === 'alfabetico_desc') {
            filas.sort((a, b) => b.children[0].textContent.localeCompare(a.children[0].textContent));
        } else if (ordenVal === 'recientes') {
            filas.reverse(); // Por defecto vienen de más antiguo a reciente (puedes ajustar esto si tu consulta cambia)
        } // 'antiguos' es el orden original

        // Limpiar y renderizar
        tbody.innerHTML = '';
        filas.forEach(fila => tbody.appendChild(fila));
    }

    ordenFiltro.addEventListener('change', filtrarYOrdenar);
    estadoFiltro.addEventListener('change', filtrarYOrdenar);
});

