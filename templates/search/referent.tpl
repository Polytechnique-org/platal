{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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


{if $plset_count}
{include core=plset.tpl}
{else}
{include wiki=Docs.Emploi}
{/if}

<a id="mentors"></a>

<p>
Actuellement, {$mentors_number} mentors et référents se sont déclarés sur {#globals.core.sitename#}.
</p>

{javascript name=jquery.jstree}
{javascript name=jobtermstree}
<script type="text/javascript">//<![CDATA[

var baseurl = $.plURL("referent/");
{literal}

/** Hides or display tree of all job terms */
function toggleJobTermsTree()
{
  $('#mentoring_terms').closest('tr').toggle();
  return false;
}

/** Function called by autocomplete when a term is selected */
function selectJobTerm(li)
{
    if (li.extra[1] < 0) {
        return;
    }
    chooseJobTermInTree(null,li.extra[1],li.selectValue);
}

/** Prepares display for a jobterm in select's dropdown
 * @param row the returned row from ajax : text, nb, id
 */
function displayJobTerm(row)
{
  if (row[1] < 0) {
    return '<em>... précise ta recherche ... <\/em>';
  }
  return row[0]+' ('+row[1]+' camarade'+((row[1] > 1)?'s':'')+')';
}

/** Function called by job terms tree when an item is clicked */
function chooseJobTermInTree(treeid, jtid, full_name)
{
  $('#jobtermText').val(full_name);
  $('#mentoring_terms').closest('tr').hide();
  updateJobTerm(jtid, $('#country_chg select').val());
}

/** Changes job term and proposes the different countries linked */
function updateJobTerm(jtid, country)
{
  $('#jobterm').val(jtid);
  $('#country_chg').closest('tr').show();
  $('#keywords').show();
  $('#country_chg').load($.plURL('search/referent/countries/' + jtid), function(response, status, xhr) {
    if (country) {
      if (status != "error") {
        $('#country_chg select').val(country);
      }
    }
  });
}

/** Function called when validating form */
function validateSearchForm(f)
{
  if (!f.jobterm.value) {
    alert('Il faut choisir un mot clef avant de lancer la recherche.');
    $('#jobtermText').val('').focus();
    return false;
  }
  return true;
}

{/literal}
//]]></script>

<form action="{$smarty.server.REQUEST_URI}" method="get" onsubmit="return validateSearchForm(this)">
  <table cellpadding="0" cellspacing="0" summary="Formulaire de recherche de référents" class="bicol">
    <tr class="impair">
      <td class="titre">
        Mot-clef&nbsp;:
      </td>
      <td>
        <input type="text" name="jobtermText" id="jobtermText" size="32" value="{$smarty.request.jobtermText}"/>
        <input type="hidden" name="jobterm" id="jobterm" value="{$smarty.request.jobterm}"/>
        <a id="jobTermsTreeToggle" href="#">{icon name=table title="Tous les mots-clefs"}</a>
      </td>
    </tr>
    <tr class="impair" style="display:none">
      <td colspan="2">
        <div id="mentoring_terms"></div>
      </td>
    </tr>
    <tr class="pair" style="display:none">
      <td class="titre">
        Pays bien connus du référent&nbsp;:
      </td>
      <td id="country_chg">
      </td>
    </tr>
    <tr class="impair" style="display:none" id="keywords">
      <td class="titre">
        Expertise (recherche texte)&nbsp;:
      </td>
      <td>
        <input type="text" value="{$smarty.request.expertise}" size="30" name="expertise" />
      </td>
    </tr>
  </table>
  <div class="center" style="margin-top: 1em;">
    <input id="search_referent" type="submit" value="Chercher" />
  </div>
</form>

<script type="text/javascript">
{literal}
$(function() {
  createJobTermsTree('#mentoring_terms', 'profile/ajax/tree/jobterms/mentors', 'mentor', 'chooseJobTermInTree');
  $("#jobtermText").autocomplete(baseurl + "autocomplete",
  {
    "selectOnly":1,
    "width":$("#jobtermText").width()*2,
    "onItemSelect" : selectJobTerm,
    "formatItem" : displayJobTerm,
    "matchSubset" : false
  });
  $('#jobTermsTreeToggle').click(toggleJobTermsTree);
{/literal}
  {if $smarty.request.jobterm}
  updateJobTerm("{$smarty.request.jobterm}", "{$smarty.request.country}");
  {/if}
{rdelim});
</script>
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
