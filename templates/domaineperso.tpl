{* $Id: domaineperso.tpl,v 1.1 2004-02-04 19:47:47 x2000habouzit Exp $ *}

{include file='include/liste_domaines.tpl' result=$result nb_dom=$nbdom domaines=$domaines}

<div class="rubrique">
  Gère les emails de ton domaine perso
</div>

<p class="normal">
 Polytechnique.org te propose de gérer les emails de ton domaine personnel.
</p>
<p class="normal">
  Effet, si tu disposes d'un domaine personnel comme ton-nom.org, tu dois utiliser un hébergeur pour ta 
  DNS, pour tes adresses emails et pour ton espace web. En général, c'est le même pour 
  les trois éléments, mais tu peux aussi utiliser des hébergeurs différents. Il en 
  existe certains qui sont gratuits (comme <a href="http://www.mydomain.com/">
  Mydomain</a>), mais pas toujours très performants. Polytechnique.org te propose de 
  s'occuper de tes emails dans un premier temps.
</p>
<p class="normal">
  Pour que ton domaine soit géré par Polytechnique.org, active d'abord le domaine dans 
  le formulaire ci-dessous. Le domaine apparaît alors en haut de cette page, places-y 
  les alias que tu désires.
</p>
<p class="normal">
  Ensuite, configure ton serveur DNS pour que le champ MX de ton domaine soit 
  a.mx.polytechnique.org (ou a.mx.m4x.org pour être plus discret
  mais pas les deux, c'est la même machine).
</p>
<p class="normal">
  Laisse le temps à la DNS de se mettre à jour (24 à 48h), et le tour est joué.
</p>
<p class="normal">
  Pour toute question, n'hesite pas à {mailto address'info@polytechnique.org' text='envoyer un mail' encode='javascript'}
</p>
<div class="ssrubrique">
  Indique le domaine que tu souhaites gérer :
</div>
<form action="{$smarty.server.REQUEST_URI}" method="post">
  <table class="bicol" cellpadding="3" summary="Saisie du domaine à gérer">
    <tr>
      <th colspan="2">
        Nom de domaine à gérer
      </th>
    </tr>
    <tr>
      <td class="titre">
        Nom :
      </td>
      <td>
	<input type="text" name="dnom" value="" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
	<input type="submit" name="submit" value="Envoyer" />
      </td>
    </tr>
  </table>
</form>
{* vim:set et sw=2 sts=2 sws=2: *}
