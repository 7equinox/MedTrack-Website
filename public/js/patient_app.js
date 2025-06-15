document.addEventListener('DOMContentLoaded', function() {
    const reminderPopup = document.getElementById('reminderPopup');
    const closePopup = document.getElementById('closePopup');
    const menuToggle = document.getElementById('menuToggle');
    const sideMenu = document.getElementById('sideMenu');
    const closeMenu = document.getElementById('closeMenu');
    const menuOverlay = document.getElementById('menuOverlay');

    if (closePopup && reminderPopup) {
        closePopup.addEventListener('click', function() {
            reminderPopup.classList.add('closing');
            reminderPopup.addEventListener('animationend', function() {
                reminderPopup.style.display = 'none';
            }, { once: true });
        });
    }

    

    const openMenu = () => {
        sideMenu.classList.add('open');
        menuOverlay.classList.add('show');
    };

    const closeAll = () => {
        sideMenu.classList.remove('open');
        menuOverlay.classList.remove('show');
    };

    if (menuToggle && sideMenu) {
        menuToggle.addEventListener('click', openMenu);
    }

    if (closeMenu && sideMenu) {
        closeMenu.addEventListener('click', closeAll);
    }

    if (menuOverlay) {
        menuOverlay.addEventListener('click', closeAll);
    }

    const currentPagePath = window.location.pathname;
    const sideMenuLinks = document.querySelectorAll('.side-menu-links a');

    sideMenuLinks.forEach(link => {
        const linkPath = new URL(link.href).pathname;
        if (linkPath === currentPagePath) {
            link.classList.add('active');
        }
    });
});