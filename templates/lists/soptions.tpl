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


{include file="lists/header_listes.tpl" on=soptions}

<h1>
  Changer les options de la liste {$details.addr}
</h1>

<form method='post' action='{$platal->pl_self(1)}'>
  {xsrf_token_field}
  <table class='bicol' cellpadding='2' cellspacing='0'>
    <tr><th colspan='2'>Options de la liste {$details.addr}</th></tr>
    <tr class='impair'>
      <td>
        <strong>msg_header&nbsp;:</strong><br />
        <span class='smaller'>ajouté au début de tous les messages.</span>
      </td>
      <td>
        <textarea cols='40' rows='8' name='msg_header'>{$options.msg_header|smarty:nodefaults|utf8_encode}</textarea>
      </td>
    </tr>
    <tr class='impair'>
      <td>
        <strong>msg_footer&nbsp;:</strong><br />
        <span class='smaller'>ajouté à la fin de tous les messages.</span>
      </td>
      <td>
        <textarea cols='40' rows='8' name='msg_footer'>{$options.msg_footer|smarty:nodefaults|utf8_encode}</textarea>
      </td>
    </tr>
    <tr><th colspan='2'>Options avancées de la liste {$details.addr}</th></tr>
    <tr class='impair'>
      <td>
        <strong>visibilité&nbsp;:</strong><br />
        <span class='smaller'>si coché, la liste sera listée dans la page de l'ensemble des listes
          (les listes d'admin doivent être décochées).</span>
      </td>
      <td>
        <label><input type='checkbox' name='advertised' {if $options.advertised}checked='checked'{/if} />
        publique</label>
      </td>
    </tr>
    <tr class='pair'>
      <td>
        <strong>archive&nbsp;:</strong><br />
        <span class='smaller'>liste archivée&nbsp;?</span>
      </td>
      <td>
        <label><input type='checkbox' name='archive' {if $options.archive}checked='checked'{/if} />
        liste archivée</label>
      </td>
    </tr>
    <tr class='impair'>
      <td>
        <strong>max_message_size&nbsp;:</strong><br />
        <span class='smaller'>taille maximale des posts en Ko.</span>
      </td>
      <td>
        <input type='text' name='max_message_size' value='{$options.max_message_size}' /> Ko
      </td>
    </tr>
  </table>

  <div class='center'>
    <br />
    <input type='submit' name='submit' value='Valider les modifications' />
  </div>
</form>


{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
