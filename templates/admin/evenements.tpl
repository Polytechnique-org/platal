{* $Id: evenements.tpl,v 1.2 2004-07-19 12:09:32 x2000habouzit Exp $ *}

{dynamic}

<div class="rubrique">
  Gestion des événements :
  {if $arch}
  [&nbsp;<a href="{$smarty.server.PHP_SELF}?arch=0">Actualités</a>&nbsp;|&nbsp;Archives&nbsp;]
  {else}
  [&nbsp;Actualités&nbsp;|&nbsp;<a href="{$smarty.server.PHP_SELF}?arch=1">Archives</a>&nbsp;]
  {/if}
</div>

{foreach from=$err item=e}
<p class="erreur">{$e|nl2br}</p>
{/foreach}

{if $mode}

<form action="{$smarty.server.PHP_SELF}" method="post" name="evenement_nouveau">
  <input type="hidden" name="evt_id" value="{$smarty.post.evt_id}" />
  <table class="bicol">
    <tr>
      <th colspan="2">Contenu du message</th>
    </tr>
    <tr>
      <td><strong>Titre</strong></td>
      <td>
        <input type="text" name="titre" size="50" maxlength="200" value="{$titre}" />
      </td>
    </tr>
    <tr>
      <td><strong>Texte</strong></td>
      <td><textarea name="texte" rows="10" cols="60">{$texte}</textarea></td>
    </tr>
  </table>

  <br />

  <table class="bicol">
    <tr>
      <th colspan="2">Informations complémentaires</th>
    </tr>
    <tr>
      <td>
        <strong>Promo min *</strong> (incluse)
      </td>
      <td>
        <input type="text" name="promo_min" size="4" maxlength="4" value="{$promo_min}" />
        &nbsp;<em>0 signifie pas de minimum</em>
      </td>
    </tr>
    <tr>
      <td>
        <strong>Promo max *</strong> (incluse)
      </td>
      <td>
        <input type="text" name="promo_max" size="4" maxlength="4" value="{$promo_max}" />
        &nbsp;<em>0 signifie pas de maximum</em>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        * sert à limiter l'affichage de l'annonce aux camarades appartenant à certaines promos seulement.
      </td>
    </tr>
    <tr>
      <td>
        <strong>Dernier jour d'affichage</strong>
      </td>
      <td>
        <select name="peremption">
          {$select}
        </select>
      </td>
    </tr>
    <tr>
      <td><strong>Message pour le validateur</strong></td>
      <td><textarea name="validation_message" cols="50" rows="7">{$validation_message}</textarea></td>
    </tr>
  </table>

  <br />

  <div class="center">
    <input type="submit" name="action" value="Proposer" />
  </div>

</form>


{else}

{foreach from=$evs item=ev}
<table class="bicol">
  <tr>
    <th>
      Posté par <a href="javascript:x()"  onclick="popWin('../x.php?x=$username')">{$ev.prenom} {$ev.nom} (X{$ev.promo})</a>
      <a href="mailto:{$ev.username}@m4x.org">lui écrire</a>
    </th>
  </tr>
  <tr class="{if $ev.fvalide}impair{else}pair{/if}">
    <td>
      <strong>{$ev.titre}</strong><br />
      {$ev.texte|nl2br}<br />
      Création : {$ev.creation_date}<br />
      {if $ev.fvalide}
      Validation : {$ev.validation_date}<br />
      {/if}
      Péremption : {$ev.peremption}<br />
      Promos : {$ev.promo_min} - {$ev.promo_max}<br />
      Message : {$ev.validation_message}
    </td>
  </tr>
  <tr>
    <th>
      <form action="{$smarty.server.PHP_SELF}" method="post" name="modif">
        <input type="hidden" name="evt_id" value="{$ev.id}" />
        <input type="hidden" name="arch" value="{$ev.arch}" />
        {if $ev.farch}
        <input type="submit" name="action" value="Desarchiver" />
        {else}
        <input type="submit" name="action" value="Editer" />
        {if $ev.fvalide}
        <input type="submit" name="action" value="Valider" />
        {else}
        <input type="submit" name="action" value="Invalider" />
        <input type="submit" name="action" value="Archiver" />
        {/if}
        <input type="submit" name="action" value="Supprimer" />
        {/if}
      </form>
    </th>
  </tr>
</table>

<br />
{/foreach}

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
