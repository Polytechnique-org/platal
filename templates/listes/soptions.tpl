{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http ://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id : admin.tpl,v 1.4 2004/09/23 18:47:00 x2000habouzit Exp $
 ***************************************************************************}

{dynamic}

{if $no_list || $smarty.session.perms neq admin }

<p class='erreur'>La liste n'existe pas ou tu n'as pas le droit de l'administrer</p>

{else}

<div class='rubrique'>
  Changer les options de la liste {$details.addr}
</div>
{if !$details.own}
<p class='erreur'>
Tu n'es pas administrateur de la liste, mais du site.
</p>
{/if}

<p>
[<a href='index.php'>listes</a>] &gt;
[<a href='moderate.php?liste={$smarty.get.liste}'>modération</a>]
[<a href='admin.php?liste={$smarty.get.liste}'>abonnés</a>]
[<a href='options.php?liste={$smarty.get.liste}'>options</a>]
{perms level=admin}
[Soptions]
{/perms}
</p>

<form method='post' action='{$smarty.server.REQUEST_URI}'>
  <table class='bicol' cellpadding='2' cellspacing='0'>
    <tr><th colspan='2'>Options de la liste {$details.addr}</th></tr>
    <tr class='impair'>
      <td>
        <strong>msg_header :</strong><br />
        <span class='smaller'>ajouté au début de tous les messages.</span>
      </td>
      <td>
        <textarea cols='40' rows='8' name='msg_header'>{$options.msg_header}</textarea>
      </td>
    </tr>
    <tr class='impair'>
      <td>
        <strong>msg_footer :</strong><br />
        <span class='smaller'>ajouté à la fin de tous les messages.</span>
      </td>
      <td>
        <textarea cols='40' rows='8' name='msg_footer'>{$options.msg_footer}</textarea>
      </td>
    </tr>
    <tr><th colspan='2'>Options avancées de la liste {$details.addr}</th></tr>
    <tr class='impair'>
      <td>
        <strong>visibilité :</strong><br />
        <span class='smaller'>si coché, la liste sera listée dans listes/index.php.
          (les listes d'admin doivent être déochées)</span>
      </td>
      <td>
        <input type='checkbox' name='advertised' {if $options.advertised}checked='checked'{/if} />
        publique
      </td>
    </tr>
    <tr class='impair'>
      <td>
        <strong>diffusion :</strong>
      </td>
      <td>
        <input type='radio' name='moderate' value='0'
        {if !$options.generic_nonmember_action && !$options.default_member_moderation}
        checked='checked'{/if} />libre<br />
        <input type='radio' name='moderate' value='1'
        {if $options.generic_nonmember_action && !$options.default_member_moderation}
        checked='checked'{/if} />modérée aux extérieurs<br />
        <input type='radio' name='moderate' value='2'
        {if $options.generic_nonmember_action && $options.default_member_moderation}
        checked='checked'{/if} />modérée
      </td>
    </tr>
    <tr class='pair'>
      <td>
        <strong>archive :</strong><br />
        <span class='smaller'>Liste archivée ?</span>
      </td>
      <td>
        <input type='checkbox' name='archive' {if $options.archive}checked='checked'{/if} />
        liste archivée
      </td>
    </tr>
    <tr class='pair'>
      <td>
        <strong>max_message_size :</strong><br />
        <span class='smaller'>Taille maximale des posts en Ko:</span>
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

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
