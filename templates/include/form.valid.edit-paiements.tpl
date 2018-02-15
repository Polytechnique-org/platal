{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

<input type="text" name="pay_titre" size="50" maxlength="200" value="{$valid->titre}" />
<br />
<div id="preview_{$valid->id()}" style="display: none; text-align: left"></div>
<textarea rows="10" cols="60" id="msg_{$valid->id()}" name="pay_msg_reponse">{$valid->msg_reponse}</textarea>
<br />
<input type="submit" name="preview" value="Aperçu" onclick="previewWiki('msg_{$valid->id()}', 'preview_{$valid->id()}', true, 'preview_{$valid->id()}'); return false;" /><br />
<strong>Site&nbsp;:</strong> <input type="text" name="pay_site" size="45" value="{$valid->site}" />
<br />
<strong>Montant&nbsp;:</strong> <input type="text" name="pay_montant" size="5" value="{$valid->montant}" />
(min <input type="text" name="pay_montant_min" size="5" value="{$valid->montant_min}" />
&nbsp;->&nbsp; max <input type="text" name="pay_montant_max" size="5" value="{$valid->montant_max}" />)
<br />
<strong>Public&nbsp;:</strong>
<label><input type="radio" name="pay_public" value="no" {if !t($valid->public)}checked="checked"{/if} />Non</label>
&nbsp;-&nbsp;
<label>Oui<input type="radio" name="pay_public" value="yes" {if t($valid->public)}checked="checked"{/if} /></label><br />
<strong>Rib_id&nbsp;:</strong> <input type="text" name="pay_rib_id" size="5" value="{$valid->rib_id}" /> <br />
{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
