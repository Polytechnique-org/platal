{* $Id: form.valid.photos.tpl,v 1.4 2004-08-24 23:06:05 x2000habouzit Exp $ *}

<form action="{$smarty.server.PHP_SELF}" method="post">
<input type="hidden" name="uid" value="{$valid->uid}" />
<input type="hidden" name="type" value="{$valid->type}" />
<input type="hidden" name="stamp" value="{$valid->stamp}" />
<table class="bicol" summary="Demande d'alias">
<tr>
  <td>Demandeur&nbsp;:</td>
  <td><a href="javascript:x()" onclick="popWin('{"x.php?x=$valid->username"|url}')">
      {$valid->prenom} {$valid->nom}
      </a>
  </td>
</tr>
<tr>
  <td class="middle" colspan="2">
    <img src="{"getphoto.php"|url}?x={$valid->uid}" style="width:110px;" alt=" [ PHOTO ] " />
    <img src="{"getphoto.php"|url}?x={$valid->uid}&amp;req=true" style="width:110px;" alt=" [ PHOTO ] " />
  </td>
</tr>
<tr>
  <td class="middle">
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
