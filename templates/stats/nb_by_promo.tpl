{* $Id: nb_by_promo.tpl,v 1.1 2004-01-27 21:17:43 x2000habouzit Exp $ *}

<div class="rubrique">
  Inscrits par promo
</div>

<p class="normal">
Voici le nombre d'inscrits par promo :
</p>

{dynamic}
<table class="bicol" cellpadding="3" cellspacing="0" summary="Statistiques">
  <tr>
    <th></th>
    <th>0</th><th>1</th><th>2</th><th>3</th><th>4</th>
    <th>5</th><th>6</th><th>7</th><th>8</th><th>9</th>
  </tr>
  {foreach item=nb10 key=lustre from=$nbs}
  <tr>
    <th>{$lustre}-</th>
    {foreach item=nb from=$nb10}
    <td class="center">{$nb|default:"-"}</td>
    {/foreach}
  </tr>
  {/foreach}
</table>
{/dynamic}

<div class="rubrique">
  Inscrits par promo en (%)
</div>

<img src="/stats/graph-promo2.png" />

{* vim:set et sw=2 sts=2 sws=2: *}
