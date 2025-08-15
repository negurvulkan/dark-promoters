{assign var='title' value='Login'}
{capture name='content'}
  <h1 data-i18n="login_title">{$title}</h1>
  <form id="login_form">
    <label><span data-i18n="username_label">Username:</span>
      <input type="text" name="username" required>
    </label>
    <br>
    <label><span data-i18n="password_label">Password:</span>
      <input type="password" name="password" required>
    </label>
    <br>
    <button type="submit" data-i18n="login_button">Login</button>
  </form>
  <p><span data-i18n="no_account">No account?</span> <a href="register.php" data-i18n="register_link">Register</a></p>
{/capture}
{include file='layout.tpl' title=$title scripts=['i18n.js','auth.js'] content=$smarty.capture.content}
