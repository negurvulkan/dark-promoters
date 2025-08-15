{assign var='title' value='Deck Builder'}
{capture name='content'}
  <main class="container py-4">
    <h1 class="mb-4" data-i18n="deckbuilder_title">{$title}</h1>
    <select id="deck_list_select" class="form-select mb-3"></select>
    <input type="text" id="deck_name" class="form-control mb-3" data-i18n-placeholder="deck_name_placeholder" placeholder="Deck name">
    <button id="save_btn" class="btn btn-primary mb-4" data-i18n="save_deck_button">Save Deck</button>
    <button id="delete_btn" class="btn btn-danger mb-4" style="display:none" data-i18n="delete_deck_button">Delete Deck</button>
    <h2 class="mt-4" data-i18n="inventory_header">Inventory</h2>
    <div id="inventory_list" class="inventory"></div>
    <h2 class="mt-4" data-i18n="deck_header">Deck</h2>
    <div id="deck_list" class="deck dropzone"></div>
  </main>
{/capture}
{include file='layout.tpl' title=$title css=['cards.css'] scripts=['i18n.js','auth.js','deckbuilder.js'] content=$smarty.capture.content}
