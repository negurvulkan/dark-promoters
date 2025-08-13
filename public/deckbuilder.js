document.addEventListener('DOMContentLoaded', () => {
  const token = localStorage.getItem('session_token');
  if (!token) {
    window.location.href = 'login.html';
    return;
  }

  const inventoryDiv = document.getElementById('inventory_list');
  const deckDiv = document.getElementById('deck_list');
  const deckNameInput = document.getElementById('deck_name');
  const saveBtn = document.getElementById('save_btn');

  let inventory = [];
  const deck = {};

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

  function renderInventory() {
    inventoryDiv.innerHTML = '';
    inventory.forEach(item => {
      const div = document.createElement('div');
      div.textContent = `${item.card_id} (${item.qty})`;
      div.draggable = true;
      div.dataset.cardId = item.card_id;
      div.addEventListener('dragstart', e => {
        e.dataTransfer.setData('text/plain', item.card_id);
      });
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
    const cid = e.dataTransfer.getData('text/plain');
    const inv = inventory.find(i => i.card_id === cid);
    if (!inv) return;
    const current = deck[cid] || 0;
    if (current < inv.qty) {
      deck[cid] = current + 1;
      renderDeck();
    }
  });

  saveBtn.addEventListener('click', async () => {
    const name = deckNameInput.value.trim();
    const cards = Object.entries(deck).map(([cid, qty]) => ({ card_id: cid, qty }));
    if (name === '') {
      alert('Name required');
      return;
    }
    try {
      const res = await fetch('/api/decks.php', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name, cards })
      });
      const json = await res.json();
      if (json.error) {
        alert(json.error);
      } else {
        alert('Deck saved');
        Object.keys(deck).forEach(k => delete deck[k]);
        deckNameInput.value = '';
        renderDeck();
      }
    } catch (err) {
      console.error(err);
    }
  });

  loadInventory();
});
