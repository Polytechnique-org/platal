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

<p>[<a href='{$platal->ns}lists'>retour à la page des listes</a>]</p>

<h1>Membres de {$platal->argv[1]}</h1>
      
<table class='tiny'>
  <tr>
    <td class='titre'>Membres</td>
    <td>
      {if $mem->total()}
      {iterate from=$mem item=m}
      {$m.redirect}
      <a href='{$platal->ns}alias/admin/{$platal->argv[1]}?del_member={$m.redirect|urlencode}'>
      {icon name=delete title='retirer membre'}</a>]
      </a>
      <br />
      {/iterate}
      {else}
      <em>aucun membres ...</em>
      {/if}
    </td>
  </tr>
  <tr>
    <td><strong>Ajouter</strong></td>
    <td>
      <form method="post" action="{$platal->ns}alias/admin/{$platal->argv[1]}">
        <div>
        <input type='text' name='add_member' />
        &nbsp;
        <input type='submit' value='ajouter' />
        </div>
      </form>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
