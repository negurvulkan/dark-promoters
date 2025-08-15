{assign var='title' value='Matches'}
{capture name='content'}
  <main class="container py-4">
    <h1 class="mb-4" data-i18n="matches_title">{$title}</h1>
    <form id="create_match_form" class="row g-3 mb-4">
      <div class="col-md-6">
        <label for="match_name" class="form-label" data-i18n="match_name_label">Name:</label>
        <input type="text" id="match_name" name="name" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label for="max_players" class="form-label" data-i18n="max_players_label">Max players:</label>
        <input type="number" id="max_players" name="max_players" class="form-control" min="2" max="4" value="4" required>
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100" data-i18n="create_match_button">Create Match</button>
      </div>
    </form>
    <table class="table table-dark table-striped">
      <thead>
        <tr>
          <th data-i18n="match_name_header">Match</th>
          <th data-i18n="players_header">Players</th>
          <th data-i18n="actions_header">Actions</th>
        </tr>
      </thead>
      <tbody id="matches_body"></tbody>
    </table>
    <template id="creator_actions_template">
      <button type="button" class="add-ai-btn"></button>
      <button type="button" class="start-btn"></button>
    </template>
  </main>
{/capture}
{include file='layout.tpl' title=$title show_logout=true scripts=['i18n.js','auth.js','matches.js'] content=$smarty.capture.content}
