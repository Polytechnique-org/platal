{* $Id: trombino.tpl,v 1.3 2004-08-30 12:18:40 x2000habouzit Exp $ *}

{dynamic}

{if $erreur}
<p class="erreur">
{$erreur}
</p>
<p>
La photo soumise n'a pu être correctement téléchargée pour la raison précédente.
La photo par défaut est donc gardée.
</p>
{/if}


<div class="rubrique">
  Trombinoscope
</div>

<form enctype="multipart/form-data" action="{$smarty.server.REQUEST_URI}" method="post">
  <table class="flags" cellspacing="0" summary="Flags">
    <tr>
      <td class="rouge"><input type="radio" checked="checked" />
      </td>
      <td class="texte">privé
      </td>
    </tr>
  </table>

  {if ($session.promo ge 1995) or ($session.promo le 2002)}
  <p>
  Si tu n'as pas encore fourni de photo, c'est celle du trombinoscope de l'X qui est
  affichée par défaut dans le profil. Si elle ne te plaît pas, ou si tu n'es quand même
  plus un tos, tu peux la remplacer par ta photo en suivant les instructions suivantes.
  </p>
  {/if}

  <table class="bicol" cellspacing="0" cellpadding="2">
    <tr>
      <th>
        Photo actuelle
      </th>
      <th>
        Photo en cours de validation<sup>(*)</sup>
      </th>
    </tr>
    <tr>
      <td class="center">
        <img src="{"getphoto.php"|url}?x={$smarty.session.uid}" width=110 alt=" [ PHOTO ] " />
      </td>
      <td class="center half">
        {if $submited}
        <img src="{"getphoto.php"|url}?x={$smarty.session.uid}&amp;req=true" width=110 alt=" [ PHOTO ] " />
        {else}
        Pas d'image soumise
        {/if}
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        Si tu ne souhaites plus montrer cette photo tu peux aussi l'effacer en la remplaçant par : <br />
        {if ($session.promo ge 1995) or ($session.promo le 2002)}
        <input type="submit" value="Trombino de l'X" name="trombi" /><br />
        {/if}
        <input type="submit" value="Image par défaut" name="suppr" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="smaller">
        * Les photos sont soumises à une validation manuelle en raison des législations relatives
        aux droits d'auteur et à la protection des mineurs. Il faut donc attendre l'intervention
        d'un administrateur pour que la photo soit prise en compte. Tu recevras un mail lorsque ta
        photo aura été contrôlée.
      </td>
    </tr>
  </table>

  <br />

  <table class="bicol" cellspacing="0" cellpadding="2">
    <tr>
      <th>
        Changement de ta photo
      </th>
    </tr>
    <tr>
      <td>
        <p>
        Nous te proposons deux possibilités pour mettre à jour ta photo (30 Ko maximum). Tout dépend
        de savoir où se trouve ta photo. Si elle est sur ton poste de travail local, c'est la première
        solution qu'il faut choisir.
        </p>
        <p>
        Si elle est sur Internet, choisis la seconde solution et nos robots iront la télécharger
        directement où il faut :-)
        </p>
      </td>
    </tr>
    <tr>
      <td class="titre">
        Sur ton ordinateur
      </td>
    </tr>
    <tr>
      <td>
        <input name="userfile" type="file" size="20" maxlength="150" />
      </td>
    </tr>
    <tr>
      <td class="bouton">
        <input type="submit" value="  OK  " name="ordi" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Sur Internet
      </td>
    </tr>
    <tr>
      <td>
        <input type="text" size="45" maxlength="140" name="photo"
        value="{$smarty.request.photo|default:"http://www.multimania.com/joe/maphoto.jpg"}" />
      </td>
    </tr>
    <tr>
      <td class="center">
        <input type="submit" value="  OK  " name="web" />
      </td>
    </tr>
  </table>

</form>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
