/**
 * APOTEK POS — Frontend interaction helpers
 * Ditulis vanilla JS, tidak bergantung library eksternal.
 *
 * Fitur yang ada di sini:
 *  - Modal konfirmasi hapus
 *  - Toggle qty di keranjang
 *  - Format Rupiah otomatis
 */

(function () {
    'use strict';

    window.AP = window.AP || {};

    /**
     * Tampilkan modal konfirmasi.
     * @param {string} message - teks konfirmasi
     * @param {string} modalId - id elemen modal
     * @param {Function} onConfirm - callback saat dikonfirmasi
     */
    AP.confirmDelete = function (message, modalId, onConfirm) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const msgEl = modal.querySelector('[data-confirm-msg]');
        const btnConfirm = modal.querySelector('[data-confirm-btn]');
        const btnCancel = modal.querySelector('[data-cancel-btn]');

        if (msgEl) msgEl.textContent = message;

        btnConfirm.onclick = function () {
            modal.classList.remove('active');
            onConfirm();
        };

        btnCancel.onclick = function () {
            modal.classList.remove('active');
        };

        modal.classList.add('active');
    };

    /**
     * Hilangkan modal.
     */
    AP.closeModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('active');
    };

    /**
     * Tutup modal jika tekan Escape.
     */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(function (m) {
                m.classList.remove('active');
            });
        }
    });

    /**
     * Format angka ke Rupiah di input (hanya format display).
     */
    AP.formatRupiahInput = function (inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;

        input.addEventListener('blur', function () {
            const raw = input.value.replace(/[^\d]/g, '');
            if (raw === '') {
                input.value = '';
                return;
            }
            input.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        });

        input.addEventListener('focus', function () {
            input.value = input.value.replace(/\./g, '');
        });
    };

    /**
     * Tampilkan notifikasi singkat (mimik alert).
     * @param {string} message
     * @param {string} type - danger | success | info
     */
    AP.notify = function (message, type) {
        type = type || 'info';
        var alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-' + type;
        alertDiv.textContent = message;
        var container = document.querySelector('.container') || document.body;
        container.insertBefore(alertDiv, container.firstChild);
        setTimeout(function () {
            if (alertDiv.parentNode) alertDiv.remove();
        }, 4000);
    };

    /**
     * Set semua csrf token ke form dalam page.
     */
    AP.injectCsrf = function () {
        var token = AP.csrfToken || '';
        var forms = document.querySelectorAll('form[method="post"]');
        forms.forEach(function (form) {
            if (!form.querySelector('[name="csrf_token"]')) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'csrf_token';
                input.value = token;
                form.appendChild(input);
            }
        });
    };
})();
