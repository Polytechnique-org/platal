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

{javascript name=jquery.jstree}
{javascript name=jobtermstree}
{foreach from=$jobs item=job key=i}
{include file="profile/jobs.job.tpl" i=$i job=$job new=false}
{/foreach}
{if $jobs|@count eq 0}
{include file="profile/jobs.job.tpl" i=0 job=0 new=true}
{/if}

<div id="add_job" class="center">
  <a href="javascript:addJob()">
    {icon name=add title="Ajouter un emploi"} Ajouter un emploi
  </a>
  <br/><br/>
</div>

<table class="bicol" style="margin-bottom: 1em" summary="Corps">
  <tr>
    <th colspan="2">
      <div class="flags" style="float: left; text-align: left">
        {include file="include/flags.radio.tpl" name="corps[pub]" val=$corps.pub}
      </div>
      Corps
    </th>
  </tr>
  <tr>
    <td class="titre">Corps d'origine</td>
    <td>
    {if $isMe}
      {$corps.originalText}
      <input type="hidden" name="corps[original]" value="{$corps.original}" />
      <input type="hidden" name="corps[originalText]" value="{$corps.originalText}" />
    {else}
      <select name="corps[original]">
        {foreach from=$original_corps item=o_corps}
        <option value="{$o_corps.id}" {if $o_corps.id eq $corps.original}selected="selected"{/if}>{$o_corps.name}</option>
        {/foreach}
      </select>
      <input type="hidden" name="corps[originalText]" value="{$corps.originalText}" />
    {/if}
    </td>
  </tr>
  <tr>
    <td class="titre">Corps actuel</td>
    <td>
      <select name="corps[current]">
        {foreach from=$current_corps item=c_corps}
        <option value="{$c_corps.id}" {if $c_corps.id eq $corps.current}selected="selected"{/if}>{$c_corps.name}</option>
        {/foreach}
      </select>
    </td>
  </tr>
  <tr>
    <td class="titre">Grade</td>
    <td>
      <select name="corps[rank]">
        {foreach from=$corps_rank item=rank}
        <option value="{$rank.id}" {if $rank.id eq $corps.rank}selected="selected"{/if}>{$rank.name}</option>
        {/foreach}
      </select>
    </td>
  </tr>
</table>

{if $smarty.session.user->checkPerms('directory_private')}
<table class="bicol" summary="CV" style="margin-top: 1.5em">
  <tr>
    <th>
      <div class="flags" style="float: left">
        <input type="checkbox" name="accesCV" checked="checked" disabled="disabled" />
        {icon name="flag_red" title="privé"}
      </div>
      Curriculum vitae
    </th>
  </tr>
  <tr>
    <td>
      <div style="float: left; width: 25%">
        <div class="smaller" style="margin-top: 40px">
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
        <textarea name="cv" {if $errors.cv}class="error"{/if} id="cv" rows="15" cols="55">{$cv}</textarea>
      </div>
    </td>
  </tr>
</table>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
