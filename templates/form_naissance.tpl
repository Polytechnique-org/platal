{* $Id: form_naissance.tpl,v 1.2 2004-01-29 16:21:53 x2000habouzit Exp $ *}
<div class="rubrique">
  Date de naissance
</div>

<form action="profil2.php" method="post">
  <p class="normal">
  Avant d'accéder à ton profil pour la première fois, tu dois donner 
  ta date de naissance au format JJMMAAAA. Elle ne sera plus demandée
  par la suite et ne pourra être changée. Elle servira en cas de
  perte du mot de passe comme sécurité supplémentaire, et uniquement 
  à cela. Elle n'est jamais visible ou lisible.
  </p>
  <br />
  <table class="bicol" border="0" cellpadding="4" cellspacing="0"
    summary="Formulaire de naissance" width="60%">
    <tr>
      <th colspan="2">
        Date de naissance
      </th>
    </tr>
    <tr>
      <td>
        <strong>Date</strong> (JJMMAAAA)
      </td>
      <td>
        <input type="text" size="8" maxlength="8" name="birth">
      </td>
    </tr>
    <tr>
      <td class="center" colspan="2">
        <input type="submit" value="Enregistrer" name="submit">
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
