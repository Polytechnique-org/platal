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

<img src='getlogo.php' alt="LOGO" style="float: right;" />

<h1>{$asso.nom} : Éditer l'accueil</h1>

<table cellpadding="0" cellspacing="0">
  <tr>
    <td class="titre">
      Logo:
    </td>
    <td>
      <input type="file" name="logo" />
    </td>
  </tr>

  <tr>
    <td class="titre">
      Site Web:
    </td>
    <td>
      <input type="text" value="{$asso.site}" name="site" />
    </td>
  </tr>

  <tr>
    <td class="titre">
      Contact:
    </td>
    <td>
      <input type="text" name="resp" value="{$asso.resp}" />
    </td>
  </tr>

  <tr>
    <td class="titre">
      Adresse mail:
    </td>
    <td>
      <input type="text" name="mail" value="{$asso.mail}" />
    </td>
  </tr>

  <tr>
    <td class="titre">
      Forum:
    </td>
    <td>
      <input type="text" name="forum" value="{$asso.forum}" />
    </td>
  </tr>

  <tr>
    <td class="titre">
      <strong>TODO: INSCRIPTION</strong>
    </td>
  </tr>

  <tr>
    <td class="titre center" colspan="2">
      <input type="checkbox" value="1" name="ax" {if $asso.ax}checked="checked"{/if} />
      groupe agréé par l'AX
    </td>
  </tr>
</table>

<br />

<textarea name="descr" cols="70" rows="15" >{$asso.descr}</textarea>

{* vim:set et sw=2 sts=2 sws=2: *}
