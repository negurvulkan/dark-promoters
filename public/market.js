document.addEventListener('DOMContentLoaded', () => {
  const token = localStorage.getItem('session_token');
  if (!token) {
    window.location.href = 'login.php';
    return;
  }

  const pointsEl = document.getElementById('points');
  const tbody = document.getElementById('packs_body');
  let packs = [];

    async function load() {
      try {
        const res = await fetch('/api/market.php', {
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
          alert(json.error || (window.i18n ? window.i18n.t('load_failed') : 'Failed to load market'));
        }
        return;
      }
      if (typeof json.points === 'number') {
        pointsEl.textContent = json.points;
      }
      packs = Array.isArray(json.packs) ? json.packs : [];
      render();
    } catch (err) {
      console.error(err);
    }
  }

  async function buy(id) {
    try {
        const res = await fetch('/api/market.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
          credentials: 'same-origin',
          body: JSON.stringify({ pack_id: id })
        });
      const json = await res.json();
      if (json.error) {
        alert(json.error || (window.i18n ? window.i18n.t('purchase_failed') : 'Purchase failed'));
        return;
      }
      if (typeof json.points === 'number') {
        pointsEl.textContent = json.points;
      }
      if (json.awarded) {
        alert('Received: ' + json.awarded.join(', '));
      }
    } catch (err) {
      console.error(err);
    }
  }

  function render() {
    tbody.innerHTML = '';
    packs.forEach(p => {
      const tr = document.createElement('tr');
      const tdId = document.createElement('td');
      tdId.textContent = p.id;
      const tdCost = document.createElement('td');
      tdCost.textContent = p.cost;
      const tdBuy = document.createElement('td');
      const btn = document.createElement('button');
      btn.textContent = window.i18n ? window.i18n.t('buy_button') : 'Buy';
      btn.addEventListener('click', () => buy(p.id));
      tdBuy.appendChild(btn);
      tr.appendChild(tdId);
      tr.appendChild(tdCost);
      tr.appendChild(tdBuy);
      tbody.appendChild(tr);
    });
  }

  document.addEventListener('i18n-loaded', render);
  load();
});
