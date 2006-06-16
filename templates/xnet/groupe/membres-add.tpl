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

{if $smarty.request.new eq x}

<h1>{$asso.nom} : Ajout d'un membre X</h1>

<form method="post" action="{$smarty.server.REQUEST_URI}">
  <p class="descr">
  Pour ajouter un X dans ton groupe, il suffit d'entrer ici une de ses adresses mail @polytechnique.org. Pour rentrer plusieurs X en une seule fois, sépare les adresses par des espaces :
  </p>
  <div class="center">
    <input type="text" name="email" size="40" value="{$smarty.request.email}" />
    <input type='submit' value='Ajouter' />
  </div>                                                                      
</form>

{else}

<h1>{$asso.nom} : Ajout d'un membre extérieur</h1>

<form method="post" action="{$smarty.server.REQUEST_URI}">
  <p class="descr">
  Pour ajouter un extérieur dans ton groupe, il suffit d'entrer ici son adresse mail,
  tu seras ensuite redirigé vers une page te permettant d'éditer son profil (nom, prenom, ...) :
  </p>
  <div class="center">
    <input type="text" name="email" size="40" value="{$smarty.request.email}" />
    <input type='submit' value='Ajouter' />
  </div>                                                                      
</form>

{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
