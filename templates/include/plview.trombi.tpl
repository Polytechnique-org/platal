{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2015 Polytechnique.org                             *}
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

{if $plset_count eq 0}
<p class="erreur">
  Aucun des camarades concernés n'a de photographie sur sa fiche
</p>
{else}
<table cellpadding="0" cellspacing="2" style="width: 100%">
  {section name=trombi loop=$set_keys start=0}
  {if $smarty.section.trombi.index % 3 == 1}
    {assign var=key_prev value=$set_keys[trombi.index_prev]}
    {assign var=key_cur  value=$set_keys[trombi]}
    {assign var=key_next value=$set_keys[trombi.index_next]}
    {assign var=profile_prev value=$set[$key_prev]|get_profile}
    {assign var=profile_cur value=$set[$key_cur]|get_profile}
    {assign var=profile_next value=$set[$key_next]|get_profile}

    <tr>
      {include file="include/plview.trombi.entry.tpl" profile=$profile_prev photo=true}
      {include file="include/plview.trombi.entry.tpl" profile=$profile_cur photo=true}
      {include file="include/plview.trombi.entry.tpl" profile=$profile_next photo=true}
    </tr>
    <tr>
      {include file="include/plview.trombi.entry.tpl" profile=$profile_prev photo=false}
      {include file="include/plview.trombi.entry.tpl" profile=$profile_cur photo=false}
      {include file="include/plview.trombi.entry.tpl" profile=$profile_next photo=false}
    </tr>
  {elseif ($smarty.section.trombi.index % 3 == 0) && ($smarty.section.trombi.last)}
    {assign var=key_cur  value=$set_keys[trombi]}
    {assign var=profile_cur value=$set[$key_cur]|get_profile}
    <tr>
      {include file="include/plview.trombi.entry.tpl" profile=$profile_cur photo=true}
      <td></td><td></td>
    </tr>
    <tr style="margin-top: 0; padding-top: 0">
      {include file="include/plview.trombi.entry.tpl" profile=$profile_cur photo=false}
      <td></td><td></td>
    </tr>
  {/if}
  {/section}
</table>
{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
