// Estilo dark acorde a tu web
const swalBase = {
  background: '#0f1115',
  color: '#e8eaf0',
  confirmButtonColor: '#2ecc71',
  cancelButtonColor: '#e74c3c',
  buttonsStyling: true,
};

const toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3500,
  timerProgressBar: true,
  background: '#0f1115',
  color: '#e8eaf0',
  didOpen: (t) => {
    t.addEventListener('mouseenter', Swal.stopTimer);
    t.addEventListener('mouseleave', Swal.resumeTimer);
  }
});

// Uso: showToast('success', 'Guardado correctamente')
window.showToast = (icon, title, opts = {}) => toast.fire({ icon, title, ...opts });

// Uso: showAlert('error', 'Ups', 'Completa todos los campos')
window.showAlert = (icon, title = '', text = '', opts = {}) =>
  Swal.fire({ icon, title, text, ...swalBase, ...opts });

// Confirmación de navegación (enlaces con clase .js-confirm o .js-logout)
document.addEventListener('click', (e) => {
  const a = e.target.closest('a.js-confirm, button.js-confirm, a.js-logout');
  if (!a) return;

  e.preventDefault();
  const href = a.getAttribute('href') || '#';
  const title = a.classList.contains('js-logout') ? '¿Cerrar sesión?' : (a.dataset.title || '¿Estás seguro?');
  const text  = a.dataset.text || (a.classList.contains('js-logout') ? 'Se cerrará tu sesión actual.' : 'Esta acción no se puede deshacer.');

  Swal.fire({
    icon: 'question',
    title, text,
    showCancelButton: true,
    confirmButtonText: 'Sí, continuar',
    cancelButtonText: 'Cancelar',
    ...swalBase
  }).then(r => {
    if (r.isConfirmed) {
      if (a.tagName === 'BUTTON' && a.form) a.form.submit();
      else window.location.href = href;
    }
  });
});
