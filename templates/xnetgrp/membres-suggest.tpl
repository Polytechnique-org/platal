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

<h1>{$asso->nom}&nbsp;: Ajout d'un membre (suite)</h1>

<p>
  L'adresse email <strong>{$email}</strong> ne correspond actuellement à aucun
  compte. Souhaites-tu qu'un compte « Extérieur » soit créé pour lui et que nous
  lui envoyions un email afin qu'il ait accès aux nombreuses fonctionnalités de
  Polytechnique.net (inscription aux évènements, télépaiement, modération des
  listes de diffusion&hellip;)&nbsp;?
</p>
<form action="{$platal->ns}member/suggest/{$hruid}/{$email}" method="post" class="center">
  {xsrf_token_field}
  <p>
    <label>Oui&nbsp;<input type="radio" name="suggest" value="yes" checked="checked" /></label>
    &nbsp;-&nbsp;
    <label><input type="radio" name="suggest" value="no" />&nbsp;Non</label>
  </p>
  <p><input type="submit" value="continuer" /></p>
</form>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
