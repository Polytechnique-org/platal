{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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
  {$issue->nl->name} du {$issue->date|date_format}
</h1>

<p style="float: left">
  {if $smarty.get.text}
  [<a href='{$nl->prefix()}/show/{$issue->id()}'>version HTML</a>]
  {else}
  [<a href='{$nl->prefix()}/show/{$issue->id()}?text=1'>version Texte</a>]
  {/if}
  {if $nl->mayEdit()}
  [<a href='{$nl->adminPrefix()}/edit/{$issue->id()}'>Éditer</a>]
  {/if}
</p>

{include file="include/massmailer-nav.tpl" issue=$issue nl=$nl}

<form method="post" action="{$platal->path}">
  <div class='center' style="clear: both">
    <input type='submit' value="me l'envoyer" name='send' />
  </div>
</form>

<table class="bicol">
  <tr><th>{$issue->title(true)}</th></tr>
  <tr>
    <td>
      {include file=$nl->tplFile() escape=true}
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
