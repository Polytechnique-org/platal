{* $Id: acces_wap.tpl,v 1.4 2004-08-26 14:44:43 x2000habouzit Exp $ *}

{if $OK}
<p class="erreur">
  Configuration du site WAP enregistrée.
</p>
{else}
<div class="rubrique">Paramètre du site WAP</div>

<p>
  Tu peux utiliser certaines fonctionnalités du site Polytechnique.org sur un terminal WAP.<br />
  (téléphone portable, PDA, ...)<br />
  Il est disponible à l'adresse https://wap.polytechnique.org (# TODO # adresse de devel: http://wap.m4x.org/?u=prenom.nom&amp;p=passsmtp)<br />
  <a href="docs/doc_wap.php">Pourquoi et comment</a> utiliser le site WAP de Polytechnique.org. (##TODO## à ecrire)<br />
</p>
<p>
  Pour utiliser le site WAP, il faut que tu l'actives explicitement en cochant la case ci dessous.
</p>
{dynamic}
<form action="{$smarty.server.REQUEST_URI}" method="post" name="wap_form">
  <table class="bicol" cellpadding="3" summary="Paramètres généraux du site WAP">
    <tr>
      <th colspan="2">
        Paramètres généraux du site WAP
      </th>
    </tr>
    <tr>
      <td class="titre">
        Activer l'accès WAP:
      </td>
      <td>
        <input type="checkbox" name="actif" {if $wap.actif}checked="checked"{/if} />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Terminal affichant les images:
      </td>
      <td>
        <input type="checkbox" name="useimage" {if $wap.useimage}checked="checked"{/if} />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Largeur de l'écran (pixels):
      </td>
      <td>
        <input type="text" name="screenwidth" size="4" value="{$wap.screenwidth}" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Hauteur de l'écran (pixels):
      </td>
      <td>
        <input type="text" name="screenwidth" size="4" value="{$wap.screenheight}" />
      </td>
    </tr>
  </table>

  <p>
    La largeur et la hauteur de l'écran sont juste utilisé à titre <strong>indicatif</strong> pour améliorer l'affichage des photos.
    Si vous ne les connaissez pas, vous pouvez les laisser à la valeur par défaut ou faire des essais successifs.<br />
  </p>
  <br />
  <table class="bicol" cellpadding="3" summary="Envoi de mail">
    <tr>
      <th colspan="2">
        Paramètres pour l'envoi de mail
      </th>
    </tr>
    <tr>
      <td class="titre">
        Adresse de l'expéditeur:
      </td>
      <td>
      	<select name="fromaddr">
          <option value="m4x" {if $wap.fromaddr eq "m4x"}selected="selected"{/if}>
          {$smarty.session.username}@m4x.org
          </option>
          <option value="polytechnique" {if $wap.fromaddr eq "polytechnique"}selected="selected"{/if}>
          {$smarty.session.username}@polytechnique.org
          </option>
          <option value="autre" {if $wap.fromaddr eq "autre"}selected="selected"{/if}>
          Autre...
          </option>
	</select>
      </td>
    </tr>
    <tr>
      <td class="titre">
        (si autre)
      </td>
      <td>
        <input type="text" name="otheraddr" size="60" value="{$wap.otheraddr}" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Mettre mon adresse en CC:
      </td>
      <td>
        <input type="checkbox" name="ccfrom" {if $wap.ccfrom}checked="checked"{/if} />
      </td>
    </tr>
  </table>
  <p>
    <br/>
    Si votre boite mail est sur un serveur <strong>IMAP</strong>, vous pouvez activer la lecture de vos mails depuis le site WAP.
  </p>
  <table class="bicol" cellpadding="3" summary="Lecture des mails">
    <tr>
      <th colspan="2">
        Paramètres pour la lecture des mails
      </th>
    </tr>
    <tr>
      <td class="titre">
        Serveur IMAP:
      </td>
      <td>
        <input type="text" name="IMAPserver" size="40" value="{$wap.IMAPserver}" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Utiliser le SSL:
      </td>
      <td>
        <input type="checkbox" name="IMAPssl" {if $wap.IMAPssl}checked="checked"{/if} />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Port:
      </td>
      <td>
        <input type="text" name="IMAPport" size="10" value="{$wap.IMAPport}" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Répertoire à lire:
      </td>
      <td>
        <input type="text" name="IMAPdir" size="40" value="{$wap.IMAPdir}" />
      </td>
    </tr>
  </table>
  <br />
  <div class="center">
    <input type="hidden" name="op" value="valid" />
    <input type="submit" value="Valider" />
  </div>
</form>
{/dynamic}
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
