{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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

{javascript name=ajax}
{javascript name=jquery.jstree}
{javascript name=jobtermstree}
<script type="text/javascript">//<![CDATA[

var baseurl = platal_baseurl + "referent/";
{literal}
var Ajax2 = new AjaxEngine();

function setSector(sector)
{
    if (sector == '') {
        document.getElementById('scat').style.display = 'none';
        document.getElementById('country').style.display = 'none';
        document.getElementById('keywords').style.display = 'none';
        document.getElementById('search_referent').disabled = 'disabled';
    } else {
        Ajax.update_html('ssect_chg', baseurl + 'ssect/' + sector);
        Ajax2.update_html('country_chg', baseurl + 'country/' + sector);
        document.getElementById('scat').style.display = '';
        document.getElementById('country').style.display = '';
        document.getElementById('keywords').style.display = '';
        document.getElementById('search_referent').disabled = '';
    }
}

function setSSectors()
{
    var sect  = document.getElementById('sect_field').value;
    var ssect = document.getElementById('ssect_field').value;
    Ajax2.update_html('country_chg', baseurl + 'country/' + sect + '/' + ssect);
}

function toggleJobTermsTree()
{
  $('#mentoring_terms').closest('tr').toggle();
  return false;
}

{/literal}
//]]></script>

<form action="{$smarty.server.REQUEST_URI}" method="get">
  <table cellpadding="0" cellspacing="0" summary="Formulaire de recherche de referents" class="bicol">
    <tr class="impair">
      <td class="titre">
        Mot-clef&nbsp;:
      </td>
      <td>
        <input type="text" name="jobterm_text" id="term_search" size="32"/>
        <input type="hidden" name="jobterm" />
        <a id="jobTermsTreeToggle" href="#">{icon name=table title="Tous les mots-clefs"}</a>
      </td>
    </tr>
    <tr class="impair" style="display:none">
      <td colspan="2">
        <div id="mentoring_terms"></div>
      </td>
    </tr>
  </table>
  <div class="center" style="margin-top: 1em;">
    <input id="search_referent" type="submit" value="Chercher" name="Chercher" />
  </div>
</form>

<script type="text/javascript">
{literal}
$(function() {
  createJobTermsTree('#mentoring_terms', 'profile/ajax/tree/jobterms/mentors', 'mentor', 'searchForJobTerm');
  $("#term_search").autocomplete(baseurl + "autocomplete",
  {
    "selectOnly":1,
    "matchSubset":0,
    "width":$("#term_search").width()
  });
  $('#jobTermsTreeToggle').click(toggleJobTermsTree);
});
{/literal}
</script>
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
