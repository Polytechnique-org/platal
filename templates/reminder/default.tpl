{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2009 Polytechnique.org                             *}
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

<div class="warnings reminder">
  <div style="float: right">
    <a href="" onclick="Ajax.update_html('reminder', '{$baseurl}/dismiss')">
      {icon name=cross title="Cacher cet avertissement."}
    </a>
  </div>
  {$text}<br />
  <div class="center">
    [<a href="" onclick="Ajax.update_html('reminder', '{$baseurl}/yes')">{icon name=add} M'inscrire</a>]
    -
    [<a href="" onclick="Ajax.update_html('reminder', '{$baseurl}/no')">{icon name=delete} Ne pas m'inscrire</a>]
  </div>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
