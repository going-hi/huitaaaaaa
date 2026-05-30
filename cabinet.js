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

  var userList = document.querySelector('[data-admin-user-list]');
  var userFilter = document.querySelector('[data-admin-user-filter]');
  if (userList && userFilter) {
    var userEmpty = document.querySelector('[data-admin-user-empty]');
    var userCount = document.querySelector('[data-admin-user-visible-count]');
    var userCards = userList.querySelectorAll('[data-admin-user-search]');

    function filterUsers() {
      var q = userFilter.value.trim().toLowerCase();
      var visible = 0;
      userCards.forEach(function (card) {
        var haystack = card.getAttribute('data-admin-user-search') || '';
        var match = q === '' || haystack.indexOf(q) !== -1;
        card.hidden = !match;
        if (match) {
          visible += 1;
        }
      });
      if (userEmpty) {
        userEmpty.hidden = visible > 0;
      }
      if (userCount) {
        userCount.textContent = String(visible);
      }
    }

    userFilter.addEventListener('input', filterUsers);
  }
})();
