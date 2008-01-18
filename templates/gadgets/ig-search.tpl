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

{if $error}
<div class="error">{$error}</div>
{elseif $set}
<div class="contact-list">
{iterate from=$set item=res}
  {if $res.contact}
  {include file="gadgets/ig-minifiche.tpl" c=$res show_action="retirer"}
  {else}
  {include file="gadgets/ig-minifiche.tpl" c=$res show_action="ajouter"}
  {/if}
{/iterate}
</div>
<div class="more">
  <a href="search?quick={$smarty.request.quick}" target="_blank">{$result_count} résultats au total</a> &gt;&gt;&gt;
</div>
{else}
<div class="welcome">
  <img src="images/skins/default_headlogo.jpg" alt="Logo Polytechnique.org" />
</div>
{/if}

<div class="search">
  <form method="get" action="gadgets/ig-search">
    <input name="extern_js" type="hidden" value="{$smarty.request.extern_js}" />
    <input name="libs" type="hidden" value="{$smarty.request.libs}" />
    <input name="mid" type="hidden" value="{$smarty.request.mid}" />
    <input name="parent" type="hidden" value="{$smarty.request.parent}" /><br />
    <input name="synd" type="hidden" value="{$smarty.request.synd}" />
    <input name="quick" type="text" value="{$smarty.request.quick}" /><br />
    <input value="Chercher sur Polytechnique.org" type="submit" />
  </form>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
