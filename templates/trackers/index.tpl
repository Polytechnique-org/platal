{* $Id: index.tpl,v 1.1 2004-02-22 21:04:23 x2000habouzit Exp $ *}

<div class="rubrique">
  Liste des trackers publics
</div>

<table class="bicol" summary="Liste des trackers">
  <tr>
    <th>Tracker</th>
    <th>Description</th>
    <th>Géré&nbsp;par</th>
  </tr>
{foreach item=t from=$trackers}
  <tr class="{cycle values="impair,pair"}">
    <td><a href="{"tracker_show.php?tr_id=`$t.tr_id`"|url}">{$t.tr_name}</a></td>
    <td>{$t.description}</td>
    <td class="right"><a href="mailto:{$t.ml_name}">{$t.short}</a></td>
  </tr>
{/foreach}
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
