{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: trombi.tpl,v 1.2 2004-10-29 02:04:24 x2000habouzit Exp $
 ***************************************************************************}

{dynamic}
 
<table cellpadding="8" cellspacing="2" style="width:100%;">
  {foreach from=$trombi_list item=p}
  {cycle values="1,2,3" assign="loop"}
  {if $loop eq "1"}
  <tr>
  {/if}
    <td class="center">
      <img src="{"getphoto.php"|url}?x={$p.forlife}" width="110" alt=" [ PHOTO ] " />
      {if $trombi_admin && $smarty.session.perms eq 'admin'}
      <a href="{"admin/admin_trombino.php"|url}?uid={$p.user_id}">
        <img src="{"images/admin.png"}" alt="[admin]" title="[admin]" />
      </a>
      {/if}
      <br />
      <a href="javascript:x()" onclick="popWin('{"fiche.php"|url}?user={$p.forlife}')">
        {$p.prenom} {$p.nom} ({$p.promo})
      </a>
    </td>
  {if $loop eq "3"}
  </tr>
  {/if}
  {/foreach}
  {if $loop eq "1"}
  <td></td><td></td></tr>
  {elseif $loop eq "2"}
  <td></td></tr>
  {/if}
  <tr>
    <td colspan='3' class='center'>
      {foreach from=$trombi_links item=l}
      {if $l.i eq $smarty.request.offset}
      <span class="erreur">{$l.text}</span>
      {else}
      <a href="{$l.u}">{$l.text}</a>
      {/if}
      {/foreach}
    </td>
  </tr>
</table>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
