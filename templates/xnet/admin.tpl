{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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

{if $smarty.get.del}

<h1>Suppression du groupe {$nom}</h1>

<form action="admin?del={$smarty.request.del}" method="post">
  {xsrf_token_field}
  <div class="center">
    <input type="submit" name="del" value="Oui, je veux supprimer ce groupe" />
  </div>
</form>

{else}

<h1>Ajouter un groupe</h1>

<form action="admin" method="post">
  {xsrf_token_field}
  <p class="descr">
  Pour ajouter un groupe, choisir ici le diminutif qu'il va utiliser,
  tu seras ensuite redirigé vers une page te permettant d'éditer le groupe&nbsp;:
  </p>
  <div class="center">
    <input type="text" name="diminutif" value="{$smarty.request.diminutif}" />
    <input type="submit" name="del" value="Ajouter" />
  </div>
</form>


<h1>Administration des groupes X.net</h1>

<table cellspacing="0" cellpadding="0" class='large'>
  {foreach from=$assos item=a key=i name=all}
  {if $i is even}<tr>{/if}
    <td><a href='admin?del={$a.diminutif}'>{icon name=delete title='delete'}</a></td>
    <td><a href='{$a.diminutif}/edit'>{$a.nom}</a></td>
    {if $i is odd || $smarty.foreach.all.last}</tr>{/if}
  {/foreach}
</table>

{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
