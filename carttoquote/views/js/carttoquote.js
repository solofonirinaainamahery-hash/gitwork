/* CartToQuote */
(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.querySelector('.ctq-btn-convert');
        if (!btn) return;
        btn.addEventListener('click', function () {
            btn.style.opacity = '0.7';
            btn.style.pointerEvents = 'none';
        });
    });
})();
