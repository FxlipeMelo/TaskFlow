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

function autoOpenModal() {
    console.log('1. Iniciando verificação do modal auto-open...');
    const autoOpenElement = document.getElementById('modal-auto-open');

    if (autoOpenElement && autoOpenElement.dataset.targetId !== '') {
        const modalId = autoOpenElement.dataset.targetId;
        console.log('2. ID encontrado na div mensageira:', modalId);

        const modalToOpen = document.getElementById(modalId);
        console.log('3. Elemento Modal encontrado no HTML:', modalToOpen);

        if (modalToOpen) {
            console.log('4. Limpando lixo fantasma do Bootstrap antigo...');

            // O SEGREDO DO TURBO: Limpa os rastros do modal anterior antes de abrir o novo
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');

            setTimeout(() => {
                console.log('5. Executando o show() do Bootstrap no novo modal...');
                // getOrCreateInstance evita duplicação de eventos na memória
                const myModal = bootstrap.Modal.getOrCreateInstance(modalToOpen);
                myModal.show();
            }, 100); // Subi o respiro de 50 para 100ms para garantir a renderização
        }
    } else {
        console.log('-> Nenhum modal com erro para abrir nesta tela.');
    }
}

function cleanUpModalsBeforeCache() {
    document.querySelectorAll('.modal.show').forEach(modal => {
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    });

    document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
}

document.addEventListener('DOMContentLoaded', () => {
    startToasts();
    startSidebar();
    autoOpenModal();
});

document.addEventListener('turbo:load', () => {
    startToasts();
    startSidebar();
    autoOpenModal();
});

document.addEventListener('turbo:before-cache', cleanUpModalsBeforeCache);

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
