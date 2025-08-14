document.addEventListener('DOMContentLoaded', () => {
  const token = localStorage.getItem('session_token');
  if (!token) {
    window.location.href = 'login.html';
    return;
  }

  const filterInput = document.getElementById('filter');
  const tbody = document.getElementById('inventory_body');
  const pointsEl = document.getElementById('points');
  let inventory = [];

  async function loadInventory() {
    try {
      const res = await fetch('/api/inventory.php', {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      const json = await res.json();
      inventory = json.inventory || [];
      if (pointsEl) {
        pointsEl.textContent = json.points || 0;
      }
      render();
    } catch (err) {
      console.error(err);
    }
  }

  function render() {
    const term = filterInput.value.toLowerCase();
    tbody.innerHTML = '';
    inventory
      .filter(item => item.card_id.toLowerCase().includes(term))
      .forEach(item => {
        const tr = document.createElement('tr');
        const tdId = document.createElement('td');
        tdId.textContent = item.card_id;
        const tdQty = document.createElement('td');
        tdQty.textContent = item.qty;
        tr.appendChild(tdId);
        tr.appendChild(tdQty);
        tbody.appendChild(tr);
      });
  }

  filterInput.addEventListener('input', render);
  loadInventory();
});
