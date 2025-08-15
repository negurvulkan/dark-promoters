{assign var='title' value='Admin'}
{capture name='content'}
  <h1>Admin</h1>
  <nav>
    <button id="load_rulesets">Rulesets</button>
    <button id="load_user_stats">User Stats</button>
    <button id="load_users">Users</button>
  </nav>
  <pre id="admin_output"></pre>
  <div id="users"></div>
{/capture}
{include file='layout.tpl' title=$title scripts=['admin.js'] content=$smarty.capture.content}
