{* $Id: form.valid.aliases.tpl,v 1.7 2004-08-29 16:02:40 x2000habouzit Exp $ *}

<form action="{$smarty.server.PHP_SELF}" method="post">
<table class="bicol" cellpadding="4" summary="Demande d'alias">
<tr>
  <td>Demandeur&nbsp;:
  </td>
  <td>
    <a href="javascript:x()" onclick="popWin('{"fiche.php"|url}?user={$valid->username}')">
    {$valid->prenom} {$valid->nom}</a> {$valid->old}
  </td>
</tr>
<tr>
  <td>Nouvel&nbsp;alias&nbsp;:</td>
  <td>{$valid->alias}@melix.net</td>
</tr>
<tr>
  <td>Motif :</td>
  <td style="border: 1px dotted inherit">
    {$this->raison|nl2br}
  </td>
</tr>
<tr>
  <td class="middle">
    <input type="hidden" name="uid" value="{$valid->uid}" />
    <input type="hidden" name="type" value="{$valid->type}" />
    <input type="hidden" name="stamp" value="{$valid->stamp}" />
    <input type="submit" name="submit" value="Accepter" />
    <br /><br />
    <input type="submit" name="submit" value="Refuser" />
  </td>
  <td>
    <p>Raison du refus:</p>
    <textarea rows="5" cols="74" name="motif"></textarea>
  </td>
</tr>
</table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
