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


{foreach from=$entreprises item=job key=i}
<div id="{"job_`$i`_cont"}">
{include file="profile/jobs.job.tpl" i=$i job=$job}
</div>
{/foreach}
{if $jobs|@count eq 0}
<div id="job_0_cont">
{include file="profile/jobs.job.tpl" i=0 job=0}
</div>
{/if}

<div id="add_job" class="center">
  <a href="javascript:addJob()">
    {icon name=add title="Ajouter un emploi"} Ajouter un emploi
  </a>
</div>

<table class="bicol" summary="CV">
  <tr>
    <th>
      Informations professionnelles - CV
    </th>
  </tr>
  <tr>
    <td>
      <div style="float: left; width: 25%">
        <div class="titre">Curriculum vitae&nbsp;:</div>
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
