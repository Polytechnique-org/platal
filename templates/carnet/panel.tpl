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
        $Id: panel.tpl,v 1.1 2004-11-06 18:18:44 x2000habouzit Exp $
 ***************************************************************************}

<h1>Carnet polytechnicien</h1>

<table class="bicol">
  <tr>
    <th colspan="2">
      Tes contacts
    </th>
  </tr>
  <tr class="impair">
    <td class='half'>
      <div class="question">
        <a href="{"carnet/mescontacts.php"|url}">Page de tes contacts</a>
      </div>
      <div class="explication">
        Tu peux ici lister tes contacts, en ajouter et en retirer.
      </div>
    </td>
    <td class='half'>
      <div class="question">
        <a href="{"carnet/mescontacts.php?trombi=1"|url}">Le trombi de tes contacts</a>
      </div>
      <div class="explication">
        La même chose que la page de tes contacts... <strong>en images !</strong>
      </div>
    </td>
  </tr>
</table>

<br />

<table class="bicol">
  <tr>
    <th colspan="2">
      Notifications
    </th>
  </tr>
  <tr class="pair">
    <td class='half'>
      <div class="question">
        <a href="{"carnet/panel.php"|url}">Bilan de tes notifications</a>
      </div>
      <div class="explication">
        Affichage de tous les évenements de camarades/promos
      </div>
    </td>
    <td class='half'>
      <div class="question">
        <a href="{"carnet/notifs.php"|url}">Configurer tes notifications</a>
      </div>
      <div class="explication">
        Être notifié des inscriptions, décès, changement de fiche, ...
      </div>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
