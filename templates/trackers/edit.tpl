{* $Id: edit.tpl,v 1.2 2004-02-23 17:06:59 x2000habouzit Exp $ *}

<div class="rubrique">
  Modification des propriétés du tracker
</div>

{dynamic}

<form action="{$smarty.server.PHP_SELF}" method="post">
  <table class="bicol">
    <tr>
      <th colspan="2">Description</th>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Nom du tracker</strong>
      </td>
      <td>
        <input type="text" name="name" size="40"
          value="{$tracker->name|default:$smarty.post.name}" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Description</strong>
      </td>
      <td>
        <input type="text" name="desc" size="40" 
          value="{$tracker->desc|default:$smarty.post.desc}" />
      </td>
    </tr>
    <tr>
      <th colspan="2">propriétés</th>
    </tr>
    <tr class="impair">
      <td class="titre">
        <strong>
          Tracker non notifiant<br />
          Tracker Perso
        </strong>
      </td>
      <td>
        <input type="checkbox" name="nomail" size="40"
          {if ($tracker->bits && $tracker->bits->hasflag("no_mail")) || $smarty.post.no_mail}checked="checked"{/if} />
          cocher pour avoir un tracker silencieux
        <br />
        <input type="checkbox" name="perso" size="40"
          {if ($tracker->bits && $tracker->bits->hasflag("perso")) || $smarty.post.perso}checked="checked"{/if} />
          cocher pour avoir un tracker perso
    </td>
    </tr>
    <tr class="impair">
      <td class="titre">
        Droits
      </td>
      <td>
        <select name="perms">
          <option value="admin"
            {if $tracker->perms eq "admin" || $smarty.post.perms eq "admin"}selected="selected"{/if}
          >admin</option>
          <option value="auth" 
            {if $tracker->perms eq "auth" || $smarty.post.perms eq "auth"}selected="selected"{/if}
          >auth</option>
          <option value="public"
            {if $tracker->perms eq "public" || $smarty.post.perms eq "public"}selected="selected"{/if}
          >public</option>
        </select>
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">Mailing List
      </td>
      <td>
        <select name="mlid">
          {foreach item=ml from=$ml_list}
          <option value="{$ml.ml_id}"
            {if $ml.ml_id eq $tracker->ml_id || $ml.ml_id eq $smarty.post.mlid}selected="selected"{/if}>{$ml.short}</option>
          {/foreach}
        </select>
      </td>
    </tr>
    <tr>
      <th colspan="2">priorités</th>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Priorité 5</strong></td>
      <td>
        <input type="text" name="pris[5]" size="40"
          value="{$tracker->pris[5]|default:$smarty.post.pris[5]}" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Priorité 4</strong></td>
      <td>
        <input type="text" name="pris[4]" size="40"
          value="{$tracker->pris[4]|default:$smarty.post.pris[4]}" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Priorité 3</strong></td>
      <td>
        <input type="text" name="pris[3]" size="40"
          value="{$tracker->pris[3]|default:$smarty.post.pris[3]}" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Priorité 2</strong></td>
      <td>
        <input type="text" name="pris[2]" size="40"
          value="{$tracker->pris[2]|default:$smarty.post.pris[2]}" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Priorité 1</strong></td>
      <td>
        <input type="text" name="pris[1]" size="40"
          value="{$tracker->pris[1]|default:$smarty.post.pris[1]}" />
      </td>
    </tr>
    <tr>
      <th colspan="2">Création d'une ML (laisser vide pour ne pas l'utiliser)</th>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Nom court</strong>
      </td>
      <td>
        <input style="text" name="short" size="40" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Adresse (avec le @)</strong>
      </td>
      <td><input style="text" name="texte" size="40" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="hidden" name="trid" value="{$tracker->tr_id|default:$smarty.post.trid}" />
        <input type="hidden" name="action" value="update" />
        <input type="submit" value="Valider" />
      </td>
  </table>
</form>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
