{* $Id: password_prompt_logged.tpl,v 1.8 2004-02-04 19:47:47 x2000habouzit Exp $ *}

<div class="center">
  <table summary="Accès sécurisé" style="width: 90%;">
    <tr>
      <td>
        <img src="{"images/cadenas_rouge.png"|url}" alt=" [ CADENAS ROUGE ] ">
      </td>
      <td>
        <div class="smaller">
          <strong>
            Pour des raisons de sécurité, il est obligatoire de taper ton mot de passe, même
            avec l'accès permanent, pour certaines opérations sensibles.
          </strong>
        </div>
      </td>
    </tr>
  </table>
</div>
<br /><br />
<form action="{$smarty.server.REQUEST_URI}" method="post" id="login" name="login" onSubmit='doChallengeResponse(); return false;'>
  <table class="tinybicol" cellpadding="4" summary="Formulaire de login">
    <tr>
      <td class="titre">
        Mot de passe:
      </td>
      <td>
        <input type="password" name="password" size=10 maxlength=10 />
      </td>
    </tr>
    <tr>
      <td>
        <img src="{"images/pi.png"|url}" alt=" [ ? ] ">
        <a href="{"recovery.php"|url}">J'ai perdu mon mot de passe</a>
      </td>
      <td style="text-align:right;">
        <input  type="submit" name="submitbtn" value="Envoyer" />
      </td>
    </tr>
  </table>
</form>
<br />
{dynamic}
{if $smarty.request.response}<!-- failed login code -->
<div class="erreur">
  Erreur d'identification. Essaie à nouveau !
</div>
{/if}

<!-- Set up the form with the challenge value and an empty reply value -->
<form action="{$smarty.server.REQUEST_URI}" method=post name="loginsub">
  <input type="hidden" name="challenge" value="{$smarty.session.session->challenge}" />
  <input type="hidden" name="username"  value="{$smarty.cookies.ORGlogin}" />
  <input type="hidden" name="response"  value="" />
</form>

{literal}
<script language="JavaScript" type="text/javascript">
  <!--
  // Activate the appropriate input form field.
  document.login.password.focus();
  // -->
</script>
{/literal}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
