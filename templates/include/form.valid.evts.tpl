{* $Id: form.valid.evts.tpl,v 1.2 2004-02-09 17:47:07 x2000habouzit Exp $ *}

<form action="{$smarty.server.PHP_SELF}" method="POST" name="modif">
  <input type="hidden" name="uid" value="{$valid->uid}" />
  <input type="hidden" name="type" value="{$valid->type}" />
  <input type="hidden" name="stamp" value="{$valid->stamp}" />
  <table class="bicol">
    <thead>
      <tr>
        <th colspan="2">Événement</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          Posté par <a href="javascript:x()"  onclick="popWin('{"x.php?x=$valid->username"|url}">
            {$valid->prenom} {$valid->nom} (X{$valid->promo})
          </a>
          [<a href="mailto:{$valid->username}@polytechnique.org">lui écrire</a>]"
        </td>
      </tr>
      <tr>
        <th>Titre</th>
        <td>{$valid->titre}</td>
      </tr>
      <tr>
        <th>Texte</th>
        <td>{$valid->texte}</td>
      </tr>
      <tr>
        <th>Péremption</th>
        <td>{$valid->peremption}</td>
      </tr>
      <tr>
        <th>Promos</th>
        <td>{$valid->pmin} - {$valid->pmax}</td>
      </tr>
      <tr>
        <th>Commentaire</th>
        <td>{$valid->comment}</td>
      </tr>
      <tr>
        <td class="center" colspan="2">
          <input type="submit" name="action" value="Valider" />
          <input type="submit" name="action" value="Invalider" />
          <input type="submit" name="action" value="Supprimer" />
        </td>
      </tr>
    </tbody>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
