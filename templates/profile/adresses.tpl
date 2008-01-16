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

{foreach key=i item=adr from=$addresses}
<div id="{"addresses_`$i`_cont"}">
{include file="profile/adresses.address.tpl" i=$i adr=$adr}
</div>
{/foreach}
{if $addresses|@count eq 0}
<div id="addresses_0_cont">
{include file="profile/adresses.address.tpl" i=0 adr=0}
</div>
{/if}

<div id="add_adr" class="center">
  <a href="javascript:addAddress()">
    {icon name=add title="Ajouter une adresse"} Ajouter une adresse
  </a>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
