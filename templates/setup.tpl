{assign var='title' value='Dark Promoters Setup'}
{capture name='content'}
  <main class="container py-4">
    {if $success}
      <h1>Setup Complete</h1>
      <p>Database configured and migrations applied.</p>
      <p><a href="/index.php" class="btn btn-primary">Go to application</a></p>
    {else}
      <h1>Dark Promoters Setup</h1>
      {if $error}
        <p class="text-danger">Error: {$error|escape}</p>
      {/if}
      <form method="post">
        <div class="mb-3">
          <label class="form-label">DB host <input name="db_host" class="form-control" value="{$values.db_host|escape}"></label>
        </div>
        <div class="mb-3">
          <label class="form-label">DB port <input name="db_port" class="form-control" value="{$values.db_port|escape}"></label>
        </div>
        <div class="mb-3">
          <label class="form-label">DB name <input name="db_name" class="form-control" value="{$values.db_name|escape}"></label>
        </div>
        <div class="mb-3">
          <label class="form-label">DB user <input name="db_user" class="form-control" value="{$values.db_user|escape}"></label>
        </div>
        <div class="mb-3">
          <label class="form-label">DB pass <input type="password" name="db_pass" class="form-control"></label>
        </div>
        <button type="submit" class="btn btn-primary">Run Setup</button>
      </form>
    {/if}
  </main>
{/capture}
{include file='layout.tpl' title=$title content=$smarty.capture.content}

