{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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

  {if !$choix}
  Les critères de recherche ne sont pas assez précis
  {elseif !$choix->total()}
  Aucun camarade non-inscrit ne correspond aux informations fournies
  {elseif $choix->total()}
  Camarades correspondants :
  <select name="userid" onchange="document.getElementById('marketing').style.display = (this.value == 0 ? 'none' : '')">
    <option value="0"></option>
    {iterate item=x from=$choix}
    <option value="{$x.user_id}" {if $choix->total() == 1}selected="selected"{/if}>{$x.prenom} {$x.nom} (X{$x.promo})</option>
    {/iterate}
  </select>
  <span id="marketing" {if $choix->total() != 1}style="display: none"{/if}><br />
    <input type="checkbox" name="market" checked="checked"
        onchange="document.getElementById('from').style.display = (this.checked ? '' : 'none')"/>
    Lui envoyer un marketing
    <select name="market_from" id="from">
      <option value="user" selected="selected">de ta part</option>
      <option value="staff">de la part de Polytechnique.org</option>
    </select>
  </span>
  {/if}

{* vim:set et sw=2 sts=2 sws=2: *}
