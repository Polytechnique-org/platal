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

<h1>Marketing</h1>

<p>
  La liste des camarades à ajouter doit comporter un camarade par ligne, sous la forme&nbsp;:
  <em>Prénom Nom Promo Email</em>, où tous les champs, sauf l'email, sont facultatifs. La promotion éventuelle doit être
  indiquée sous la forme <em>X2004</em>.
</p>
{if $errors}
<p class="erreur">
  Les lignes incomplètes ou erronées ont été remises dans le cadre ci-dessous pour que tu puisses les corriger et/ou les complèter.
</p>
{/if}

<form method="post" action="{$platal->ns}carnet/batch" enctype="multipart/form-data">
  {xsrf_token_field}
  <div>
  <textarea name="list" cols="60" rows="30">{if $errors}{foreach from=$incomplete item=line}{$line}
{/foreach}{/if}</textarea><br />
  Marketer les camarades non inscrits à Polytechnique.org&nbsp;:<br />
  <label>en ton nom<input type="radio" name="origin" value="user" checked="checked" /></label>&nbsp;-&nbsp;
  <label><input type="radio" name="origin" value="staff" />au nom de l'équipe Polytechnique.org </label><br />
  <input type="submit" name="add" value="Ajouter" />
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
