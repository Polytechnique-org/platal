{* $Id: form.valid.evts.tpl,v 1.5 2004-08-29 16:02:40 x2000habouzit Exp $ *}

<form action="{$smarty.server.PHP_SELF}" method="post" name="modif">
  <table class="bicol">
    <tr>
      <th class="titre" colspan="2">Événement</th>
    </tr>
    <tr>
      <td>
        Posté par <a href="javascript:x()"  onclick="popWin('{"fiche.php"|url}?user={$valid->username}">
          {$valid->prenom} {$valid->nom} (X{$valid->promo})
        </a>
        [<a href="mailto:{$valid->username}@polytechnique.org">lui écrire</a>]
      </td>
    </tr>
    <tr>
      <td class="titre">Titre</td>
      <td>{$valid->titre}</td>
    </tr>
    <tr>
      <td class="titre">Texte</td>
      <td>{$valid->texte}</td>
    </tr>
    <tr>
      <td class="titre">Péremption</td>
      <td>{$valid->peremption}</td>
    </tr>
    <tr>
      <td class="titre">Promos</td>
      <td>{$valid->pmin} - {$valid->pmax}</td>
    </tr>
    <tr>
      <td class="titre">Commentaire</td>
      <td>{$valid->comment}</td>
    </tr>
    <tr>
      <td class="center" colspan="2">
        <input type="hidden" name="uid" value="{$valid->uid}" />
        <input type="hidden" name="type" value="{$valid->type}" />
        <input type="hidden" name="stamp" value="{$valid->stamp}" />
        <input type="submit" name="action" value="Valider" />
        <input type="submit" name="action" value="Invalider" />
        <input type="submit" name="action" value="Supprimer" />
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
