{literal}
<script language="JavaScript" type="text/javascript">
<!--

    function langue_add()
    {
        var selectid = document.prof_annu.langue_sel_add.selectedIndex;
        document.prof_annu.langue_id.value = document.prof_annu.langue_sel_add.options[selectid].value;
	var selectid_level = document.prof_annu.langue_level_sel_add.selectedIndex;
        document.prof_annu.langue_level.value = document.prof_annu.langue_level_sel_add.options[selectid_level].value;
        document.prof_annu.langue_op.value = "ajouter";
	document.prof_annu.submit();
    } // function langue_add()

    function langue_del( lid )
    {
        document.prof_annu.langue_id.value = lid;
        document.prof_annu.langue_op.value = "retirer";
        document.prof_annu.submit();
    } // function langue_del( id )

    function comppros_add()
    {
        var selectid = document.prof_annu.comppros_sel_add.selectedIndex;
        document.prof_annu.comppros_id.value = document.prof_annu.comppros_sel_add.options[selectid].value;
	var selectid_level = document.prof_annu.comppros_level_sel_add.selectedIndex;
        document.prof_annu.comppros_level.value = document.prof_annu.comppros_level_sel_add.options[selectid_level].value;
        document.prof_annu.comppros_op.value = "ajouter";
	document.prof_annu.submit();
    } // function langue_add()

    function comppros_del( cid )
    {
        document.prof_annu.comppros_id.value = cid;
        document.prof_annu.comppros_op.value = "retirer";
        document.prof_annu.submit();
    } // function comppros_del( id )
//-->
</script>
{/literal}

<div class="blocunite_tab">
    <table class="bicol" width="98%" cellspacing="0" cellpadding="0" 
    summary="Profil: Compétences professionnelles">
      <tr>
        <th colspan="3">
	  Compétences professionnelles
	</th>
      </tr>
      <input type="hidden" value="" name="comppros_op">
      <input type="hidden" value="" name="comppros_id">
      <input type="hidden" value="" name="comppros_level">
      <tr>
	<td colspan="3" class="pflags">
	  <table class="flags" summary="Flags" cellpadding="0" cellspacing="0" border="0">
	    <tr>
	      <td class="rouge">
	        <input type="checkbox" name="accesX" checked disabled>
	      </td>
	      <td class="texte">
	        ne peut être ni public ni transmis à l'AX
	      </td>
	    </tr>
	  </table>
	</td>
      </tr>
      <tr class="impair">
        <td class="colg">
	  <span class="titre">Domaine</span>
	</td>
	<td class="colm">
          <span class="titre">Niveau</span>
	</td>
        <td class="cold" width="15%">
        &nbsp;
	</td>
      </tr>
       {print_comppros}
   </table>
</div>


<div class="blocunite">
    <table class="bicol" width="98%" cellspacing="0" cellpadding="0" 
    summary="Profil: Compétences linguistiques">
      <tr>
        <th colspan="3">
	  Compétences linguistiques
	</th>
      </tr>
      <input type="hidden" value="" name="langue_op">
      <input type="hidden" value="" name="langue_id">
      <input type="hidden" value="" name="langue_level">
      <tr>
	<td colspan="3" class="pflags">
	  <table class="flags" summary="Flags" cellpadding="0" cellspacing="0" border="0">
	    <tr>
	      <td class="rouge">
	        <input type="checkbox" name="accesX" checked disabled>
	      </td>
	      <td class="texte">
	        ne peut être ni public ni transmis à l'AX
	      </td>
	    </tr>
	  </table>
	</td>
      </tr>
      <tr class="impair">
        <td class="colg">
	  <span class="titre">Langue</span>
	</td>
	<td class="colm">
          <span class="titre">Niveau</span>
	</td>
        <td class="cold" width="15%">
	  <span class="lien"><a href="javascript:x()" onclick="popWin('aide.php#niveau_langue','remplissage','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=500')">Quel niveau ?</a></span>
        </td>
      </tr>
      {print_langues}
   </table>
</div>
