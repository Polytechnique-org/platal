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

{if t($too_many)}
Les critères de recherche ne sont pas assez précis.
{elseif !t($users) || $users|@count eq 0}
Aucun camarade non-inscrit ne correspond aux informations fournies.
{else}
Camarades correspondants&nbsp;:
<select name="userid" onchange="updateSuggestions('{$platal->ns}', this.value)">
  <option value="0" {if $users|@count neq 1}selected="selected"{/if}>&nbsp;</option>
  {foreach item=user from=$users}
  <option value="{$user->id()}" {if $users|@count == 1}selected="selected"{/if}>{profile user=$user link=false promo=true}</option>
  {/foreach}
</select>
{if !$same_email}
<span id="marketing" style="display: none"><br />
  <label>
    <input type="checkbox" name="marketing" onchange="$('#marketing_from').toggle()" />
    Lui envoyer un marketing
  </label>
  <select name="marketing_from" id="marketing_from">
    <option value="user" selected="selected">de ta part.</option>
    <option value="staff">de la part de Polytechnique.org.</option>
  </select>
</span>
<span id="broken" style="display: none"><br />
  Ce camarade est inscrit, mais l'email fourni ne fait pas partie de ses adresses de redirection.<br />
  <label>
    <input type="checkbox" name="broken" />
    Lui suggérer d'ajouter cette adresse email à ses redirections.
  </label>
</span>
{/if}
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
