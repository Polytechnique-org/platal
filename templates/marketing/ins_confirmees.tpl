{* $Id: ins_confirmees.tpl,v 1.4 2004-08-29 16:02:40 x2000habouzit Exp $ *}

{dynamic}

<table class="bicol" summary="liste des nouveaux inscrits">
  <tr>
    <th>Inscription</th>
    <th>Promo</th>
    <th>Nom</th>
  </tr>
{foreach item=in from=$ins}
  <tr class="{cycle values="impair,pair"}">
    <td class="center">{$in.date_ins|date_format:"%d/%m/%Y - %H:%M"}</td>
    <td class="center">
      <a href="promo.php?promo={$in.promo}">{$in.promo}</a>
    </td>
    <td>
      <a href="javascript:x()"  onclick="popWin('{"fiche.php"|url}?user={$in.username}')">
        {$in.nom} {$in.prenom}</a>
    </td>
  </tr>
{/foreach}
</table>

<br />
<div class="right">
  [<a href="{$smarty.server.PHP_SELF}?sort=date_ins">par date</a>]
  [<a href="{$smarty.server.PHP_SELF}?sort=promo">par promo</a>]
</div>
<p>
{$nb_ins} Polytechniciens se sont inscrits depuis le début de la semaine !
</p>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
