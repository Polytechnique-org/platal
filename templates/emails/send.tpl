{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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

<h1>Envoyer un email</h1>

<script type="text/javascript">//<![CDATA[
  {literal}
  function _selectAll(id) {
    var list = document.getElementById(id);
    for (i = 0 ; i < list.length ; i++) {
      list.options[i].selected = true;
    }
  }

  var sent = false;
  function check(form)
  {
    _selectAll('to_contacts');
    _selectAll('cc_contacts');
    if(form.sujet.value == "" && !confirm("Le sujet de l'email est vide, veux-tu continuer ?")) {
        form.sujet.focus();
        return false;
    }
    if (form.to.value == "" && form.cc.value == ""
        && document.getElementById('to_contacts').length == 0 && document.getElementById('cc_contacts').length == 0) {
      if (form.bcc.value == "") {
        alert("Il faut définir au moins un destinataire.");
        return false;
      }
      if (!confirm("Tous les destinataires sont en copie cachée, veux-tu continuer ?")) {
        form.to.focus();
        return false;
      }
    }
    sent = true;
    return true;
  }

  function saveMessage() {
    var form = document.forms.form_mail;
    var toc = "";
    var ccc = "";
    if (form['to_contacts[]'] != undefined) {
      for (var i = 0 ; i < form['to_contacts[]'].length ; ++i) {
        toc += form['to_contacts[]'].options[i].value + ";";
      }
      for (var i = 0 ; i < form['cc_contacts[]'].length ; ++i) {
        ccc += form['cc_contacts[]'].options[i].value + ";";
      }
    }
    $.xpost("emails/send",
           { save: true,
             token: {/literal}'{xsrf_token}'{literal},
             from: form.from.value,
             to_contacts: toc,
             cc_contacts: ccc,
             contenu: form.contenu.value,
             to: form.to.value,
             sujet: form.sujet.value,
             cc: form.cc.value,
             bcc: form.bcc.value });
  }

  var doAuth = true;
  function _keepAuth() {
    doAuth = true;
  }

  function keepAuth() {
    if (doAuth) {
      saveMessage();
      doAuth = false;
      setTimeout("_keepAuth()", 10000);
    }
  }

  function _move(idFrom, idTo) {
    var from = document.getElementById(idFrom);
    var to   = document.getElementById(idTo);

    var index = new Array();
    var j = 0;
    for (i = 0 ; i < from.length ; i++) {
      if (from.options[i].selected) {
        var option = document.createElement('option');
        option.value = from.options[i].value;
        option.text  = from.options[i].text;
        try {
          to.add(option, null);
        } catch(ex) {
          to.add(option);
        }
        index[j++] = i;
      }
    }
    for (i = index.length - 1 ; i >= 0 ; i--) {
      from.remove(index[i]);
    }
  }

  function addTo() {
    _move('contacts', 'to_contacts');
  }

  function removeTo() {
    _move('to_contacts', 'contacts');
  }

  function addCc() {
    _move('contacts', 'cc_contacts');
  }

  function removeCc() {
    _move('cc_contacts', 'contacts');
  }

  function updateWikiView(box) {
    if (box.checked) {
      document.getElementById("preview_bt").style.display = "none";
      document.getElementById("preview").style.display = "none";
      document.getElementById("preview_pv").style.display = "none";
    } else {
      document.getElementById("preview_bt").style.display = "";
      document.getElementById("preview").style.display = "";
    }
  }

  function toggleWiki()
  {
    if ($('[name="wiki"]').val() == 'text') {
      $('[name="wiki"]').val('wiki');
    } else {
      $('[name="wiki"]').val('text');
    }
  }

  $(window).unload(
    function() {
      if (sent) {
        return true;
      }
      saveMessage();
      return true;
    });
  $(
    function() {
      // Remove empty options in select (they were added only for HTML
      // compatibility).
      $('#to_contacts option[value=""]').remove();
      $('#cc_contacts option[value=""]').remove();
    });

    $(function() {
      $("[name='to']").focus();
    });
  {/literal}
//]]>
</script>

<p>
  <small>{icon name=information title="Envoi d'email"} Pour envoyer un email, tu peux soit le faire depuis l'interface
  ci-dessous, soit utiliser <a href="Xorg/SMTPSecurise">notre serveur d'envoi SMTP</a>.</small>
</p>

<form action="emails/send" method="post" enctype="multipart/form-data" id="form_mail" onsubmit="return check(this);">
  {xsrf_token_field}
  <table class="bicol" cellpadding="2" cellspacing="0">
    <tr>
      <th colspan="2">Destinataires</th>
    </tr>
    <tr>
      <td class="titre">de&nbsp;:</td>
      <td>
        <input type='hidden' name='signature' value='1' />
        <input type='text' name='from' size='60'
          value="{if $smarty.request.from}{$smarty.request.from}{else}{$preferences.from_email}{/if}" />
      </td>
    </tr>
    <tr>
      <td class="titre">à&nbsp;:</td>
      <td>
        <input type='text' name='to' size='60' value="{$smarty.request.to}" />
      </td>
    </tr>
    <tr>
      <td class="titre">copie&nbsp;:</td>
      <td>
        <input type='text' name='cc' size='60' value="{$smarty.request.cc}" />
      </td>
    </tr>
    <tr>
      <td class="titre">copie cachée&nbsp;:</td>
      <td>
        <input type='text' name='bcc' size='60' value="{$smarty.request.bcc}" />
      </td>
    </tr>
    <tr class="pair">
      <td colspan="2" class="smaller">
        &bull;&nbsp;Les destinataires sont simplement séparés par des virgules.<br />
        &bull;&nbsp;Pense à te mettre en copie cachée de l'email pour en avoir une trace.
      </td>
    </tr>
    {if $contacts|@count}
    <tr>
      <th colspan="2">
        Destinataires parmi tes contacts
      </th>
    </tr>
    <tr>
      <td colspan="2" style="padding: 4px">
        <div style="float: right; width: 40%;">
          <select id="to_contacts" name="to_contacts[]" multiple="multiple" style="width: 100%; height: 5em">
          {foreach key=key item=contact from=$contacts}
          {if t($smarty.request.to_contacts) && in_array($contact->hrpid,$smarty.request.to_contacts)}
          <option value="{$contact->hrpid}">
            {$contact->full_name}
          </option>
          {assign var="to_not_empty" value="true"}
          {/if}
          {/foreach}
          {if !$to_not_empty}
          {* HTML compatibility *}
          <option value="">&nbsp;</option>
          {/if}
          </select>
          {if !$to_not_empty}
          {/if}
          <br />
          <select id="cc_contacts" name="cc_contacts[]" multiple="multiple" style="width: 100%; height: 5em">
          {foreach key=key item=contact from=$contacts}
          {if t($smarty.request.cc_contacts) && in_array($contact->hrpid,$smarty.request.cc_contacts)}
          <option value="{$contact->hrpid}">
            {$contact->full_name}
          </option>
          {assign var="cc_not_empty" value="true"}
          {/if}
          {/foreach}
          {if !$cc_not_empty}
          {* HTML compatibility *}
          <option value="">&nbsp;</option>
          {/if}
          </select>
        </div>
        <div style="width: 19%; text-align: center; height: 8em; float: right;">
          <div style="height: 4em">
              Destinataires<br />
              <a href="emails/send/addTo" onclick="addTo(); return false" style="text-decoration: none">&gt;&gt; &gt;&gt;</a><br />
              <a href="emails/send/removeTo" onclick="removeTo(); return false" style="text-decoration: none">&lt;&lt; &lt;&lt;</a>
          </div>
          <div style="height: 4em">
              En copie<br />
              <a href="emails/send/addCc" onclick="addCc(); return false" style="text-decoration: none">&gt;&gt; &gt;&gt;</a><br />
              <a href="emails/send/removeCc" onclick="removeCc(); return false" style="text-decoration: none">&lt;&lt; &lt;&lt;</a>
          </div>
        </div>
        <div style="float: right; width: 40%">
          <select id="contacts" name="all_contacts[]" multiple="multiple" style="height: 10em; width: 100%">
            {foreach item=contact from=$contacts}
            {if !(isset($smarty.request.to_contacts|smarty:nodefaults) && isset($smarty.request.cc_contacts|smarty:nodefaults)) ||
                (!in_array($contact->hrpid,$smarty.request.to_contacts) && !in_array($contact->hrpid,$smarty.request.cc_contacts))}
            <option value="{$contact->hrpid}">
              {$contact->full_name}
            </option>
            {/if}
            {/foreach}
          </select>
        </div>
      </td>
    </tr>
    {/if}
  </table>
  {if $contacts|@count eq 0}
    {* Current Javascript code needs some elements to exist. *}
    {* TODO: rewrite the code to accept non-existent elements without raising exceptions *}
    <select id="to_contacts" name="to_contacts[]" multiple="multiple" style="display: none;">
      <option value="">&nbsp;</option>
    </select>
    <select id="cc_contacts" name="cc_contacts[]" multiple="multiple" style="display: none;">
      <option value="">&nbsp;</option>
    </select>
    <select id="contacts" name="all_contacts[]" multiple="multiple" style="display: none;">
      <option value="">&nbsp;</option>
    </select>
  {/if}
  <fieldset>
    <legend>Sujet&nbsp;:&nbsp;<input type='text' name='sujet' size='60' value="{$smarty.request.sujet}" /></legend>
    <div class="center">
      Tu peux utiliser des <a href="wiki_help" class="popup3">{icon name=information title="Syntaxe wiki"} marqueurs wiki</a> pour formatter ton texte.<br />
      <small><label>
        <input type="hidden"  name="wiki" value="{$smarty.request.wiki|default:$preferences.from_format}" />
        <input type="checkbox" {if $smarty.request.wiki eq "text" || (!$smarty.request.wiki && $preferences.from_format eq "text")}checked="checked"{/if}
        onchange="updateWikiView(this); toggleWiki();" />
        coche cette case pour envoyer l'email en texte brut, sans formattage
      </label></small>
    </div>
    <div id="preview">
      <div id="preview_pv" style="display: none">
        <strong>Aperçu de l'email&nbsp;:</strong>
        <div id="mail_preview">
        </div>
        <div class="center">
          <input type="submit" name="submit" value="Envoyer" />
        </div>
      </div>
      <div class="center">
        <input type="submit" name="preview" id="preview_bt_top" value="Aperçu"
               onclick="previewWiki('mail_text', 'mail_preview', true, 'preview_pv'); return false;" />
      </div>
    </div>
    <textarea name='contenu' rows="30" cols="75" id="mail_text" onkeyup="keepAuth()">
{$smarty.request.contenu}
{if !$smarty.request.contenu}
-- 
{$smarty.session.user->full_name}
{/if}</textarea>
    <script type="text/javascript">//<![CDATA[
      {literal}
      function removeAttachments()
      {
          $.xget('email/send');
          $('#att_already').hide();
          $('#att_form').show();
      }
      {/literal}
    //]]></script>
    {if $uploaded_f|@count}
    <div id="att_already">
      <strong>{icon name=email_attach}&nbsp;Pièce jointe&nbsp;:&nbsp;</strong>
      {$uploaded_f[0]}
      <a href="javascript:removeAttachments()">
        {icon name=cross alt="Supprimer" title="Supprimer la pièce jointe"}
      </a>
    </div>
    {/if}
    <div id="att_form" {if $uploaded_f|@count neq 0}style="display: none"{/if}>
      <strong>{icon name=email_attach}&nbsp;Ajouter une pièce jointe (max. {$maxsize})&nbsp;:&nbsp;</strong>
      <input type="file" name="uploaded" />
    </div>
    <div class="center">
      <input type="submit" name="preview" id="preview_bt" value="Aperçu"
             onclick="previewWiki('mail_text', 'mail_preview', true, 'preview_pv'); return false;" />
      <input type="submit" name="submit" value="Envoyer" />
    </div>
  </fieldset>
</form>


{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
