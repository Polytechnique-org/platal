{* $Id: sendmail.tpl,v 1.5 2004-08-26 14:44:43 x2000habouzit Exp $ *}

{dynamic}

{$error}

<div class="rubrique">
  Envoyer un mail
</div>

<ul>
  <li>
    Les destinataires sont simplement séparés par des espaces ou des virgules
  </li>
  <li>
    Pour les destinataires en polytechnique.org, le domaine n'est pas nécessaire
  </li>
  <li>
    Pense à te mettre en copie cachée du mail sinon tu n'auras aucun moyen de retrouver 
    le mail que tu envoies par cette page
  </li>
</ul>

<form action="{$smarty.server.REQUEST_URI}" method="post">
  <table class="bicol" cellpadding="2" cellspacing="0" summary="En-têtes du message">
    <tr> 
      <th colspan="2">en-têtes</th>
    </tr>
    <tr> 
      <td class="titre">de&nbsp;:</td>
      <td>
        <input type='hidden' name='signature' value='1' />
        <input type='text' name='from' size='45' value='{if $smarty.request.from}
{$smarty.request.from}
{else}
"{$smarty.session.prenom} {$smarty.session.nom}" &lt;{$smarty.session.username}@polytechnique.org&gt;
{/if}' />
      </td>
    </tr>
    <tr> 
      <td class="titre">à&nbsp;:</td>
      <td>
        <input type='text' name='to' size='45' value="{$smarty.request.to}" />
      </td>
    </tr>
    <tr> 
      <td class="titre">copie&nbsp;:</td>
      <td>
        <input type='text' name='cc' size='45' value="{$smarty.request.cc}" />
      </td>
    </tr>
    <tr> 
      <td class="titre">copie cachée&nbsp;:</td>
      <td>
        <input type='text' name='bcc' size='45' value="{$smarty.request.bcc}" />
      </td>
    </tr>
    <tr> 
      <td class="titre">sujet&nbsp;:</td>
      <td> 
        <input type='text' name='sujet' size='45' value="{$smarty.request.sujet}" />
      </td>
    </tr>
  </table>

{if $nb_contacts}
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
      <input type="checkbox" name="contacts[{$contact.username}]"
      value="{"`$contact.prenom` `$contact.nom` &lt;`$contact.username`@polytechnique.org&gt;"}"
        {if $smarty.request.contacts && $smarty.request.contacts.username}checked="checked"{/if} />
      <a href="javascript:x()" onclick="popWin('x.php?x={$contact.username}')">{$contact.prenom} {$contact.nom}</a> (X{$contact.promo})
    </td>
{if $key is odd}
  </tr>
{/if}
{/foreach}
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
      <th>
        contenu
      </th>
    </tr>
    <tr> 
      <td class="center">
        <textarea name='contenu' rows="30" cols="75">
{$smarty.request.contenu}
{if $smarty.request.signature}

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

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
