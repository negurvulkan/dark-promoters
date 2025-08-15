{assign var='title' value='Dark Promoters Setup'}
{capture name='content'}
  {if $success}
    <h1>Setup Complete</h1>
    <p>Database configured and migrations applied.</p>
    <p><a href="index.php">Go to application</a></p>
  {else}
    <h1>Dark Promoters Setup</h1>
    {if $error}
      <p style="color:red;">Error: {$error|escape}</p>
    {/if}
    <form method="post">
      <label>DB host <input name="db_host" value="{$values.db_host|escape}"></label><br>
      <label>DB port <input name="db_port" value="{$values.db_port|escape}"></label><br>
      <label>DB name <input name="db_name" value="{$values.db_name|escape}"></label><br>
      <label>DB user <input name="db_user" value="{$values.db_user|escape}"></label><br>
      <label>DB pass <input type="password" name="db_pass"></label><br>
      <button type="submit">Run Setup</button>
    </form>
  {/if}
{/capture}
{include file='layout.tpl' title=$title content=$smarty.capture.content}

