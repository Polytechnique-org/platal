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
        $Id: poly.tpl,v 1.5 2004/08/31 19:09:20 x2000habouzit Exp $
 ***************************************************************************}


{literal}
<script type="text/javascript">//<![CDATA[
  /** defgroup user_profile Gestion du profil utilisateur */

  /** ajout d'un binet au profil de l'utilisateur en base de données
  * le binet est déterminé par binet_id qui est sélectionné dans un SELECT
  * @ingroup user_profile
  * @return VOID
  */
  function binet_add()
  {
    var selid = document.forms.prof_annu.binet_sel.selectedIndex;
    document.forms.prof_annu.binet_id.value = document.forms.prof_annu.binet_sel.options[selid].value;
    document.forms.prof_annu.binet_op.value = "ajouter";
    document.forms.prof_annu.submit();
  } // function binet_add()



  /** suppression d'un binet du profil de l'utilisateur en base de données
  * @ingroup user_profile
  * @param id INT id du binet
  * @return VOID
  */
  function binet_del( id )
  {
    document.forms.prof_annu.binet_id.value = id;
    document.forms.prof_annu.binet_op.value = "retirer";
    document.forms.prof_annu.submit();
  } // END function binet_del( id )



  /** ajout d'un groupeX au profil de l'utilisateur en base de données
  * le groupeX est déterminé par groupex_id qui est sélectionné dans un SELECT
  * @ingroup user_profile
  * @return VOID
  */
  function groupex_add()
  {
    var selid = document.forms.prof_annu.groupex_sel.selectedIndex;
    document.forms.prof_annu.groupex_id.value = document.forms.prof_annu.groupex_sel.options[selid].value;
    document.forms.prof_annu.groupex_op.value = "ajouter";
    document.forms.prof_annu.submit();
  } // END function groupex_add()

  /** suppression d'un groupeX du profil de l'utilisateur en base de données
  * @ingroup user_profile
  * @param id INT id du groupeX
  * @return VOID
  */
  function groupex_del( id )
  {
    document.forms.prof_annu.groupex_id.value = id;
    document.forms.prof_annu.groupex_op.value = 'retirer';
    document.forms.prof_annu.submit();
  } // END function groupex_del( id )

  //]]>
</script>
{/literal}
<div class="blocunite_tab">
  <table class="bicol" cellspacing="0" cellpadding="0" 
    summary="Profil: Informations Polytechniciennes">
    <tr>
      <th colspan="3">
        Informations polytechniciennes
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
    <tr class="top">
      <td class="colg">
        <span class="titre">(ex-)Section</span>
      </td>
      <td class="colm">
        <select name="section">
          {select_db_table table="sections" valeur=$section}
        </select>
      </td>
      <td class="cold">
        &nbsp;
      </td>
    </tr>
    <!-- Binets -->
    <tr class="top">
      <td class="colg">
        <span class="titre">(ex-)Binet(s)</span>
      </td>
      {foreach from=$binets item=b}
      <td class="colm">
        <span class="valeur">{$b.text}</span>
      </td>
      <td class="cold">
        <span class="lien">
          <a href="javascript:binet_del({$b.id});">retirer</a>
        </span>
      </td>
    </tr>
    <tr>
      <td class="colg">
        &nbsp;
      </td>
      {/foreach}
      <td class="colm">
        <select name="binet_sel">
          {select_db_table table="binets_def" valeur=0 champ="text" pad='1'}
        </select>
      </td>
      <td class="cold">
        <span class="lien">
          <a href="javascript:binet_add();">ajouter</a>
        </span>
      </td>
    </tr>
    <!-- Groupes X -->
    <tr class="top">
      <td class="colg">
        <span class="titre">Groupe(s) X</span>
      </td>
      {foreach from=$groupesx item=g}
      <td class="colm">
        <span class="valeur">{$g.text}</span>
      </td>
      <td class="cold">
        <span class="lien">
          <a href="javascript:groupex_del({$g.id});">retirer</a>
        </span>
      </td>
    </tr>
    <tr>
      <td class="colg">
        &nbsp;
      </td>
      {/foreach}
      <td class="colm">
        <select name="groupex_sel">
          {select_db_table table="groupesx_def" valeur=0 champ="text" pad='1'}
        </select>
      </td>
      <td class="dcold">
        <span class="lien">
          <a href="javascript:groupex_add();">ajouter</a>
        </span>
      </td>
    </tr>
  </table>
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
