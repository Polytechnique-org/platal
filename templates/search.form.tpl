<div class="rubrique">
  Recherche
</div>
{if $error}
  <p class="error">
    {$error}
  </p>
{/if}
<div class="center">
  <form action="{$smarty.server.PHP_SELF}" method="post">
    <input type="hidden" name="public_directory" value="{$public_directory}">
    <table class="tinybicol" cellpadding="3" summary="Recherche">
      <tr>
        <td>Nom</td>
        <td><input type="text" name="name" size="50" maxlength="50" /></td>
      </tr>
      <tr>
        <td>Prénom</td>
        <td><input type="text" name="firstname" size="50" maxlength="50" /></td>
      </tr>
      <tr>
        <td>Promotion</td>
        <td>
          <select name="egal">
            <option value="=" selected>&nbsp;=&nbsp;</option>
            <option value=">" >&nbsp;&gt;&nbsp;</option>
            <option value="<" >&nbsp;&lt;&nbsp;</option>
          </select>
          <input type="text" name="promo" size="4" maxlength="4" />
        </td>
      </tr>
      <tr>
        <td colspan="2" class="center"><input type="submit" name="rechercher" value="Ok" /></td>
      </tr>
    </table>
  </form>
</div>
