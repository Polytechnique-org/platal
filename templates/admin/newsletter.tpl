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
        $Id: newsletter.tpl,v 1.2 2004-10-18 12:57:47 x2000habouzit Exp $
 ***************************************************************************}


<div class="rubrique">
  Lettre de Polytechnique.org
</div>

{dynamic}
<table class="bicol" cellpadding="3" cellspacing="0" summary="liste des NL">
  <tr>
    <th>date</th>
    <th>titre</th>
  </tr>
  <tr>
    <td colspan='2'><a href='?new'>Créer une nouvelle ML</a></td>
  </tr>
  {foreach item=nl from=$nl_list}
  <tr class="{cycle values="pair,impair"}">
    <td>{$nl.date|date_format:"%Y-%m-%d"}</td>
    <td>
      <a href="{"admin/newsletter_edit.php"|url}?nid={$nl.id}">{$nl.titre|default:"[no title]"}</a>
    </td>
  </tr>
  {/foreach}
</table>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
