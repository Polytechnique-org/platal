{* $Id: form.evenement.tpl,v 1.1 2004-07-19 13:35:36 x2000habouzit Exp $ *}

<form action="{$smarty.server.PHP_SELF}" method="post" name="evenement_nouveau">
  <input type="hidden" name="evt_id" value="{$smarty.post.evt_id}" />
  <table class="bicol">
    <tr>
      <th colspan="2">Contenu du message</th>
    </tr>
    <tr>
      <td><strong>Titre</strong></td>
      <td>
        <input type="text" name="titre" size="50" maxlength="200" value="{$titre}" />
      </td>
    </tr>
    <tr>
      <td><strong>Texte</strong></td>
      <td><textarea name="texte" rows="10" cols="60">{$texte}</textarea></td>
    </tr>
  </table>

  <br />

  <table class="bicol">
    <tr>
      <th colspan="2">Informations complémentaires</th>
    </tr>
    <tr>
      <td>
        <strong>Promo min *</strong> (incluse)
      </td>
      <td>
        <input type="text" name="promo_min" size="4" maxlength="4" value="{$promo_min}" />
        &nbsp;<em>0 signifie pas de minimum</em>
      </td>
    </tr>
    <tr>
      <td>
        <strong>Promo max *</strong> (incluse)
      </td>
      <td>
        <input type="text" name="promo_max" size="4" maxlength="4" value="{$promo_max}" />
        &nbsp;<em>0 signifie pas de maximum</em>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        * sert à limiter l'affichage de l'annonce aux camarades appartenant à certaines promos seulement.
      </td>
    </tr>
    <tr>
      <td>
        <strong>Dernier jour d'affichage</strong>
      </td>
      <td>
        <select name="peremption">
          {$select}
        </select>
      </td>
    </tr>
    <tr>
      <td><strong>Message pour le validateur</strong></td>
      <td><textarea name="validation_message" cols="50" rows="7">{$validation_message}</textarea></td>
    </tr>
  </table>

  <br />

  <div class="center">
    <input type="submit" name="action" value="Proposer" />
  </div>

</form>

{* vim:set et sw=2 sts=2 sws=2: *}
