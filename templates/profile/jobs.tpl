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

<script type="text/javascript">//<![CDATA[
{literal}

function removeJob(id, pref)
{
  document.getElementById(id + '_cont').style.display = 'none';
  if (document.forms.prof_annu[pref + '[new]'].value == '0') {
    document.getElementById(id + '_grayed').style.display = '';
    document.getElementById(id + '_grayed_name').innerHTML =
      document.forms.prof_annu[pref + "[name]"].value.replace('<', '&lt;');
  }
  document.forms.prof_annu[pref + "[removed]"].value = "1";
}

function restoreJob(id, pref)
{
  document.getElementById(id + '_cont').style.display = '';
  document.getElementById(id + '_grayed').style.display = 'none';
  document.forms.prof_annu[pref + "[removed]"].value = "0";
}

function updateSecteur(nb, id, pref, sel)
{
  var secteur = document.forms.prof_annu[pref + '[secteur]'].value;
  if (secteur == '') {
    secteur = '-1';
  }
  Ajax.update_html(id + '_ss_secteur', 'profile/ajax/secteur/' +nb + '/' + secteur + '/' + sel);
}

function makeAddJob(id)
{
  return function(data)
         {
           $('#add_job').before(data);
           updateSecteur('job_' + id, 'job[' + id + ']', '');
         };
}

function addJob()
{
  var i = 0;
  while (document.getElementById('job_' + i) != null) {
    ++i;
  }
  $.get(platal_baseurl + 'profile/ajax/job/' + i, makeAddJob(i));
}

{/literal}
//]]></script>

{foreach from=$entreprises item=job key=i}
{include file="profile/jobs.job.tpl" i=$i job=$job new=false}
<script type="text/javascript">updateSecteur({$i}, '{"job_`$i`"}', '{"job[`$i`]"}', '{$job.ss_secteur}');</script>
{/foreach}
{if $jobs|@count eq 0}
{include file="profile/jobs.job.tpl" i=0 job=0 new=true}
<script type="text/javascript">updateSecteur(0, 'job_0', 'job[0]', '-1');</script></script>
{/if}

<div id="add_job" class="center">
  <a href="javascript:addJob()">
    {icon name=add title="Ajouter un emploi"} Ajouter un emploi
  </a>
</div>

<table class="bicol" summary="CV" style="margin-top: 1.5em">
  <tr>
    <th>
      Curriculum vitae
    </th>
  </tr>
  <tr>
    <td>
      <div style="float: left; width: 25%">
        <div class="flags">
          <span class="rouge"><input type="checkbox" name="accesCV" checked="checked" disabled="disabled" /></span>
          <span class="texte">privé</span>
        </div>
        <div class="smaller" style="margin-top: 30px">
          <a href="Xorg/FAQ?display=light#cv" class="popup_800x480">
            {icon name="lightbulb" title="Astuce"}Comment remplir mon CV&nbsp;?
          </a><br />
          <a href="wiki_help" class="popup3">
            {icon name=information title="Syntaxe wiki"} Voir la syntaxe wiki
          </a>
          <div class="center">
            <input type="submit" name="preview" value="Aperçu du CV"
                   onclick="previewWiki('cv',  'cv_preview', true, 'cv_preview'); return false;" />
          </div>
        </div>
      </div>
      <div style="float: right">
        <div id="cv_preview" style="display: none"></div>
        <textarea name="cv" id="cv" rows="15" cols="55">{$cv}</textarea>
      </div>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
