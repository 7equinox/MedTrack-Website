// public/js/auth.js
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.querySelector('.toggle-icon');

    if (toggleIcon) {
        toggleIcon.addEventListener('click', function() {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                this.style.backgroundImage = "url(\"data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236c757d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpath d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'%3e%3c/path%3e%3ccircle cx='12' cy='12' r='3'%3e%3c/circle%3e%3c/svg%3e\")";
            } else {
                passwordField.type = 'password';
                this.style.backgroundImage = "url(\"data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236c757d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpath d='M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24'%3e%3c/path%3e%3cline x1='1' y1='1' x2='23' y2='23'%3e%3c/line%3e%3c/svg%3e\")";
            }
        });
    }
}); 