(function () {
  'use strict';

  const navToggle = document.querySelector('.nav-toggle');
  const navLinks = document.querySelector('.nav-links');

  function setMenuOpen(open) {
    if (!navToggle || !navLinks) {
      return;
    }
    navToggle.classList.toggle('is-open', open);
    navLinks.classList.toggle('is-open', open);
    document.body.classList.toggle('menu-open', open);
    navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    navToggle.setAttribute('aria-label', open ? 'Закрыть меню' : 'Открыть меню');
  }

  if (navToggle && navLinks) {
    navToggle.addEventListener('click', function () {
      setMenuOpen(!navLinks.classList.contains('is-open'));
    });

    navLinks.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        setMenuOpen(false);
      });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        setMenuOpen(false);
      }
    });
  }

  const sections = document.querySelectorAll('.section, .hero-visual, .step');

  const observerOptions = {
    root: null,
    rootMargin: '0px 0px -80px 0px',
    threshold: 0.1
  };

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
      }
    });
  }, observerOptions);

  sections.forEach(function (el) {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
  });

  const style = document.createElement('style');
  style.textContent = `
    .section.is-visible,
    .hero-visual.is-visible,
    .step.is-visible {
      opacity: 1 !important;
      transform: translateY(0) !important;
    }
  `;
  document.head.appendChild(style);
})();
