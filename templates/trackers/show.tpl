{* $Id: show.tpl,v 1.1 2004-02-23 18:04:33 x2000habouzit Exp $ *}

{dynamic}

<p class="normal">
  [<a href="index.php">Liste des trackers</a>]
  [<a href="post.php?tr_id={$smarty.get.tr_id}">Poster dans ce tracker</a>]
</p>

<div class="rubrique">
  Tracker {$tracker->name}
</div>
<table class="bicol" cellpadding="3">
  <tr>
    <th>Date</th>
    <th>Sujet</th>
    <th>Assigné à</th>
  </tr>
{foreach item=rq from=$requests}
  <tr class="pri{$rq.pri}">
    <td>{$rq.date|date_format:"%m&nbsp;%Y"}</td>
    <td><a href="show_rq.php?tr_id={$smarty.get.tr_id}&amp;rq_id={$rq.rq_id}">{$rq.summary}</a></td>
    <td class="right">{if $rq.username}<a href="mailto:{$rq.username}@polytechnique.org">{$rq.username}</a>{else}-{/if}</td>
  </tr>
{/foreach}
</table>
{/dynamic}

<br />
<div class="ssrubrique">
  Couleurs des priorités
</div>
<table summary="priorités">
  <tr>
    <td class="pri1">1</td>
    <td class="pri2">2</td>
    <td class="pri3">3</td>
    <td class="pri4">4</td>
    <td class="pri5">5</td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
