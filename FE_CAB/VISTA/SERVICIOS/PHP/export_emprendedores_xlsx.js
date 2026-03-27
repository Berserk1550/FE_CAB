        //const HOME = <?php echo json_encode($homeUrl, JSON_UNESCAPED_SLASHES); ?>;
            const countEl = document.getElementById('count');
            const goBtn = document.getElementById('goNow');
            let seconds = 5;

            function tick() {
                seconds--;
                if (countEl) countEl.textContent = seconds;
                if (seconds <= 0) {
                    location.assign(HOME);
                } else {
                    setTimeout(tick, 1000);
                }
            }
            setTimeout(tick, 1000);
            goBtn?.addEventListener('click', () => location.assign(HOME));