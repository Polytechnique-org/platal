{* $Id: form.valid.epouses.tpl,v 1.3 2004-08-24 23:06:05 x2000habouzit Exp $ *}

<form action="{$smarty.server.PHP_SELF}" method="post">
<input type="hidden" name="uid" value="{$valid->uid}" />
<input type="hidden" name="type" value="{$valid->type}" />
<input type="hidden" name="stamp" value="{$valid->stamp}" />
<table class="bicol" cellpadding="4" summary="Demande d'alias d'épouse">
<tr>
  <td>Demandeur&nbsp;:</td>
  <td>
    <a href="javascript:x()" onclick="popWin('/x.php?x={$valid->username}')">
      {$valid->prenom} {$valid->nom}
    </a>
    {if $valid->oldepouse}({$valid->oldepouse} - {$valid->oldalias}){/if}
  </td>
</tr>
<tr>
  <td>&Eacute;pouse&nbsp;:</td>
  <td>{$valid->epouse}</td>
</tr>
<tr>
  <td>Nouvel&nbsp;alias&nbsp;:</td>
  <td>{$valid->alias}</td>
</tr>
{if $valid->homonyme}
<tr>
<td colspan="2">
  <span class="erreur">Probleme d'homonymie !
  <a href="javascript:x()"  onclick="popWin('{"x.php?x=$valid->homonyme"|url}"> {$valid->homonyme}</a>
  </span>
</td>
</tr>
{/if}
<tr>
  <td class="middle">
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
