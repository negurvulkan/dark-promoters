document.addEventListener('DOMContentLoaded', () => {
  const token = localStorage.getItem('session_token');
  if (!token) {
    window.location.href = 'login.php';
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
