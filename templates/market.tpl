{assign var='title' value='Market'}
{capture name='content'}
  <main class="container py-4">
    <h1 class="mb-4" data-i18n="market_title">{$title}</h1>
    <div class="mb-3">
      <span data-i18n="points_label">Points:</span> <span id="points">{$points|default:0}</span>
    </div>
    <table class="table table-dark table-striped">
      <thead>
        <tr>
          <th data-i18n="pack_id_header">Pack ID</th>
          <th data-i18n="cost_header">Cost</th>
          <th data-i18n="buy_header">Buy</th>
        </tr>
      </thead>
      <tbody id="packs_body"></tbody>
    </table>
  </main>
{/capture}
{include file='layout.tpl' title=$title show_logout=true scripts=['i18n.js','auth.js','market.js'] content=$smarty.capture.content}
