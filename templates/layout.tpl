<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{$title|escape}</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  {foreach from=$css item=href}
  <link rel="stylesheet" href="{$href}">
  {/foreach}
</head>
<body>
  <header class="container d-flex justify-content-between align-items-center py-3">
    <div class="d-flex align-items-center gap-2">
      <label for="lang-switch" class="mb-0" data-i18n="language_label">Language:</label>
      <select id="lang-switch" class="form-select form-select-sm w-auto">
        <option value="en">EN</option>
        <option value="de">DE</option>
      </select>
    </div>
    {if $is_logged_in}
    <nav class="navbar navbar-expand">
      <ul class="navbar-nav">
        {foreach from=$nav_links item=link}
        {if $link.key != 'nav_admin' || $show_admin}
        <li class="nav-item"><a class="nav-link" href="{$link.href}" data-i18n="{$link.key}">{$i18n[$link.key]}</a></li>
        {/if}
        {/foreach}
      </ul>
    </nav>
    {/if}
    {if $show_logout}
    <button id="logout_btn" class="btn btn-secondary btn-sm" data-i18n="logout_button">Logout</button>
    {/if}
  </header>
  {$content}
  {foreach from=$scripts item=src}
  <script src="{$src}"></script>
  {/foreach}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
