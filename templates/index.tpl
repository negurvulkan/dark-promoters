{assign var='title' value='Dark Promoters'}
{capture name='content'}
  <main class="container py-4">
    <h1 class="mb-4" data-i18n="welcome_title">{$title}</h1>
    <p data-i18n="intro_text">Organize gothic/alt-scene events and compete for the best show.</p>
    {if !$is_logged_in}
    <div id="auth_links" class="mt-3">
      <a href="public/login.php" class="btn btn-primary me-2" data-i18n="login_button">Login</a>
      <a href="public/register.php" class="btn btn-secondary" data-i18n="register_button">Register</a>
    </div>
    {/if}
  </main>
{/capture}
{include file='layout.tpl' title=$title css=['public/cards.css'] scripts=['public/i18n.js','public/auth.js','public/app.js'] content=$smarty.capture.content}
