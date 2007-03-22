{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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

<h1>Envoyer un mail</h1>

<script type="text/javascript">//<![CDATA[
  {literal}
  function _selectAll(id) {
    var list = document.getElementById(id);
    for (i = 0 ; i < list.length ; i++) {
      list.options[i].selected = true;
    }
  }

  function check(form)
  {
    var send = true;
    _selectAll('to_contacts');
    _selectAll('cc_contacts');
    if(form.sujet.value == "" && !confirm("Le sujet du mail est vide, veux-tu continuer ?")) {
        form.sujet.focus();
        return false;
    }
    if (form.to.value == "" && form.cc.value == ""
        && document.getElementById('to_contacts').length == 0 && document.getElementById('cc_contacts').length == 0) {
      if (form.bcc.value == "") {
        alert("Il faut définir au moins un destinataire au mail");
        return false;
      }
      if (!confirm("Tous les destinataires sont en copie cachée, veux-tu continuer ?")) {
        form.to.focus();
        return false;
      }
    }
    return true;
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
  {/literal}
//]]>
</script>

<form action="emails/send" method="post" enctype="multipart/form-data" onsubmit="return check(this);">
  <table class="bicol" cellpadding="2" cellspacing="0">
    <tr> 
      <th colspan="2">Destinataires</th>
    </tr>
    <tr> 
      <td class="titre">de&nbsp;:</td>
      <td>
        <input type='hidden' name='signature' value='1' />
        <input type='text' name='from' size='60' value='{if $smarty.request.from}
{$smarty.request.from}
{else}
"{$smarty.session.prenom} {$smarty.session.nom}" &lt;{$smarty.session.bestalias}@{#globals.mail.domain#}&gt;
{/if}' />
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
        &bull;&nbsp;Les destinataires sont simplement séparés par des virgules<br />
        &bull;&nbsp;Pense à te mettre en copie cachée du mail pour en avoir une trace
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
          {if in_array($contact.forlife, $smarty.request.to_contacts)}
          <option value="{$contact.forlife}">
            {$contact.prenom} {$contact.nom} (X{$contact.promo})
          </option>
          {/if}
          {/foreach}
          </select><br />
          <select id="cc_contacts" name="cc_contacts[]" multiple="multiple" style="width: 100%; height: 5em">
          {foreach key=key item=contact from=$contacts}
          {if in_array($contact.forlife, $smarty.request.cc_contacts)}
          <option value="{$contact.forlife}">
            {$contact.prenom} {$contact.nom} (X{$contact.promo})
          </option>
          {/if}
          {/foreach}
          </select>
        </div>
        <div style="width: 19%; text-align: center; height: 8em; float: right;">
          <div style="height: 4em">
              Destinataires<br />
              <a href="" onclick="addTo(); return false" style="text-decoration: none">&gt;&gt; &gt;&gt;</a><br />
              <a href="" onclick="removeTo(); return false" style="text-decoration: none">&lt;&lt; &lt;&lt;</a>
          </div>
          <div style="height: 4em">
              En copie<br />
              <a href="" onclick="addCc(); return false" style="text-decoration: none">&gt;&gt; &gt;&gt;</a><br />
              <a href="" onclick="removeCc(); return false" style="text-decoration: none">&lt;&lt; &lt;&lt;</a>
          </div>
        </div>
        <div style="float: right; width: 40%">
          <select id="contacts" name="all_contacts[]" multiple="multiple" style="height: 10em; width: 100%">
            {foreach item=contact from=$contacts}
            {if !in_array($contact.forlife, $smarty.request.to_contacts) && !in_array($contact.forlife, $smarty.request.cc_contacts)}
            <option value="{$contact.forlife}">
              {$contact.prenom} {$contact.nom} (X{$contact.promo})
            </option>
            {/if}
            {/foreach}
          </select>
        </div>
        {foreach item=contact from=$contacts}
        <input type="hidden" name="contacts[{$contact.forlife}]" value="{$contact.prenom} {$contact.nom} &lt;{$contact.forlife}@{#globals.mail.domain#}&gt;" />
        {/foreach}
      </td>
    </tr>
    {/if}
  </table>

  <fieldset>
    <legend>Sujet&nbsp;:&nbsp;<input type='text' name='sujet' size='60' value="{$smarty.request.sujet}" /></legend>
    <div class="center">
      Tu peux utiliser des marqueurs <em>wiki</em> pour formatter ton texte.<br />
      <small><input type="checkbox" name="nowiki" value="1" {if $smarty.request.nowiki}checked="checked"{/if} />
      coche cette case pour envoyer le mail en texte brut, sans formattage</small>
    </div>
    <textarea name='contenu' rows="30" cols="75">
{$smarty.request.contenu}
{if !$smarty.request.contenu}
-- 
{$smarty.session.prenom} {$smarty.session.nom}
{/if}</textarea>
    <script type="text/javascript">//<![CDATA[
      {literal}
      function removeAttachments()
      {
          Ajax.update_html(null, 'emails/send', null);
          document.getElementById('att_already').style.display = 'none';
          document.getElementById('att_form').style.display = '';
      }
      {/literal}
    //]]></script>
    {if $uploaded_f|@count}
    <div id="att_already">
      <strong>{icon name=email_attach}&nbsp;Pièce jointe&nbsp;:&nbsp;</strong>
      {$uploaded_f[0]}
      <a href="javascript:removeAttachments()">
        <img src="images/icons/bin_empty.gif" alt="Supprimer" title="Supprimer la pièce jointe" />
      </a>
    </div>
    {/if}
    <div id="att_form" {if $uploaded_f|@count neq 0}style="display: none"{/if}>
      <strong>{icon name=email_attach}&nbsp;Ajouter une pièce jointe (max. {$maxsize})&nbsp;:&nbsp;</strong>
      <input type="file" name="uploaded" />
    </div>
    <div class="center">
      <input type="submit" name="submit" value="Envoyer" />
    </div>
  </fieldset>
</form>


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
