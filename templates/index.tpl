{assign var='title' value='Dark Promoters'}
{capture name='content'}
  <main>
    <h1 data-i18n="welcome_title">{$title}</h1>
    <p data-i18n="intro_text">Organize gothic/alt-scene events and compete for the best show.</p>
    {if $is_logged_in}
    <p id="user_links">
      {foreach from=$user_links item=link}
      <a href="{$link.href}" data-i18n="{$link.key}">{$link.text}</a>
      {/foreach}
      {if $show_admin}
      <a id="admin_link" href="admin.html">Admin</a>
      {/if}
    </p>
    {else}
    <p id="auth_links">
      <a href="login.html" data-i18n="login_button">Login</a>
      <a href="register.html" data-i18n="register_button">Register</a>
    </p>
    {/if}
  </main>
{/capture}
{include file='layout.tpl' title=$title css=['cards.css'] scripts=['i18n.js','auth.js','app.js'] content=$smarty.capture.content}
