document.addEventListener('DOMContentLoaded', async () => {
  const token = localStorage.getItem('session_token');
  if (!token) {
    window.location.href = 'login.php';
    return;
  }

  const filterInput = document.getElementById('filter');
  const tbody = document.getElementById('inventory_body');
  const pointsEl = document.getElementById('points');
  let inventory = [];
  const cardsById = {};

  async function loadCards() {
    try {
      const res = await fetch('/api/cards.php');
      const json = await res.json().catch(() => ({}));
      const cards = Array.isArray(json.cards) ? json.cards : [];
      cards.forEach(c => {
        if (c && c.id) {
          cardsById[c.id] = c;
        }
      });
    } catch (err) {
      console.error(err);
    }
  }

  async function loadInventory() {
    try {
      const res = await fetch('/api/inventory.php', {
        headers: {
          'Authorization': `Bearer ${token}`
        },
        credentials: 'same-origin'
      });
      const json = await res.json().catch(() => ({}));
      if (!res.ok || json.error) {
        if (res.status === 401) {
          window.location.href = 'login.php';
        } else {
          alert(json.error || (window.i18n ? window.i18n.t('load_failed') : 'Failed to load inventory'));
        }
        return;
      }
      inventory = Array.isArray(json.inventory) ? json.inventory : [];
      if (pointsEl && typeof json.points === 'number') {
        pointsEl.textContent = json.points;
      }
      render();
    } catch (err) {
      console.error(err);
    }
  }

  function getCardName(id) {
    const card = cardsById[id];
    if (!card) return id;
    const locale = window.i18n ? window.i18n.locale : 'en';
    if (card.name && typeof card.name === 'object') {
      return card.name[locale] || card.name.en || id;
    } else if (typeof card.name === 'string') {
      return card.name;
    }
    return id;
  }

  function render() {
    const term = filterInput.value.toLowerCase();
    tbody.innerHTML = '';
    inventory
      .filter(item => getCardName(item.card_id).toLowerCase().includes(term))
      .forEach(item => {
        const tr = document.createElement('tr');
        const tdName = document.createElement('td');
        tdName.textContent = getCardName(item.card_id);
        const tdQty = document.createElement('td');
        tdQty.textContent = item.qty;
        tr.appendChild(tdName);
        tr.appendChild(tdQty);
        tbody.appendChild(tr);
      });
  }

  filterInput.addEventListener('input', render);
  document.addEventListener('i18n-loaded', render);
  await loadCards();
  await loadInventory();
});

