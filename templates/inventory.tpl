{assign var='title' value='Inventory'}
{capture name='content'}
  <h1 data-i18n="inventory_title">{$title}</h1>
  <div>
    <span data-i18n="points_label">Points:</span> <span id="points">{$points|default:0}</span>
  </div>
  <input type="text" id="filter" data-i18n-placeholder="filter_placeholder" placeholder="Filter cards">
  <table>
    <thead>
      <tr><th data-i18n="card_id_header">Card ID</th><th data-i18n="quantity_header">Quantity</th></tr>
    </thead>
    <tbody id="inventory_body"></tbody>
  </table>
{/capture}
{include file='layout.tpl' title=$title show_logout=true scripts=['i18n.js','auth.js','inventory.js'] content=$smarty.capture.content}
