(function () {
    const REFRESH_INTERVAL = 5000; // 5 segundos
    const MONITOR_USER = <?php echo json_encode(ADMIN_USER); ?>;

    const btn = document.getElementById('btnRefresh');
    const chk = document.getElementById('autoRefresh');
    const headerInfoTime = document.getElementById('headerInfoTime');
    let timer = null;

    function setClassLevel(element, value, highWarn, highCrit) {
        if (!element) return;
        element.classList.remove('success', 'warning', 'danger');
        if (value == null) return;
        if (value > highCrit) {
            element.classList.add('danger');
        } else if (value > highWarn) {
            element.classList.add('warning');
        } else {
            element.classList.add('success');
        }
    }

    function applyMetrics(data) {
        if (!data) return;

        // Header time + uptime
        if (headerInfoTime && data.time) {
            let txt = data.time + ' | Usuario: ' + MONITOR_USER;
            if (data.uptime && data.uptime.formatted) {
                txt += ' | Uptime: ' + data.uptime.formatted;
            }
            headerInfoTime.textContent = txt;
        }

        // Health score
        const healthCircle = document.getElementById('healthCircle');
        const healthLabel = document.getElementById('healthLabel');
        if (data.health && healthCircle && healthLabel) {
            healthCircle.textContent = data.health.score;
            healthLabel.textContent = data.health.label;

            healthCircle.classList.remove('excellent', 'good', 'fair', 'poor');
            if (data.health.level) {
                healthCircle.classList.add(data.health.level);
            }
        }

        // Load info
        const loadInfo = document.getElementById('loadInfo');
        if (data.load && loadInfo) {
            loadInfo.textContent = 'Load avg: 1m ' + data.load['1m'] +
                                   ' | 5m ' + data.load['5m'] +
                                   ' | 15m ' + data.load['15m'];
        }

        // CPU
        if (data.cpu != null) {
            const v = parseFloat(data.cpu);
            const cpuValue = document.getElementById('cpuValue');
            const cpuBar = document.getElementById('cpuBar');
            const cpuBox = document.getElementById('cpuBox');
            if (cpuValue) cpuValue.textContent = v.toFixed(1) + '%';
            if (cpuBar) {
                cpuBar.style.width = Math.max(0, Math.min(100, v)) + '%';
                cpuBar.textContent = v.toFixed(1) + '%';
                setClassLevel(cpuBar, v, 70, 90);
            }
            if (cpuBox) {
                setClassLevel(cpuBox, v, 70, 90);
            }
        }

        // Memoria sistema
        if (data.mem) {
            const p = parseFloat(data.mem.percent);
            const memValue = document.getElementById('memValue');
            const memBar = document.getElementById('memBar');
            const memBox = document.getElementById('memBox');
            const memInfo = document.getElementById('memInfo');
            if (memValue && !isNaN(p)) memValue.textContent = p.toFixed(2) + '%';
            if (memBar && !isNaN(p)) {
                memBar.style.width = Math.max(0, Math.min(100, p)) + '%';
                memBar.textContent = p.toFixed(2) + '%';
                setClassLevel(memBar, p, 80, 95);
            }
            if (memBox && !isNaN(p)) {
                setClassLevel(memBox, p, 80, 95);
            }
            if (memInfo && data.mem.used_f && data.mem.total_f) {
                memInfo.textContent = data.mem.used_f + ' / ' + data.mem.total_f;
            }
        }

        // Disco
        if (data.disk) {
            const p = parseFloat(data.disk.percent);
            const diskValue = document.getElementById('diskValue');
            const diskBar = document.getElementById('diskBar');
            const diskBox = document.getElementById('diskBox');
            const diskInfo = document.getElementById('diskInfo');
            if (diskValue && !isNaN(p)) diskValue.textContent = p.toFixed(2) + '%';
            if (diskBar && !isNaN(p)) {
                diskBar.style.width = Math.max(0, Math.min(100, p)) + '%';
                diskBar.textContent = p.toFixed(2) + '%';
                setClassLevel(diskBar, p, 75, 90);
            }
            if (diskBox && !isNaN(p)) {
                setClassLevel(diskBox, p, 75, 90);
            }
            if (diskInfo && data.disk.used_f && data.disk.total_f) {
                diskInfo.textContent = data.disk.used_f + ' / ' + data.disk.total_f;
            }
        }

        // MySQL
        if (data.mysql) {
            const mysqlBox = document.getElementById('mysqlBox');
            const mysqlState = document.getElementById('mysqlState');
            const mysqlVersion = document.getElementById('mysqlVersion');
            const mysqlThreads = document.getElementById('mysqlThreads');
            const mysqlError = document.getElementById('mysqlError');

            if (data.mysql.status === 'OK') {
                if (mysqlBox) {
                    mysqlBox.classList.remove('danger');
                    mysqlBox.classList.add('success');
                }
                if (mysqlState) mysqlState.textContent = 'OK';
                if (mysqlVersion && data.mysql.version) {
                    mysqlVersion.textContent = data.mysql.version;
                }
                if (mysqlThreads && data.mysql.threads != null) {
                    mysqlThreads.textContent = data.mysql.threads;
                }
                if (mysqlError) mysqlError.textContent = '';
            } else {
                if (mysqlBox) {
                    mysqlBox.classList.remove('success');
                    mysqlBox.classList.add('danger');
                }
                if (mysqlState) mysqlState.textContent = 'ERROR';
                if (mysqlError) {
                    mysqlError.textContent = data.mysql.error || 'Error desconocido';
                }
            }
        }

        // Memoria PHP
        if (data.phpMem) {
            const usage = parseFloat(data.phpMem.usage || 0);
            const percent = data.phpMem.percent != null ? parseFloat(data.phpMem.percent) : null;
            const phpBox = document.getElementById('phpBox');
            const phpUsageValue = document.getElementById('phpUsageValue');
            const phpBar = document.getElementById('phpBar');
            const phpInfo = document.getElementById('phpInfo');

            if (phpUsageValue && data.phpMem.usage_f) {
                phpUsageValue.textContent = data.phpMem.usage_f;
            }

            if (percent != null && phpBar && phpBar.classList) {
                const p = Math.max(0, Math.min(100, percent));
                if (phpBar.classList.contains('progress-fill')) {
                    phpBar.style.width = p + '%';
                    phpBar.textContent = p.toFixed(2) + '% de ' + (data.phpMem.limit_f || '');
                }
                if (phpBox) {
                    setClassLevel(phpBox, p, 75, 90);
                }
                setClassLevel(phpBar, p, 75, 90);
            }

            if (phpInfo && data.phpMem.peak_f) {
                phpInfo.textContent = 'Pico de memoria en esta petición: ' + data.phpMem.peak_f;
            }
        }

        // Uptime & load detallados
        if (data.uptime && data.uptime.formatted) {
            const uptimeText = document.getElementById('uptimeText');
            if (uptimeText) uptimeText.textContent = data.uptime.formatted;
        }
        if (data.load) {
            const l1 = document.getElementById('load1m');
            const l5 = document.getElementById('load5m');
            const l15 = document.getElementById('load15m');
            if (l1) l1.textContent = data.load['1m'];
            if (l5) l5.textContent = data.load['5m'];
            if (l15) l15.textContent = data.load['15m'];
        }

        // Problemas críticos
        const critCard = document.getElementById('critCard');
        const critContainer = document.getElementById('critContainer');
        if (critCard && critContainer) {
            critContainer.innerHTML = '';
            if (Array.isArray(data.crit) && data.crit.length > 0) {
                critCard.style.display = '';
                data.crit.forEach(function (item) {
                    const div = document.createElement('div');
                    div.className = 'alert';
                    const strong = document.createElement('strong');
                    strong.textContent = item.title || 'Alerta';
                    div.appendChild(strong);
                    const text = document.createTextNode(item.msg || '');
                    div.appendChild(text);
                    critContainer.appendChild(div);
                });
            } else {
                critCard.style.display = 'none';
            }
        }
    }

// URL base del mismo script que estás viendo ahora (sin parámetros)
const AJAX_URL = window.location.href.split('?')[0];

function fetchMetrics() {
    fetch(AJAX_URL + '?ajax=1', { cache: 'no-store' })
        .then(function (r) { 
            return r.json(); 
        })
        .then(function (data) { 
            applyMetrics(data); 
        })
        .catch(function (err) { 
            console.warn('Error al actualizar métricas', err); 
        });
}


    function startAuto() {
        if (timer) return;
        // Llamada inmediata + luego cada 5s
        fetchMetrics();
        timer = setInterval(fetchMetrics, REFRESH_INTERVAL);
    }

    function stopAuto() {
        if (!timer) return;
        clearInterval(timer);
        timer = null;
    }

    if (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            // Recarga completa para refrescar también URLs críticas
            window.location.reload();
        });
    }

    if (chk) {
        const saved = localStorage.getItem('monitor_auto_refresh');
        if (saved === '1') {
            chk.checked = true;
            startAuto();
        }

        chk.addEventListener('change', function () {
            if (chk.checked) {
                localStorage.setItem('monitor_auto_refresh', '1');
                startAuto();
            } else {
                localStorage.setItem('monitor_auto_refresh', '0');
                stopAuto();
            }
        });
    }
})();