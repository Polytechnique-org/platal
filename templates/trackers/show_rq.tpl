{* $Id: show_rq.tpl,v 1.2 2004-04-26 14:17:19 x2000habouzit Exp $ *}

{dynamic}

<p class="normal">
  [<a href="index.php">Liste des trackers</a>]
  [<a href="show.php?tr_id={$smarty.get.tr_id}">Revenir au tracker</a>]
</p>

<div class="rubrique">
  {$request.summary} (posté le {$request.date|date_format:"%d %b %Y"})
</div>

<form action="{$smarty.server.REQUEST_URI}" method="post">
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
    <tr><th colspan="4">Changer des propriétés</th></tr>
    <tr class="impair">
      <td class="center">
        <select name="n_pri">
          <option value="5" {if $request.pri eq 5}selected="selected"{/if}>{$tracker->pris[5]}</option>
          <option value="4" {if $request.pri eq 4}selected="selected"{/if}>{$tracker->pris[4]}</option>
          <option value="3" {if $request.pri eq 3}selected="selected"{/if}>{$tracker->pris[3]}</option>
          <option value="2" {if $request.pri eq 2}selected="selected"{/if}>{$tracker->pris[2]}</option>
          <option value="1" {if $request.pri eq 1}selected="selected"{/if}>{$tracker->pris[1]}</option>
        </select>
      </td>
      <td class="center">
        {if $request.username}
        <a href="mailto:{$request.username}@polytechnique.org">{$request.username}</a>
        {else}-{/if}
      </td>
      <td class="center">
        <select name="n_admin">
          <option value="">-</option>
          {foreach item=a from=$admins}
          <option value="{$a.user_id}" {if $a.username eq $request.admin}selected="selected"{/if}>{$a.username}</option>
          {/foreach}
        </select>
      </td>
      <td class="center">
        {$request.state}
      </td>
    </tr>
    <tr>
      <td class="center" colspan="4">
        <input type="submit" value="modifier" name="n_sub" />
      </td>
    </tr>
  </table>
</form>

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
  <tr><th>{$fup.username}&nbsp;&nbsp;&nbsp;<span class="smaller">le {$fup.date|date_format:"%d %b %Y"}</span></th></tr>
  <tr><td><tt>{$fup.texte|escape|nl2br}</tt></td></tr>
  {/foreach}
</table>
<p class="normal">
  [<a href="answer.php?tr_id={$smarty.get.tr_id}&amp;rq_id={$smarty.get.rq_id}">Répondre</a>]
</p>
{/if}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
