{* $Id: mentor.tpl,v 1.5 2004-08-26 14:44:45 x2000habouzit Exp $ *}

{literal}
<script language="JavaScript" type="text/javascript">
  <!--

  function mentor_pays_add()
  {
    var selid = document.prof_annu.mentor_pays_id_new.selectedIndex;
    document.prof_annu.mentor_pays_id.value = document.prof_annu.mentor_pays_id_new.options[selid].value;
    document.prof_annu.mentor_pays_name.value = document.prof_annu.mentor_pays_id_new.options[selid].text;
    document.prof_annu.mentor_pays_op.value = "ajouter";
    document.prof_annu.submit();
  } // function mentor_pays_add()

  function mentor_pays_del( pid )
  {
    document.prof_annu.mentor_pays_id.value = pid;
    document.prof_annu.mentor_pays_op.value = "retirer";
    document.prof_annu.submit();
  } // function mentor_pays_del( pid )

  function mentor_secteur_add()
  {
    var selid_secteur = document.prof_annu.mentor_secteur_id_new.selectedIndex;
    document.prof_annu.mentor_secteur_id.value = document.prof_annu.mentor_secteur_id_new.options[selid_secteur].value;
    document.prof_annu.mentor_secteur_name.value = document.prof_annu.mentor_secteur_id_new.options[selid_secteur].text;
    var selid_ss_secteur = document.prof_annu.mentor_ss_secteur_id_new.selectedIndex;
    document.prof_annu.mentor_ss_secteur_id.value = document.prof_annu.mentor_ss_secteur_id_new.options[selid_ss_secteur].value;
    document.prof_annu.mentor_ss_secteur_name.value = document.prof_annu.mentor_ss_secteur_id_new.options[selid_ss_secteur].text;
    document.prof_annu.mentor_secteur_op.value = "ajouter";
    document.prof_annu.submit();
  } // function mentor_secteur_add()

  function mentor_secteur_del( sid )
  {
    document.prof_annu.mentor_secteur_id.value = sid;
    document.prof_annu.mentor_secteur_op.value = "retirer";
    document.prof_annu.submit();
  } // function mentor_secteur_del( sid )

  //-->
</script>
{/literal}

<p>
Si tu acceptes que ceux de nos camarades qui,
<ul>
  <li>encore jeunes, sont en train de bâtir leur projet professionnel,</li>
  <li>ou bien, plus âgés, souhaitent réorienter leur carrière,</li>
</ul>
te contactent afin de te demander conseil, dans les domaines que tu connais
bien, et pour lesquels tu pourrais les aider, remplis cette rubrique.<br />
Tu peux mentionner ici les domaines de compétences, les expériences 
notamment internationales sur la base desquels tu seras identifiable depuis
<a href="referent.php">la page de recherche d'un conseil professionnel</a>.
</p>

