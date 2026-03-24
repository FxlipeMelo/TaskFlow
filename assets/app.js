import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.scss';

import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

function startToasts() {
    const toastElList = document.querySelectorAll('.toast');
    const toastList = [...toastElList].map(toastEl => new bootstrap.Toast(toastEl, {delay: 3000}));
    toastList.forEach(toast => toast.show());
}

function startSidebar() {
    const toggleBtn = document.getElementById('desktopToggle');
    const sidebar = document.getElementById('sidebar');

    if (toggleBtn && sidebar) {
        toggleBtn.onclick = function () {
            sidebar.classList.toggle('collapsed');
        };
    }
}

document.addEventListener('DOMContentLoaded', () => {
    startToasts();
    startSidebar();
});

document.addEventListener('turbo:load', () => {
    startToasts();
    startSidebar();
});

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
