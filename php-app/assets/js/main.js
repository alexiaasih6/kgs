/**
 * Main JavaScript - PT. Kreatif Global Solusindo (KGS)
 * Handles Dark/Light Theme, Mobile Navigation, and UI Interactions
 */

(function () {
  function getStoredMode() {
    try {
      var saved = localStorage.getItem('kgs_color_mode');
      if (saved === 'dark') return 'dark';
      if (saved === 'light') return 'light';
    } catch (e) {}
    return 'light';
  }

  function applyColorMode(mode) {
    var cleanMode = mode === 'dark' ? 'dark' : 'light';
    var isDark = cleanMode === 'dark';
    var root = document.documentElement;

    if (isDark) {
      root.classList.add('dark');
      root.setAttribute('data-theme', 'dark');
    } else {
      root.classList.remove('dark');
      root.setAttribute('data-theme', 'light');
    }
    root.setAttribute('data-color-mode', cleanMode);

    // Highlight active theme buttons ([data-theme-btn] and [data-theme-mode])
    var btns = document.querySelectorAll('[data-theme-btn], [data-theme-mode]');
    btns.forEach(function (btn) {
      var btnMode = btn.getAttribute('data-theme-btn') || btn.getAttribute('data-theme-mode');
      if (btnMode === cleanMode) {
        btn.classList.add('is-active');
      } else {
        btn.classList.remove('is-active');
      }
    });

    // Swap default SVG logo if needed
    var logos = document.querySelectorAll('.kgs-brand-logo');
    logos.forEach(function (img) {
      var defaultLogo = img.getAttribute('data-default-logo') || '';
      if (defaultLogo === 'assets/images/logo-kgs.svg') {
        img.setAttribute(
          'src',
          isDark ? 'assets/images/logo-kgs-white.svg' : 'assets/images/logo-kgs.svg'
        );
      }
    });
  }

  function setMode(mode) {
    var cleanMode = mode === 'dark' ? 'dark' : 'light';
    try {
      localStorage.setItem('kgs_color_mode', cleanMode);
    } catch (e) {}
    applyColorMode(cleanMode);
  }

  function toggleMode() {
    var current = getStoredMode();
    setMode(current === 'dark' ? 'light' : 'dark');
  }

  window.setKgsColorMode = setMode;
  window.setKgsThemeMode = setMode;
  window.toggleKgsColorMode = toggleMode;
  window.cycleKgsThemeMode = toggleMode;

  document.addEventListener('DOMContentLoaded', function () {
    applyColorMode(getStoredMode());

    // Mobile Menu Toggle (supports both #mobileMenuBtn/#mobileMenu and #mobile-menu-btn/#mobile-menu)
    var mobileBtn = document.getElementById('mobileMenuBtn') || document.getElementById('mobile-menu-btn');
    var mobileMenu = document.getElementById('mobileMenu') || document.getElementById('mobile-menu');

    if (mobileBtn && mobileMenu) {
      mobileBtn.addEventListener('click', function () {
        mobileMenu.classList.toggle('hidden');
      });
    }

    // Auto-dismiss flash alerts after 6 seconds
    var flashAlerts = document.querySelectorAll('[data-auto-dismiss]');
    flashAlerts.forEach(function (alertEl) {
      setTimeout(function () {
        alertEl.style.transition = 'opacity 0.4s ease';
        alertEl.style.opacity = '0';
        setTimeout(function () {
          alertEl.remove();
        }, 400);
      }, 6000);
    });
  });
})();
