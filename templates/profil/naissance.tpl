{* $Id: naissance.tpl,v 1.4 2004-08-26 14:44:45 x2000habouzit Exp $ *}

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
  <p>
  {#profil_naissance_intro#}
  </p>
  <br />
  <table class="tinybicol" cellpadding="4" cellspacing="0" summary="Formulaire de naissance">
    <tr>
      <th colspan="2">
        {#profil_date_titre#}
      </th>
    </tr>
    <tr>
      <td>
        <strong>Date</strong> (JJMMAAAA)
      </td>
      <td>
        <input type="text" size="8" maxlength="8" name="birth" />
      </td>
    </tr>
    <tr>
      <td class="center" colspan="2">
        <input type="submit" value="Enregistrer" name="submit" />
      </td>
    </tr>
  </table>
</form>


{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
