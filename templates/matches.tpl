{assign var='title' value='Matches'}
{capture name='content'}
  <h1 data-i18n="matches_title">{$title}</h1>
  <form id="create_match_form">
    <label for="match_name" data-i18n="match_name_label">Name:</label>
    <input type="text" id="match_name" name="name" required>
    <label for="max_players" data-i18n="max_players_label">Max players:</label>
    <input type="number" id="max_players" name="max_players" min="2" max="4" value="4" required>
    <button type="submit" data-i18n="create_match_button">Create Match</button>
  </form>
  <table>
    <thead>
      <tr>
        <th data-i18n="match_name_header">Match</th>
        <th data-i18n="players_header">Players</th>
        <th data-i18n="actions_header">Actions</th>
      </tr>
    </thead>
    <tbody id="matches_body"></tbody>
  </table>
{/capture}
{include file='layout.tpl' title=$title show_logout=true scripts=['i18n.js','auth.js','matches.js'] content=$smarty.capture.content}
