{* $Id: show_rq.tpl,v 1.1 2004-02-23 21:50:38 x2000habouzit Exp $ *}

{dynamic}

<p class="normal">
  [<a href="index.php">Liste des trackers</a>]
  [<a href="show.php?tr_id={$smarty.get.tr_id}">Revenir au tracker</a>]
</p>

<div class="rubrique">
  {$request.summary} (posté le {$request.date|date_format:"%d.%m.%Y"})
</div>

<table class="bicol">
  <tr>
    <th>priorité</th>
    <th>Soumis par</th>
    <th>Assigné à </th>
    <th>Etat actuel</th>
  </tr>
  <tr class="impair">
    <td class="center">
      {$tracker->pris[$request.pri]}
    </td>
    <td class="center">
      {if $request.username}
      <a href="mailto:{$request.username}@polytechnique.org">{$request.username}</a>
      {else}-{/if}
    </td>
    <td class="center">
      {if $request.admin}
      <a href="mailto:{$request.admin}@polytechnique.org">{$request.admin}</a>
      {else}-{/if}
    </td>
    <td class="center">
      {$request.state}
    </td>
  </tr>
  <tr><th colspan="4">Texte posté</th></tr>
  <tr><td colspan="4"><tt>{$request.texte|escape|nl2br}</tt></td></tr>
</table>

<br />

<div class="rubrique">
  Réponses
</div>
<p class="normal">
  [<a href="answer.php?tr_id={$smarty.get.tr_id}&amp;rq_id={$smarty.get.rq_id}">Répondre</a>]
</p>
{if $fups}
<table class="bicol">
  {foreach item=fup from=$fups}
  <tr><th>{$fup.username}&nbsp;&nbsp;&nbsp;<span class="smaller">le {$fup.date|date_format:"%d.%m.%Y"}</span></th></tr>
  <tr><td><tt>{$fup.texte|escape|nl2br}</tt></td></tr>
  {/foreach}
</table>
<p class="normal">
  [<a href="answer.php?tr_id={$smarty.get.tr_id}&amp;rq_id={$smarty.get.rq_id}">Répondre</a>]
</p>
{/if}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
