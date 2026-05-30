(function () {
  'use strict';

  if (!document.body.classList.contains('cabinet-page')) {
    return;
  }

  var toggle = document.querySelector('[data-cabinet-menu-toggle]');
  var backdrop = document.querySelector('.cabinet-sidebar-backdrop');

  function setOpen(open) {
    document.body.classList.toggle('cabinet-nav-open', open);
    if (toggle) {
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
  }

  if (toggle) {
    toggle.addEventListener('click', function () {
      setOpen(!document.body.classList.contains('cabinet-nav-open'));
    });
  }

  if (backdrop) {
    backdrop.addEventListener('click', function () {
      setOpen(false);
    });
  }

  document.querySelectorAll('.cabinet-sidebar a, .cabinet-bottom-nav a').forEach(function (link) {
    link.addEventListener('click', function () {
      if (window.matchMedia('(max-width: 900px)').matches) {
        setOpen(false);
      }
    });
  });
})();
