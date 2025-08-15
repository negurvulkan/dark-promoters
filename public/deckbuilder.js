document.addEventListener('DOMContentLoaded', () => {
  const token = localStorage.getItem('session_token');
  if (!token) {
    window.location.href = 'login.php';
    return;
  }

  const inventoryDiv = document.getElementById('inventory_list');
  const deckDiv = document.getElementById('deck_list');
  const deckNameInput = document.getElementById('deck_name');
  const deckListSelect = document.getElementById('deck_list_select');
  const saveBtn = document.getElementById('save_btn');

  let inventory = [];
  const deck = {};
  let currentDeckId = null;

  async function loadInventory() {
    try {
      const res = await fetch('/api/inventory.php', {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      const json = await res.json();
      inventory = json.inventory || [];
      renderInventory();
    } catch (err) {
      console.error(err);
    }
  }

  function addCardToDeck(cid) {
    const inv = inventory.find(i => i.card_id === cid);
    if (!inv) return;
    const current = deck[cid] || 0;
    if (current < inv.qty) {
      deck[cid] = current + 1;
      renderDeck();
    }
  }

  function renderInventory() {
    inventoryDiv.innerHTML = '';
    inventory.forEach(item => {
      const div = document.createElement('div');
      div.textContent = `${item.card_id} (${item.qty})`;
      div.draggable = true;
      div.dataset.cardId = item.card_id;
      div.addEventListener('dragstart', e => {
        e.dataTransfer.setData('card_id', item.card_id);
      });
      div.addEventListener('click', () => addCardToDeck(item.card_id));
      inventoryDiv.appendChild(div);
    });
  }

  function renderDeck() {
    deckDiv.innerHTML = '';
    Object.entries(deck).forEach(([cid, qty]) => {
      const div = document.createElement('div');
      div.textContent = `${cid} x${qty}`;
      div.addEventListener('click', () => {
        deck[cid]--;
        if (deck[cid] <= 0) delete deck[cid];
        renderDeck();
      });
      deckDiv.appendChild(div);
    });
  }

  deckDiv.addEventListener('dragover', e => e.preventDefault());
  deckDiv.addEventListener('drop', e => {
    e.preventDefault();
    const cid = e.dataTransfer.getData('card_id');
    if (cid) addCardToDeck(cid);
  });

  async function loadDecks() {
    try {
      const res = await fetch('/api/decks.php', {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      const json = await res.json();
      deckListSelect.innerHTML = '';
      const opt = document.createElement('option');
      opt.value = '';
      opt.textContent = 'New Deck';
      deckListSelect.appendChild(opt);
      (json.decks || []).forEach(d => {
        const o = document.createElement('option');
        o.value = d.id;
        o.textContent = `${d.name} (${d.card_count})`;
        deckListSelect.appendChild(o);
      });
    } catch (err) {
      console.error(err);
    }
  }

  deckListSelect.addEventListener('change', async () => {
    const id = deckListSelect.value;
    if (!id) {
      currentDeckId = null;
      deckNameInput.value = '';
      Object.keys(deck).forEach(k => delete deck[k]);
      renderDeck();
      return;
    }
    try {
      const res = await fetch(`/api/decks.php?id=${id}`, {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      const json = await res.json();
      currentDeckId = json.id;
      deckNameInput.value = json.name || '';
      Object.keys(deck).forEach(k => delete deck[k]);
      (json.cards || []).forEach(c => {
        deck[c.card_id] = c.qty;
      });
      renderDeck();
    } catch (err) {
      console.error(err);
    }
  });

  saveBtn.addEventListener('click', async () => {
    const name = deckNameInput.value.trim();
    const cards = Object.entries(deck).map(([cid, qty]) => ({ card_id: cid, qty }));
    if (name === '') {
      alert('Name required');
      return;
    }
    const method = currentDeckId ? 'PUT' : 'POST';
    const body = currentDeckId ? { id: currentDeckId, name, cards } : { name, cards };
    try {
      const res = await fetch('/api/decks.php', {
        method,
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(body)
      });
      const json = await res.json();
      if (json.error) {
        alert(json.error);
      } else {
        alert('Deck saved');
        Object.keys(deck).forEach(k => delete deck[k]);
        deckNameInput.value = '';
        currentDeckId = null;
        renderDeck();
        await loadDecks();
        deckListSelect.value = '';
      }
    } catch (err) {
      console.error(err);
    }
  });

  loadInventory();
  loadDecks();
});
