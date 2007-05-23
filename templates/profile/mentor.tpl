{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
{*  http://opensource.polytechnique.org/                                  *}
{*                                                                        *}
{*  This program is free software; you can redistribute it and/or modify  *}
{*  it under the terms of the GNU General Public License as published by  *}
{*  the Free Software Foundation; either version 2 of the License, or     *}
{*  (at your option) any later version.                                   *}
{*                                                                        *}
{*  This program is distributed in the hope that it will be useful,       *}
{*  but WITHOUT ANY WARRANTY; without even the implied warranty of        *}
{*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *}
{*  GNU General Public License for more details.                          *}
{*                                                                        *}
{*  You should have received a copy of the GNU General Public License     *}
{*  along with this program; if not, write to the Free Software           *}
{*  Foundation, Inc.,                                                     *}
{*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA               *}
{*                                                                        *}
{**************************************************************************}


{literal}
<script type="text/javascript">
  //<![CDATA[
  function mentor_pays_add()
  {
    var selid = document.forms.prof_annu.mentor_pays_id_new.selectedIndex;
    document.forms.prof_annu.mentor_pays_id.value = document.forms.prof_annu.mentor_pays_id_new.options[selid].value;
    document.forms.prof_annu.mentor_pays_name.value = document.forms.prof_annu.mentor_pays_id_new.options[selid].text;
    document.forms.prof_annu.mentor_pays_op.value = "ajouter";
    document.forms.prof_annu.submit();
  } // function mentor_pays_add()

  function mentor_pays_del( pid )
  {
    document.forms.prof_annu.mentor_pays_id.value = pid;
    document.forms.prof_annu.mentor_pays_op.value = "retirer";
    document.forms.prof_annu.submit();
  } // function mentor_pays_del( pid )

  function mentor_secteur_add()
  {
    var selid_secteur = document.forms.prof_annu.mentor_secteur_id_new.selectedIndex;
    document.forms.prof_annu.mentor_secteur_id.value = document.forms.prof_annu.mentor_secteur_id_new.options[selid_secteur].value;
    document.forms.prof_annu.mentor_secteur_name.value = document.forms.prof_annu.mentor_secteur_id_new.options[selid_secteur].text;
    var selid_ss_secteur = document.forms.prof_annu.mentor_ss_secteur_id_new.selectedIndex;
    document.forms.prof_annu.mentor_ss_secteur_id.value = document.forms.prof_annu.mentor_ss_secteur_id_new.options[selid_ss_secteur].value;
    document.forms.prof_annu.mentor_ss_secteur_name.value = document.forms.prof_annu.mentor_ss_secteur_id_new.options[selid_ss_secteur].text;
    document.forms.prof_annu.mentor_secteur_op.value = "ajouter";
    document.forms.prof_annu.submit();
  } // function mentor_secteur_add()

  function mentor_secteur_del( sid )
  {
    document.forms.prof_annu.mentor_secteur_id.value = sid;
    document.forms.prof_annu.mentor_secteur_op.value = "retirer";
    document.forms.prof_annu.submit();
  } // function mentor_secteur_del( sid )

  //]]>
</script>
{/literal}

<p>
Si tu acceptes que ceux de nos camarades qui,
</p>
<ul>
  <li>encore jeunes, sont en train de bâtir leur projet professionnel,</li>
  <li>ou bien, plus âgés, souhaitent réorienter leur carrière,</li>
</ul>
<p>
te contactent afin de te demander conseil, dans les domaines que tu connais
bien, et pour lesquels tu pourrais les aider, remplis cette rubrique.<br />
Tu peux mentionner ici les domaines de compétences, les expériences 
notamment internationales sur la base desquels tu seras identifiable depuis
<a href="referent/search">la page de recherche d'un conseil professionnel</a>.
</p>

<div class="blocunite_tab">
  <table class="bicol" cellspacing="0" cellpadding="0" summary="Profil: Mentoring">
    <tr>
      <th colspan="3">
        Pays dont tu connais bien la culture professionnelle
        <input type="hidden" value="" name="mentor_pays_op" />
        <input type="hidden" value="00" name="mentor_pays_id" />
        <input type="hidden" value="" name="mentor_pays_name" />
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
              privé
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
    {foreach from=$mentor_pays item=pays key=i}
    <tr class="{cycle values="pair,impair"}">
      <td class="colg">
        <span class="valeur">{$pays}</span>
      </td>
      <td class="colm">
        <span class="valeur">&nbsp;&nbsp;</span>
      </td>
      <td class="cold">
        <span class="lien"><a href="javascript:mentor_pays_del('{$mentor_pid.$i}');">retirer</a></span>
      </td>
    </tr>
    {/foreach}
    {if $can_add_pays}
    <tr class="{cycle values="pair,impair"}">
      <td class="colg">
        <select name="mentor_pays_id_new">
          {geoloc_country country='00'}
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
        <input type="hidden" value="" name="mentor_secteur_op" />
        <input type="hidden" value="" name="mentor_secteur_id" />
        <input type="hidden" value="" name="mentor_secteur_name" />
        <input type="hidden" value="" name="mentor_ss_secteur_id" />
        <input type="hidden" value="" name="mentor_ss_secteur_name" />
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
              privé
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
    {foreach from=$mentor_secteur item=secteur key=i}
    <tr class="{cycle values="pair,impair"}">
      <td class="colg">
        <span class="valeur">{$secteur}</span>
      </td>
      <td class="colm">
        <span class="valeur">{$mentor_ss_secteur.$i}</span>
      </td>
      <td class="cold">
        <span class="lien"><a href="javascript:mentor_secteur_del('{$mentor_sid.$i}');">retirer</a></span>
      </td>
    </tr>
    {/foreach}
    {if $can_add_secteurs}
    <tr class="{cycle values="pair,impair"}">
      <td class="colg">
        <select name="mentor_secteur_id_new" onchange="javascript:submit()">
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
              privé
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
        contact avec toi, en prennent l'initiative. <strong>Il est obligatoire de
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

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
