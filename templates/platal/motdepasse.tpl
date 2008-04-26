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
  {javascript name="jquery"}
  <script type="text/javascript">//<![CDATA[
  {literal}
    function getType(char) {
      if (char >= 'a' && char <= 'z') {
        return 1;
      } else if (char >= 'A' && char <= 'Z') {
        return 2;
      } else if (char >= '0' && char <= '9') {
        return 3;
      } else {
        return 4;
      }
    }
    function checkPassword(box) {
      var prev = 0;
      var prop = 0;
      var pass = box.value;
      var types = Array(0, 0, 0, 0, 0);
      for (i = 0 ; i < pass.length ; ++i) {
        type = getType(pass.charAt(i));
        if (prev != 0 && prev != type) {
          prop += 5;
        }
        if (i >= 5) {
          prop += 5;
        }
        if (types[type] == 0) {
          prop += 10;
        }
        types[type]++;
        prev = type;
      }
      if (prop > 100) {
        prop = 100;
      } else if (prop < 0) {
        prop = 0;
      }
      ok = (prop >= 60);
      $("#passwords").width(prop + "%").css("background-color", ok ? "green" : "red");
      if (ok) {
        $(":submit").removeAttr("disabled");
      } else {
        $(":submit").attr("disabled", "disabled");
      }
    }
    {/literal}
  //]]></script>
  <table class="tinybicol" cellpadding="3" cellspacing="0"
    summary="Formulaire de mot de passe">
    <tr>
      <th colspan="2">
        Saisie du nouveau mot de passe
      </th>
    </tr>
    <tr>
      <td class="titre">
        Nouveau mot de passe&nbsp;:
      </td>
      <td>
        <input type="password" size="10" maxlength="10" name="nouveau" onkeyup="checkPassword(this)" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Retape-le une fois&nbsp;:
      </td>
      <td>
        <input type="password" size="10" maxlength="10" name="nouveau2" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Sécurité
      </td>
      <td>
        <div style="border: 1px solid white; width: 250px; height: 7px; background-color: #444">
          <div id="passwords" style="height: 100%; background-color: red; width: 0px"></div>
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="submit" value="Changer" name="submitn" disabled="disabled"
               onclick="EnCryptedResponse(); return false;" />
      </td>
    </tr>
  </table>
</form>
<form action="{$smarty.server.REQUEST_URI}" method="post" id="changepass2">
<p>
{xsrf_token_field}
<input type="hidden" name="response2"  value="" />
</p>
</form>

<p>
  Note bien qu'il s'agit là du mot de passe te permettant de t'authentifier sur le site {#globals.core.sitename#} ;
  le mot de passe te permettant d'utiliser le serveur <a href="./Xorg/SMTPSécurisé">SMTP</a> et <a href="Xorg/NNTPSécurisé">NNTP</a>
  de {#globals.core.sitename#} (si tu as <a href="./password/smtp">activé l'accès SMTP et NNTP</a>)
  est indépendant de celui-ci et tu peux le modifier <a href="./password/smtp">ici</a>.
</p>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
