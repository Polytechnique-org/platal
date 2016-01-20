{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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

<strong>Nom&nbsp;:</strong>
<input type="text" name="name" size="25" maxlength="200" value="{$valid->name}" /><br />
<strong>Acronyme&nbsp;:</strong>
<input type="text" name="acronym" size="25" maxlength="200" value="{$valid->acronym}" /><br />
<strong>Site web&nbsp;:</strong>
<input type="text" name="url" size="25" maxlength="200" value="{$valid->url}" /><br />
<strong>Email&nbsp;:</strong>
<input type="text" name="email" size="25" maxlength="255" value="{$valid->email}" /><br />
<strong>Holding&nbsp;:</strong>
<input type="text" name="holdingid" size="25" maxlength="200" value="{$valid->holdingid}" /><br />
<strong>Code SIREN&nbsp;:</strong>
<input type="text" name="SIREN" size="25" maxlength="200" value="{$valid->SIREN}" /><br />
<strong>Code NAF&nbsp;:</strong>
<input type="text" name="NAF_code" size="25" maxlength="200" value="{$valid->NAF_code}" /><br />
<strong>Code AX&nbsp;:</strong>
<input type="text" name="AX_code" size="25" maxlength="200" value="{$valid->AX_code}" /><br />
<strong>Adresse&nbsp;:</strong>
<textarea cols="30" rows="4" name="address">{$valid->address}</textarea><br />
<strong>Téléphone&nbsp;:</strong>
<input type="text" name="tel" size="25" maxlength="200" value="{$valid->tel}" /><br />
<strong>Fax&nbsp;:</strong>
<input type="text" name="fax" size="25" maxlength="200" value="{$valid->fax}" /><br />

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
