//ESTE JS SE ENCUENTRA DENTRO DEL 1ER HTML

//const HOME = <?php echo json_encode($homeUrl, JSON_UNESCAPED_SLASHES); ?>;
            const c   = document.getElementById('count');
            const go  = document.getElementById('goNow');
            let s = 5;
            (function tick() {
                s--;
                if (c) c.textContent = s;
                if (s <= 0) location.assign(HOME);
                else setTimeout(tick, 1000);
            })();
            go?.addEventListener('click', () => location.assign(HOME));

