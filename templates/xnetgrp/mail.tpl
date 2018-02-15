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


<h1>{$asso->nom}&nbsp;: Envoyer un email</h1>

<p class="descr">
Ton message peut être personnalisé&nbsp;: si tu rentres les mots &lt;cher&gt;, &lt;prenom&gt;,
&lt;nom&gt;, ces mots seront remplacés, pour chacun des destinataires, par "cher" accordé au
masculin ou féminin, par son prénom, ou son nom.
</p>

<script type="text/javascript">//<![CDATA[
  {literal}
  function check(form)
  {
    if(form.sujet.value == "" && !confirm("Le sujet de l'email est vide, veux-tu continuer ?")) {
        form.sujet.focus();
        return false;
    }
    $('input[name=send]').hide()
  }

  function updateWikiView(box) {
    if (!box.checked) {
      document.getElementById("preview_bt").style.display = "none";
      document.getElementById("preview").style.display = "none";
    } else {
      document.getElementById("preview_bt").style.display = "";
    }
  }
  {/literal}
//]]></script>

<form action="{$platal->ns}mail" method="post" enctype="multipart/form-data" onsubmit="return check(this);">
  {xsrf_token_field}
  <table class='bicol'>
    <tr>
      <th colspan="2">Écrire un email&nbsp;:</th>
    </tr>
    <tr>
      <td class="titre">Expéditeur&nbsp;:</td>
      <td>
        <input type="text" name="from" size="55" maxlength="255"
          value="{if $smarty.request.from}{$smarty.request.from}{else}&quot;{$user->fullName()}&quot; &lt;{$user->bestEmail()}&gt;{/if}" />
      </td>
    </tr>
    <tr>
      <td class="titre">Répondre à&nbsp;:</td>
      <td>
        <input type="text" name="replyto" size="55" maxlength="255" value="{$smarty.request.replyto}" />
      </td>
    </tr>

    <tr>
      <th colspan="2">Destinataires</th>
    </tr>
    {assign var=ml_members value=""}
    {foreach from=$listes item=l}
      {if $l.list == "members" || $l.list == "membres" || $l.list == "membre"}
        {assign var=ml_members value=$l.addr}
      {/if}
    <tr>
      <td style='padding-left: 1em' class='titre'>
        {$l.list}
      </td>
      <td>
        <label><input type="checkbox" name="ml[{$l.list}]" value="1" {if $smarty.request.ml[$l.list]}checked="checked"{/if} />
        {$l.addr}</label>
        <a href="{$platal->ns}lists/members/{$l.list}" class="popup">(voir composition)</a>
      </td>
    </tr>
    {/foreach}

    <tr>
      <th colspan="2">Contenu de l'email</th>
    </tr>
    <tr>
      <td class="titre">
        Sujet&nbsp;:
      </td>
      <td><input type="text" name="sujet" value="{$smarty.request.sujet}" size="55" maxlength="70" /></td>
    </tr>
    <tr>
      <td colspan="2" class="smaller">
        <input type="checkbox" name="wiki" value="1" checked="checked" onchange="updateWikiView(this);" id="wiki_syntaxe"/>
        <label for="wiki_syntaxe">activer </label><a href="wiki_help" class="popup3">la syntaxe wiki</a>
        <label for="wiki_syntaxe">pour le formattage du message</label>
      </td>
    </tr>
    <tr id="preview" class="pair" style="display: none">
      <td colspan="2">
        <div id="mail_preview"></div>
        <div class="center"><input type="submit" name="send" value="Envoyer le message" /></div>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <textarea name="body" id="mail_text" cols="72" rows="25">
{if $smarty.request.body}
{$smarty.request.body}
{else}
&lt;cher&gt; &lt;prenom&gt;,

Nous avons le plaisir de t'adresser la lettre mensuelle du groupe {$asso->nom}.

(insérer le texte&hellip;)

Le bureau du groupe {$asso->nom}.
{/if}</textarea>
      </td>
    </tr>
    <tr>
      <td class="titre">
        {icon name=email_attach} Attacher un fichier
      </td>
      <td>
        <input type="file" name="uploaded" />
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <p style="font-size: larger;">
          <b>Attention</b>&nbsp;: si après avoir cliqué sur le bouton "Envoyer le message"
          la page met un temps long à répondre, ceci peut être lié au fait que le serveur
          est en train d'envoyer beaucoup de mails et cet outil n'est certainement pas
          adapté pour cette situation.
        </p>
        {if $ml_members}
        <p>
          Au lieu d'utiliser cet outil, il est possible d'utiliser la
          liste de diffusion <a href="mailto:{$ml_members}">{$ml_members}</a>
          ainsi que la
          <a href="{$platal->ns}nl">newsletter</a> du groupe pour écrire au groupe.
        </p>
        {else}
        <p>
          Au lieu d'utiliser cet outil, il est possible d'utiliser la
          <a href="{$platal->ns}nl">newsletter</a> du groupe pour écrire au groupe,
          ou alors de créer une liste de diffusion membres@{$asso->mail_domain}.
        </p>
        {/if}
        <p>
          Pour connaître quelles solutions Polytechnique.org propose pour effectuer
          un envoi massif de mails ciblés, merci de contacter
          <a href="mailto:contact@polytechnique.org">contact@polytechnique.org</a>.
        </p>
      </td>
    </tr>
    <tr>
      <td colspan="2" align="center">
        <input type="submit" name="preview" id="preview_bt" value="Aperçu" onclick="previewWiki('mail_text', 'mail_preview', true, 'preview'); return false;" />
        <input type="submit" name="send" value="Envoyer le message" />
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
