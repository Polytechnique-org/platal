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
          <select name="egal1">
            <option value="=" selected>&nbsp;=&nbsp;</option>
            <option value=">" >&nbsp;&gt;&nbsp;</option>
            <option value="<" >&nbsp;&lt;&nbsp;</option>
          </select>
          <input type="text" name="promo1" size="4" maxlength="4" />
          &nbsp;ET&nbsp;
          <select name="egal2">
            <option value="=" selected>&nbsp;=&nbsp;</option>
            <option value=">" >&nbsp;&gt;&nbsp;</option>
            <option value="<" >&nbsp;&lt;&nbsp;</option>
          </select>
          <input type="text" name="promo2" size="4" maxlength="4" />
        </td>
      </tr>
{if $advanced eq "1"}
      <tr>
        <th colspan="2">Divers</th>
      </tr>
      <tr>
        <td>Nationalité</td>
        <td>
          <select name="nationalite">
          {section name=nationalite loop=$choix_nationalites}
            <option value="{$choix_nationalites[nationalite].id}">
              {$choix_nationalites[nationalite].text}
            </option>
          {/section}
          </select>
        </td>
      </tr>
      <tr>
        <td>Binet</td>
        <td>
          <select name="binet">
          <option value="0"></option>
          {section name=binet loop=$choix_binets}
            <option value="{$choix_binets[binet].id}">
              {$choix_binets[binet].text}
            </option>
          {/section}
          </select>
        </td>
      </tr>
      <tr>
        <td>Groupe X</td>
        <td>
          <select name="groupex">
          <option value="0"></option>
          {section name=groupex loop=$choix_groupesx}
            <option value="{$choix_groupesx[groupex].id}">
              {$choix_groupesx[groupex].text}
            </option>
          {/section}
          </select>
        </td>
      </tr>
      <tr>
        <td>Section</td>
        <td>
          <select name="section">
          {section name=section loop=$choix_sections}
            <option value="{$choix_sections[section].id}">
              {$choix_sections[section].text}
            </option>
          {/section}
          </select>
        </td>
      </tr>
{/if}
      <tr>
        <td colspan="2" class="center"><input type="submit" name="rechercher" value="Ok" /></td>
      </tr>
    </table>
  </form>
</div>
