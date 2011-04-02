{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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


{if t($smarty.post.delete_account)}
<form method="post" action="admin/user/{$user->login()}">
  {xsrf_token_field}
  <fieldset>
    <legend>Confirmer la suppression de l'utilisateur {$user->hruid}</legend>

    {if $user->hasProfile()}
    <p>
      <input type="checkbox" name="clear_profile" /> Vider la fiche de
      l'utilisateur.
    </p>
    {else}
    <p>
      <input type="checkbox" name="erase_account" {if $user->state eq 'pending'}checked="checked"{/if} /> Supprimer le compte définitivement.
    </p>
    {/if}
    <p>
      <input type="hidden" name="uid" value="{$user->uid}" />
      <input type="submit" name="account_deletion_cancel" value="Annuler" />
      <input type="submit" name="account_deletion_confirmation" value="Confirmer" />
    </p>
  </fieldset>
</form>
{elseif t($smarty.post.erase_account)}
<p>
  <a href="admin/accounts">Retourner à la gestion des comptes</a>
</p>
{else}
{literal}

<script type="text/javascript">
//<![CDATA[
function del_alias(alias) {
  document.forms.alias.del_alias.value = alias;
  document.forms.alias.submit();
}

function del_profile(pid) {
  document.forms.profiles.del_profile.value = pid;
  document.forms.profiles.submit();
}

function del_fwd(fwd) {
  document.forms.fwds.del_fwd.value = fwd;
  document.forms.fwds.submit();
}

function del_openid(id) {
  document.forms.openid.del_openid.value = id;
  document.forms.openid.submit();
}

function act_fwd(fwd, activate) {
  if (activate)
    document.forms.fwds.activate_fwd.value = fwd;
  else
    document.forms.fwds.deactivate_fwd.value = fwd;
  document.forms.fwds.submit();
}
function clean_fwd(fwd) {
  document.forms.fwds.clean_fwd.value = fwd;
  document.forms.fwds.submit();
}
function ban_write()
{
    document.forms.bans.write_perm.value = "!xorg.*";
}
function ban_read()
{
    document.forms.bans.read_perm.value = "!xorg.*";
}

$(function() {
  $('#tabs').tabs();
  $('.ui-tabs-nav li').width('24%')
    .click(function() { $(this).children('a').click() });
});

// ]]>
</script>
{/literal}

<div id="tabs">
  <ul style="margin-top: 0">
    <li><a href="#account"><span>Compte de {$user->login()}</span></a></li>
    <li><a href="#emails"><span>Emails</span></a></li>
    <li><a href="#authext"><span>OpenID</span></a></li>
    <li><a href="#forums"><span>Forums</span></a></li>
  </ul>

<div id="account">
<form id="auth" method="post" action="admin/user/{$user->login()}#account">
  {xsrf_token_field}
  <h1>Informations sur le compte</h1>
  <p class="smaller">
    Dernière connexion le <strong>{$lastlogin|date_format:"%d %B %Y, %T"}</strong>
    depuis <strong>{$host}</strong>.
  </p>

  <table class="bicol">
    <tr>
      <th colspan="2">
        <div style="float: right; text-align: right">
          {if $user->state eq 'pending'}
          Non-inscrit
          {else}
          Inscrit le {$user->registration_date|date_format}
          {/if}
        </div>
        <div style="float: left; text-align: left">
          {icon name=user_gray} {$user->hruid} (uid {$user->id()})
        </div>
        <input type="hidden" name="uid" value="{$user->id()}" />
      </th>
    </tr>
    <tr>
      <td class="titre">Nom complet<br />
        <span class="smaller">Prénom NOM</span>
      </td>
      <td>{if $hasProfile}{$user->fullName()}{else}<input type="text" name="full_name" maxlength="255" value="{$user->fullName()}" />{/if}</td>
    </tr>
    <tr>
      <td class="titre">Nom annuaire<br />
        <span class="smaller">NOM Prénom</span>
      </td>
      <td>{if $hasProfile}{$user->directoryName()}{else}<input type="text" name="directory_name" maxlength="255" value="{$user->directoryName()}" />{/if}</td>
    </tr>
    <tr>
      <td class="titre">Nom affiché</td>
      <td>{if $hasProfile}{$user->displayName()}{else}<input type="text" name="display_name" maxlength="255" value="{$user->displayName()}" />{/if}</td>
    </tr>
    <tr>
      <td class="titre">Sexe</td>
      <td>
        <label>femme <input type="radio" name="sex" value="female" {if $user->isFemale()}checked="checked"{/if} /></label>
        <label><input type="radio" name="sex" value="male" {if !$user->isFemale()}checked="checked"{/if} /> homme</label>
      </td>
    </tr>
    <tr>
      <td class="titre">Email</td>
      <td>{if $user->checkPerms('mail')}{$user->forlifeEmail()}{else}<input type="text" name="email" size="40" maxlength="255" value="{$user->forlifeEmail()}" />{/if}</td>
    </tr>
    <tr class="impair">
      <td class="titre">Mot de passe</td>
      <td>
        <div style="float: left">
          <input type="text" name="new_plain_password" size="10" maxlength="256" value="********" />
          <input type="hidden" name="pwhash" value="" />
        </div>
        <div style="float: left; margin-top: 5px;">
          {checkpasswd prompt="new_plain_password" submit="dummy_none"}
        </div>
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">Mot de passe SMTP</td>
      <td>
        <div style="float: left">
          <input type="password" name="weak_password" size="10" maxlength="256" value="" />
          {if $user->weak_access}
          <input type="submit" name="disable_weak_access" value="Supprimer" />
          {/if}
        </div>
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">Accès RSS</td>
      <td>
        <label>
          <input type="checkbox" name="token_access" {if $user->token_access}checked="checked"{/if} value="1" />
          activer l'accès
        </label>
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">Skin</td>
      <td>
        <select name="skin">
          <option value="" {if !$user->skin}selected="selected"{/if}>Aucune (défaut du système)</option>
          {iterate from=$skins item=skin}
          <option value="{$skin.id}" {if $user->skin eq $skin.id}selected="selected"{/if}>{$skin.name}</option>
          {/iterate}
        </select>
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">Etat du compte</td>
      <td>
        <select name="state">
          <option value="pending" {if $user->state eq 'pending'}selected="selected"{/if}>pending (Non-inscrit)</option>
          <option value="active" {if $user->state eq 'active'}selected="selected"{/if}>active (Inscrit, peut se logguer)</option>
          <option value="disabled" {if $user->state eq 'disabled'}selected="selected"{/if}>disabled (Inscrit, accès interdit)</option>
        </select><br />
        <label>
          <input type="checkbox" name="is_admin" value="1" {if $user->is_admin}checked="checked"{/if} />
          administrateur du site
        </label>
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">Type de compte</td>
      <td>
        <select name="type">
          {iterate from=$account_types item=type}
          <option value="{$type.type}" {if $user->type eq $type.type}selected="selected"{/if}>{$type.type} ({$type.perms})</option>
          {/iterate}
        </select>
        <a href="admin/account/types">{icon name=wrench title=Gérer} gérer</a>
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">
        Surveillance
      </td>
      <td>
        <label><input type="checkbox" name="watch" {if $user->watch}checked="checked"{/if} value="1" />
        Surveiller l'activité de ce compte</label><br />
        <span class="smaller">Cette option permet d'avoir des logs complets de l'activité
        du compte via le logger, et d'être alerté lors des connexions de l'utilisateur.</span>
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">
        Commentaire
      </td>
      <td>
        <input type="text" name="comment" size="40" maxlength="64" value="{$user->comment}" />
      </td>
    </tr>
    <tr class="impair">
      <td colspan="2" class="center">
        <input type="submit" name="update_account" value="Mettre à jour" onclick="return hashResponse('new_plain_password', false, false, false);" />
        <input type="submit" name="su_account" value="Prendre l'identité" />
        <input type="submit" name="log_account" value="Consulter les logs" />
        {if $user->state neq 'pending'}
        <input type="submit" name="delete_account" value="Désinscrire" />
        {elseif !$user->hasProfile()}
        <input type="submit" name="delete_account" value="Supprimer le compte" />
        {/if}
      </td>
    </tr>
  </table>
</form>

<h1>Fiches associées au compte</h1>

<form id="profiles" method="post" action="admin/user/{$user->login()}#account">
  {xsrf_token_field}
  <table class="bicol">
    <tr>
      <th></th>
      <th>Identifiant de la fiche</th>
      <th></th>
    </tr>
    {iterate from=$profiles item=profile}
    <tr>
      <td><input type="radio" name="owner" value="{$profile.pid}" {if $profile.owner}checked="checked"{/if}
                 onclick="this.form.submit()" /></td>
      <td>{$profile.hrpid} (pid {$profile.pid})</td>
      <td class="right">
        <a href="profile/edit/{$profile.hrpid}">{icon name=user_edit}</a>
        <a href="profile/{$profile.hrpid}" class="popup2">{icon name=user_suit}</a>
        <a href="javascript:del_profile({$profile.pid})">{icon name=cross}</a>
      </td>
    </tr>
    {/iterate}
    {if $profiles->total() > 0}
    <tr>
      <td>
        <input type="radio" name="owner" value="0" onclick="this.form.submit()" />
      </td>
      <td>None</td>
      <td></td>
    </tr>
    {/if}
    <tr class="pair">
      <td colspan="3">
        <input type="hidden" name="del_profile" value="" />
        <input type="text" maxlength="64" name="new_profile" />
        <input type="submit" name="add_profile" value="Ajouter" />
      </td>
    </tr>
  </table>
</form>

<h1>Groupes dont l'utilisateur est membre</h1>

<table class="bicol">
  <tr>
    <th>Nom du groupe</th>
    <th>Permissions</th>
  </tr>
  {foreach from=$user->groups() item=group}
  <tr class="impair">
    <td>{$group.nom}</td>
    <td style="text-align: right">
      {$group.perms}
      <a href="http://www.polytechnique.net/{$group.diminutif}/member/{$user->hruid}">
      {icon name="user_edit" title="Modifier l'inscription"}
      </a>
    </td>
  </tr>
  {/foreach}
</table>

</div>

<div id="emails">
<h1>Gestion de l'adresse X.org</h1>

<form id="alias" method="post" action="admin/user/{$user->login()}#emails">
  {xsrf_token_field}
  <table class="bicol" cellpadding="2" cellspacing="0">
    <tr>
      <th class="alias" colspan="3">
        Alias email de l'utilisateur
      </th>
    </tr>
    {iterate from=$aliases item=a}
    <tr class="{cycle values="impair,pair"}">
      <td>
        <input type="radio" name='best' {if $a.bestalias}checked="checked"{/if} value='{$a.email}' onclick="this.form.submit()" />
      </td>
      <td>
        {if $a.forlife}<strong>{$a.email}</strong>{elseif $a.alias}<em>{$a.email}</em>{else}{$a.email}{/if}
        {if $a.expire}<span class='erreur'>(expire le {$a.expire|date_format})</span>{/if}
      </td>
      {if $a.forlife}
      <td>garanti à vie*</td>
      {else}
      <td class="action">
        <a href="javascript:del_alias('{$a.email}')">{icon name=cross}</a>
      </td>
      {/if}
    </tr>
    {/iterate}
    <tr class="{cycle values="impair,pair"}">
      <td colspan="2" class="detail">
        <input type="text" name="email" size="29" maxlength="255" value="" />
      </td>
      <td class="action">
        <input type="hidden" name="uid" value="{$user->id()}" />
        <input type="hidden" name="del_alias" value="" />
        <input type="submit" name="add_alias" value="Ajouter" />
      </td>
    </tr>
    <tr class="{cycle values="impair,pair"}">
      <td colspan="3" class="desc">
        <strong>* à ne modifier qu'avec l'accord express de l'utilisateur !!!</strong>
      </td>
    </tr>
  </table>
</form>

<br />

<form id="fwds" method="post" action="admin/user/{$user->login()}#emails">
  {xsrf_token_field}
  <table class="bicol" cellpadding="2" cellspacing="0">
    <tr>
      <th colspan="5">
        Redirections
      </th>
    </tr>
    {assign var=actives value=false}
    {assign var=disabled value=false}
    {foreach item=mail from=$emails}
    {cycle assign=class values="impair,pair"}
    <tr class="{$class}">
      {if $mail->active && $mail->has_disable()}
        {assign var=actives value=true}
      {elseif $mail->disabled && $mail->has_disable()}
        {assign var=disabled value=true}
      {/if}
      <td class="titre">
        {if $mail->active}active{elseif $mail->disabled}suspendue{/if}
      </td>
      <td>
        <span class="smaller">
          {if !$mail->disabled}
          <a href="javascript:act_fwd('{$mail->email}',{if $mail->active}false{else}true{/if})">
            {if $mail->active}des{elseif $mail->broken}ré{/if}activer
          </a>
          {/if}
        </span>
      </td>
      <td>
        {if $mail->broken}<span style="color: #f00">{/if}
        {if $mail->type == 'googleapps'}<a href="admin/googleapps/user/{$user->login()}">{/if}
        {$mail->display_email}
        {if $mail->type == 'googleapps'}</a>{/if}
        {if $mail->broken}<em> (en panne)</em></span>{/if}
      </td>
      <td>
        {if $mail->type != 'imap'}<span class="smaller">(niveau {$mail->filter_level} : {$mail->action})</span>{/if}
      </td>
      <td class="action">
        {if $mail->is_removable()}
        <a href="javascript:del_fwd('{$mail->email}')">{icon name=cross}</a>
        {/if}
      </td>
    </tr>
    {if $mail->broken && $mail->broken_date neq "0000-00-00"}
    <tr class="{$class}">
      <td colspan="4" class="smaller" style="color: #f00">
        {icon name=error title="Panne"}
        Panne de {$mail->display_email} le {$mail->broken_date|date_format}
        {if $mail->broken_date neq $mail->last}confirmée le {$mail->last|date_format}{/if}
      </td>
      <td class="action">
        <a href="javascript:clean_fwd('{$mail->email}')">effacer les pannes</a>
      </td>
    </tr>
    {/if}
    {/foreach}
    {cycle assign=class values="impair,pair"}
    <tr class="{$class}">
      <td class="titre" colspan="2">
        Ajouter une adresse
      </td>
      <td colspan="2">
        <input type="text" name="email" size="29" maxlength="255" value="" />
      </td>
      <td class="action">
        <input type="hidden" name="uid" value="{$user->id()}" />
        <input type="hidden" name="del_fwd" value="" />
        <input type="hidden" name="clean_fwd" value="" />
        <input type="hidden" name="activate_fwd" value="" />
        <input type="hidden" name="deactivate_fwd" value="" />
        <input type="submit" name="add_fwd" value="Ajouter" />
      </td>
    </tr>
    <tr class="{$class}">
      <td colspan="5" class="center">
        {if $actives}
        <input type="submit" name="disable_fwd" value="Désactiver la redirection des emails" />
        {/if}
        {if $disabled}
        <input type="submit" name="enable_fwd" value="Réactiver la redirection des emails" />
        {/if}
      </td>
    </tr>
  </table>
</form>

{test_email hruid=$user->login()}

<h1>Autres adresses de l'utilisateur</h1>

<table class="bicol">
  <tr>
    <th colspan="3">Mailing lists auquelles l'utilisateur appartient</th>
  </tr>
  {foreach from=$mlists item=mlist}
  <tr>
    <td>
      <a href="http://listes.polytechnique.org/members/{$mlist.addr|replace:"@":"_"}">
      {$mlist.addr}
      </a>
    </td>
    <td>
      <input type="checkbox" disabled="disabled" {if $mlist.sub}checked="checked"{/if} /> Membre
    </td>
    <td>
      <input type="checkbox" disabled="disabled" {if $mlist.own}checked="checked"{/if} /> Modérateur
    </td>
  </tr>
  {/foreach}
</table>

<br />
<table class="bicol">
  <tr>
    <th>Alias de groupe auquel l'utilisateur appartient</th>
  </tr>
  {foreach from=$virtuals item=virtual}
  <tr class="{cycle values="impair,pair"}">
    <td>{$virtual}</td>
  </tr>
  {/foreach}
</table>
</div>

<div id="authext">
<h1>Gestion des autorisations d'authentification externe</h1>

<form id="openid" method="post" action="admin/user/{$user->login()}#authext">
  {xsrf_token_field}
  <table class="bicol">
    <tr>
      <th colspan="2">Sites de confiance</th>
    </tr>
    {iterate from=$openid item=site}
    <tr class="{cycle values="pair,impair"}">
      <td><a href="{$site.url}">{$site.url}</a></td>
      <td><a href="javascript:del_openid({$site.id})">{icon name=cross}</a></td>
    </tr>
    {/iterate}
  </table>
  <div><input type="hidden" name="del_openid"/></div>
</form>
</div>

<div id="forums">
<h1>Gestion de l'accès au forums</h1>

<form id="bans" method="post" action="admin/user/{$user->login()}#forums">
  {xsrf_token_field}
  <table class="bicol">
    <tr>
      <th colspan="4">
        Permissions sur les forums
      </th>
    </tr>
    <tr class="impair">
      <td class="titre">
        Poster
      </td>
      <td>
        <input type="text" name="write_perm" size="32" maxlength="255" value="{$bans.write_perm}" />
      </td>
      <td class="action">
        <a href="javascript:ban_write()">Bannir</a>
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">
        Lire
      </td>
      <td>
        <input type="text" name="read_perm" size="32" maxlength="255" value="{$bans.read_perm}" />
      </td>
      <td class="action">
        <a href="javascript:ban_read()">Bannir</a>
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">
        Commentaire
      </td>
      <td colspan="2">
        <input type="text" name="comment" size="40" maxlength="255" value="{$bans.comment}" />
      </td>
    </tr>
    <tr class="center">
      <td colspan="3">
        <input type="hidden" name="uid" value="{$user->id()}" />
        <input type="submit" name="b_edit" value="Modifier" />
      </td>
    </tr>
  </table>
</form>
</div>
</div>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
