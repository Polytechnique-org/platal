{* $Id: volontaire.tpl,v 1.1 2004-07-17 13:08:38 x2000habouzit Exp $ *}

{dynamic}

{foreach from=$errros item=err}
<p class="erreur">{$err}</p>
{/foreach}

<div class="rubrique">
  Marketing volontaire
</div>

<div class="ssrubrique">
  Adresses neuves
</div>

<table class="bicol" cellpadding="3" summary="Adresses neuves">
  <tr>
    <th>Camarade concerné</th>
    <th>Adresse email</th>
    <th>Camarade "informateur"</th>
    <th>Dernière adresse connue</th>
    <th>Lui écrire ?</th>
  </tr>
  {foreach from=$neuves item=it}
  <tr class="{cycle values="pair,impair"}">
    <td>{$it.nom} {$it.prenom} (X{$it.promo})</td>
    <td>{$it.email}</td>
    <td>{$it.snom} {$it.sprenom} (X{$it.spromo})</td>
    <td>{$it.last_known_email}</td>
    <td>
      {if $it.mailperso}
      <a href="utilisateurs.php?xmat={$it.dest}&amp;sender={$it.expe}&amp;from={$it.sprenom}%20{$it.snom}%20<{$it.susername}&#64;polytechnique.org>&amp;mail={$it.email}&amp;submit=Mailer">Perso</a>
      {else}
      <a href="utilisateurs.php?xmat={$it.dest}&amp;sender={$it.expe}&amp;from=Equipe%20Polytechnique.org%20<register&#64;polytechnique.org>&amp;mail={$it.email}&amp;submit=Mailer">Equipe</a>
      {/if}
      <a href="{$smarty.server.PHP_SELF}?done={$it.id}">Fait !</a>
      <a href="{$smarty.server.PHP_SELF}?del={$it.id}">Del</a>
    </td>
  </tr>
  {/foreach}
</table>

<br />
<br />

<div class="ssrubrique">
  Adresses déjà utilisées
</div>

<table class="bicol" cellpadding="3" summary="Adresses déjà utilisées">
  <tr>
    <th>Camarade concerné</th>
    <th>Adresse email</th>
    <th>Camarade "informateur"</th>
    <th>inscrit?</th>
  </tr>
  {foreach from=$neuves item=it}
  <tr class="{cycle values="pair,impair"}">
    <td>{$it.nom} {$it.prenom} (X{$it.promo})</td>
    <td>{$it.email}</td>
    <td>{$it.snom} {$it.sprenom} (X{$it.spromo})</td>
    <td>{if $it.inscrit}OUI{else}NON{/if}</td>
  </tr>
  {/foreach}
</table>

<p>
{$rate.j} inscrits sur {$rate.i} sollicités, soit {$rate.rate}% de succès.
</p>
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
