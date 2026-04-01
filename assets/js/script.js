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
    if (!confirm(el.dataset.confirm)) {
      e.preventDefault();
    }
  });
});

// ! AJAXXXXXXXXXXXXXXXXX
document.addEventListener('click', function (e) {
  var btn = e.target.closest('.like-btn:not(.like-btn-static)');
  if (!btn) return;

  var commentId = btn.dataset.commentId;
  if (!commentId) return;

  btn.disabled = true;
  fetch('../Posts/like-comment.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ comment_id: parseInt(commentId, 10) })
  })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (data.error) return;
      btn.classList.toggle('liked', data.liked);
      btn.querySelector('.like-count').textContent = data.count;
      btn.classList.add('like-animate');
      setTimeout(function () { btn.classList.remove('like-animate'); }, 300);
    })
    .finally(function () { btn.disabled = false; });
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
