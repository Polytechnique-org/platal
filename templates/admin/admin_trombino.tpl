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


<h1>
  Gestion du trombino
</h1>

<p>
Photo actuelle de {$forlife}
</p>

<img src="../getphoto.php?x={$forlife}" alt="[ PHOTO ]" />
<br />

<p>
<a href="?uid={$smarty.request.uid}&amp;action=supprimer">Supprimer cette photo</a>
</p>

<p>
<a href="?uid={$smarty.request.uid}&amp;action=ecole">Voir sa photo de trombi récupérée à l'école (si disponible)</a>
</p>

<form action="{$smarty.server.PHP_SELF}" method="post" enctype="multipart/form-data">
  <div>
    <input type="hidden" name="uid" value="{$smarty.request.uid}" />
    <input type="hidden" name="action" value="valider" />
    <input name="userfile" type="file" size="20" maxlength="150" />
    <input type="submit" value="Envoyer" />
  </div>
</form>


{* vim:set et sw=2 sts=2 sws=2: *}
