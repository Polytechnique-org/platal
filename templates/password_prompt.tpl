{* $Id: password_prompt.tpl,v 1.11 2004-02-04 22:14:12 x2000habouzit Exp $ *}
<noscript>
  <span class="erreur">
    Ton navigateur n'accepte pas le javaScript !!
  </span>
  <span class="normal">
    Cette forme de script web est nécessaire pour l'utilisation du site.
    Pour en savoir plus, regarde la <a href="faq.php#connect">FAQ</a>.
  </span>
</noscript>

<div class="rubrique">
  Accès réservé aux Polytechniciens
</div>
{min_auth level="cookie"}
<p class="normal">
<strong>Merci de rentrer ton mot de passe pour démarrer une connexion au site.</strong>
Si tu n'es pas {insert name="getName" script="insert.password.inc.php"}, change le login ci-dessous, ou rends-toi sur
<a href="{"inscrire.php"|url}">la page d'inscription</a>.
</p>
{/min_auth}

{only_public}
<p class="normal">
<strong>Tu ne connais pas ton mot de passe ?</strong>
</p>
<ul>
  <li>
  Si tu viens de terminer ta pré-inscription, <strong>il est dans le mail</strong> que
  nous t'avons envoyé (expéditeur pre-inscription@polytechnique.org).
  </li>
  <li>
  Si tu n'es jamais venu sur le site, <span class="warning">il faut
    t'enregistrer auprès de nous</span> pour obtenir un accès. Polytechnique.org
  c'est l'e-mail des X, l'annuaire en ligne, plus un tas d'autres services.
  Nous te fournirons un accès le plus rapidement possible.<strong><a
      href="inscrire.php">Clique ici pour nous demander tes paramètres personnels.</a></strong>
  </li>
</ul>
{/only_public}

<br />

<form action="{$smarty.server.REQUEST_URI}" method="post" name="login" onSubmit="doChallengeResponse(); return false;">
  <table class="tinybicol" cellpadding="4" summary="Formulaire de login">
    <tr>
      <th colspan="2">Connexion</th>
    </tr>
    <tr>
      <td class="titre">
        Login (prenom.nom) :
      </td>
      <td>
        <input type="text" name="username"size=20 maxlength=50
          value="{insert name="getUserName" script="insert.password.inc.php"}" />
      </td>
    </tr>
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
        <img src="{"images/pi.png"|url}" alt=" [ ? ] " />
        <a href="{"recovery.php"|url}">J'ai perdu mon mot de passe</a>
      </td>
      <td style="text-align:right;">
        <input type="submit" name="submitbtn" value="Envoyer" />
      </td>
    </tr>
  </table>
</form>
<p class="normal">
Problème de connexion ? <a href="{"faq.php#connect"|url}">La réponse est là.</a>
<br />
(Activer obligatoirement le <strong>javascript</strong>)
</p>

{dynamic}
{if $smarty.request.response}<!-- failed login code //-->
<br />
<div class="erreur">
  Erreur d'identification. Essaie à nouveau !
</div>
{/if}

<!-- Set up the form with the challenge value and an empty reply value //-->
<form action="{$smarty.server.REQUEST_URI}" method=post name="loginsub">
  <input type="hidden" name="challenge" value="{$smarty.session.session->challenge}" />
  <input type="hidden" name="response"  value="" />
  <input type="hidden" name="username"  value="" />
</form>
{/dynamic}

{literal}
<script language="JavaScript" type="text/javascript">
  <!--
  // Activate the appropriate input form field.
  if (document.login.username.value == '') {
    document.login.username.focus();
    } else {
    document.login.password.focus();
  }
  // -->
</script>
{/literal}

{* vim:set et sw=2 sts=2 sws=2: *}
