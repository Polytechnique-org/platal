{* $Id: form.valid.emploi.tpl,v 1.4 2004-08-25 09:43:59 x2000habouzit Exp $ *}

<form action="{$smarty.server.PHP_SELF}" method="post">
  <table class="bicol" cellpadding="4" summary="Annonce emploi">
    <thead>
      <tr>
        <th colspan="2">Offre d'emploi</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Demandeur</td>
        <td>{$valid->entreprise} ({$valid->mail})</td>
      </tr>
      <tr>
        <td>Titre du post</td>
        <td>{$valid->titre}</td>
      </tr>
      <tr>
        <td colspan="2"><pre>{$valid->text}</pre></td>
      </tr>
      <tr>
        <td class="center" colspan="2">
          <input type="hidden" name="uid" value="{$valid->uid}" />
          <input type="hidden" name="type" value="{$valid->type}" />
          <input type="hidden" name="stamp" value="{$valid->stamp}" />
          <input type="submit" name="submit" value="Accepter" />
          <input type="submit" name="submit" value="Refuser" />
        </td>
      </tr>
    </tbody>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
