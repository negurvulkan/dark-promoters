(async function() {
  const params = new URLSearchParams(location.search);
  const gameId = parseInt(params.get('game_id'), 10);
  if (!gameId) {
    console.error('missing game_id');
    return;
  }

  const tableEl = document.getElementById('table');
  const handEl = document.getElementById('player-hand');
  const logEl = document.getElementById('log');
  const phaseEl = document.getElementById('phase-display');
  const playerEl = document.getElementById('player-display');

  let expectedVersion = 0;
  let currentState = null;

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        observer.unobserve(img);
      }
    });
  }, { rootMargin: '50px' });

  function createCardEl(card) {
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

  function renderZone(el, cards, clickable) {
    el.innerHTML = '';
    cards.forEach(card => {
      const cardEl = createCardEl(card);
      if (clickable) {
        cardEl.addEventListener('click', () => playCard(card));
      }
      el.appendChild(cardEl);
    });
  }

  function renderLog(entries) {
    logEl.innerHTML = '';
    entries.forEach(entry => {
      const div = document.createElement('div');
      div.textContent = typeof entry === 'string' ? entry : JSON.stringify(entry);
      logEl.appendChild(div);
    });
  }

  function renderState(state) {
    currentState = state;
    phaseEl.textContent = state.phase || '';
    playerEl.textContent = state.current_player || '';
    renderZone(tableEl, state.table || [], false);
    renderZone(handEl, state.hand || [], true);
    renderLog(state.log || []);
  }

  async function loadState() {
    try {
      const res = await fetch(`/api/state.php?game_id=${gameId}`);
      const data = await res.json();
      expectedVersion = data.version;
      renderState(data.state);
    } catch (e) {
      console.error('Failed to load state', e);
    }
  }

  async function playCard(card) {
    const action = { type: 'play', card: card.id || card };
    try {
      const res = await fetch('/api/act.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          game_id: gameId,
          expected_version: expectedVersion,
          action
        })
      });
      if (res.status === 409) {
        await loadState();
        return;
      }
      const data = await res.json();
      if (data.state && typeof data.state === 'object') {
        expectedVersion = data.state.version || expectedVersion + 1;
        renderState(data.state);
      }
    } catch (e) {
      console.error('Failed to send action', e);
    }
  }

  function initStream() {
    if (typeof EventSource === 'function') {
      const es = new EventSource(`/api/stream.php?game_id=${gameId}`);
      es.onmessage = e => {
        try {
          const state = JSON.parse(e.data);
          expectedVersion = parseInt(e.lastEventId, 10) || expectedVersion;
          state.version = expectedVersion;
          renderState(state);
        } catch (err) {
          console.error('Bad SSE data', err);
        }
      };
      es.onerror = () => {
        es.close();
        setTimeout(initStream, 3000);
      };
    } else {
      setInterval(loadState, 5000);
    }
  }

  document.addEventListener('i18n-loaded', () => {
    if (currentState) {
      renderState(currentState);
    }
  });

  await loadState();
  initStream();
})();
