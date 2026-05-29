(function () {
  'use strict';

  var body = document.body;
  if (!body.classList.contains('cabinet-page')) {
    return;
  }

  var toggleBtn = document.querySelector('[data-cabinet-menu-toggle]');
  var backdrop = document.querySelector('.cabinet-sidebar-backdrop');
  var sidebar = document.querySelector('.cabinet-sidebar');

  function setNavOpen(open) {
    body.classList.toggle('cabinet-nav-open', open);
    if (toggleBtn) {
      toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
  }

  if (toggleBtn) {
    toggleBtn.addEventListener('click', function () {
      setNavOpen(!body.classList.contains('cabinet-nav-open'));
    });
  }

  if (backdrop) {
    backdrop.addEventListener('click', function () {
      setNavOpen(false);
    });
  }

  document.querySelectorAll('.cabinet-sidebar a').forEach(function (link) {
    link.addEventListener('click', function () {
      if (window.matchMedia('(max-width: 960px)').matches) {
        setNavOpen(false);
      }
    });
  });

  document.querySelectorAll('.kb-tree__item--branch > .kb-tree__link').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      var item = btn.closest('.kb-tree__item--branch');
      if (!item || window.matchMedia('(max-width: 960px)').matches) {
        return;
      }
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
        return;
      }
      var children = item.querySelector(':scope > .kb-tree__children');
      if (!children) {
        return;
      }
      e.preventDefault();
      item.classList.toggle('is-expanded');
    });
  });

  document.querySelectorAll('[data-course-progress]').forEach(function (form) {
    form.addEventListener('submit', function () {
      var btn = form.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.textContent = 'Сохранение…';
      }
    });
  });

  window.addEventListener('resize', function () {
    if (window.matchMedia('(min-width: 961px)').matches) {
      setNavOpen(false);
    }
  });
})();
