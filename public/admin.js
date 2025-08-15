document.addEventListener('DOMContentLoaded', () => {
  const token = localStorage.getItem('session_token');
  if (!token) {
    window.location.href = 'login.php';
    return;
  }
  const output = document.getElementById('admin_output');
  const usersDiv = document.getElementById('users');
  async function call(endpoint) {
    try {
      const res = await fetch(endpoint, { headers: { 'Authorization': `Bearer ${token}` } });
      output.textContent = await res.text();
    } catch (err) {
      console.error(err);
    }
  }
  
  async function loadUsers() {
    try {
      const res = await fetch('/api/admin/user_list.php', { headers: { 'Authorization': `Bearer ${token}` } });
      const data = await res.json();
      usersDiv.innerHTML = '';
      data.users.forEach(u => {
        const div = document.createElement('div');
        div.textContent = `${u.id} ${u.username} admin:${u.is_admin} points:${u.points}`;
        const updateBtn = document.createElement('button');
        updateBtn.textContent = 'Update';
        updateBtn.addEventListener('click', async () => {
          const username = prompt('Username', u.username);
          const isAdmin = confirm('Admin?');
          await fetch('/api/admin/user_update.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ id: u.id, username, is_admin: isAdmin ? 1 : 0 })
          });
          loadUsers();
        });
        const delBtn = document.createElement('button');
        delBtn.textContent = 'Delete';
        delBtn.addEventListener('click', async () => {
          if (!confirm('Delete user?')) return;
          await fetch('/api/admin/user_delete.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ id: u.id })
          });
          loadUsers();
        });
        const addBtn = document.createElement('button');
        addBtn.textContent = '+Pts';
        addBtn.addEventListener('click', async () => {
          const amt = parseInt(prompt('Points to add', '0'), 10);
          if (!Number.isNaN(amt) && amt > 0) {
            await fetch('/api/admin/user_points.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
              },
              body: JSON.stringify({ id: u.id, delta: amt })
            });
            loadUsers();
          }
        });
        const subBtn = document.createElement('button');
        subBtn.textContent = '-Pts';
        subBtn.addEventListener('click', async () => {
          const amt = parseInt(prompt('Points to deduct', '0'), 10);
          if (!Number.isNaN(amt) && amt > 0) {
            await fetch('/api/admin/user_points.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
              },
              body: JSON.stringify({ id: u.id, delta: -amt })
            });
            loadUsers();
          }
        });
        div.append(' ', updateBtn, ' ', delBtn, ' ', addBtn, ' ', subBtn);
        usersDiv.appendChild(div);
      });
    } catch (err) {
      console.error(err);
    }
  }
  document.getElementById('load_rulesets').addEventListener('click', () => call('/api/admin/ruleset_list.php'));
  document.getElementById('load_user_stats').addEventListener('click', () => call('/api/admin/user_stats.php'));
  document.getElementById('load_users').addEventListener('click', loadUsers);
});
