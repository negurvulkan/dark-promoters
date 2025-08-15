{assign var='title' value='Deck Builder'}
{capture name='content'}
  <h1 data-i18n="deckbuilder_title">{$title}</h1>
  <input type="text" id="deck_name" data-i18n-placeholder="deck_name_placeholder" placeholder="Deck name">
  <button id="save_btn" data-i18n="save_deck_button">Save Deck</button>
  <h2 data-i18n="inventory_header">Inventory</h2>
  <div id="inventory_list" class="inventory"></div>
  <h2 data-i18n="deck_header">Deck</h2>
  <div id="deck_list" class="deck dropzone"></div>
{/capture}
{include file='layout.tpl' title=$title css=['cards.css'] scripts=['i18n.js','auth.js','deckbuilder.js'] content=$smarty.capture.content}
