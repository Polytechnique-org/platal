{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: skill.tpl,v 1.5 2004-08-31 11:25:42 x2000habouzit Exp $
 ***************************************************************************}


{literal}
<script type="text/javascript">
  //<![CDATA[
  function langue_add()
  {
    var selectid = document.forms.prof_annu.langue_sel_add.selectedIndex;
    document.forms.prof_annu.langue_id.value = document.forms.prof_annu.langue_sel_add.options[selectid].value;
    var selectid_level = document.forms.prof_annu.langue_level_sel_add.selectedIndex;
    document.forms.prof_annu.langue_level.value = document.forms.prof_annu.langue_level_sel_add.options[selectid_level].value;
    document.forms.prof_annu.langue_op.value = "ajouter";
    document.forms.prof_annu.submit();
  } // function langue_add()

  function langue_del( lid )
  {
    document.forms.prof_annu.langue_id.value = lid;
    document.forms.prof_annu.langue_op.value = "retirer";
    document.forms.prof_annu.submit();
  } // function langue_del( id )

  function comppros_add()
  {
    var selectid = document.forms.prof_annu.comppros_sel_add.selectedIndex;
    document.forms.prof_annu.comppros_id.value = document.forms.prof_annu.comppros_sel_add.options[selectid].value;
    var selectid_level = document.forms.prof_annu.comppros_level_sel_add.selectedIndex;
    document.forms.prof_annu.comppros_level.value = document.forms.prof_annu.comppros_level_sel_add.options[selectid_level].value;
    document.forms.prof_annu.comppros_op.value = "ajouter";
    document.forms.prof_annu.submit();
  } // function langue_add()

  function comppros_del( cid )
  {
    document.forms.prof_annu.comppros_id.value = cid;
    document.forms.prof_annu.comppros_op.value = "retirer";
    document.forms.prof_annu.submit();
  } // function comppros_del( id )
  //]]>
</script>
{/literal}

<div class="blocunite_tab">
  <table class="bicol"cellspacing="0" cellpadding="0" 
    summary="Profil: Compétences professionnelles">
    <tr>
      <th colspan="3">
        Compétences professionnelles
        <input type="hidden" value="" name="comppros_op" />
        <input type="hidden" value="" name="comppros_id" />
        <input type="hidden" value="" name="comppros_level" />
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
    <tr class="impair">
      <td class="colg">
        <span class="titre">Domaine</span>
      </td>
      <td class="colm">
        <span class="titre">Niveau</span>
      </td>
      <td class="cold" style="width:15%">
        &nbsp;
      </td>
    </tr>
    {section name=comp loop=$nb_cpro+1 start=1}
    {assign var='i' value=$smarty.section.comp.index}
    {if $i%2}
    <tr class="pair">
      {else}
      <tr class="impair">
        {/if}
        <td class="colg">
          <span class="valeur">{$cpro_name.$i}</span>
        </td>
        <td class="colm">
          <span class="valeur">&nbsp;&nbsp;{$cpro_level.$i}</span>
        </td>
        <td class="cold">
          <span class="lien"><a href="javascript:comppros_del('{$cpro_id.$i}');">retirer</a></span>
        </td>
      </tr>
      {/section}
      {if $nb_cpro < $nb_cpro_max}
      {if $i%2}
      <tr class="pair">
        {else}
        <tr class="impair">
          {/if}
          <td class="colg">
            <select name="comppros_sel_add">
              {select_competence competence=""}
            </select>
          </td>
          <td class="colm">
            <select name="comppros_level_sel_add">
              {select_competence_level level=""}
            </select>
          </td>
          <td class="cold">
            <span class="lien"><a href="javascript:comppros_add();">ajouter</a></span>
          </td>
        </tr>
        {/if}   
      </table>
    </div>


    <div class="blocunite">
      <table class="bicol" cellspacing="0" cellpadding="0" 
        summary="Profil: Compétences linguistiques">
        <tr>
          <th colspan="3">
            Compétences linguistiques
            <input type="hidden" value="" name="langue_op" />
            <input type="hidden" value="" name="langue_id" />
            <input type="hidden" value="" name="langue_level" />
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
        <tr class="impair">
          <td class="colg">
            <span class="titre">Langue</span>
          </td>
          <td class="colm">
            <span class="titre">Niveau</span>
          </td>
          <td class="cold" style="width:15%">
            <span class="lien"><a href="javascript:x()" onclick="popWin('aide.php#niveau_langue','remplissage','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=500')">Quel niveau ?</a></span>
          </td>
        </tr>
        {section name=lg loop=$nb_lg+1 start=1}
        {assign var='i' value=$smarty.section.lg.index}
        {if $i%2}
        <tr class="pair">
          {else}
          <tr class="impair">
            {/if}
            <td class="colg">
              <span class="valeur">{$langue_name.$i}</span>
            </td>
            <td class="colm">
              <span class="valeur">&nbsp;&nbsp;{if $langue_level.$i == 0}-{else}{$langue_level.$i}{/if}</span>
            </td>
            <td class="cold">
              <span class="lien"><a href="javascript:langue_del('{$langue_id.$i}');">retirer</a></span>
            </td>
          </tr>
          {/section}
          {if $nb_lg < $nb_lg_max}
          {if $i%2}
          <tr class="pair">
            {else}
            <tr class="impair">
              {/if}
              <td class="colg">
                <select name="langue_sel_add">
                  {select_langue langue=""}
                </select>
              </td>
              <td class="colm">
                <select name="langue_level_sel_add">
                  {select_langue_level level=0}
                </select>
              </td>
              <td class="cold">
                <span class="lien"><a href="javascript:langue_add();">ajouter</a></span>
              </td>
            </tr>
            {/if}
          </table>
        </div>

{* vim:set et sw=2 sts=2 sws=2: *}
