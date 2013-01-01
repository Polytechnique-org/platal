{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2013 Polytechnique.org                             *}
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


{if $deleted}

<p>[<a href='{$platal->ns}lists'>Voir toutes les listes</a>]</p>

{else}

{include file="lists/header_listes.tpl" on=delete}

<h1>
  Détruire la liste {$details.addr}&nbsp;?
</h1>

<form method='post' action='{$platal->pl_self(1)}'>
  {xsrf_token_field}
  <table class='tinybicol' cellpadding='2' cellspacing='0'>
    <tr class='impair'>
      <td>
        Veux tu réellement détruire la liste {$details.addr}&nbsp;?<br />
        Pour valider ton choix, écris en majuscules (sans espace) «&nbsp;OUI&nbsp;»&nbsp;:
        <input type='text' size='3' maxlength='3' name="valid" />
      </td>
    </tr>
    <tr class='pair'>
      <td>
        <label>Si tu veux préserver les archives de la liste, décoche la case ci-contre.
        <input type="checkbox" checked="checked" name="del_archive" /></label>
      </td>
    </tr>
    <tr class='impair'>
      <td class="center">
        <input type="submit" value="Détruire !" />
      </td>
    </tr>
  </table>
</form>

{/if}


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