<div class="blocunite_tab">
  <table class="bicol" cellspacing="0" cellpadding="0" 
    summary="Profil: Mentoring">
    <tr>
      <th colspan="3">
        Pays dont tu connais bien la culture professionnelle
      </th>
    </tr>
    <input type="hidden" value="" name="mentor_pays_op" />
    <input type="hidden" value="00" name="mentor_pays_id" />
    <input type="hidden" value="" name="mentor_pays_name" />
    <tr>
      <td colspan="3" class="pflags">
        <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
          <tr>
            <td class="rouge">
              <input type="checkbox" name="accesX" checked="checked" disabled="disabled" />
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
        <span class="titre">Pays</span>
      </td>
      <td class="colm">
        <span class="titre"></span>
      </td>
      <td class="cold" style="width:15%">
        &nbsp;
      </td>
    </tr>
    {section name=pays loop=$nb_mentor_pays+1 start=1}
    {assign var='i' value=$smarty.section.pays.index}
    {if $i%2}
    <tr class="pair">
      {else}
      <tr class="impair">
        {/if}
        <td class="colg">
          <span class="valeur">{$mentor_pays.$i}</span>
        </td>
        <td class="colm">
          <span class="valeur">&nbsp;&nbsp;</span>
        </td>
        <td class="cold">
          <span class="lien"><a href="javascript:mentor_pays_del('{$mentor_pid.$i}');">retirer</a></span>
        </td>
      </tr>
      {/section}
      {if $nb_mentor_pays < $max_mentor_pays}
      {if $i%2}
      <tr class="pair">
        {else}
        <tr class="impair">
          {/if}
          <td class="colg">
            <select name="mentor_pays_id_new">
              {geoloc_pays pays='00'}
            </select>
          </td>
          <td class="colm">
          </td>
          <td class="cold">
            <span class="lien"><a href="javascript:mentor_pays_add();">ajouter</a></span>
          </td>
        </tr>
        {/if}
      </table>
    </div>

    <div class="blocunite">
      <table class="bicol" cellspacing="0" cellpadding="0" 
        summary="Profil: Mentoring">
        <tr>
          <th colspan="3">
            Secteurs d'activité dans lesquels tu as beaucoup exercé
          </th>
        </tr>
        <input type="hidden" value="" name="mentor_secteur_op" />
        <input type="hidden" value="" name="mentor_secteur_id" />
        <input type="hidden" value="" name="mentor_secteur_name" />
        <input type="hidden" value="" name="mentor_ss_secteur_id" />
        <input type="hidden" value="" name="mentor_ss_secteur_name" />
        <tr>
          <td colspan="3" class="pflags">
            <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
              <tr>
                <td class="rouge">
                  <input type="checkbox" name="accesX" checked="checked" disabled="disabled" />
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
            <span class="titre">Secteur</span>
          </td>
          <td class="colm">
            <span class="titre">Sous-Secteur</span>
          </td>
          <td class="cold" style="width:15%">
            &nbsp;
          </td>
        </tr>
        {section name=secteur loop=$nb_mentor_secteurs+1 start=1}
        {assign var='i' value=$smarty.section.secteur.index}
        {if $i%2}
        <tr class="pair">
          {else}
          <tr class="impair">
            {/if}
            <td class="colg">
              <span class="valeur">{$mentor_secteur.$i}</span>
            </td>
            <td class="colm">
              <span class="valeur">{$mentor_ss_secteur.$i}</span>
            </td>
            <td class="cold">
              <span class="lien"><a href="javascript:mentor_secteur_del('{$mentor_sid.$i}');">retirer</a></span>
            </td>
          </tr>
          {/section}
          {if $nb_mentor_secteurs < $max_mentor_secteurs}
          {if $i%2}
          <tr class="pair">
            {else}
            <tr class="impair">
              {/if}
              <td class="colg">
                <select name="mentor_secteur_id_new" OnChange="javascript:submit()">
                  {select_secteur secteur=$mentor_secteur_id_new}
                </select>
              </td>
              <td class="colm">
                <select name="mentor_ss_secteur_id_new">
                  {select_ss_secteur secteur=$mentor_secteur_id_new ss_secteur=''}
                </select>
              </td>
              <td class="cold">
                <span class="lien"><a href="javascript:mentor_secteur_add();">ajouter</a></span>
              </td>
            </tr>
            {/if}
          </table>
        </div>

        <div class="blocunite">
          <table class="bicol" cellspacing="0" cellpadding="0" 
            summary="Profil: Mentoring">
            <tr>
              <th colspan="3">
                Expérience et expertises que tu acceptes de faire partager
              </th>
            </tr>
            <tr>
              <td colspan="3" class="pflags">
                <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
                  <tr>
                    <td class="rouge">
                      <input type="checkbox" name="accesX" checked="checked" disabled="disabled" />
                    </td>
                    <td class="texte">
                      ne peut être ni public ni transmis à l'AX
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td colspan="3">
                Dans cette case il te faut indiquer en quelques mots ce qui t'a
                amené à acquérir l'expérience indiquée, et dans quelle mesure tu
                veux bien que ceux de nos camarades qui seraient intéressés par un
                contact avec toi, en prenne l'initiative. <strong>Il est obligatoire de
                  remplir cette dernière case pour apparaître dans la base de données
                  des "Mentors".</strong>
                <br />
              </td>
            </tr>
            <tr>
              <td colspan="3">
                <textarea rows="8" cols="60" name="mentor_expertise">{$mentor_expertise}</textarea>
              </td>
            </tr>
          </table>
        </div>

        {* vim:set et sw=2 sts=2 sws=2: *}
