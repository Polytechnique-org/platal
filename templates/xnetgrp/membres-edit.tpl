{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2015 Polytechnique.org                             *}
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

<script type="text/javascript">
{literal}
  function showInformations(box)
  {
      var state = (box.value != 'virtual') ? '' : 'none';
      document.getElementById('prenom').style.display = state;
      document.getElementById('sexe').style.display = state;
      document.getElementById('password').style.display = state;
  }
{/literal}
</script>

<h1>{$asso->nom}&nbsp;: gestion des membres</h1>

<p>
[<a href='{$platal->ns}annuaire'>Retour à l'annuaire</a>]
</p>

<h2>
  Édition du profil de {profile user=$user groupperms=false sex=false promo=true}
  {if $user->bestEmail()}
  <a href="mailto:{$user->bestEmail()}">{icon name=email title="mail"}</a>
  {/if}
</h2>

{if $user->type eq 'x' || $user->type eq 'master' || $user->type eq 'phd'}
{if $user->state eq 'pending'}
<p>
  {"Ce"|sex:"Cette":$user} camarade n'est pas {"inscrit"|sex:"inscrite":$user}.
  <a href="{$globals->xnet->xorg_baseurl}marketing/public/{$user->login()}" class='popup'>Si tu connais son adresse email,
    <strong>n'hésite pas à nous la transmettre !</strong>
  </a>
</p>
{elseif $user->state neq 'disabled' && $user->lost}
<p>
  {"Ce"|sex:"Cette":$user} camarade n'a plus d'adresse de redirection valide.
  <a href="{$globals->xnet->xorg_baseurl}marketing/broken/{$user->login()}">
    Si tu en connais une, <strong>n'hésite pas à nous la transmettre</strong>.
  </a>
</p>
{/if}
{/if}

<form method="post" action="{$platal->ns}member/{$platal->argv[1]}">
  {xsrf_token_field}
  <table cellpadding="0" cellspacing="0" class='tinybicol'>
    {if hasPerm('admin')}
    <tr class="pair">
      <td class="titre">
        Identifiant unique&nbsp;:
      </td>
      <td>
        {$user->hruid}
        <a href="https://www.polytechnique.org/admin/user/{$user->hruid}">{icon name="user_edit" title="Administer"}</a>
      </td>
    </tr>
    {/if}
    <tr class="pair">
      <td class="titre">
        Permissions&nbsp;:
      </td>
      <td>
        <select name="group_perms">
          <option value="membre" {if $user->group_perms eq 'membre'}selected="selected"{/if}>Membre</option>
          <option value="admin" {if $user->group_perms eq 'admin'}selected="selected"{/if}>Animateur</option>
        </select>
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">
        Poste&nbsp;:
      </td>
      <td>
        <select name="group_position">
          <option value=""{if $user->group_position eq ''} selected="selected"{/if}></option>
          {foreach from=$positions item=position}
          <option value="{$position}"{if $user->group_position eq $position} selected="selected"{/if}>{$position}</option>
          {/foreach}
        </select>
      </td>
    </tr>
    {if $user->type eq 'virtual' || ($user->type eq 'xnet' && !$user->perms)}
    <tr class="impair">
      <td class="titre">
        Type d'utilisateur&nbsp;:
      </td>
      <td>
        <select name="type" onchange="showInformations(this); return true">
          <option value="xnet" {if $user->type neq 'virtual'}selected="selected"{/if}>Personne physique</option>
          <option value="virtual" {if $user->type eq "virtual"}selected="selected"{/if}>Personne morale</option>
        </select>
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">Nom complet&nbsp;:</td>
      <td>{$user->fullName()}</td>
    </tr>
    <tr class="impair">
      <td class="titre">Nom annuaire&nbsp;:</td>
      <td>{$user->directoryName()}</td>
    </tr>
    <tr class="impair">
      <td class="titre">Nom&nbsp;:</td>
      <td>
        <input type="text" value="{$user->lastname}" name="lastname" size="40" />
      </td>
    </tr>
    {if $user->type neq "virtual"}
    <tr class="impair">
      <td class="titre">Prénom&nbsp;:</td>
      <td>
        <input type="text" value="{$user->firstname}" name="firstname" size="40" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">Nom affiché&nbsp;:</td>
      <td>
        <input type="text" value="{$user->displayName()}" name="display_name" size="40" />
      </td>
    </tr>
    {/if}
    <tr id="sexe" class="impair" {if $user->type eq "virtual"}style="display: none"{/if}>
      <td class="titre">
        Sexe&nbsp;:
      </td>
      <td>
        <select name="sex">
          <option value="male"{if !$user->isFemale()} selected="selected"{/if}>Homme</option>
          <option value="female"{if $user->isFemale()} selected="selected"{/if}>Femme</option>
        </select>
      </td>
    </tr>
    {if !$user->perms}
    <tr class="impair">
      <td class="titre">
        Email&nbsp;:
      </td>
      <td>
        <input type="text" value="{$user->forlifeEmail()}" name="email" size="40" />
      </td>
    </tr>
    {/if}
    {/if}
    <tr class="impair">
      <td class="titre">
        Commentaire&nbsp;:
      </td>
      <td>
        <input type="text" name="comm" value="{$user->group_comm}" size="40" maxlength="255" /><br />
        <small>Poste, origine&hellip; (accessible à toutes les personnes autorisées à consulter l'annuaire)</small>
      </td>
    </tr>
    {if $asso->has_nl}
    <tr class="impair">
      <td class="titre">
        Newsletter&nbsp;:
      </td>
      <td>
        <label>Inscrit<input type="radio" name="newsletter" value="1" {if $nl_registered eq 1}checked="checked"{/if} /></label>
        &nbsp;-&nbsp;
        <label><input type="radio" name="newsletter" value="0" {if $nl_registered eq 0}checked="checked"{/if} />Non inscrit</label>
      </td>
    </tr>
    {/if}
    {if $user->type eq 'xnet'}
    {include file="xnetgrp/members_new_form.tpl" registered=true}
    {/if}
    {if $user->type eq 'xnet' && $suggest}
    <tr>
      <td colspan="2">
        <label>
          <input type="checkbox" name="suggest" />
          coche cette case si tu souhaites qu'un compte «&nbsp;Extérieur&nbsp;» soit créé
          pour cette personne et que nous lui envoyions un email afin qu'il ait
          accès aux nombreuses fonctionnalités de Polytechnique.net (inscription
          aux évènements, télépaiement, modération des listes de diffusion&hellip;)
        </label>
      </td>
    </tr>
    {/if}
    {if $user->type eq 'xnet' && $pending_xnet_account}
    <tr>
      <td colspan="2">
        <label>
          <input type="checkbox" name="again" />
          Cette personne a un compte «&nbsp;Extérieur&nbsp;» en attente d'activation de sa part. Pour la relancer, il suffit
          de cocher la case ci-contre.
        </label>
      </td>
    </tr>
    {/if}
  </table>

  {if $user->bestEmail()}
  <h2>Abonnement aux listes</h2>

  <table cellpadding="0" cellspacing="0" class='large'>
    <tr>
      <th>&nbsp;</th>
      <th>Liste</th>
      <th>Description</th>
      <th>Nb</th>
    </tr>
    {foreach from=$listes item=liste}
    <tr>
      <td class='right'>
        <input type='hidden' name='ml1[{$liste.list}]' value='{$liste.sub}' />
        <input type='checkbox' name='ml2[{$liste.list}]' {if $liste.sub eq 2}checked="checked"{/if} />
      </td>
      <td>
        <a href='{$platal->ns}lists/members/{$liste.list}'>{$liste.list}</a>
      </td>
      <td>{$liste.desc|smarty:nodefaults}</td>
      <td class='right'>{$liste.nbsub}</td>
    </tr>
    {foreachelse}
    <tr><td colspan='4'>Pas de listes pour ce groupe</td></tr>
    {/foreach}
  </table>

  <h2>Abonnement aux alias</h2>

  <table cellpadding="0" cellspacing="0" class='large'>
    <tr>
      <th>&nbsp;</th>
      <th>Alias</th>
    </tr>

    {foreach from=$alias key=address item=sub}
    <tr>
      <td align='right'>
        <input type='hidden' name='ml3[{$address}]' value='{$sub}' />
        <input type='checkbox' name='ml4[{$address}]' {if $sub}checked="checked"{/if} />
      </td>
      <td>
        <a href='{$platal->ns}alias/admin/{$address}'>{$address}</a>
      </td>
    </tr>
    {foreachelse}
    <tr><td colspan='2'>Pas d'alias pour ce groupe</td></tr>
    {/foreach}
  </table>
  {else}
  <p>Cette personne n'a pas d'email renseigné sur le site et ne peut donc pas être inscrite aux listes de diffusions et aux alias du groupe.</p>
  {/if}

  <div class="center">
    <br />
    <input type="submit" name='change' value="Valider ces changements" onclick="return hashResponse('new_plain_password', false, false, false);" />
    &nbsp;
    <input type="reset" value="Annuler ces changements" />
  </div>

</form>


{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
