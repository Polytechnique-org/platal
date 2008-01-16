{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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


<h1>
  Gestion du trombino
</h1>

<p>
Photo actuelle de {$forlife}
</p>

<img src="photo/{$forlife}" alt="[ PHOTO ]" />
<br />

<p>
<a href="admin/trombino/{$uid}/delete">Supprimer cette photo</a>
</p>

<p>
<a href="admin/trombino/{$uid}/original">Voir sa photo de trombi récupérée à l'école (si disponible)</a>
</p>

<form action="admin/trombino/{$uid}/new" method="post" enctype="multipart/form-data">
  <div>
    <input name="userfile" type="file" size="20" maxlength="150" />
    <input type="submit" value="Envoyer" />
  </div>
</form>


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
