{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
{*  http://opensource.polytechnique.org/                                  *}
{*                                                                        *}
{*  This program is free software; you can redistribute it and/or modify  *}
{*  it under the terms of the GNU General Public License as published by  *}
{*  the Free Software Foundation; either version 2 of the License, or     *}
{*  (at your option) any later version.                                   *}
{*                                                                        *}
{*  This program is distributed in the hope that it will be useful,       *}
{*  but WITHOUT ANY WARRANTY; without even the implied warranty of        *}
{*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *}
{*  GNU General Public License for more details.                          *}
{*                                                                        *}
{*  You should have received a copy of the GNU General Public License     *}
{*  along with this program; if not, write to the Free Software           *}
{*  Foundation, Inc.,                                                     *}
{*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA               *}
{*                                                                        *}
{**************************************************************************}

<h1>{$asso.nom} :
{if ($smarty.request.add || $smarty.request.mod) && $admin}
<a href='{$smarty.server.PHP_SELF}'>Evénements</a>
{else}
Evénements
{/if}
</h1>

{if !$logged}
  <p class="descr">
     Aucune manifestation publique n'a été saisie par ce groupe pour l'instant...
  </p>
{elseif $get_form}
  {include file='xnet/groupe/form_evenement.tpl'}
{else}

  {if $admin}
  <p class="center">
  [<a href="{$marty.server.PHP_SELF}?add=1">Annoncer un nouvel événement</a>]
  </p>
  {/if}

  {if $nb_evt eq 0}

  <p class="descr">
    Aucun événement n'a été référencé par les animateurs du groupe.
  </p>

  {else}

  {iterate from=$evenements item=e}
  <table class="tiny" cellspacing="0" cellpadding="0">
    <tr>
      <th colspan="2">
        {$e.intitule}
        {if $admin || $e.show_participants}
        <a href="evt-admin.php?eid={$e.eid}"><img src="{rel}/images/loupe.gif" title="Liste des participants" alt="Liste des participants" /></a>
        {/if}
        {if $admin}
        <a href="{$smarty.session.PHP_SELF}?mod=1&amp;eid={$e.eid}"><img src="{rel}/images/profil.png" title="Edition de l'événement" alt="Edition de l'événement" /></a>
        <a href="{$smarty.session.PHP_SELF}?sup=1&amp;eid={$e.eid}"><img src="{rel}/images/del.png" alt="Suppression de {$e.intitule}" title="Suppression de {$e.intitule}" /></a>
        {/if}
      </th>
    </tr>
    <tr>
      <td class="titre">date :</td>
      <td>
        {$e.debut}{if $e.fin} - {$e.fin}{/if}
      </td>
    </tr>
    <tr>
      <td class="titre">annonceur :</td>
      <td>
        <a href='https://polytechnique.org/fiche.php?user={$e.alias}' class='popup2'>{$e.prenom} {$e.nom} ({$e.promo})</a>
      </td>
    </tr>
    <tr>
      <td class="titre">
        <a href='evt-detail.php?eid={$e.eid}'>Détails...</a> 
      </td>
      <td>
        {if $e.inscrit}
        <small>tu es inscrit à cet événément !</small>
        {else}
        <small>tu n'es pas encore inscrit à cet événément !</small>
        {/if}
      </td>
    </tr>
  </table>
  <br />
  {/iterate}

  {/if}

{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
