{* $Id: alias.tpl,v 1.7 2004-08-30 12:18:40 x2000habouzit Exp $ *}

{if $success}
  <p>
    a demande de création des alias <strong>{dyn s=$success}@melix.net</strong> et
    <strong>{dyn s=$success}@melix.org</strong> a bien été enregistrée. Après
    vérification, tu recevras un mail te signalant l'ouverture de ces adresses.
  </p>
  <p>
    Encore merci de nous faire confiance pour tes e-mails !
  </p>
{else}
  {if $error}
  <p class="erreur">{dyn s=$error}</p>
  {/if}

  <div class="rubrique">
    Adresses e-mail personnalisées
  </div>

  <p>
    Pour plus de <strong>convivialité</strong> dans l'utilisation de tes mails, tu peux choisir une adresse
    e-mail discrète et personnalisée. Ce nouvel e-mail peut par exemple correspondre à ton surnom.
  </p>
  <p>
    Pour de plus amples informations sur ce service, nous t'invitons à consulter
    <a href="{"docs/doc_melix.php"|url}">cette documentation</a> qui répondra
    sans doute à toutes tes questions
  </p>

  {dynamic on="0$actuel"}
  <p>
  <strong>Note : tu as déjà l'alias {$actuel}, or tu ne peux avoir qu'un seul alias à la fois.
    Si tu effectues une nouvelle demande l'ancien alias sera effacé.</strong>
  </p>
  {/dynamic}

  {dynamic on="0$demande"}
  <p>
  <strong>Note : tu as déjà effectué une demande pour {$demande->alias}, dont le traitement est
    en cours. Si tu souhaites modifier ceci refais une demande, sinon ce n'est pas la peine.</strong>
  </p>
  {/dynamic}

  <br />
  <form action="{$smarty.server.PHP_SELF}" method="post">
    <table class="tinybicol" cellpadding="4" summary="Demande d'alias">
      <tr>
        <th colspan="2">Demande d'alias</th>
      </tr>
      <tr>
        <td>Alias demandé :</td>
      </tr>
      <tr>
        <td><input type="text" name="alias" value="{dyn s=$r_alias}" />@melix.net et @melix.org</td>
      </tr>
      <tr>
        <td>Brève explication :</td>
      </tr>
      <tr>
        <td><textarea rows="5" cols="50" name="raison">{dyn s=$r_raison}</textarea></td>
      </tr>
      <tr>
        <td><input type="submit" name="submit" value="Envoyer" /></td>
      </tr>
    </table>
  </form>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
