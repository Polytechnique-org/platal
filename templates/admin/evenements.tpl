{* $Id: evenements.tpl,v 1.3 2004-07-19 13:35:35 x2000habouzit Exp $ *}

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

{include file="include/form.evenement.tpl"}

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
        <input type="submit" name="action" value="Invalider" />
        <input type="submit" name="action" value="Archiver" />
        {else}
        <input type="submit" name="action" value="Valider" />
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
