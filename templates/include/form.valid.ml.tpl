{* $Id: form.valid.ml.tpl,v 1.3 2004-08-24 09:07:58 x2000habouzit Exp $ *}

<form action="{$smarty.server.PHP_SELF}" method="POST">
<input type="hidden" name="uid" value="{$valid->uid}" />
<input type="hidden" name="type" value="{$valid->type}" />
<input type="hidden" name="stamp" value="{$valid->stamp}" />
<table class="bicol">
<tr>
  <td>Demandeur&nbsp;:</td>
  <td>
    <a href="javascript:x()" onclick="popWin('/x.php?x={$valid->username}')">
    {$valid->prenom} {$valid->nom}
    </a>
  </td>
</tr>
<tr>
  <td>Motif :</td>
  <td>{$valid->comment|nl2br}
  </td>
</tr>
<tr>
  <td style="border-top:1px dotted inherit">
    Alias :
  </td>
  <td style="border-top:1px dotted inherit">
    <input type="text" name="alias" value="{$valid->alias}" />@polytechnique.org
  </td>
</tr>
<tr>
  <td>Topic :</td>
  <td><input type="text" name="topic" size="60" value="{$valid->topic}" />
  </td>
</tr>
<tr>
  <td>Propriétés :</td>
  <td>
    <input type="checkbox" name="publique" {if $valid->publique}checked="checked"{/if} />Publique
    <input type="checkbox" name="libre" {if $valid->libre}checked="checked"{/if} />Libre
    <input type="checkbox" name="freeins" {if $valid->freeins}checked="checked"{/if} />Freeins
    <input type="checkbox" name="archive" {if $valid->archive}checked="checked"{/if} />Archive
  </td>
</tr>
<tr>
  <td style="border-top:1px dotted inherit">Modéros :</td>
  <td style="border-top:1px dotted inherit">{$valid->moderos_txt}</td>
</tr>
<tr>
  <td>Membres :</td>
  <td>{$valid->membres_txt}</td>
</tr>
<tr>
  <td class="middle" style="border-top:1px dotted inherit">
    <input type="submit" name="submit" value="Accepter" />
    <br /><br />
    <input type="submit" name="submit" value="Refuser" />
  </td>
  <td style="border-top:1px dotted inherit">
    <p>Explication complémentaire (refus ou changement de config, ...)</p>
    <textarea rows="5" cols="74" name=motif></textarea>
  </td>
</tr>
</table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
