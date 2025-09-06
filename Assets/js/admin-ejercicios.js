// Assets/js/admin-ejercicios.js

(function () {
  'use strict';

  const $ = (s, p = document) => p.querySelector(s);
  const $$ = (s, p = document) => Array.from(p.querySelectorAll(s));

  document.addEventListener('DOMContentLoaded', () => {
    prepararConfirmaciones();
    mostrarFlashSiExiste();
  });

  function prepararConfirmaciones() {
    document.addEventListener('click', async (ev) => {
      const btn = ev.target.closest('.js-confirm');
      if (!btn) return;

      const form = btn.closest('form');
      if (!form) return;

      ev.preventDefault();

      const titulo = btn.dataset.title || '¿Estás seguro?';
      const texto  = btn.dataset.text  || 'Esta acción no se puede deshacer.';
      const textoConfirmar = btn.dataset.confirm || 'Sí, continuar';
      const textoCancelar  = btn.dataset.cancel  || 'Cancelar';

      const res = await Swal.fire({
        title: titulo,
        text: texto,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: textoConfirmar,
        cancelButtonText: textoCancelar,
        confirmButtonColor: '#d33'
      });

      if (!res.isConfirmed) return;

      // Prevención de doble envío
      const btnSubmit = form.querySelector('[type=submit]');
      if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.dataset.originalText = btnSubmit.textContent;
        btnSubmit.textContent = 'Enviando...';
      }

      form.submit();
    });
  }

  // Lee variables inyectadas por PHP (si existen) para mostrar toasts
  function mostrarFlashSiExiste() {
    const flash = $('#js-flash-datos');
    if (!flash) return;

    const tipo = flash.dataset.tipo;     // success | error | info | warning | question
    const msg  = flash.dataset.mensaje || '';
    if (!tipo || !msg) return;

    Swal.fire({
      toast: true,
      position: 'top-end',
      timer: 2600,
      showConfirmButton: false,
      icon: tipo,
      title: msg
    });
  }
})();
