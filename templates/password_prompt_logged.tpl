<div style="text-align:center">
  <table width="90%" summary="Accès sécurisé">
    <tr>
      <td>
        <img src="{"images/cadenas_rouge.png"|url}" alt=" [ CADENAS ROUGE ] ">
      </td>
      <td>
        <div class="explication">
          Pour des raisons de sécurité, il est obligatoire de taper ton mot de passe, même
          avec l'accès permanent, pour certaines opérations sensibles.
        </div>
      </td>
    </tr>
  </table>
</div>
<br /><br />
<form action="{$smarty.server.REQUEST_URI}" method="post" name="login" onSubmit='doChallengeResponse(); return false;'>
  <table class="bicol" align="center" cellpadding="4" summary="Formulaire de login">
    <tr>
      <td>
        <span class="login">Mot de passe:</span>
      </td>
      <td>
        <input type="password" name="password" size=10 maxlength=10>
      </td>
    </tr>
    <tr>
      <td>
        <img src="{"images/pi.png"|url}" alt=" [ ? ] ">
        <a href="{"recovery.php"|url}">J'ai perdu mon mot de passe</a>
      </td>
      <td align=right>
        <input  type="submit" name="submitbtn" value="Envoyer">
      </td>
    </tr>
  </table>
</form>
<br />
{dynamic}
{if $smarty.request.response}<!-- failed login code -->
<div class="warning">
  Erreur d'identification. Essaie à nouveau !
</div>
{/if}

<!-- Set up the form with the challenge value and an empty reply value -->
<form action="{$smarty.server.REQUEST_URI}" method=post name="loginsub">
  <input type="hidden" name="challenge" value="{$smarty.session.challenge}">
  <input type="hidden" name="username" value="{$smarty.cookie.ORGlogin}">
  <input type="hidden" name="response"  value="">
</form>
{/dynamic}

{literal}
<script language="JavaScript" type="text/javascript">
  <!--
  // Activate the appropriate input form field.
  document.login.password.focus();
  // -->
</script>
{/literal}
