(function() {
  async function loadTranslations(locale) {
    try {
      const baseRes = await fetch('../i18n/ui.en.json');
      const base = await baseRes.json();
      if (locale === 'en') {
        return base;
      }
      try {
        const res = await fetch(`../i18n/ui.${locale}.json`);
        const extra = await res.json();
        return Object.assign({}, base, extra);
      } catch (e) {
        return base;
      }
    } catch (e) {
      return {};
    }
  }

  function applyTranslations(translations, locale) {
    document.documentElement.lang = locale;
    document.querySelectorAll('[data-i18n]').forEach(el => {
      const key = el.getAttribute('data-i18n');
      if (translations[key]) {
        el.textContent = translations[key];
      }
    });
    document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
      const key = el.getAttribute('data-i18n-placeholder');
      if (translations[key]) {
        el.placeholder = translations[key];
      }
    });
  }

  document.addEventListener('DOMContentLoaded', async () => {
    const stored = localStorage.getItem('locale') || 'en';
    let translations = await loadTranslations(stored);
    let locale = stored;
    applyTranslations(translations, locale);
    document.dispatchEvent(new CustomEvent('i18n-loaded', { detail: locale }));

    window.i18n = {
      t: (k) => translations[k] || k,
      get locale() { return locale; },
      async setLocale(newLocale) {
        locale = newLocale;
        localStorage.setItem('locale', newLocale);
        translations = await loadTranslations(newLocale);
        applyTranslations(translations, newLocale);
        document.dispatchEvent(new CustomEvent('i18n-loaded', { detail: newLocale }));
      }
    };

    const switchEl = document.getElementById('lang-switch');
    if (switchEl) {
      switchEl.value = locale;
      switchEl.addEventListener('change', e => {
        window.i18n.setLocale(e.target.value);
      });
    }
  });
})();
