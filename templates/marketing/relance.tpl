{* $Id: relance.tpl,v 1.1 2004-07-17 14:16:48 x2000habouzit Exp $ *}

<div class="rubrique">
  Relance
</div>

{dynamic}
{foreach from=$sent item=l}
<p>{$l}</p>
{/foreach}

<form action="{$smarty.server.PHP_SELF}" method="post">
  <table class="bicol" summary="liste des inscriptions non confirmées">
    <tr>
      <th>Date</th>
      <th>Promo</th> 
      <th>Nom</th>
      <th>Dernière relance</th>
      <th>&nbsp;</th>
    </tr>
    {foreach from=$relance item=it}
    <tr class="{cycle values="pair,impair"}">
      <td class="center">{$it.date}</td>
      <td class="center">{$it.promo}</td>
      <td>{$it.nom} {$it.prenom}</td>
      <td class="center">
        {if $it.relance eq "0000-00-00"}Jamais{else}{$it.relance}{/if}
      </td>
      <td class="center">
        <input type="checkbox" name="{$it.matricule}" value="1" />
      </td>
    </tr>
    {/foreach}
  </table>

  <p>
  {$nb} Polytechniciens n'ont pas effectué jusqu'au bout leur inscription.
  </p>
  <div class="center">
    <input type="submit" name="relancer" value="Relancer" />
  </div>
</form>
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
