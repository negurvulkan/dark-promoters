document.addEventListener('DOMContentLoaded', () => {
  const token = localStorage.getItem('session_token');
  if (!token) {
    window.location.href = 'login.php';
    return;
  }

  const tbody = document.getElementById('matches_body');
  const form = document.getElementById('create_match_form');
  const nameInput = document.getElementById('match_name');
  const maxInput = document.getElementById('max_players');
  const creatorTpl = document.getElementById('creator_actions_template');
  let currentUser = null;

  async function loadMatches() {
    try {
      const res = await fetch('/api/matches_list.php', {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      const json = await res.json();
      currentUser = json.user_id;
      render(json.matches || []);
    } catch (err) {
      console.error(err);
    }
  }

  function render(matches) {
    tbody.innerHTML = '';
    matches.forEach(match => {
      const tr = document.createElement('tr');
      const tdName = document.createElement('td');
      tdName.textContent = match.name;
      const tdPlayers = document.createElement('td');
      const playersText = match.players
        .map(p => p.is_ai ? (window.i18n ? window.i18n.t('ai_bot') : 'AI Bot') : p.username)
        .join(', ');
      tdPlayers.textContent = `${match.players.length}/${match.max_players} ${playersText}`.trim();
      const tdActions = document.createElement('td');
      const joined = match.players.some(p => p.id === currentUser);
      const humanCount = match.players.filter(p => !p.is_ai).length;
      const aiCount = match.players.filter(p => p.is_ai).length;
      if (!joined && match.players.length < match.max_players) {
        const joinBtn = document.createElement('button');
        joinBtn.textContent = window.i18n ? window.i18n.t('join_button') : 'Join';
        joinBtn.addEventListener('click', async () => {
          try {
            await fetch('/api/matches_join.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
              },
              body: JSON.stringify({ match_id: match.id })
            });
            loadMatches();
          } catch (err) {
            console.error(err);
          }
        });
        tdActions.appendChild(joinBtn);
      }
      if (match.creator_id === currentUser) {
        const frag = creatorTpl.content.cloneNode(true);
        const addAiBtn = frag.querySelector('.add-ai-btn');
        const startBtn = frag.querySelector('.start-btn');
        addAiBtn.textContent = window.i18n ? window.i18n.t('add_ai_button') : 'Add AI';
        startBtn.textContent = window.i18n ? window.i18n.t('start_button') : 'Start';
        if (match.players.length < match.max_players) {
          addAiBtn.addEventListener('click', async () => {
            try {
              await fetch('/api/matches_add_ai.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ match_id: match.id })
              });
              loadMatches();
            } catch (err) {
              console.error(err);
            }
          });
        } else {
          addAiBtn.remove();
        }
        startBtn.disabled = humanCount < 1 || aiCount < 1;
        startBtn.addEventListener('click', async () => {
          try {
            await fetch('/api/matches_start.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
              },
              body: JSON.stringify({ match_id: match.id })
            });
            loadMatches();
          } catch (err) {
            console.error(err);
          }
        });
        tdActions.appendChild(frag);
      }
      tr.appendChild(tdName);
      tr.appendChild(tdPlayers);
      tr.appendChild(tdActions);
      tbody.appendChild(tr);
    });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = nameInput.value.trim();
    const maxPlayers = parseInt(maxInput.value, 10);
    try {
      await fetch('/api/new_match.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({ name, max_players: maxPlayers })
      });
      nameInput.value = '';
      loadMatches();
    } catch (err) {
      console.error(err);
    }
  });

  loadMatches();
  setInterval(loadMatches, 5000);
});
