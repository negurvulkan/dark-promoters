{assign var='title' value='Game'}
{capture name='content'}
  <main>
    <section id="table" class="card-grid"></section>
    <section id="player-hand" class="card-grid"></section>
    <section id="log"></section>
  </main>
{/capture}
{include file='layout.tpl' title=$title css=['cards.css'] scripts=['i18n.js','game.js'] content=$smarty.capture.content}
