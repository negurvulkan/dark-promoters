async function sendAuth(url, data) {
  const res = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  });
  return res.json();
}

document.addEventListener('DOMContentLoaded', () => {
  const authLinks = document.getElementById('auth_links');
  const userLinks = document.getElementById('user_links');
  const adminLink = document.getElementById('admin_link');
  if (authLinks && userLinks) {
    const token = localStorage.getItem('session_token');
    authLinks.style.display = token ? 'none' : '';
    userLinks.style.display = token ? '' : 'none';
    if (adminLink) {
      if (!token) {
        adminLink.style.display = 'none';
      } else {
        fetch('/api/admin/user_stats.php', {
          headers: { 'Authorization': `Bearer ${token}` }
        }).then(res => {
          adminLink.style.display = res.ok ? '' : 'none';
        }).catch(() => {
          adminLink.style.display = 'none';
        });
      }
    }
  }

  const loginForm = document.getElementById('login_form');
  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(loginForm);
      const payload = Object.fromEntries(formData.entries());
      try {
        const json = await sendAuth('/api/login.php', payload);
        if (json.session_token) {
          localStorage.setItem('session_token', json.session_token);
          window.location.href = 'index.html';
        } else {
          alert(json.error || (window.i18n ? window.i18n.t('login_failed') : 'Login failed'));
        }
      } catch (err) {
        console.error(err);
      }
    });
  }

  const registerForm = document.getElementById('register_form');
  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(registerForm);
      const payload = Object.fromEntries(formData.entries());
      try {
        const json = await sendAuth('/api/register.php', payload);
        if (json.session_token) {
          localStorage.setItem('session_token', json.session_token);
          window.location.href = 'index.html';
        } else {
          alert(json.error || (window.i18n ? window.i18n.t('registration_failed') : 'Registration failed'));
        }
      } catch (err) {
        console.error(err);
      }
    });
  }

  const logoutBtn = document.getElementById('logout_btn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
      const token = localStorage.getItem('session_token');
      try {
        await fetch('/api/logout.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
          },
          body: JSON.stringify({ session_token: token })
        });
      } catch (err) {
        console.error(err);
      } finally {
        localStorage.removeItem('session_token');
        window.location.href = 'login.html';
      }
    });
  }
});
