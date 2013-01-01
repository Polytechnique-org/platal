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

<div style="margin-bottom: 0.5em">
  Polytechnique.org te fournit un compte Google Apps qui te permet
  de disposer des applications web de Google (GMail, Google Calendar,
  Google Docs, et bien d'autres) sur ton adresse Polytechnique.org
  habituelle (en savoir plus).
</div>
<div class="center">
  <a href="{$reminder->baseurl()}/yes" style="text-decoration: none">
    {icon name=add} M'inscrire
  </a> -
  <a href="reminder/no" onclick="$('#reminder').updateHtml('{$reminder->baseurl()}/no'); return false" style="text-decoration: none">
    {icon name=delete} Ne pas m'inscrire
  </a> -
  <a href="reminder/later" onclick="$('#reminder').updateHtml('{$reminder->baseurl()}/dismiss'); return false" style="text-decoration: none">
    {icon name=cross} Décider plus tard
  </a> -
  <a class="popup2" style="text-decoration: none" href="Xorg/GoogleApps">{icon name=information} En savoir plus</a>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
