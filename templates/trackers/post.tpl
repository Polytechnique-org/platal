{* $Id: post.tpl,v 1.1 2004-02-23 18:43:41 x2000habouzit Exp $ *}

{dynamic}

<p class="normal">
  [<a href="index.php">Liste des trackers</a>]
  [<a href="show.php?tr_id={$smarty.get.tr_id}">Retourner au tracker</a>]
</p>

<div class="rubrique">
  Poster dans {$tracker->name}
</div>

<form method="post" action="{$smarty.server.REQUEST_URI}">
  <table class="bicol" cellpadding="3">
    <tr class="impair">
      <td class="titre">
        Priorité
      </td>
      <td>
        <select name="prio">
          <option value="5">{$tracker->pris[5]}</option>
          <option value="4">{$tracker->pris[4]}</option>
          <option value="3" selected="selected">{$tracker->pris[3]}</option>
          <option value="2">{$tracker->pris[2]}</option>
          <option value="1">{$tracker->pris[1]}</option>
        </select>
      </td>
    </tr>
    <tr>
      <td class="titre">
        Statut
      </td>
      <td>
        <select name="statut">
          {foreach item=st from=$states}
          <option value="{$st.st_id}">{$st.texte}</option>
          {/foreach}
        </select>
      </td>
    </tr>
    <tr>
      <td class="titre"> Sujet </td>
      <td><input name="sujet" type="text" size="40" value="{$smarty.post.sujet}" /></td>
    </tr>
    <tr>
      <td colspan="2"><textarea name="text" cols="74" rows="20">{$smarty.post.text}</textarea></td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="submit" value="Valider" />
      </td>
    </tr>
  </table>
</form>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
