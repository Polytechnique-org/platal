{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: evenements.tpl,v 1.8 2004-11-13 15:56:35 x2000habouzit Exp $
 ***************************************************************************}


{dynamic}

<h1>
  Gestion des événements :
  {if $arch}
  [&nbsp;<a href="{$smarty.server.PHP_SELF}?arch=0">Actualités</a>&nbsp;|&nbsp;Archives&nbsp;]
  {else}
  [&nbsp;Actualités&nbsp;|&nbsp;<a href="{$smarty.server.PHP_SELF}?arch=1">Archives</a>&nbsp;]
  {/if}
</h1>

{if $mode}

{include file="include/form.evenement.tpl"}

{else}

{foreach from=$evs item=ev}
<table class="bicol">
  <tr>
    <th>
      Posté par <a href="{"fiche.php"|url}?user={$ev.forlife}" class='popup2'>{$ev.prenom} {$ev.nom} (X{$ev.promo})</a>
      <a href="mailto:{$ev.forlife}@m4x.org">lui écrire</a>
    </th>
  </tr>
  <tr class="{if $ev.fvalide}impair{else}pair{/if}">
    <td>
      <strong>{$ev.titre}</strong><br />
      {$ev.texte|nl2br}<br />
      <hr />
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
      <form action="{$smarty.server.PHP_SELF}" method="post">
        <div>
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
        </div>
      </form>
    </th>
  </tr>
</table>

<br />
{/foreach}

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
