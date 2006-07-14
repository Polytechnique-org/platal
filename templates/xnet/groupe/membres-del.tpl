{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

{if $smarty.post.confirm}

<p class="descr">
<a href="{rel}/{$platal->ns}annuaire">retour à l'annuaire</a>
</p>

{else}
 
<h1>{$asso.nom} : gestion des memebres</h1>

<h2>
  Suppression du membre : {$user.prenom} {$user.nom}
</h2>


<form method="post" action="{$smarty.server.REQUEST_URI}">
  <div class="center">
    <p class="descr">
    Etes-vous sûr de vouloir supprimer {$user.prenom} {$user.nom} du groupe,
    lui retirer tous les droits associés à son statut de membre,
    et le désabonner de toutes les listes de diffusion du groupe ?
    </p>
    <input type='submit' name='confirm' value='Oui, je le désinscris complètement du groupe !' />
  </div>
</form>

{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
