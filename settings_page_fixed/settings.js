document.addEventListener('DOMContentLoaded', function() {
    const pfpInput = document.getElementById('pfpInput');
    const pfpForm = document.getElementById('pfpForm');

    if (pfpInput && pfpForm) {
        pfpInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                pfpForm.submit();
            }
        });
    }

    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;
    const themeKey = 'theme'; 
    const savedTheme = localStorage.getItem(themeKey);

    if (savedTheme === 'dark') {
        body.classList.add('dark');
        if (themeToggle) {
            themeToggle.checked = true;
        }
    }

    if (themeToggle) {
        themeToggle.addEventListener('change', function() {
            if (this.checked) {
                body.classList.add('dark');
                localStorage.setItem(themeKey, 'dark');
            } else {
                body.classList.remove('dark');
                localStorage.setItem(themeKey, 'light');
            }
        });
    }

});