{* $Id: stats_promo.tpl,v 1.1 2004-02-04 19:47:48 x2000habouzit Exp $ *}

{dynamic}
<div class="rubrique">
  Statistiques de la promotion {$promo}
</div>

<div class="ssrubrique">
  Nombre d'inscrits de la promotion {$promo}
</div>

<div class="center">
  <img src="{"stats/graph_promo.php?promo=$promo"|url}" alt=" [ INSCRITS ] " width="640" height="480">
</div>
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
