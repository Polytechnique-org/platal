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
 ***************************************************************************}


<tr class="impair">
  <td class="titre">Épouse&nbsp;:</td>
  <td>{$valid->epouse}</td>
</tr>
<tr class="impair">
  <td class="titre">Nouvel&nbsp;alias&nbsp;:</td>
  <td>{$valid->alias|default:"<span class='erreur'>suppression</span>"}</td>
</tr>
{if $valid->homonyme}
<tr class="impair">
  <td colspan="2">
    <span class="erreur">Probleme d'homonymie !
      <a href="{rel}/fiche.php?user=$valid->homonyme" class="popup2">{$valid->homonyme}</a>
    </span>
  </td>
</tr>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
