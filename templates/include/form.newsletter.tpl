{* $Id: form.newsletter.tpl,v 1.1 2004-02-11 11:51:53 x2000habouzit Exp $ *}

<form method="post" action="{$smarty.server.PHP_SELF}">
  <input type="hidden" name="nl_id" value="{$nl_id}">
  <input type="hidden" name="action" value="update">
  <table class="bicol" summary="Formulaire de saisie de newsletter">
    <tr>
      <th colspan="2">
        {$form_title}
      </th>
    </tr>
    <tr>
      <td>
        date
      </td>
      <td>
        <input type="text" name="nl_date" value="{$nl_date|default:$smarty.now|date_format:"%Y-%m-%d"}" />
      </td>
    </tr>
    <tr>
      <td>
        titre
      </td>
      <td>
        <input type="text" name="nl_titre" size="40" value="{$nl_titre}" />
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <textarea name="nl_text" rows="100" cols="80">{$nl_text}</textarea>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="bouton">
        <input type="submit" value="Envoyer">
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
