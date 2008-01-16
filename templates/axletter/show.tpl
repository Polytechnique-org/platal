{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

<h1 style="clear: both">
  {if $am->_date}
  Lettre de l'AX du {$am->_date|date_format}
  {else}
  Lettre de l'AX en préparation
  {/if}
</h1>

<p style="float: left">
{if $smarty.get.text}
[<a href='{$platal->pl_self()}'>version HTML</a>]
{else}
[<a href='{$platal->pl_self()}?text=1'>version Texte</a>]
{/if}
{if !$am->_date}
[<a href='ax/edit'>éditer</a>]
{/if}
</p>

{include file="include/massmailer-nav.tpl" mm=$am base=ax}

<form method="post" action="{$platal->path}">
  <div class='center' style="clear: both">
    <input type='submit' value="me l'envoyer" name='send' />
  </div>
</form>

<table class="bicol">
  <tr><th>{$am->title(true)}</th></tr>
  <tr>
    <td>
      {include file="axletter/letter.tpl"}
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
