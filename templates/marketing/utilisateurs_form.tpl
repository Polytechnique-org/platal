{* $Id: utilisateurs_form.tpl,v 1.3 2004-08-26 14:44:45 x2000habouzit Exp $ *}

{dynamic}

<div class="rubrique">
  Envoyer un mail de pr&eacute;-inscription
</div>

<p>
Le nom, pr&eacute;nom et promotion sont pris dans la table d'identification.  Le login sera automatiquement
calcul&eacute; &agrave; partir de ces données.
</p>

<form action="{$smarty.server.PHP_SELF}" method="get">
  <table cellpadding="3" class="bicol" summary="Envoyer un mail">
    <tr>
      <th colspan="2">
        Envoyer un mail
      </th>
    </tr>
    <tr>
      <td class="titre">
        Prénom :
      </td>
      <td>
        {$row.prenom}
      </td>
    </tr>
    <tr>
      <td class="titre">
        Nom :
      </td>
      <td>
        {$row.nom}
      </td>
    </tr>
    <tr>
      <td class="titre">
        Promo :
      </td>
      <td>
        {$row.promo}
      </td>
    </tr>
    <tr>
      <td class="titre">
        From du mail :
      </td>
      <td>
        <input type="text" size="40" maxlength="60" name="from"
        value="{$smarty.request.from|default:"`$smarty.session.username`@polytechnique.org"}" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Adresse e-mail devinée :
      </td>
      <td>
        <input type="text" size="40" maxlength="60" name="mail"
        value="{$smarty.request.mail}" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="bouton">
        <input type="hidden" name="xmat" value="{$smarty.request.xmat}" />
        <input type="hidden" name="sender" value="{$smarty.request.sender|default:$smarty.session.uid}" />
        <input type="submit" name="submit" value="Envoyer le mail" />
      </td>
    </tr>
  </table>
</form>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
