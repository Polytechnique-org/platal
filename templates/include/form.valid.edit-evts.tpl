{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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

<input type="text" name="titre" size="50" maxlength="200" value="{$valid->titre}" />
<br />
<textarea rows="10" cols="60" name="texte">{$valid->texte}</textarea>
<br />
<strong>Péremption&nbsp;:</strong> <input type="text" name="peremption" size="10" value="{$valid->peremption}" />
<strong>Promos&nbsp;:&nbsp;</strong>
min <input type="text" name="promo_min" size="4" maxlength="4" value="{$valid->pmin}" />
&nbsp;->&nbsp;max <input type="text" name="promo_max" size="4" maxlength="4" value="{$valid->pmax}" /><br />
<strong>Illustration&nbsp;:</strong> <input type="file" name="image" />

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
