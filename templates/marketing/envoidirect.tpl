{* $Id: envoidirect.tpl,v 1.3 2004-08-26 09:22:22 x2000habouzit Exp $ *}

<div class="rubrique">
  Liste des sollicités inscrits récemment
</div>

{dynamic}

<table class="bicol" summary="liste des sollicités inscrits">
  <tr>
    <th>Date sollicitation</th>
    <th>Promo</th>
    <th>Nom</th>
    <th>Email</th>
    <th>Date inscription</th>
  </tr>
  {foreach from=$recents item=it}
  <tr class="{cycle values="pair,impair"}">
    <td class="center">{$it.date_envoi|date_format:"%e %b %Y"} (par {$it.sender|truncate:4:""})</td>
    <td class="center">
      <a href="promo.php?promo={$it.promo}">{$it.promo}</a>
    </td>
    <td>{$it.nom} {$it.prenom}</td>
    <td>
      <a href="mailto:{$it.email}">{$it.email}</a>
    </td>
    <td class="center">{$it.date_ins|date_format:"%e %b %Y"}</td>
  </tr>
  {/foreach}
</table>
<p>
{$nbrecents} Polytechniciens ont été sollicités et se sont inscrits.
</p>

<div class="rubrique">
  Liste des sollicités non inscrits
</div>

<table class="bicol" summary="liste des sollicités non inscrits">
  <tr>
    <th>Date sollicitation</th>
    <th>Promo</th>
    <th>Nom</th>
    <th>Email</th>
  </tr>
  {foreach from=$notsub item=it}
  <tr class="{cycle values="pair,impair"}">
    <td class="center">{$it.date_envoi|date_format:"%e %b %Y"} (par {$it.sender|truncate:4:""})</td>
    <td class="center">
      <a href="promo.php?promo={$it.promo}">{$it.promo}</a>
    </td>
    <td>{$it.nom} {$it.prenom}</td>
    <td>
      <a href="mailto:{$it.email}">{$it.email}</a>
    </td>
  </tr>
  {/foreach}
</table>

<p>
{$nbnotsub} Polytechniciens ont été sollicités et ne se sont toujours pas inscrits.
</p>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
