{* $Id: motdepassemd5.tpl,v 1.2 2004-02-02 11:48:35 x2000habouzit Exp $ *}

<div class="rubrique">
  Changer de mot de passe
</div>

<p class="normal">
  Ton mot de passe doit faire au moins <strong>6 caractères</strong> quelconques. Attention
  au type de clavier que tu utilises (qwerty?) et aux majuscules/minuscules.
</p>
<p class="normal">
  Pour une sécurité optimale, ton mot de passe circule de manière cryptée (https) et est
  stocké crypté irréversiblement sur nos serveurs.
</p>
<br />
<form action="{$smarty.server.REQUEST_URI}" method=POST id="changepass" name="changepass">
  <table class="tinybicol" cellpadding="3" cellspacing="0"
    summary="Formulaire de mot de passe">
    <tr>
      <th colspan="2">
        Saisie du nouveau mot de passe
      </th>
    </tr>
    <tr>
      <td class="bicoltitre">
        Nouveau mot de passe :
      </td>
      <td>
        <input type="password" size="10" maxlength="10" name="nouveau" />
      </td>
    </tr>
    <tr>
      <td class="bicoltitre">
        Retape-le une fois :
      </td>
      <td>
        <input type="password" size="10" maxlength="10" name="nouveau2" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="submit" value="Changer" name="submitn" onClick="EnCryptedResponse(); return false;" />
      </td>
    </tr>
  </table>
</form>
</div>
<form action="{$smarty.server.REQUEST_URI}" method=POST id="changepass2" name="changepass2">
  <input type="hidden" name="response2"  value="" />
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
