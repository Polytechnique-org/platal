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

<h1>{if $asso->nom}{$asso->nom}&nbsp;: {/if}Éditer l'accueil</h1>

<form method="post" action="{$platal->ns}edit" enctype="multipart/form-data">
  {xsrf_token_field}
  {if $super}
  <table cellpadding="0" cellspacing="0" class='tiny'>
    <tr>
      <td class="titre">
        Nom&nbsp;:
      </td>
      <td>
        <input type="text" size="40" value="{if $error}{$nom}{else}{$asso->nom}{/if}" name="nom" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Diminutif&nbsp;:
      </td>
      <td>
        <input type="text" size="40" value="{if $error}{$diminutif}{else}{$asso->diminutif}{/if}" name="diminutif" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Domaine DNS&nbsp;:
      </td>
      <td>
        <input type="text" size="40" value="{if $error}{$mail_domain}{else}{$asso->mail_domain}{/if}" name="mail_domain" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Catégorie&nbsp;:
      </td>
      <td>
        <select name="cat">
          <option value="groupesx" {if $cat eq 'GroupesX'}selected="selected"{/if}>Groupes X</option>
          <option value="binets" {if $cat eq 'Binets'}selected="selected"{/if}>Binets</option>
          <option value="promotions" {if $cat eq 'Promotions'}selected="selected"{/if}>Promotions</option>
          <option value="institutions" {if $cat eq 'Institutions'}selected="selected"{/if}>Institutions</option>
        </select>
      </td>
    </tr>
    <tr>
      <td class="titre">
        Domaine&nbsp;:
      </td>
      <td>
        <select name="dom">
          <option value="">&nbsp;</option>
          {iterate from=$domains item=d}
          <option value="{$d.id}" {if $d.id eq $dom}selected="selected"{/if}>{$d.nom} [{$d.cat}]</option>
          {/iterate}
        </select>
      </td>
    </tr>
    <tr>
      <td class="titre center" colspan="2">
        <label><input type="checkbox" value="1" name="ax" {if $ax}checked="checked"{/if} />
        groupe agréé par l'AX</label> le <input type="text" size="10" maxlength="10" value="{if $error}{$axDate}{else}{$asso->axDate}{/if}" name="axDate" />
        <small>(ex: 01/01/1970)</small>
      </td>
    </tr>
    <tr>
      <td class="titre center" colspan="2">
        <label><input type="checkbox" value="1" name="disable_mails" {if $disable_mails}checked="checked"{/if} />
        désactiver l'envoi de mails</label>
      </td>
    </tr>
  </table>
  <p></p>
  {/if}

  <table cellpadding="0" cellspacing="0" class='tiny'>
    <tr>
      <td class="titre">
        Logo&nbsp;:
      </td>
      <td>
        <input type="file" name="logo" />
      </td>
    </tr>

    <tr>
      <td class="titre">
        Site web&nbsp;:
      </td>
      <td>
        <input type="text" size="40" value="{if $error}{$site}{else}{$asso->site|default:"http://"}{/if}" name="site" />
      </td>
    </tr>

    <tr>
      <td class="titre">
        Contact&nbsp;:
      </td>
      <td>
        <input type="text" size="40" name="resp" value="{if $error}{$resp}{else}{$asso->resp}{/if}" />
      </td>
    </tr>

    <tr>
      <td class="titre">
        Adresse email&nbsp;:
      </td>
      <td>
        <input type="text" size="40" name="mail" value="{if $error}{$mail}{else}{$asso->mail}{/if}" />
      </td>
    </tr>

    <tr>
      <td class="titre">Téléphone</td>
      <td>
        <input type="text" maxlength="28" name="phone" value="{if $error}{$phone}{else}{$asso->phone}{/if}" />
      </td>
    </tr>
    <tr>
      <td class="titre">Fax</td>
      <td>
        <input type="text" maxlength="28" name="fax" value="{if $error}{$fax}{else}{$asso->fax}{/if}" />
      </td>
    </tr>
    <tr>
      <td class="titre">Adresse</td>
      <td>
        <textarea name="address" cols="30" rows="4">{if $error}{$address}{else}{$asso->address}{/if}</textarea>
      </td>
    </tr>

    <tr>
      <td class="titre">
        Forum&nbsp;:
      </td>
      <td>
        <input type="text" size="40" name="forum" value="{if $error}{$forum}{else}{$asso->forum}{/if}" />
      </td>
    </tr>

    <tr>
      <td class="titre">
        Inscription possible&nbsp;:
      </td>
      <td>
        <label><input type="radio" value="1" {if $inscriptible eq 1}checked="checked"{/if} name="inscriptible" />oui</label>
        <label><input type="radio" value="0" {if $inscriptible neq 1}checked="checked"{/if} name="inscriptible" />non</label>
      </td>
    </tr>

    <tr>
      <td class="titre">
        Notifier les demandes d'inscriptions&nbsp;:
      </td>
      <td>
        <label><input type="radio" value="1" {if $notify_all}checked="checked"{/if} name="notify_all"
          onclick="$('#notification').hide()"/>à tous les animateurs</label>
        <label><input type="radio" value="0" {if !$notify_all}checked="checked"{/if} name="notify_all"
          onclick="$('#notification').show()" />seulement à certains</label>
      </td>
    </tr>
    <tr id="notification" {if $notify_all}style="display: none"{/if}>
      <td></td>
      <td>
      {if $notified || $unnotified}
        <ul>
        {if $notified}
        {foreach from=$notified item=user}
          <li>
            <label><input type="checkbox" name="to_notify_{$user->id()}" checked="checked" />{$user->fullName(true)}</label>
          </li>
        {/foreach}
        {/if}
        {if $unnotified}
        {foreach from=$unnotified item=user}
          <li>
            <label><input type="checkbox" name="to_notify_{$user->id()}" />{$user->fullName(true)}</label>
          </li>
        {/foreach}
        {/if}
        </ul>
      {/if}
      </td>
    </tr>

    <tr>
      <td class="titre">
        Lien pour l'inscription&nbsp;:<br />
        <em>laisser vide par défaut</em>
      </td>
      <td>
        <input type="text" size="40" name="sub_url" value="{if $error}{$sub_url}{else}{$asso->sub_url}{/if}" />
      </td>
    </tr>

    <tr>
      <td class="titre">
        Lien pour la désinscription&nbsp;:<br/>
        <em>laisser vide par défaut</em>
      </td>
      <td>
        <input type="text" size="40" name="unsub_url" value="{if $error}{$unsub_url}{else}{$asso->unsub_url}{/if}" />
      </td>
    </tr>

    <tr>
      <td class="titre">
        Message de bienvenue&nbsp;:<br />
        <em>envoyé à l'inscription</em>
      </td>
      <td>
        <textarea cols='40' rows='8' name='welcome_msg'>{if $error}{$welcome_msg}{else}{$asso->welcome_msg}{/if}</textarea>
      </td>
    </tr>

    <tr>
      <td class="titre center" colspan="2">
        Diffusion de la liste des membres&nbsp;:
        <select name="pub">
          <option value="public" {if $pub eq 'public'}selected="selected"{/if}>Publique</option>
          <option value="membre" {if $pub eq 'membre'}selected="selected"{/if}>Aux membres du groupe</option>
          <option value="private" {if $pub eq 'private'}selected="selected"{/if}>Aux animateurs du groupe</option>
        </select>
      </td>
    </tr>
    <tr>
      <td class="titre center" colspan="2">
        <label><input type="checkbox" value="1" name="notif_unsub" {if $notif_unsub}checked="checked"{/if} />
        prévenir les animateurs lors de la désinscription d'un membre</label>
      </td>
    </tr>
    <tr>
      <td class="titre">
        État du groupe&nbsp;:
      </td>
      <td>
        <select name="status">
          <option value="active" {if $status eq 'active'}selected="selected"{/if}>Actif</option>
          <option value="inactive" {if $status eq 'inactive'}selected="selected"{/if}>Inactif (visible sur la page "tous les groupes")</option>
          <option value="dead" {if $status eq 'dead'}selected="selected"{/if}>Mort (absent de la page "tous les groupes")</option>
        </select>
      </td>
    </tr>
  </table>

  <div class="center">
    <input type="submit" name="submit" value="Enregistrer" />
  </div>

  <div class="center">
    <div id="preview_descr" style="display: none; text-align: justify"></div>
    <br />
    <a href="wiki_help" class="popup3">
      {icon name=information title="Syntaxe wiki"} Voir la syntaxe wiki autorisée pour la description.
    </a>
    <textarea name="descr" cols="70" rows="15" id="descr">{if $error}{$descr}{else}{$asso->descr}{/if}</textarea>
    <input type="submit" name="preview" value="Aperçu de la description"
           onclick="previewWiki('descr', 'preview_descr', true, 'preview_descr'); return false;" /><br />
    <input type="submit" name="submit" value="Enregistrer" />
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
