// Sanitización celular
        const celular = document.getElementById('celular');
        celular?.addEventListener('input', function() {
            const clean = this.value.replace(/\D+/g, '').slice(0, 15);
            this.value = clean;

            if (clean.length >= 7 && clean.length <= 15) {
                this.classList.remove('has-error');
                this.classList.add('has-success');
            } else if (clean.length > 0) {
                this.classList.remove('has-success');
                this.classList.add('has-error');
            } else {
                this.classList.remove('has-success', 'has-error');
            }
        });

        // Validación correo
        const correo = document.getElementById('correo');
        correo?.addEventListener('blur', function() {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && regex.test(this.value)) {
                this.classList.remove('has-error');
                this.classList.add('has-success');
            } else if (this.value) {
                this.classList.remove('has-success');
                this.classList.add('has-error');
            }
        });

        // Indicador de fortaleza
        const contrasena = document.getElementById('contrasena');
        const strengthMeter = document.getElementById('strengthMeter');
        const strengthBar = document.getElementById('strengthBar');
        const strengthLabel = document.getElementById('strengthLabel');
        const strengthText = document.getElementById('strengthText');

        function calculateStrength(pwd) {
            let score = 0;
            if (pwd.length >= 6) score++;
            if (pwd.length >= 10) score++;
            if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score++;
            if (/\d/.test(pwd)) score++;
            if (/[^a-zA-Z0-9]/.test(pwd)) score++;
            return score;
        }

        contrasena?.addEventListener('input', function() {
            const pwd = this.value;

            if (pwd.length === 0) {
                strengthMeter.style.display = 'none';
                this.classList.remove('has-error', 'has-success');
                return;
            }

            strengthMeter.style.display = 'block';
            const strength = calculateStrength(pwd);

            strengthBar.classList.remove('weak', 'medium', 'strong');
            strengthLabel.classList.remove('weak', 'medium', 'strong');
            this.classList.remove('has-error', 'has-success');

            if (strength <= 2) {
                strengthBar.classList.add('weak');
                strengthLabel.classList.add('weak');
                strengthText.textContent = 'Contraseña débil';
                this.classList.add('has-error');
            } else if (strength <= 3) {
                strengthBar.classList.add('medium');
                strengthLabel.classList.add('medium');
                strengthText.textContent = 'Contraseña aceptable';
            } else {
                strengthBar.classList.add('strong');
                strengthLabel.classList.add('strong');
                strengthText.textContent = 'Contraseña fuerte';
                this.classList.add('has-success');
            }

            checkMatch();
        });

        // Verificación de coincidencia
        const confirmar = document.getElementById('confirmar');
        const matchIndicator = document.getElementById('matchIndicator');
        const matchText = document.getElementById('matchText');

        function checkMatch() {
            const pwd = contrasena.value;
            const conf = confirmar.value;

            if (conf.length === 0) {
                matchIndicator.classList.remove('show', 'match', 'no-match');
                confirmar.classList.remove('has-error', 'has-success');
                return;
            }

            matchIndicator.classList.add('show');

            if (pwd === conf) {
                matchIndicator.classList.remove('no-match');
                matchIndicator.classList.add('match');
                matchText.textContent = 'Las contraseñas coinciden';
                confirmar.classList.remove('has-error');
                confirmar.classList.add('has-success');
            } else {
                matchIndicator.classList.remove('match');
                matchIndicator.classList.add('no-match');
                matchText.textContent = 'Las contraseñas no coinciden';
                confirmar.classList.remove('has-success');
                confirmar.classList.add('has-error');
            }
        }

        confirmar?.addEventListener('input', checkMatch);

        // Toggle password visibility
        document.querySelectorAll('.toggle-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.dataset.target;
                const input = document.getElementById(targetId);
                if (!input) return;

                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                this.textContent = isPassword ? 'Ocultar' : 'Ver';
            });
        });

        // Form submit
        const form = document.getElementById('mainForm');
        const submitBtn = document.getElementById('submitBtn');

        form?.addEventListener('submit', function(e) {
            const pwd = contrasena.value;
            const conf = confirmar.value;

            if (pwd.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres.');
                contrasena.focus();
                return;
            }

            if (pwd !== conf) {
                e.preventDefault();
                alert('Las contraseñas no coinciden.');
                confirmar.focus();
                return;
            }

            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });

        // Auto-save draft
        const STORAGE_KEY = 'sena_form_draft';
        let saveTimeout;

        function saveDraft() {
            const draft = {
                nombres: document.getElementById('nombres').value,
                apellidos: document.getElementById('apellidos').value,
                celular: celular.value,
                correo: correo.value,
                timestamp: Date.now()
            };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(draft));
        }

        function loadDraft() {
            try {
                const draft = JSON.parse(localStorage.getItem(STORAGE_KEY));
                if (!draft) return;

                const ONE_HOUR = 60 * 60 * 1000;
                if (Date.now() - draft.timestamp > ONE_HOUR) {
                    localStorage.removeItem(STORAGE_KEY);
                    return;
                }

                ['nombres', 'apellidos', 'celular', 'correo'].forEach(id => {
                    const input = document.getElementById(id);
                    if (input && !input.value && draft[id]) {
                        input.value = draft[id];
                    }
                });
            } catch (e) {}
        }

        ['nombres', 'apellidos', 'celular', 'correo'].forEach(id => {
            const input = document.getElementById(id);
            input?.addEventListener('input', () => {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(saveDraft, 3000);
            });
        });

        loadDraft();
        form?.addEventListener('submit', () => localStorage.removeItem(STORAGE_KEY));