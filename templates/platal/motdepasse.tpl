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


<h1>
  Changer de mot de passe
</h1>

<p>
  Ton mot de passe doit faire au moins <strong>6 caractères</strong> quelconques. Attention
  au type de clavier que tu utilises (qwerty?) et aux majuscules/minuscules.
</p>
<p>
  Pour une sécurité optimale, ton mot de passe circule de manière cryptée (https) et est
  stocké crypté irréversiblement sur nos serveurs.
</p>
<br />
<form action="{$smarty.server.REQUEST_URI}" method="post" id="changepass">
  <table class="tinybicol" cellpadding="3" cellspacing="0"
    summary="Formulaire de mot de passe">
    <tr>
      <th colspan="2">
        Saisie du nouveau mot de passe
      </th>
    </tr>
    <tr>
      <td class="titre">
        Nouveau mot de passe :
      </td>
      <td>
        <input type="password" size="10" maxlength="10" name="nouveau" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Retape-le une fois :
      </td>
      <td>
        <input type="password" size="10" maxlength="10" name="nouveau2" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="submit" value="Changer" name="submitn" onclick="EnCryptedResponse(); return false;" />
      </td>
    </tr>
  </table>
</form>
<form action="{$smarty.server.REQUEST_URI}" method="post" id="changepass2">
<p>
<input type="hidden" name="response2"  value="" />
</p>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
