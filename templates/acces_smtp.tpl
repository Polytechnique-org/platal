{* $Id: acces_smtp.tpl,v 1.6 2004-08-26 14:44:43 x2000habouzit Exp $ *}

{dynamic on="0$message"}
<p class="erreur">
{$message}
</p>
{/dynamic}

<div class="rubrique">
{if $actif}Modification du mot de passe SMTP/NNTP{else}Activation de ton compte SMTP/NNTP{/if}  
</div>

{literal}
<script language="javascript" type="text/javascript">
  <!--
  function CheckResponse() {
    pw1 = document.smtppass_form.smtppass1.value;
    pw2 = document.smtppass_form.smtppass2.value;
    if (pw1 != pw2) {
      alert ("\nErreur : les deux champs ne sont pas identiques !");
      exit;
      return false;
    }
    if (pw1.length < 6) {
      alert ("\nErreur : le nouveau mot de passe doit faire au moins 6 caractères !");
      exit;
      return false;
    }
    document.smtppass_form.op.value='Valider';
    document.smtppass_form.submit();
    return true;
  }

  function SupprimerMdp() {
    document.smtppass_form.op.value='Supprimer';
    document.smtppass_form.submit();
  }
  // -->
</script>
{/literal}

<p>
  <a href="docs/doc_smtp.php">Pourquoi et comment</a> utiliser le serveur SMTP de Polytechnique.org. <br />
  <a href="docs/doc_nntp.php">Pourquoi et comment</a> utiliser le serveur NNTP de Polytechnique.org. <br />
</p>
<p>
{if $actif}
  Clique sur <strong>"Supprimer"</strong> si tu veux supprimer ton compte SMTP/NNTP.
{else}
  Pour activer un compte SMTP/NNTP sur <strong>ssl.polytechnique.org</strong>, tape un mot de passe ci-dessous.
{/if}
</p>
<form action="{$smarty.server.REQUEST_URI}" method="post" name="smtppass_form" id="smtppass_form">
  <table class="tinybicol" cellpadding="3" summary="Définition du mot de passe">
    <tr>
      <th colspan="2">
        Définition du mot de passe
      </th>
    </tr>
    <tr>
      <td class="titre">
        Mot de passe :
      </td>
      <td>
        <input type="password" size=15 maxlength=15 name="smtppass1" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Retape-le une fois (pour vérification):
      </td>
      <td>
        <input type="password" size=15 maxlength=15 name="smtppass2" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="hidden" name="op" value="" />
        <input type="submit" value="Valider" onClick="CheckResponse(); return false;" />
{if $actif}
        &nbsp;&nbsp;<input type="submit" value="Supprimer" onClick="SupprimerMdp();" />
{/if}
      </td>
    </tr>
  </table>
</form>
<p>
  Ce mot de passe peut être le même que celui d'accès au site. Il doit faire au
  moins <strong>6 caractères</strong> quelconques. Attention au type de clavier que tu
  utilises (qwerty?) et aux majuscules/minuscules.
</p>

{* vim:set et sw=2 sts=2 sws=2: *}
