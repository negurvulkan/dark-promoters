{assign var='title' value='Register'}
{capture name='content'}
  <main class="container py-4">
    <h1 class="mb-4" data-i18n="register_title">{$title}</h1>
    <form id="register_form">
      <div class="mb-3">
        <label class="form-label"><span data-i18n="username_label">Username:</span>
          <input type="text" name="username" class="form-control" required>
        </label>
      </div>
      <div class="mb-3">
        <label class="form-label"><span data-i18n="password_label">Password:</span>
          <input type="password" name="password" class="form-control" required>
        </label>
      </div>
      <button type="submit" class="btn btn-primary" data-i18n="register_button">Register</button>
    </form>
    <p class="mt-3"><span data-i18n="already_have_account">Already have an account?</span> <a href="login.php" data-i18n="login_link">Login</a></p>
  </main>
{/capture}
{include file='layout.tpl' title=$title scripts=['i18n.js','auth.js'] content=$smarty.capture.content}
