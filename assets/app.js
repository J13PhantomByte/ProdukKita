/* Progressive enhancement: confirm destructive actions and guide keyboard users. */
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".delete-form button[data-product-name]").forEach((button) => {
    button.addEventListener("click", (event) => {
      const name = button.dataset.productName || "produk ini";
      const confirmed = window.confirm(`Hapus "${name}"? Tindakan ini tidak dapat dibatalkan.`);
      if (!confirmed) event.preventDefault();
    });
  });

  const firstInvalid = document.querySelector('[aria-invalid="true"]');
  if (firstInvalid) firstInvalid.focus();

  document.querySelectorAll("form").forEach((form) => {
    form.addEventListener("submit", () => {
      const submit = form.querySelector('button[type="submit"]');
      if (submit && !submit.dataset.noLoading) {
        submit.dataset.originalText = submit.textContent;
      }
    });
  });
});
