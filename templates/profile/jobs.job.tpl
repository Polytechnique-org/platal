{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

{assign var=jobid value="job_`$i`"}
{assign var=jobpref value="jobs[`$i`]"}
<div id="{$jobid}">
  <input type="hidden" name="{$jobpref}[removed]" value="0" />
  <input type="hidden" name="{$jobpref}[new]" value="{if $new}1{else}0{/if}" />
  <table id="{$jobid}_grayed" class="bicol" style="display: none; margin-bottom: 1em">
    <tr>
      <th class="grayed">
        <div style="float: right">
          <a href="javascript:restoreJob('{$jobid}', '{$jobpref}')">{icon name=arrow_refresh title="Restaure l'emploi"}</a>
        </div>
        Restaurer l'entreprise n°{$i+1}&nbsp;:&nbsp;<span id="{$jobid}_grayed_name"></span>
      </th>
    </tr>
  </table>
  <table id="{$jobid}_cont" class="bicol" summary="Entreprise n°{$i+1}" style="margin-bottom: 1em">
    <tr>
      <th colspan="2" style="text-align: right">
        <div class="flags" style="float: left; text-align: left">
          {include file="include/flags.radio.tpl" name="`$jobpref`[pub]" val=$job.pub}
        </div>
        Entreprise n°{$i+1}&nbsp;:
        <input type="text" {if $job.name_error}class="error"{/if} size="35" maxlength="100"
               name="{$jobpref}[name]" value="{$job.name}" />
        <a href="javascript:removeJob('{$jobid}', '{$jobpref}')">
          {icon name=cross title="Supprimer cet emploi"}
        </a>
      </th>
    </tr>
    <tr>
      <td class="titre">Page Web</td>
      <td>
        <input type="text" size="35" maxlength="255" {if $job.web_error}class="error"{/if}
               name="{$jobpref}[web]" value="{$job.web}" />
      </td>
    </tr>
    <tr>
      <td class="titre">Secteur d'activité</td>
      <td>
        <select name="{$jobpref}[secteur]" onchange="updateJobSecteur({$i}, '{$jobid}', '{$jobpref}', ''); return true;">
          <option value="">&nbsp;</option>
          {iterate from=$secteurs item=secteur}
          <option value="{$secteur.id}" {if $secteur.id eq $job.secteur}selected="selected"{/if}>
            {$secteur.label}
          </option>
          {/iterate}
        </select>
      </td>
    </tr>
    <tr>
      <td class="titre">Sous-Secteur d'activité</td>
      <td id="{$jobid}_ss_secteur">
        <input type="hidden" name="{$jobpref}[ss_secteur]" value="{$job.ss_secteur|default:'-1'}" />
      </td> 
    </tr>
    <tr>
      <td class="titre">Poste occupé</td>
      <td>
        <input type="text" size="35" maxlength="120" {if $job.poste_error}class="error"{/if}
               name="{$jobpref}[poste]" value="{$job.poste}" />
      </td>
    </tr>
    <tr>
      <td class="titre">Fonction occupée</td>
      <td>
        <select name="{$jobpref}[fonction]">
          <option value="">&nbsp;</option>
          {assign var=ingroup value=false}
          {iterate from=$fonctions item=fonct}
          {if $fonct.title}
            {if $ingroup}</optgroup>{/if}
            <optgroup label="{$fonct.fonction_fr}">
            {assign var=ingroup value=true}
          {/if}
          <option value="{$fonct.id}" {if $fonct.id eq $job.fonction}selected="selected"{/if}>
            {$fonct.fonction_fr}
          </option>
          {/iterate}
          {if $ingroup}</optgroup>{/if}
        </select>
      </td>
    </tr>
    <tr class="pair">
      <td colspan="2">
        <span class="titre">E-mail professionnel&nbsp;:</span>
        <input type="text" size="30" maxlength="60" {if $job.email_error}class="error"{/if}
               name="{$jobpref}[email]" value="{$job.email}" />
        <span class="flags">
          {include file="include/flags.radio.tpl" name="`$jobpref`[email_pub]" val=$job.mail_pub}
        </span>
      </td>
    </tr>
    <tr class="pair">
      <td colspan="2">
        <div style="float: left">
          <div class="titre">Adresse</div>
          <div class="flags">
            {include file="include/flags.radio.tpl" name="`$jobpref`[adr][pub]" val=$job.adr.pub}
          </div>
          <div style="margin-top: 20px; clear: both">
            {include file="geoloc/form.address.tpl" name="`$jobpref`[adr]" id="`$jobid`_adr" adr=$job.adr}
          </div>
        </div>
        <div style="float: right; width: 50%">
          <div class="titre">Téléphone</div>
          <div class="flags">
            {include file="include/flags.radio.tpl" name="`$jobpref`[tel_pub]" val=$job.tel_pub}
          </div>
          <table style="clear: both">
            <tr>
              <td>Bureau&nbsp;:</td>
              <td>
                <input type="text" size="18" maxlength="18" {if $job.tel_error}class="error"{/if}
                       name="{$jobpref}[tel]" value="{$job.tel}" />
              </td>
            </tr>
            <tr>
              <td>Fax&nbsp;:</td>
              <td>
                <input type="text" size="18" maxlength="18" {if $job.fax_error}class="error"{/if}
                       name="{$jobpref}[fax]" value="{$job.fax}" /></td>
            </tr>
            <tr>
              <td>Mobile&nbsp;:</td>
              <td>
                <input type="text" size="18" maxlength="18" {if $job.mobile_error}class="error"{/if}
                       name="{$jobpref}[mobile]" value="{$job.mobile}" />
              </td>
            </tr>
          </table>
        </div>
      </td>
    </tr>
  </table>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
