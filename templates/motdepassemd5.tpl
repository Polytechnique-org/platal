{* $Id: motdepassemd5.tpl,v 1.5 2004-08-25 09:52:08 x2000habouzit Exp $ *}

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
<form action="{dynamic}{$smarty.server.REQUEST_URI}{/dynamic}" method="post" id="changepass">
  <table class="tinybicol" cellpadding="3" cellspacing="0"
    summary="Formulaire de mot de passe">
    <tr>
      <th colspan="2">
        Saisie du nouveau mot de passe
      </th>
    </tr>
    <tr>
      <td class="titre">
        Nouveau mot de passe :
      </td>
      <td>
        <input type="password" size="10" maxlength="10" name="nouveau" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Retape-le une fois :
      </td>
      <td>
        <input type="password" size="10" maxlength="10" name="nouveau2" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="submit" value="Changer" name="submitn" onclick="EnCryptedResponse(); return false;" />
      </td>
    </tr>
  </table>
</form>
<form action="{$smarty.server.REQUEST_URI}" method="post" id="changepass2">
<p>
<input type="hidden" name="response2"  value="" />
</p>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
