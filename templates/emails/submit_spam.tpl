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

<h1>Soumettre un spam</h1>

<p>Ce formulaire permet de soumettre à l'antispam les mails mal filtrés.</p>

<form method="post" action="{$platal->pl_self()}" enctype="multipart/form-data">
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

{* vim:set et sw=2 sts=2 sws=2: *}
