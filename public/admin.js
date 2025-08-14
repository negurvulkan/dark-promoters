document.addEventListener('DOMContentLoaded', () => {
  const token = localStorage.getItem('session_token');
  if (!token) {
    window.location.href = 'login.html';
    return;
  }
  const output = document.getElementById('admin_output');
  async function call(endpoint) {
    try {
      const res = await fetch(endpoint, { headers: { 'Authorization': `Bearer ${token}` } });
      output.textContent = await res.text();
    } catch (err) {
      console.error(err);
    }
  }
  document.getElementById('load_rulesets').addEventListener('click', () => call('/api/admin/ruleset_list.php'));
  document.getElementById('load_user_stats').addEventListener('click', () => call('/api/admin/user_stats.php'));
});
