{* $Id: liste_domaines.tpl,v 1.3 2004-08-26 14:44:44 x2000habouzit Exp $ *}

{dynamic}
{$result}

{if $nb_dom}
<div class="rubrique">
Administrer le routage email sur ton(tes) domaine(s)
</div>

<p>
  Voici le(s) domaine(s) dont tu es administrateur.
  Pour administrer un domaine, il te suffit à l'heure actuelle de cliquer sur son nom.
  Cependant, prends bien note que cette administration se fera bientôt depuis le site www.polytechnique.net.
</p>

<div class="right">
{foreach item=dom from=$domaines}
  <a href="{"domaine.php?domaine=$dom"|escape:"url"|url}">{$dom}</a>
  <br />
{/foreach}
</div>
{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
