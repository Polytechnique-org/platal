{* $Id: edit.tpl,v 1.1 2004-02-22 21:04:23 x2000habouzit Exp $ *}

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
        <input type="text" name="trf_name" size="40"
          value="{$tracker->name|default:$smarty.post.trf_name}" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Description</strong>
      </td>
      <td>
        <input type="text" name="trf_desc" size="40" 
          value="{$tracker->desc|default:$smarty.post.trf_desc}" />
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
        <input type="checkbox" name="trf_no_mail" size="40"
          {if $tracker->bits->hasflag("no_mail") || $smarty.post.trf_no_mail}checked="checked"{/if} />
        cocher pour avoir un tracker silencieux<br />
        <input type="checkbox" name="trf_perso" size="40"
          {if $tracker->bits->hasflag("perso") || $smarty.post.trf_perso}checked="checked"{/if} />
        cocher pour avoir un tracker perso
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">
        Droits
      </td>
      <td>
        <select name="trf_perms">
          <option value="admin"
            {if $tracker->perms eq "admin" || $smarty.post.trf_perms eq "admin"}selected="selected"{/if}
          >admin</option>
          <option value="auth" 
            {if $tracker->perms eq "auth" || $smarty.post.trf_perms eq "auth"}selected="selected"{/if}
          >auth</option>
          <option value="public"
            {if $tracker->perms eq "public" || $smarty.post.trf_perms eq "public"}selected="selected"{/if}
          >public</option>
        </select>
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">Mailing List
      </td>
      <td>
        <select name="trf_ml_id">
          {*
          <?
          $req=mysql_query("SELECT ml_id,short FROM trackers.mail_lists ORDER BY short");
          while(list($id,$short) = mysql_fetch_row($req)) {
          echo "<option value=\"$id\"".($ml_id==$id ? " selected=\"selected\"" : "").">$short</option>\n";
          }
          mysql_free_result($req);
          ?>
          *}
        </select>
      </td>
    </tr>
    <tr>
      <th colspan="2">priorités</th>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Priorité 5</strong></td>
      <td>
        <input type="text" name="trf_pris[5]" size="40"
          value="{$tracker->pris[5]|default:$smarty.post.trf_pris[5]}" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Priorité 4</strong></td>
      <td>
        <input type="text" name="trf_pris[4]" size="40"
          value="{$tracker->pris[4]|default:$smarty.post.trf_pris[4]}" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Priorité 3</strong></td>
      <td>
        <input type="text" name="trf_pris[3]" size="40"
          value="{$tracker->pris[3]|default:$smarty.post.trf_pris[3]}" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Priorité 2</strong></td>
      <td>
        <input type="text" name="trf_pris[2]" size="40"
          value="{$tracker->pris[2]|default:$smarty.post.trf_pris[2]}" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Priorité 1</strong></td>
      <td>
        <input type="text" name="trf_pris[1]" size="40"
          value="{$tracker->pris[1]|default:$smarty.post.trf_pris[1]}" />
      </td>
    </tr>
    <tr>
      <th colspan="2">Création d'une ML (laisser vide pour ne pas l'utiliser)</th>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Nom court</strong>
      </td>
      <td>
        <input style="text" name="mlf_short" size="40" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre"><strong>Adresse (avec le @)</strong>
      </td>
      <td><input style="text" name="mlf_texte" size="40" />
      </td>
    </tr>
  </table>
  <br />
  <input type="hidden" name="tr_id" value="{$tracker->tr_id|default:$smarty.post.tr_id}" />
  <input type="hidden" name="action" value="update" />
  <input type="submit" value="Valider" />
</form>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
