{if $etat_naissance == 'ok'}
  <script language="javascript" type="text/javascript">
  <!--
    alert ("\nDate de naissance enregistrée.\n\nTu peux maintenant modifier ton profil.");
  // -->
  </script>
{else}
  {if $etat_naissance == 'erreur'}
    <p class="erreur">
     {#profil_naissance_erreur#}
    </p>
  {/if}

  <div class="rubrique">
    {#profil_naissance_titre#}
  </div>

<form action="profil.php" method="post">
  <p class="normal">
    {#profil_naissance_intro#}
  </p>
  <br />
  <div align="center">
    <table class="bicol" border="0" cellpadding="4" cellspacing="0" summary="Formulaire de naissance" width="60%">
      <tr>
        <th colspan="2">
          {#profil_date_titre#}
        </th>
      </tr>
      <tr>
        <td>
	  <b>Date</b> (JJMMAAAA)
        </td>
        <td>
          <input type="text" size="8" maxlength="8" name="birth">
        </td>
      </tr>
      <tr>
        <td align="center" colspan="2">
          <input type="submit" value="Enregistrer" name="submit">
        </td>
      </tr>
    </table>
  </div>
</form>


{/if}

