{* $Id: form.valid.sondages.tpl,v 1.1 2004-02-08 12:38:26 x2000habouzit Exp $ *}

<form action="{$smarty.server.PHP_SELF}" method="POST">
<input type="hidden" name="uid" value="{$valid->uid}" />
<input type="hidden" name="type" value="{$valid->type}" />
<input type="hidden" name="stamp" value="{$valid->stamp}" />
<table class="bicol" cellpadding="4" summary="Sondage">
<tr>
  <td>Demandeur&nbsp;:
  </td>
  <td><a href="javascript:x()" onclick="popWin('{"x.php?x=$valid->username"|url}')">
      {$valid->prenom} {$valid->nom}</a>
    {if $valid->old}({$valid->old}){/if}
  </td>
</tr>
<tr>
  <td>Titre du sondage&nbsp;:</td>
  <td>{$valid->titre}</td>
</tr>
<tr>
  <td>Prévisualisation du sondage&nbsp;:</td>
  <td><a href="{"sondages/questionnaire.php?SID=$valid->sid"|url}" target="_blank">{$valid->titre}</a>
  </td>
</tr>
<tr>
  <td>Alias du sondage&nbsp;:</td>
  <td><input type="text" name="alias" value="{$valid->alias}" />&nbsp;(ne doit pas contenir le caractère ')
  </td>
</tr>
<tr>
  <td style="vertical-align: middle;">
      <input type="submit" name="submit" value="Accepter" />
      <br /><br />
      <input type="submit" name="submit" value="Refuser" />
  </td>
  <td>
      <p>Raison du refus:</p>
      <textarea rows="5" cols="74" name=motif></textarea>
  </td>
</tr>
</table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
