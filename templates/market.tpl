{assign var='title' value='Market'}
{capture name='content'}
  <h1 data-i18n="market_title">{$title}</h1>
  <div>
    <span data-i18n="points_label">Points:</span> <span id="points">{$points|default:0}</span>
  </div>
  <table>
    <thead>
      <tr>
        <th data-i18n="pack_id_header">Pack ID</th>
        <th data-i18n="cost_header">Cost</th>
        <th data-i18n="buy_header">Buy</th>
      </tr>
    </thead>
    <tbody id="packs_body"></tbody>
  </table>
{/capture}
{include file='layout.tpl' title=$title show_logout=true scripts=['i18n.js','auth.js','market.js'] content=$smarty.capture.content}
