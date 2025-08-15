{assign var='title' value='Register'}
{capture name='content'}
  <h1 data-i18n="register_title">{$title}</h1>
  <form id="register_form">
    <label><span data-i18n="username_label">Username:</span>
      <input type="text" name="username" required>
    </label>
    <br>
    <label><span data-i18n="password_label">Password:</span>
      <input type="password" name="password" required>
    </label>
    <br>
    <button type="submit" data-i18n="register_button">Register</button>
  </form>
  <p><span data-i18n="already_have_account">Already have an account?</span> <a href="login.php" data-i18n="login_link">Login</a></p>
{/capture}
{include file='layout.tpl' title=$title scripts=['i18n.js','auth.js'] content=$smarty.capture.content}
