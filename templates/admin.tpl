{assign var='title' value='Admin'}
{capture name='content'}
  <main class="container py-4">
    <h1 class="mb-4">Admin</h1>
    <nav class="mb-3">
      <button id="load_rulesets" class="btn btn-primary me-2">Rulesets</button>
      <button id="load_user_stats" class="btn btn-secondary me-2">User Stats</button>
      <button id="load_users" class="btn btn-secondary">Users</button>
    </nav>
    <pre id="admin_output" class="bg-dark text-white p-3"></pre>
    <div id="users"></div>
  </main>
{/capture}
{include file='layout.tpl' title=$title scripts=['admin.js'] content=$smarty.capture.content}
