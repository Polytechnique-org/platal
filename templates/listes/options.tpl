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

{if $no_list || ( !$details.own && $smarty.session.perms neq admin )}

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
[<a href='index.php'>listes</a>] »
[<a href='members.php?liste={$smarty.request.liste}'>{$smarty.request.liste}</a>]
[<a href='trombi.php?liste={$smarty.request.liste}'>trombino</a>] »
[<a href='moderate.php?liste={$smarty.get.liste}'>modération</a>]
[<a href='admin.php?liste={$smarty.get.liste}'>abonnés</a>]
[options]
{perms level=admin} »
[<a href='soptions.php?liste={$smarty.get.liste}'>Soptions</a>]
[<a href='check.php?liste={$smarty.get.liste}'>check</a>]
{/perms}
</p>

<form method='post' action='{$smarty.server.REQUEST_URI}'>
  <table class='bicol' cellpadding='2' cellspacing='0'>
    <tr><th colspan='2'>Options de la liste {$details.addr}</th></tr>
    <tr class='impair'>
      <td>
        <strong>description :</strong><br />
        <span class='smaller'>une courte phrase pour décrire la liste.</span>
      </td>
      <td>
        <input type='text' size='40' name='description' value="{$options.description}" />
      </td>
    </tr>
    <tr class='impair'>
      <td>
        <strong>info :</strong><br />
        <span class='smaller'>une description plus longue de la liste.</span>
      </td>
      <td>
        <textarea cols='40' rows='8' name='info'>{$options.info}</textarea>
      </td>
    </tr>
    <tr class='pair'>
      <td>
        <strong>welcome_msg :</strong><br />
        <span class='smaller'>un texte de bienvenue incorporé au mail envoyé aux nouveaux
          inscrits.</span>
      </td>
      <td>
        <textarea cols='40' rows='8' name='welcome_msg'>{$options.welcome_msg}</textarea>
      </td>
    </tr>
    <tr class='impair'>
      <td>
        <strong>goodbye_msg :</strong><br />
        <span class='smaller'>un texte d'au revoir incorporé au mail de départ envoyé aux
          utilisateurs qui se désinscrivent.  Ce mail peut être désactivé</span>
      </td>
      <td>
        <input type='checkbox' name='send_goodbye_msg'
        {if $options.send_goodbye_msg}checked='checked'{/if} /> activer le mail de départ.  <br />
        <textarea cols='40' rows='8' name='goodbye_msg'>{$options.goodbye_msg}</textarea>
      </td>
    </tr>
    <tr><th colspan='2'>Options avancées de la liste {$details.addr}</th></tr>
    <tr class='impair'>
    <td>
        <strong>subject_prefix :</strong><br />
        <span class='smaller'>Un préfixe ajouté dans le sujet de chaque mail envoyé sur la liste.</span>
      </td>
      <td>
        <input type='text' name='subject_prefix' size='40' value="{$options.subject_prefix}" />
      </td>
    </tr>
    <tr class='impair'>
      <td>
        <strong>admin_notify_mchanges :</strong><br />
        <span class='smaller'>être notifé des inscriptions/désinscriptions sur cette liste.</span>
      </td>
      <td>
        <input type='checkbox' name='admin_notify_mchanges'
        {if $options.admin_notify_mchanges}checked='checked'{/if} /> Notification activée.
      </td>
    </tr>
    <tr class='impair'>
      <td>
        <strong>subscribe_policy :</strong><br />
        <span class='smaller'>détermine si les inscriptions à la liste sont modérées ou non.</span>
      </td>
      <td>
        <input type='checkbox' name='subscribe_policy'
        {if $options.subscribe_policy eq 2}checked='checked'{/if} /> Inscription modérée.
      </td>
    </tr>
  </table>

  <div class='center'>
    <br />
    <input type='submit' name='submit' value="Valider les modifications" />
  </div>
</form>

{if $details.diff eq 1}

<div class='rubrique'>
  Addresses non modérées de {$details.addr}
</div>
<p>
Les envoi des personnes utilisant les adresses ne sont pas modérés.
</p>

<p class='erreur'>
Attention, cette liste est à utiliser pour des non-X ou des non-inscrits à la liste :
</p>
<p>
les X inscrits à la liste doivent ajouter leurs adresses usuelles parmis leurs adresses de
redirection en mode 'inactif'. le logiciel de mailing list saura se débrouiller tout seul.
</p>

<form method='post' action='{$smarty.server.REQUEST_URI}'>
  <table class='tinybicol' cellpadding='2' cellspacing='0'>
    <tr><th>Addresses non modérées</th></tr>
    <tr>
      <td>
        {if $options.accept_these_nonmembers|@count}
        {foreach from=$options.accept_these_nonmembers item=addr}
        {$addr}<a href='?liste={$smarty.get.liste}&amp;atn_del={$addr}'>
          <img src='{"images/retirer.gif"|url}' alt='retirer de la whitelist' />
        </a><br />
        {/foreach}
        {else}
        <em>vide</em>
        {/if}
      </td>
    </tr>
    <tr class='center'>
      <td>
        <input type='text' size='32' name='atn_add' />
        &nbsp;
        <input type='submit' value='ajouter' />
      </td>
    </tr>
  </table>
</form>
{/if}

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
