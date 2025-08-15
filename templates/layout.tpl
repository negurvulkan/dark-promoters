<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{$title|escape}</title>
  {foreach from=$css item=href}
  <link rel="stylesheet" href="{$href}">
  {/foreach}
</head>
<body>
  <header>
    <label for="lang-switch" data-i18n="language_label">Language:</label>
    <select id="lang-switch">
      <option value="en">EN</option>
      <option value="de">DE</option>
    </select>
    {if $show_logout}
    <button id="logout_btn" data-i18n="logout_button">Logout</button>
    {/if}
  </header>
  {$content}
  {foreach from=$scripts item=src}
  <script src="{$src}"></script>
  {/foreach}
</body>
</html>
