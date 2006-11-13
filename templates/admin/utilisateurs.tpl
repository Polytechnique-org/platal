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


{if $smarty.session.suid}
<p class="erreur">
Attention, déjà en SUID !!!
</p>
{/if}

<h1>
  Gestion des utilisateurs
</h1>


{if $smarty.post.u_kill_conf}
<form method="post" action="admin/user">
  <div class="center">
    <input type="hidden" name="user_id" value="{$smarty.request.user_id}" />
    Confirmer la suppression de {$smarty.request.user_id}&nbsp;&nbsp;
    <input type="submit" name="u_kill" value="continuer" />
  </div>
</form>
{else}

<form method="post" action="admin/user">
  <table class="tinybicol" cellspacing="0" cellpadding="2">
    <tr>
      <th>
        Administrer
      </th>
    </tr>
    {if !$smarty.request.login && !$mr.forlife}
    <tr class="pair">
      <td class="center">
        Il est possible d'entrer ici n'importe quelle adresse mail : redirection, melix, ou alias.
      </td>
    </tr>
    {/if}
    <tr>
      <td class="center">
        <input type="text" name="login" size="40" maxlength="255" value="{$smarty.request.login|default:$mr.forlife}" />
      </td>
    </tr>
    <tr>
      <td class="center">
        <input type="hidden" name="hashpass" value="" />
        <input type="submit" name="select" value=" edit " /> &nbsp;&nbsp;
        <input type="submit" name="suid_button" value=" su " />  &nbsp;&nbsp;
        <input type="submit" name="ax_button" value=" AX " /> &nbsp;&nbsp;
        <input type="submit" name="logs_button" value=" logs " />
      </td>
    </tr>
  </table>
</form>

{if $mr}

<p class="smaller">
Derniére connexion le <strong>{$lastlogin|date_format:"%d %B %Y, %T"}</strong>
depuis <strong>{$host}</strong>
</p>

{literal}
<script type="text/javascript">
//<![CDATA[
function doEditUser() {
  document.forms.auth.hashpass.value = hash_encrypt(document.forms.edit.password.value);
  document.forms.auth.password.value = "";
  document.forms.auth.submit();
}

function del_alias(alias) {
  document.forms.alias.del_alias.value = alias;
  document.forms.alias.submit();
}

function del_fwd(fwd) {
  document.forms.fwds.del_fwd.value = fwd;
  document.forms.fwds.submit();
}
function act_fwd(fwd, activate) {
  if (activate)
    document.forms.fwds.activate_fwd.value = fwd;
  else
    document.forms.fwds.deactivate_fwd.value = fwd;
  document.forms.fwds.submit();
}
// ]]>
</script>
{/literal}

<form id="auth" method="post" action="admin/user">
  <table cellspacing="0" cellpadding="2" class="tinybicol">
    <tr>
      <th colspan="2">
        <div style="float: right; text-align: right">
          Matricule = {$mr.matricule}<br />
          Matricule AX = {$mr.matricule_ax}
        </div>
        <div style="float: left; text-align: left">
          UID = {$mr.user_id}<br />
          Inscription = {$mr.date_ins|date_format}
        </div>
        <input type="hidden" name="user_id" value="{$mr.user_id}" />
      </th>
    </tr>
    <tr class="pair">
      <td class="titre">
        Mot de passe
      </td>
      <td>
        <input type="text" name="newpass_clair" size="10" maxlength="10" value="********" />
        <input type="hidden" name="passw" size="32" maxlength="32" value="{$mr.password}" />
        <input type="hidden" name="hashpass" value="" />
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">
        Nom
      </td>
      <td>
        <input type="text" name="nomN" size="20" maxlength="255" value="{$mr.nom}" />
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">
        Nom d'usage
      </td>
      <td>
        <input type="text" name="nomusageN" size="20" maxlength="255" value="{$mr.nom_usage}" />
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">
        Prénom
      </td>
      <td>
        <input type="text" name="prenomN" size="20" maxlength="30" value="{$mr.prenom}" />
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">
        Sexe
      </td>
      <td>
        femme <input type="radio" name="sexeN" {if $mr.sexe eq 'femme'}checked="checked"{/if} value='femme'/>
        <input type="radio" name="sexeN" {if $mr.sexe neq 'femme'}checked="checked"{/if} value=''/> homme
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">
        Droits
      </td>
      <td>
        <select name="permsN">
          <option value="user" {if $mr.perms eq "user"}selected="selected"{/if}>user</option>
          <option value="admin" {if $mr.perms eq "admin"}selected="selected"{/if}>admin</option>
          <option value="pending" {if $mr.perms eq "pending"}selected="selected"{/if}>pending</option>
          <option value="disabled" {if $mr.perms eq "disabled"}selected="selected"{/if}>disabled</option>
        </select>
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">
        Date de naissance
      </td>
      <td>
        <input type="text" name="naissanceN" size="12" maxlength="10" value="{$mr.naissance}" />
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">
        Date de décès
      </td>
      <td>
        <input type="text" name="decesN" size="12" maxlength="10" value="{$mr.deces}" />
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">
        Promo
      </td>
      <td>
        <input type="text" name="promoN" size="4" maxlength="4" value="{$mr.promo}" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">
        Commentaire
      </td>
      <td>
        <input type="text" name="commentN" size="40" maxlength="64" value="{$mr.comment}" />
      </td>
    </tr>
    {if $mr.perms eq 'pending'}
    <tr class="center">
      <td colspan="2">
        <input onclick="doEditUser(); return true;" type="submit" name="u_edit" value="UPDATE" />
      </td>
    </tr>
    {else}
    <tr class="center">
      <td>
        <a href="profile/{$mr.forlife}" class="popup2">[Voir fiche]</a>
      </td>
      <td>
        <input onclick="doEditUser(); return true;" type="submit" name="u_edit" value="UPDATE" />
      </td>
    </tr>
    <tr class="center">
      <td>
        <a href="admin/trombino/{$mr.user_id}">[Trombino]</a>
      </td>
      <td>
        <input type="submit" name="u_kill_conf" value="Désinscrire" />
      </td>
    </tr>
    {/if}
  </table>
