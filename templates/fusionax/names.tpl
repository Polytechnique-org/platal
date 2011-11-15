{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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

<h2><a href="fusionax">Fusion des annuaires X.org - AX</a> / noms</h2>

<p></p>

{if $action eq "first"}
{if $firstnameIssues|@count eq 0}
<p>Aucun problème avec les prénoms.</p>
{else}
<p><a href="fusionax/names/first/true">Obtenir le csv des conflits.</a></p>
<table class="bicol">
  <tr>
    <th>pid</th>
    <th>ax id</th>
    <th>hrpid</th>
    <th colspan="4">Prénoms</th>
  </tr>
  <tr>
    <th></th>
    <th></th>
    <th></th>
    <th>AX</th>
    <th>initial</th>
    <th>principal</th>
    <th>ordinaire</th>
  </tr>
{foreach from=$firstnameIssues item=profile}
  <tr>
    <td>{$profile.pid}</td>
    <td>{$profile.ax_id}</td>
    <td>{$profile.hrpid}</td>
    <td>{$profile.prenom}</td>
    <td>{$profile.firstname_initial}</td>
    <td>{$profile.firstname_main}</td>
    <td>{$profile.firstname_ordinary}</td>
  </tr>
{/foreach}
</table>
{/if}
{elseif $action eq "last"}
{if $lastnameIssues|@count eq 0}
<p>Aucun problème avec les noms.</p>
{else}
<p><a href="fusionax/names/last/true">Obtenir le csv des conflits.</a></p>
<table class="bicol">
  <tr>
    <th>pid</th>
    <th>ax id</th>
    <th>hrpid</th>
    <th colspan="7">Noms</th>
  </tr>
  <tr>
    <th></th>
    <th></th>
    <th></th>
    <th>AX patro</th>
    <th>AX usuel</th>
    <th>AX complet</th>
    <th>initial</th>
    <th>principal</th>
    <th>marital</th>
    <th>ordinaire</th>
  </tr>
{foreach from=$lastnameIssues item=profile}
  <tr>
    <td>{$profile.pid}</td>
    <td>{$profile.ax_id}</td>
    <td>{$profile.hrpid}</td>
    <td>{$profile.Nom_patronymique}</td>
    <td>{$profile.Nom_usuel}</td>
    <td>{$profile.Nom_complet}</td>
    <td>{$profile.lastname_initial}</td>
    <td>{$profile.lastname_main}</td>
    <td>{$profile.lastname_marital}</td>
    <td>{$profile.lastname_ordinary}</td>
  </tr>
{/foreach}
</table>
{/if}

{else}
<p>
  Il y a {$total} camarades dans l'intersection de nos bases.
</p>
<ul>
  <li><a href="fusionax/names/last">{$lastnameIssues} des noms de l'AX ne sont pas dans les notres.</a></li>
  <li><a href="fusionax/names/first">{$firstnameIssues} des prénoms de l'AX ne sont pas dans les notres.</a></li>
</ul>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
