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
        $Id: search.quick.form.tpl,v 1.2 2004-11-04 14:54:41 x2000habouzit Exp $
 ***************************************************************************}

<h1>Recherche simple dans l'annuaire</h1>

{if $error}<p class="error">{$error}</p>{/if}

<form id="recherche" action="{"search.php"|url}" method="get">
  <table class="bicol" cellspacing="0" cellpadding="4">
    <tr>
      <td class='center' style="width: 78%">
        <input type='text' name="quick" value="{$smarty.request.quick}" style="width: 100%" /><br />
      </td>
      <td>
        <input type="submit" value="Chercher" />
        {min_auth level="cookie"}
        <br /><a class='smaller' href="advanced_search.php">Recherche&nbsp;avancée</a>
        {/min_auth}
      </td>
    </tr>
  </table>
</form>

<br />

{* vim:set et sw=2 sts=2 sws=2: *}
