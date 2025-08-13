(async function() {
  const state = { locale: 'en', cards: [] };
  const container = document.getElementById('card-container');
  const langSwitch = document.getElementById('lang-switch');

  const typeTranslations = {
    act: { en: 'Act', de: 'Auftritt' },
    sponsor: { en: 'Sponsor', de: 'Sponsor' },
    location: { en: 'Location', de: 'Ort' },
    marketing: { en: 'Marketing', de: 'Marketing' },
    sabotage: { en: 'Sabotage', de: 'Sabotage' }
  };

  langSwitch.addEventListener('change', () => {
    state.locale = langSwitch.value;
    renderCards();
  });

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
    art.alt = card.name[state.locale] || card.id;
    art.dataset.src = card.art || 'https://via.placeholder.com/250x210?text=Art';
    art.loading = 'lazy';
    observer.observe(art);
    cardEl.appendChild(art);

    const title = document.createElement('div');
    title.className = 'title';
    title.textContent = card.name[state.locale] || card.name;
    cardEl.appendChild(title);

    const typeLine = document.createElement('div');
    typeLine.className = 'type-line';
    const typeTrans = typeTranslations[card.type];
    typeLine.textContent = typeTrans ? typeTrans[state.locale] : card.type;
    cardEl.appendChild(typeLine);

    const rules = document.createElement('div');
    rules.className = 'rules';
    if (card.rules && typeof card.rules === 'object') {
      rules.textContent = card.rules[state.locale] || '';
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
