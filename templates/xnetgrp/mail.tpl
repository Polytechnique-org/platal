{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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


<h1>{$asso.nom}&nbsp;: Envoyer un mail</h1>

<p class="descr">
Ton message peut être personnalisé&nbsp;: si tu rentres les mots &lt;cher&gt;, &lt;prenom&gt;,
&lt;nom&gt;, ces mots seront remplacés, pour chacun des destinataires, par "cher" accordé au
masculin ou féminin, par son prénom, ou son nom.
</p>

<script type="text/javascript">//<![CDATA[
  {literal}
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
 
<form action="{$platal->ns}mail" method="post" enctype="multipart/form-data">
  <table class='bicol'>
    <tr>
      <th colspan="2">Écrire un mail&nbsp;:</th>
    </tr>
    <tr>
      <td class="titre">Expéditeur&nbsp;:</td>
      <td>
        <input type="text" name="from" size="55" maxlength="70"
          value="{if $smarty.request.from}{$smarty.request.from}{else}&quot;{$smarty.session.prenom} {$smarty.session.nom}&quot; <{$smarty.session.bestalias}@polytechnique.org>{/if}" />
      </td>
    </tr>
    <tr>
      <td class="titre">Répondre à&nbsp;:</td>
      <td>
        <input type="text" name="replyto" size="55" maxlength="70" value="{$smarty.request.replyto}" />
      </td>
    </tr>

    <tr>
      <th colspan="2">Destinataires</th>
    </tr>
    <tr valign="top">
      <td style='padding-left: 1em' class='titre'>
        <em>annuaire</em>
      </td>
      <td>
        <input type="checkbox" name="membres[X]" value="1"  {if $smarty.request.membres.X}checked="checked"{/if} />
        <em>écrire à tous les X de l'annuaire du groupe</em><br />
        <input type="checkbox" name="membres[ext]" value="1"  {if $smarty.request.membres.ext}checked="checked"{/if} />
        <em>écrire à tous les extérieurs de l'annuaire du groupe</em><br />
        <input type="checkbox" name="membres[groupe]" value="1"  {if $smarty.request.membres.groupe}checked="checked"{/if} />
        <em>écrire à toutes les personnes morales de l'annuaire du groupe</em>
        <a href="{$platal->ns}annuaire" class='popup'>(voir annuaire)</a><br />
      </td>
    </tr>

    {foreach from=$listes item=l}
    <tr>
      <td style='padding-left: 1em' class='titre'>
        {$l.list}
      </td>
      <td>
        <input type="checkbox" name="ml[{$l.list}]" value="1" {if $smarty.request.ml[$l.list]}checked="checked"{/if} />
        {$l.addr}
        <a href="{$platal->ns}lists/admin/{$l.list}" class="popup">(voir composition)</a>
      </td>
    </tr>
    {/foreach}

    <tr>
      <th colspan="2">Contenu du mail</th>
    </tr>
    <tr>
      <td class="titre">
        Sujet&nbsp;:
      </td>
      <td><input type="text" name="sujet" value="{$smarty.request.sujet|default:"remplir le sujet ..."}" size=55 maxlength=70></td>
    </tr>
    <tr>
      <td colspan="2" class="smaller">
        <input type="checkbox" name="wiki" value="1" checked="1" onchange="updateWikiView(this);" />
        activer <a href="wiki_help" class="popup3">la syntaxe wiki</a> pour le formattage du message
      </td>
    </tr>
    <tr id="preview" class="pair" style="display: none">
      <td colspan="2" id="mail_preview">
        <div id="mail_preview"></div>
        <div class="center"><input type="submit" name="send" value="Envoyer le message"></div>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <textarea name="body" id="mail_text" cols="72" rows="25">
{if $smarty.request.body}
{$smarty.request.body}
{else}
&lt;cher&gt; &lt;prenom&gt;,

Nous avons le plaisir de t'adresser la lettre mensuelle du groupe {$asso.nom}.

(insérer le texte...)

Le bureau du groupe {$asso.nom}.
{/if}
         </textarea>
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
      <td colspan="2" align="center">
        <input type="submit" name="preview" id="preview_bt" value="Aperçu" onclick="previewWiki('mail_text', 'mail_preview', true, 'preview'); return false;" />
        <input type="submit" name="send" value="Envoyer le message" />
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
