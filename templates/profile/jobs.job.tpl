{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2009 Polytechnique.org                             *}
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
{assign var=sector_text value="sector_text_"|cat:$i}
{assign var=sector value="sector_"|cat:$i}
{assign var=entreprise value="entreprise_"|cat:$i}
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
        {if $job.tmp_name}{$job.tmp_name} <small>(en cours de validation)</small>{else}
        <input type="text" class="enterprise_name {if $job.name_error}error{/if}" size="35" maxlength="100"
               name="{$jobpref}[name]" value="{$job.name}" />
        {/if}
        <a href="javascript:removeJob('{$jobid}', '{$jobpref}')">
          {icon name=cross title="Supprimer cet emploi"}
        </a>
      </th>
    </tr>
    {if !$job.tmp_name}
    <tr class="{$entreprise}">
      <td class="center" colspan="2">
        <small>Si ton entreprise ne figure pas dans la liste,
        <a href="javascript:addEntreprise({$i})">clique ici</a> et complète les informations la concernant.</small>
      </td>
    </tr>
    {/if}
    <tr class="{$entreprise}" style="display: none">
      <td class="titre">Acronyme</td>
      <td>
        <input type="text" size="35" maxlength="255" {if $job.acronym_error}class="error"{/if}
               name="{$jobpref}[acronym]" value="{$job.acronym}" />
      </td>
    </tr>
    <tr class="{$entreprise}" style="display: none">
      <td class="titre">Page web</td>
      <td>
        <input type="text" size="35" maxlength="255" {if $job.hq_web_error}class="error"{/if}
               name="{$jobpref}[hq_web]" value="{$job.hq_web}" />
      </td>
    </tr>
    <tr class="{$entreprise}" style="display: none">
      <td class="titre">Email de contact</td>
      <td>
        <input type="text" maxlength="60" {if $job.hq_email_error}class="error"{/if}
               name="{$jobpref}[hq_email]" value="{$job.hq_email}" />
      </td>
    </tr>
    <tr class="{$entreprise}" style="display: none">
      <td colspan="2">
        <div style="float: left">
          <div class="titre">Adresse du siège</div>
          <div style="margin-top: 20px; clear: both">
            {include file="geoloc/form.address.tpl" name="`$jobpref`[hq_adr]" id="`$jobid`_adr" adr=$job.hq_adr}
          </div>
        </div>
      </td>
    </tr>
    <tr class="{$entreprise}" style="display: none">
      <td class="titre">Téléphone</td>
      <td>
        <input type="text" maxlength="28" {if $job.hq_tel_error}class="error"{/if}
               name="{$jobpref}[hq_tel]" value="{$job.hq_tel}" />
      </td>
    </tr>
    <tr class="{$entreprise}" style="display: none">
      <td class="titre">Fax</td>
      <td>
        <input type="text" maxlength="28" {if $job.hq_fax_error}class="error"{/if}
               name="{$jobpref}[hq_fax]" value="{$job.hq_fax}" />
      </td>
    </tr>

    <tr class="pair">
      <td colspan="2" class="center" style="font-style: italic">Ta place dans l'entreprise</td>
    </tr>
    <tr class="pair {$sector_text}">
      <td class="titre">Secteur d'activité</td>
      <td>
        <input type="text" class="sector_name {if $job.sector_error}error{/if}" size="35" maxlength="100"
               name="{$jobpref}[sss_secteur_name]" value="{$job.sss_secteur_name}" />
        <a href="javascript:displayAllSector({$i})">{icon name="table" title="Tous les secteurs"}</a>
      </td>
    </tr>
    <tr class="pair {$sector}" style="display: none">
      <td class="titre" rowspan="3">Secteur&nbsp;d'activité</td>
      <td>
        <select name="{$jobpref}[secteur]" onchange="updateJobSector({$i}, '')">
          <option value="">&nbsp;</option>
          {foreach from=$secteurs item=secteur}
          <option value="{$secteur.id}" {if $secteur.id eq $job.secteur}selected="selected"{/if}>
            {$secteur.label}
          </option>
          {/foreach}
        </select>
      </td>
    </tr>
    <tr class="pair {$sector}" style="display: none">
      <td id="{$jobid}_ss_secteur">
        <input type="hidden" name="{$jobpref}[ss_secteur]" value="{$job.ss_secteur|default:'-1'}" />
      </td>
    </tr>
    <tr class="pair {$sector}" style="display: none">
      <td id="{$jobid}_sss_secteur">
        <input type="hidden" name="{$jobpref}[sss_secteur]" value="{$job.sss_secteur|default:'-1'}" />
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">Fonction&nbsp;occupée</td>
      <td>
        <select name="{$jobpref}[fonction]">
          <option value="">&nbsp;</option>
          {assign var=ingroup value=false}
          {foreach from=$fonctions item=fonct}
          {if $fonct.title}
            {if $ingroup}</optgroup>{/if}
            <optgroup label="{$fonct.fonction_fr}">
            {assign var=ingroup value=true}
          {/if}
          <option value="{$fonct.id}" {if $fonct.id eq $job.fonction}selected="selected"{/if}>
            {$fonct.fonction_fr}
          </option>
          {/foreach}
          {if $ingroup}</optgroup>{/if}
        </select>
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">Description</td>
      <td>
        <input type="text" size="35" maxlength="120" {if $job.description_error}class="error"{/if}
           name="{$jobpref}[description]" value="{$job.description}" /><br /><br />
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">Page&nbsp;perso</td>
      <td>
          <input type="text" size="35" maxlength="255" {if $job.w_web_error}class="error"{/if}
                 name="{$jobpref}[w_web]" value="{$job.w_web}" />
      </td>
    </tr>
    <tr class="pair">
      <td colspan="2">
        <div style="float: left">
          <div class="titre">Adresse</div>
          <div class="flags">
            {include file="include/flags.radio.tpl" name="`$jobpref`[w_adr][pub]" val=$job.w_adr.pub}
          </div>
          <div style="margin-top: 20px; clear: both">
            {include file="geoloc/form.address.tpl" name="`$jobpref`[hq_adr]" id="`$jobid`_adr" adr=$job.hq_adr}
          </div>
        </div>
      </td>
    </tr>
    {include file="include/emails.combobox.tpl" name=$jobpref|cat:'[w_email]' val=$job.w_email
             class="pair" i=$i error=$job.w_email_error prefix="w_" pub=$job.w_email_pub id=$i}
    <tr class="pair">
      <td colspan="2">
        {foreach from=$job.w_tel key=t item=tel}
          <div id="{"`$jobid`_w_tel_`$t`"}" style="clear: both">
            {include file="profile/phone.tpl" prefname="`$jobpref`[w_tel]" prefid="`$jobid`_w_tel" telid=$t tel=$tel}
          </div>
        {/foreach}
        {if $job.w_tel|@count eq 0}
          <div id="{"`$jobid`_w_tel_0"}" style="clear: both">
            {include file="profile/phone.tpl" prefname="`$jobpref`[w_tel]" prefid="`$jobid`_w_tel" telid=0 tel=0}
          </div>
        {/if}
        <div id="{$jobid}_w_tel_add" class="center" style="clear: both; padding-top: 4px;">
          <a href="javascript:addTel('{$jobid}_w_tel', '{$jobpref}[w_tel]')">
            {icon name=add title="Ajouter un numéro de téléphone"} Ajouter un numéro de téléphone
          </a>
        </div>
      </td>
    </tr>
  </table>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
