{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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


{if !$details.own}
<p class='erreur'>
Tu n'es pas administrateur de la liste, mais du site.
</p>
{/if}

{include file="listes/header_listes.tpl" on=options}

<h1>
  Changer les options de la liste {$details.addr}
</h1>

<form method='post' action='{$platal->ns}lists/options/{$platal->argv[1]}'>
  <table class='bicol' cellpadding='2' cellspacing='0'>
    <tr><th colspan='2'>Options de la liste {$details.addr}</th></tr>
    <tr class='impair'>
      <td>
        <strong>description courte :</strong><br />
        <span class='smaller'>une courte phrase pour décrire la liste.</span>
      </td>
      <td>
        <input type='text' size='40' name='description' value="{$options.description}" />
      </td>
    </tr>
    <tr class='impair'>
      <td>
        <strong>description longue :</strong><br />
        <span class='smaller'>une description plus longue de la liste.</span>
      </td>
      <td>
        <textarea cols='40' rows='8' name='info'>{$options.info}</textarea>
      </td>
    </tr>
    <tr class='pair'>
      <td>
        <strong>message de bienvenue :</strong><br />
        <span class='smaller'>un texte de bienvenue incorporé au mail envoyé aux nouveaux
         abonnés à la liste.</span>
      </td>
      <td>
        <textarea cols='40' rows='8' name='welcome_msg'>{$options.welcome_msg}</textarea>
      </td>
    </tr>
    <tr class='impair'>
      <td>
        <strong>message d'adieu :</strong><br />
        <span class='smaller'>un texte d'au revoir incorporé au mail de départ envoyé aux
          utilisateurs qui se désinscrivent.  Ce mail peut être désactivé.</span>
      </td>
      <td>
        <input type='checkbox' name='send_goodbye_msg'
        {if $options.send_goodbye_msg}checked='checked'{/if} /> activer le mail d'adieu.  <br />
        <textarea cols='40' rows='8' name='goodbye_msg'>{$options.goodbye_msg}</textarea>
      </td>
    </tr>
    <tr><th colspan='2'>Options avancées de la liste {$details.addr}</th></tr>
    <tr class='impair'>
    <td>
        <strong>ajout dans le sujet :</strong><br />
        <span class='smaller'>Un préfixe (optionnel) ajouté dans le sujet de chaque mail envoyé sur la liste te permet de trier plus facilement ton courrier.</span>
      </td>
      <td>
        <input type='text' name='subject_prefix' size='40' value="{$options.subject_prefix}" />
      </td>
    </tr>
    <tr class='impair'>
      <td>
        <strong>notification de (dés)abonnement :</strong><br />
        <span class='smaller'>notifier les modérateurs des inscriptions/désinscriptions d'utilisateurs sur cette liste.</span>
      </td>
      <td>
        <input type='checkbox' name='admin_notify_mchanges'
        {if $options.admin_notify_mchanges}checked='checked'{/if} /> Notification activée.
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
    <tr class='impair'>
      <td>
        <strong>inscription libre ou modérée :</strong><br />
        <span class='smaller'>détermine si les inscriptions à la liste sont modérées ou non.</span>
      </td>
      <td>
        <input type='checkbox' name='subscribe_policy'
        {if $options.subscribe_policy eq 2}checked='checked'{/if} /> Inscription modérée.
      </td>
    </tr>
    <tr class='impair'>
      <td>
        <strong>antispam :</strong><br />
        <span class='smaller'>détermine la politique de l'antispam sur cette liste.</span>
      </td>
      <td>
        <em><a name='antispam' id='antispam'></a>que faire des mails marqués « [spam probable] » ?</em><br />
        <input type='radio' name='bogo_level' value='0' {if !$bogo_level}checked='checked'{/if} /> les laisser passer<br />
        <input type='radio' name='bogo_level' value='1' {if $bogo_level eq 1}checked='checked'{/if} /> les envoyer aux modérateurs pour approbation<br />
        <input type='radio' name='bogo_level' value='2' {if $bogo_level eq 2}checked='checked'{/if} /> les supprimer
      </td>
    </tr>
  </table>

  <div class='center'>
    <br />
    <input type='submit' name='submit' value="Valider les modifications" />
  </div>
</form>

{if $details.diff eq 1}

<h1>
  Adresses non modérées de {$details.addr}
</h1>
<p>
Les envois des personnes utilisant ces adresses ne sont pas modérés.
</p>

<p class='erreur'>
Attention, cette liste est à utiliser pour des non-X ou des non-inscrits à la liste :
</p>
<p>
les X inscrits à la liste doivent ajouter leurs adresses usuelles parmis leurs adresses de
redirection en mode 'inactif'. le logiciel de mailing list saura se débrouiller tout seul.
</p>

<form method='post' action='{$platal->ns}lists/options/{$platal->argv[1]}'>
  <table class='tinybicol' cellpadding='2' cellspacing='0'>
    <tr><th>Addresses non modérées</th></tr>
    <tr>
      <td>
        {if $options.accept_these_nonmembers|@count}
        {foreach from=$options.accept_these_nonmembers item=addr}
        {$addr}<a href='{$platal->ns}lists/options/{$platal->argv[1]}&amp;atn_del={$addr}'>
          <img src="images/retirer.gif" alt='retirer de la whitelist' title="retirer {$addr} de la whitelist" />
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


{* vim:set et sw=2 sts=2 sws=2: *}
