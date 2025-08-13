(async function() {
  const state = { cards: [] };
  const container = document.getElementById('card-container');

  document.addEventListener('i18n-loaded', renderCards);

  async function loadCards() {
    try {
      const res = await fetch('../api/cards.php');
      const data = await res.json();
      state.cards = data.cards || [];
    } catch (e) {
      console.error('Failed to load cards', e);
    }
  }

  function renderCard(card) {
    const cardEl = document.createElement('div');
    cardEl.className = 'card';
    if (card.style && card.style.color) {
      cardEl.style.borderColor = card.style.color;
    }

    const art = document.createElement('img');
    art.className = 'art';
    const locale = window.i18n ? window.i18n.locale : 'en';
    let titleText = '';
    if (card.name && typeof card.name === 'object') {
      titleText = card.name[locale] || card.name.en || card.id;
    } else if (typeof card.name === 'string') {
      titleText = card.name;
    } else if (card.name_key) {
      titleText = window.i18n ? window.i18n.t(card.name_key) : card.id;
    } else {
      titleText = card.id;
    }
    art.alt = titleText;
    art.dataset.src = card.art || 'https://via.placeholder.com/250x210?text=Art';
    art.loading = 'lazy';
    observer.observe(art);
    cardEl.appendChild(art);

    const title = document.createElement('div');
    title.className = 'title';
    title.textContent = titleText;
    cardEl.appendChild(title);

    const typeLine = document.createElement('div');
    typeLine.className = 'type-line';
    typeLine.textContent = window.i18n ? window.i18n.t(`type_${card.type}`) : card.type;
    cardEl.appendChild(typeLine);

    const rules = document.createElement('div');
    rules.className = 'rules';
    if (card.rules && typeof card.rules === 'object') {
      rules.textContent = card.rules[locale] || card.rules.en || '';
    } else if (card.rules_key && window.i18n) {
      rules.textContent = window.i18n.t(card.rules_key);
    }
    cardEl.appendChild(rules);

    const frame = document.createElement('div');
    frame.className = 'frame';
    cardEl.appendChild(frame);
    return cardEl;
  }

  function renderCards() {
    container.innerHTML = '';
    state.cards.forEach(card => {
      container.appendChild(renderCard(card));
    });
  }

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        observer.unobserve(img);
      }
    });
  }, { rootMargin: '50px' });

  await loadCards();
  renderCards();
})();
