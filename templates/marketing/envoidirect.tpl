{* $Id: envoidirect.tpl,v 1.4 2004-08-30 09:36:23 x2000habouzit Exp $ *}

<div class="rubrique">
  Liste des sollicités inscrits récemment
</div>

{dynamic}

<table class="bicol" summary="liste des sollicités inscrits">
  <tr>
    <th>Date</th>
    <th>Par</th>
    <th>Nom</th>
    <th>inscription</th>
  </tr>
  {foreach from=$recents item=it}
  <tr class="{cycle values="pair,impair"}">
    <td>{$it.date_envoi|date_format:"%e&nbsp;%b&nbsp;%y"}</td>
    <td>{$it.sender|lower|truncate:4:""}</td>
    <td>
      <a href="mailto:{$it.email}" title="{$it.email}">{$it.nom} {$it.prenom}</a>
      (x<a href="promo.php?promo={$it.promo}">{$it.promo}</a>)
    </td>
    <td>{$it.date_ins|date_format:"%e&nbsp;%b&nbsp;%y"}</td>
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
    <th>Date</th>
    <th>Par</th>
    <th>Nom</th>
  </tr>
  {foreach from=$notsub item=it}
  <tr class="{cycle values="pair,impair"}">
    <td>{$it.date_envoi|date_format:"%e&nbsp;%b&nbsp;%y"}</td>
    <td>{$it.sender|lower|truncate:4:""}</td>
    <td>
      <a href="mailto:{$it.email}" title="{$it.email}">{$it.nom} {$it.prenom}</a>
      (x<a href="promo.php?promo={$it.promo}">{$it.promo}</a>)
    </td>
  </tr>
  {/foreach}
</table>

<p>
{$nbnotsub} Polytechniciens ont été sollicités et ne se sont toujours pas inscrits.
</p>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
