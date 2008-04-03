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

{if $tips}
{if $full}
<fieldset id="tod">
{/if}
  <legend>{icon name=lightbulb} {if !$tips.special}
    Astuce&nbsp;: {$tips.titre}
    {if hasPerm('admin') && !$tips.special && !$nochange}
    <a href="admin/tips/edit/{$tips.id}">{icon name="page_edit" title="Editer"}</a>
    {/if}
  {else}
    <span style="color: red; font-weight: bold;">{$tips.titre}</span>
  {/if}
  </legend>
  {tidy}
  {$tips.text|nl2br|smarty:nodefaults}
  {/tidy}
  {if !$nochange}
  <div class="right">
    <a href="" onclick="Ajax.update_html('tod', 'ajax/tips/{$tips.id}'); return false" style="text-decoration: none">
      Astuce suivante {icon name=resultset_next title="Astuce suivante"}
    </a>
  </div>
  {/if}
{if $full}
</fieldset>
{/if}
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
