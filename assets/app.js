'use strict';

document.querySelectorAll('.delete-form').forEach((form) => {
  form.addEventListener('submit', (event) => {
    const button = form.querySelector('button[data-product-name]');
    const name = button?.dataset.productName || 'produk ini';
    if (!window.confirm(`Yakin ingin menghapus "${name}"? Tindakan ini tidak dapat dibatalkan.`)) {
      event.preventDefault();
    }
  });
});
