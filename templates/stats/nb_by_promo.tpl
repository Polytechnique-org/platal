{* $Id: nb_by_promo.tpl,v 1.3 2004-08-26 14:44:46 x2000habouzit Exp $ *}

<div class="rubrique">
  Inscrits par promo
</div>

<p>
Voici le nombre d'inscrits par promo :
</p>

<table class="bicol" cellpadding="3" cellspacing="0" summary="Statistiques">
  <tr>
    <th></th>
    <th>0</th><th>1</th><th>2</th><th>3</th><th>4</th>
    <th>5</th><th>6</th><th>7</th><th>8</th><th>9</th>
  </tr>
{dynamic}
  {foreach item=nb10 key=lustre from=$nbs}
  <tr>
    <th>{$lustre}-</th>
    {foreach item=nb from=$nb10}
    <td class="center">
      {if $nb}
      <a href="{"stats/stats_promo.php?promo=`$nb.promo`"|url}">{$nb.nb}</a>
      {else}
      -
      {/if}
    </td>
    {/foreach}
  </tr>
  {/foreach}
{/dynamic}
</table>

<div class="rubrique">
  Inscrits par promo en (%)
</div>

<img src="/stats/graph-promo2.png" />

{* vim:set et sw=2 sts=2 sws=2: *}
