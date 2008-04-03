{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

<span class="flags">
    <input type="checkbox"
        {if $sn.pub neq 'private'} checked="checked"{/if}
        {if $sn.pub eq 'always public'} disabled="disabled"{else} name="search_name[{$i}][pub]"{/if}/>
    {icon name="flag_green" title="site public"}
</span>&nbsp;
<input type="text" name="search_name[{$i}][name]" value="{$sn.search_name}" size="30"/>
<select name="search_name[{$i}][type]">
  <option value="firstname"{if $sn.name_type eq 'firstname'} selected="selected"{/if}>Prénom</option>
  <option value="lastname"{if $sn.name_type eq 'lastname'} selected="selected"{/if}>Nom de famille</option>
  <option value="surname"{if $sn.name_type eq 'surname'} selected="selected"{/if}>Surnom</option>
</select>
{if $sn.pub neq 'always public'}
  <a href="javascript:removeSearchName({$i})">
    {icon name=cross title="Supprimer ce nom de recherche"}
  </a>
  {if $newsn}
    <span style="display:none" id="search_name_{$i}_new">Nouveau</span>
  {else}
    <input type="hidden" name="search_name[{$i}][removed]" value=""/>
  {/if}
{else}
    {icon name="null"}
{/if}
