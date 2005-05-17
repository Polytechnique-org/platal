{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
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

<p>[<a href='{if $it_is_xnet}listes.php{else}index.php{/if}'>Voir toutes les listes</a>]</p>
<p class="erreur">La liste a été détruite !</p>

{else}

{if !$details.own}
<p class='erreur'>
Tu n'es pas administrateur de la liste, mais du site.
</p>
{/if}

{include file="listes/header_listes.tpl" on=delete}

<h1>
  Détruire la liste {$details.addr} ?
</h1>

<form method='post' action='{$smarty.server.REQUEST_URI}'>
  <table class='tinybicol' cellpadding='2' cellspacing='0'>
    <tr class='impair'>
      <td>
        Veux tu réellement détruire la liste {$details.addr} ?<br />
        Pour valider ton choix, écris en majuscules (sans espace) « OUI » :
        <input type='text' size='3' maxlength='3' name="valid" />
      </td>
    </tr>
    <tr class='pair'>
      <td>
        Si tu veux préserver les archives de la liste, décoche la case ci-contre.
        <input type="checkbox" checked="checked" name="del_archive" />
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


{* vim:set et sw=2 sts=2 sws=2: *}