</form>
{if $mr.perms neq 'pending'}
<p>
Ne pas utiliser [Désinscrire] si le but est d'exclure la personne.
Pour ceci changer ses permissions en 'disabled'.
</p>
<form id="alias" method="post" action="admin/user">
  <table class="tinybicol" cellpadding="2" cellspacing="0">
    <tr>
      <th class="alias" colspan="2">
        Alias e-mail
      </th>
    </tr>
    {iterate from=$aliases item=a}
    <tr class="{cycle values="impair,pair"}">
      <td>
        <input type="radio" name='best' {if $a.best}checked="checked"{/if} value='{$a.alias}' onclick="this.form.submit()" />
        {if $a.for_life}<strong>{$a.alias}</strong>{else}{$a.alias}{/if}
        {if $a.expire}<span class='erreur'>(expire le {$a.expire|date_format})</span>{/if}
      </td>
      {if $a.for_life}
      <td>garanti à vie*</td>
      {else}
      <td class="action">
        <a href="javascript:del_alias('{$a.alias}')">delete</a>
      </td>
      {/if}
    </tr>
    {/iterate}
    <tr class="{cycle values="impair,pair"}">
      <td class="detail">
        <input type="text" name="email" size="29" maxlength="60" value="" />
      </td>
      <td class="action">
        <input type="hidden" name="user_id" value="{$mr.user_id}" />
        <input type="hidden" name="del_alias" value="" />
        <input type="submit" name="add_alias" value="Ajouter" />
      </td>
    </tr>
  </table>
</form>

<p>
<strong>* à ne modifier qu'avec l'accord express de l'utilisateur !!!</strong>
</p>

<form id="fwds" method="post" action="admin/user#fwds">
  <table class="bicol" cellpadding="2" cellspacing="0">
    <tr>
      <th colspan="4">
        Redirections
      </th>
    </tr>
    {foreach item=mail from=$emails}
    {cycle assign=class values="impair,pair"}
    <tr class="{$class}">
      <td class="titre">
        {if $mail->active}active{/if}
      </td>
      <td>
        <span class="smaller"><a href="javascript:act_fwd('{$mail->email}',{if $mail->active}false{else}true{/if})">{if $mail->active}des{elseif $mail->broken}ré{/if}activer</a></span>
      </td>
      <td>
        {if $mail->broken}<span style="color: #f00">{/if}
        {$mail->email}
        {if $mail->broken}<em> (en panne)</em></span>{/if}
      </td>
      <td class="action">
        <a href="javascript:del_fwd('{$mail->email}')">delete</a>
      </td>
    </tr>
    {if $mail->panne && $mail->panne neq "0000-00-00"}
    <tr class="{$class}">
      <td colspan="4" class="smaller" style="color: #f00">
        {icon name=error title="Panne"}
        Panne de {$mail->email} le {$mail->panne|date_format}
        {if $mail->panne neq $mail->last}confirmée le {$mail->last|date_format}{/if}
      </td>
    </tr>
    {/if}
    {/foreach}
    <tr class="{cycle values="impair,pair"}">
      <td class="titre" colspan="2">
        Ajouter un email
      </td>
      <td>
        <input type="text" name="email" size="29" maxlength="60" value="" />
      </td>
      <td class="action">
        <input type="hidden" name="user_id" value="{$mr.user_id}" />
        <input type="hidden" name="del_fwd" value="" />
        <input type="hidden" name="activate_fwd" value="" />
        <input type="hidden" name="deactivate_fwd" value="" />
        <input type="submit" name="add_fwd" value="Ajouter" />
      </td>
    </tr>
  </table>
</form>

{/if}
{/if}
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
