{assign var='title' value='Game'}
{capture name='content'}
  <main class="container py-4">
    <section id="table" class="card-grid mb-4"></section>
    <section id="player-hand" class="card-grid mb-4"></section>
    <section id="log" class="mt-4"></section>
  </main>
{/capture}
{include file='layout.tpl' title=$title css=['cards.css'] scripts=['i18n.js','game.js'] content=$smarty.capture.content}
