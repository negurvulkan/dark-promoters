{assign var='title' value='Dark Promoters'}
{capture name='content'}
  <main class="container py-4">
    <h1 class="mb-4" data-i18n="welcome_title">{$title}</h1>
    <p data-i18n="intro_text">Organize gothic/alt-scene events and compete for the best show.</p>
    {if $is_logged_in}
    <div id="user_links" class="mt-3">
      {foreach from=$user_links item=link}
      <a href="{$link.href}" class="btn btn-primary me-2 mb-2" data-i18n="{$link.key}">{$link.text}</a>
      {/foreach}
      {if $show_admin}
      <a id="admin_link" href="admin.php" class="btn btn-warning mb-2">Admin</a>
      {/if}
    </div>
    {else}
    <div id="auth_links" class="mt-3">
      <a href="login.php" class="btn btn-primary me-2" data-i18n="login_button">Login</a>
      <a href="register.php" class="btn btn-secondary" data-i18n="register_button">Register</a>
    </div>
    {/if}
  </main>
{/capture}
{include file='layout.tpl' title=$title css=['cards.css'] scripts=['i18n.js','auth.js','app.js'] content=$smarty.capture.content}
