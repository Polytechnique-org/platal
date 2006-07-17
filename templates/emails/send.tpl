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

<h1>Envoyer un mail</h1>

<ul>
  <li>
    Les destinataires sont simplement séparés par des virgules
  </li>
  <li>
    Pense à te mettre en copie cachée du mail sinon tu n'auras aucun moyen de retrouver 
    le mail que tu envoies par cette page
  </li>
</ul>

<script type="text/javascript">//<![CDATA[
  {literal}
  function check(form) {
    if(form.sujet.value == "") {
      form.sujet.focus();
      return confirm ("Le sujet du mail est vide, veux tu continuer ?");
    }
    return true;
  }
  {/literal}
//]]>
</script>

<form action="emails/send" method="post" onsubmit="return check(this);">
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
  </table>

  {if $contacts|@count}
  <ul>
    <li>
      <p>
        Tu peux également ajouter certains de tes contacts aux destinataires :
      </p>
    </li>
  </ul>

  <table class="bicol" cellpadding="2" cellspacing="0" summary="Destinataires parmi les contacts">
{foreach key=key item=contact from=$contacts}
{if $key is even}
  <tr class="{cycle values="impair,pair"}">
{/if}
    <td>
      <input type="checkbox" name="contacts[{$contact.forlife}]"
        value="{$contact.prenom} {$contact.nom} &lt;{$contact.forlife}@{#globals.mail.domain#}&gt;"
        {if $smarty.request.contacts && $smarty.request.contacts.forlife}checked="checked"{/if} />
      <a href="profile/{$contact.forlife}" class="popup2">{$contact.prenom} {$contact.nom}</a> (X{$contact.promo})
    </td>
{if $key is odd}
  </tr>
{/if}
{/foreach}
{if $key is even}
    <td></td>
  </tr>
{/if}
  </table>
{/if}

  <ul>
    <li>
      Ne mets que du texte dans le contenu, pas de tags HTML
    </li>
    <li>
      Il n'est pas possible d'envoyer un fichier en attachement
    </li>
  </ul>

  <table class="bicol" cellspacing="0" cellpadding="2" summary="Corps du message">
    <tr> 
      <th>sujet</th>
    </tr>
    <tr> 
      <td class="center"> 
        <input type='text' name='sujet' size='75' value="{$smarty.request.sujet}" />
      </td>
    </tr>
    <tr> 
      <th>
        Corps du mail
      </th>
    </tr>
    <tr> 
      <td class="center">
        <textarea name='contenu' rows="30" cols="75">
{$smarty.request.contenu}
{if !$smarty.request.contenu}
-- 
{$smarty.session.prenom} {$smarty.session.nom}
{/if}</textarea>
      </td>
    </tr>
    <tr> 
      <td class="center">
        <input type="submit" name="submit" value="Envoyer" />
      </td>
    </tr>
  </table>
</form>


{* vim:set et sw=2 sts=2 sws=2: *}
