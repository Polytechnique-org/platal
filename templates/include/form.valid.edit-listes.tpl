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

<strong>Nom de la liste&nbsp;:</strong>
<input type="text" name="listname" size="25" maxlength="200" value="{$valid->liste}" /><br />
<strong>Type de liste&nbsp;:</strong>
<label><input type="radio" name="assotype" value="binet" {if $valid->asso eq "binet"}checked="checked"{/if} />binet</label>
<label><input type="radio" name="assotype" value="" {if !$valid->asso}checked="checked"{/if} />quelconque</label>
<label><input type="radio" name="assotype" value="alias" {if $valid->asso eq "alias"}checked="checked"{/if} />alias</label>
<label><input type="radio" name="assotype" value="groupex" {if $valid->asso eq "groupex"}checked="checked"{/if} />groupe X</label><br />
<strong>Domaine&nbsp;:</strong> <input type="text" name="domainname" size="25" maxlength="200" value="{$valid->domain}" />

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
