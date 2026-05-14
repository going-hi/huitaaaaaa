(function () {
  'use strict';

  // Мобильное меню
  const navToggle = document.querySelector('.nav-toggle');
  const navLinks = document.querySelector('.nav-links');

  if (navToggle && navLinks) {
    navToggle.addEventListener('click', function () {
      navToggle.classList.toggle('is-open');
      navLinks.classList.toggle('is-open');
      document.body.classList.toggle('menu-open');
    });
  }

  // Плавное появление секций при скролле
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

  // Стили для видимости при появлении
  const style = document.createElement('style');
  style.textContent = `
    .section.is-visible,
    .hero-visual.is-visible,
    .step.is-visible {
      opacity: 1 !important;
      transform: translateY(0) !important;
    }
    .nav-links.is-open {
      display: flex !important;
      position: fixed;
      top: 72px;
      left: 0;
      right: 0;
      bottom: 0;
      flex-direction: column;
      justify-content: center;
      gap: 24px;
      background: rgba(15, 15, 18, 0.98);
      backdrop-filter: blur(12px);
    }
    .nav-toggle.is-open span:nth-child(1) {
      transform: rotate(45deg) translate(5px, 5px);
    }
    .nav-toggle.is-open span:nth-child(2) {
      opacity: 0;
    }
    .nav-toggle.is-open span:nth-child(3) {
      transform: rotate(-45deg) translate(5px, -5px);
    }
    .nav-toggle span {
      transition: transform 0.25s ease, opacity 0.25s ease;
    }
    body.menu-open { overflow: hidden; }
  `;
  document.head.appendChild(style);
})();
