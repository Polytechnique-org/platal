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

<h1>Soumettre un spam</h1>

<form method="post" action="{$platal->pl_self()}" enctype="multipart/form-data">
  {xsrf_token_field}
  <table class="tinybicol">
    <tr>
      <td>
        Soumettre un
        <select name="type">
          <option value="spam" {if $smarty.request.type neq 'spam'}selected="selected"{/if}>spam</option>
          <option value="nonspam" {if $smarty.request.type eq 'nonspam'}selected="selected"{/if}>non-spam</option>
        </select>
        mal filtré.
      </td>
    </tr>
    <tr>
      <td>
        <input type="file" name="mail" />
      </td>
    </tr>
  </table>

  <p class="center">
    <input type="submit" name="send_email" value="Envoyer" />
  </p>
</form>

<p>
  Pour soumettre un (non-)spam mal détecté par notre <a href="emails/antispam">antispam</a>, il suffit d'enregistrer
  les sources (regarde dans la documentation de ton client email dans la liste ci-dessous) de l'email en tant que fichier
  depuis ton client email, puis de soumettre ce fichier à l'interface ci-dessus en sélectionnant l'action adaptée&nbsp;:
</p>
<ul>
  <li><strong>spam</strong>&nbsp;: pour soumettre un spam mal reconnu&nbsp;;</li>
  <li><strong>nonspam</strong>&nbsp;: pour soumettre un email légitime mal reconnu.</li>
</ul>

{include wiki=Xorg.Mails part=clients}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
