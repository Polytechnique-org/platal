{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
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

<h1>Raccourcisseur d'url</h1>

<form action="admin/url" method="post">
  {xsrf_token_field}
  <table class="bicol">
    <tr>
      <th>Url&nbsp;:</th>
      <td><input type="text" name="url" value="{if t($url)}{$url}{/if}" /></td>
    </tr>
    <tr>
      <th>Alias (optionnel)&nbsp;:</th>
      <td>
        <input type="text" name="alias" size="42" maxlength="255" value="{if t($alias)}{$alias}{/if}" />
        <small>(peut contenir lettres, chiffres, tirets et /)</small>
      </td>
    </tr>
  </table>
  <p class="center"><input type="submit" value="Raccourcir" /></p>
</form>

<h3>Explications</h3>
<p>
  L'alias peut être demandé. Dans ce cas, sa longueur maximal autorisée est
  de 255 lettres, chiffres, tirets ou /. Ce dernier permet de définir des
  domaines pour regrouper des raccourcis liés. Par exemple, « nl-04-04/ »
  pourrait être utilisé comme base pour les urls de la lettre mensuelle d'avril
  2004.<br />
  Si aucun alias n'est fournit, le site en génère un de 6 caractères aléatoires
  accolés à la la base «&nbsp;a/&nbsp;» (par exemple&nbsp;: « a/azerty ». Ce
  préfixe «&nbsp;a/&nbsp;» et réservé à cet usage et ne peut être utilisé pour
  former une url choisie.
</p>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
