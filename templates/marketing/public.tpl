{* $Id: public.tpl,v 1.2 2004-07-17 11:23:09 x2000habouzit Exp $ *}

{dynamic}
{if $smarty.request.num}

{if $smarty.request.valide}

<p class=\"normal\">
  Merci de nous avoir communiqué cette information !  Un administrateur de Polytechnique.org va
  envoyer un email de proposition d'inscription à Polytechnique.org à {$prenom} {$nom} dans les
  toutes prochaines heures (ceci est fait à la main pour vérifier qu'aucun utilisateur malveillant
  ne fasse mauvais usage de cette fonctionnalité...).
</p>
<p class=\"normal\">
  <strong>Merci de ton aide à la reconnaissance de notre site !</strong> Tu seras informé par email de
  l'inscription de {$prenom} {$nom} si notre camarade accepte de rejoindre la communauté des X sur
  le web !
</p>

{else}

{if $prenom}
<div class="rubriqu">
  Et si nous proposions à {$prenom} {$nom} de s'inscrire à Polytechnique.org ?
</div>

<p class="normal">
  En effet notre camarade n'a pour l'instant pas encore rejoint la communauté des X sur le web...
  C'est dommage, et en nous indiquant son adresse email, tu nous permettrais de lui envoyer une
  proposition d'inscription.
</p>
<p class="normal">
  Si tu es d'accord, merci d'indiquer ci-dessous l'adresse email de {$prenom} {$nom} si tu la
  connais.  Nous nous permettons d'attirer ton attention sur le fait que nous avons besoin d'être
  sûrs que cette adresse est bien la sienne, afin que la partie privée du site reste uniquement
  accessible aux seuls polytechniciens. Merci donc de ne nous donner ce renseignement uniquement si
  tu es certain de sa véracité !
</p>
<p class="normal">
  Nous pouvons au choix lui écrire au nom de l'équipe Polytechnique.org, ou bien, si tu le veux
  bien, en ton nom. A toi de choisir la solution qui te paraît la plus adaptée !! Une fois {$prenom}
  {$nom} inscrit, nous t'enverrons un email pour te prévenir que son inscription a réussi.
</p>

<form method="post" action="{$smarty.server.PHP_SELF}">
  <input type="hidden" name="num" value="{$smarty.request.num}" />
  <table class="bicol" summary="Fiche camarade">
    <tr class="impair"><td>Nom :</td><td>{$nom}</td></tr>
    <tr class="pair"><td>Prénom :</td><td>{$prenom}</td></tr>
    <tr class="impair"><td>Promo :</td><td>{$promo}</td></tr>
    <tr class="pair">
      <td>Adresse email :</td>
      <td>
        <input type="text" name="mail" size="30" maxlength="50" />
      </td>
    </tr>
    <tr class="impair">
      <td>Nous lui écrirons :</td>
      <td>
        <input type="radio" name="origine" value="perso" checked="checked" /> en ton nom<br />
        <input type="radio" name="origine" value="equipe" /> au nom de l'équipe Polytechnique.org
      </td>
    </tr>
  </table>
  <br />
  <input type="submit" name="valide" value="Valider" />
</form>
{/if}

{/if}

{/if}
{/dynamic}


{* vim:set et sw=2 sts=2 sws=2: *}
