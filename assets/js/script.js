/* --- Footer year --- */
document.querySelectorAll('.footer-year').forEach(function (el) {
  el.textContent = new Date().getFullYear();
});

/* --- Hamburger nav --- */
const navToggle = document.querySelector('.nav-toggle');
const navLinks  = document.querySelector('.nav-links');
if (navToggle && navLinks) {
  navToggle.addEventListener('click', function () {
    navLinks.classList.toggle('open');
    navToggle.classList.toggle('open');
  });
}

/* --- Flash dismiss --- */
document.querySelectorAll('.flash').forEach(function (flash) {
  const btn = document.createElement('button');
  btn.className   = 'flash-close';
  btn.textContent = '×';
  btn.setAttribute('aria-label', 'Fermer');
  btn.addEventListener('click', function () { flash.remove(); });
  flash.appendChild(btn);
});

/* --- Delete confirmation --- */
document.querySelectorAll('[data-confirm]').forEach(function (el) {
  el.addEventListener('click', function (e) {
    e.preventDefault();
    if (!confirm(el.dataset.confirm)) return;
    window.location.href = el.href;
  });
});

/* --- Avatar preview --- */
const avatarInput = document.getElementById('avatar');
if (avatarInput) {
  avatarInput.addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (ev) {
        document.getElementById('preview-avatar').src = ev.target.result;
      };
      reader.readAsDataURL(file);
    }
  });
}
