/**1ER HTML */
(function() {
            let seconds = 5;
            const countEl = document.getElementById('secs');
            const home = document.getElementById('goNow').href;
            
            const timer = setInterval(function() {
                seconds--;
                if (countEl) countEl.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(timer);
                    location.replace(home);
                }
            }, 1000);
            
            setTimeout(function() {
                location.replace(home);
            }, 5000);
        })();
    
/**2DO HTML */
