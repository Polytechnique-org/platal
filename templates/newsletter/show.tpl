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
        $Id: show.tpl,v 1.1 2004-10-16 18:17:51 x2000habouzit Exp $
 ***************************************************************************}

{dynamic}
<div class="rubrique">
  Lettre de Polytechnique.org de {$nl->_date|date_format:"%B %Y"}
</div>

<p>[<a href='index.php'>liste des lettres</a>]</p>

<form method="post" action="{$smarty.server.PHP_SELF}">
  <div class='center'>
    <input type='submit' value="me l'envoyer" name='send' />
  </div>
</form>

<table class="bicol" cellpadding="3" cellspacing="0">
  <tr>
    <td>
      <div class='nl'>
        {$nl->toHtml()}
      </div>
    </td>
  </tr>
</table>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
