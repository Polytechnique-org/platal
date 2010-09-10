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

<script type="text/javascript" src="javascript/jquery.jstree.js"></script>
{assign var=jobid value="job_"|cat:$i}
{assign var=jobpref value="jobs[`$i`]"}
{assign var=sector_text value="sector_text_"|cat:$i}
{assign var=sector value="sector_"|cat:$i}
{assign var=entreprise value="entreprise_"|cat:$i}
<div id="{$jobid}">
  <input type="hidden" name="{$jobpref}[removed]" value="0" />
  <input type="hidden" name="{$jobpref}[new]" value="{if $new}1{else}0{/if}" />
  <input type="hidden" name="{$jobpref}[id]" value="{$i}" />
  <input type="hidden" name="{$jobpref}[jobid]" value="{$job.jobid}" />
  <table id="{$jobid}_grayed" class="bicol" style="display: none; margin-bottom: 1em">
    <tr>
      <th class="grayed">
        <div style="float: right">
          <a href="javascript:restoreJob('{$jobid}','{$jobpref}')">{icon name=arrow_refresh title="Restaure l'emploi"}</a>
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
        <input type="text" class="enterpriseName{if $job.name_error} error{/if}" size="35" maxlength="100"
               name="{$jobpref}[name]" value="{$job.name}" />
        {/if}
        <a href="javascript:removeJob('{$jobid}','{$jobpref}')">
          {icon name=cross title="Supprimer cet emploi"}
        </a>
      </th>
    </tr>
    {if !$job.tmp_name && !$job.name}
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
               name="{$jobpref}[hq_acronym]" value="{$job.hq_acronym}" />
      </td>
    </tr>
    <tr class="{$entreprise}" style="display: none">
      <td class="titre">Page web</td>
      <td>
        <input type="text" size="35" maxlength="255" {if $job.hq_url}class="error"{/if}
               name="{$jobpref}[hq_url]" value="{$job.hq_url}" />
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
            {include file="geoloc/form.address.tpl" prefname="`$jobpref`[hq_address]"
                     prefid="`$jobid`_address" address=$job.hq_address}
          </div>
        </div>
      </td>
    </tr>
    <tr class="{$entreprise}" style="display: none">
      <td class="titre">Téléphone</td>
      <td>
        <input type="text" maxlength="28" {if $job.hq_tel_error}class="error"{/if}
               name="{$jobpref}[hq_fixed]" value="{$job.hq_fixed}" />
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
      <td class="titre">Mots-clefs</td>
      <td class="job_terms">
        <input type="text" class="term_search" size="35"/>
        <a href="javascript:toggleJobTermsTree({$i})">{icon name="table" title="Tous les mots-clefs"}</a>
        <script type="text/javascript">
        /* <![CDATA[ */
        $(function() {ldelim}
          {foreach from=$job.terms item=term}
          addJobTerm("{$i}", "{$term.jtid}", "{$term.full_name|replace:'"':'\\"'}");
          {/foreach}
          $('#job_{$i} .term_search').autocomplete(platal_baseurl + 'profile/jobterms',
            {ldelim}
              "formatItem" : displayJobTerm,
              "extraParams" : {ldelim} "jobid" : "{$i}" {rdelim},
              "width" : $('#job_{$i} .term_search').width()*2,
              "onItemSelect" : selectJobTerm,
              "matchSubset" : false
            {rdelim});
        {rdelim});
        /* ]]> */
        </script>
      </td>
    </tr>
    <tr class="pair">
      <td colspan="2" class="term_tree">
      </td>
    </tr>
    <tr class="pair {$sector}" style="display: none">
      <td class="titre" rowspan="4">Secteur&nbsp;d'activité</td>
      <td>
        <select name="{$jobpref}[sector]" onchange="updateJobSector({$i}, ''); emptyJobSubSector({$i}); emptyJobAlternates({$i});">
          <option value="0">&nbsp;</option>
          {foreach from=$sectors item=item}
          <option value="{$item.id}" {if $item.id eq $job.sector}selected="selected"{/if}>
            {$item.label}
          </option>
          {/foreach}
        </select>
      </td>
    </tr>
    <tr class="pair {$sector}" style="display: none">
      <td id="{$jobid}_subSector">
        <input type="hidden" name="{$jobpref}[subSector]" value="{$job.subSector|default:0}" />
      </td>
    </tr>
    <tr class="pair {$sector}" style="display: none">
      <td id="{$jobid}_subSubSector">
        <input type="hidden" name="{$jobpref}[subSubSector]" value="{$job.subSubSector|default:0}" />
      </td>
    </tr>
    <tr class="pair {$sector}" style="display: none">
      <td id="{$jobid}_alternates">
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">Description</td>
      <td>
        <input type="text" size="35" maxlength="120" {if $job.description_error}class="error"{/if}
           name="{$jobpref}[description]" value="{$job.description}" />
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">Page&nbsp;perso</td>
      <td>
          <input type="text" size="35" maxlength="255" {if $job.w_rul}class="error"{/if}
                 name="{$jobpref}[w_url]" value="{$job.w_url}" />
      </td>
    </tr>
    <tr class="pair">
      <td colspan="2">
        <div style="float: left">
          <div class="titre">Adresse</div>
          <div class="flags">
            {include file="include/flags.radio.tpl" name="`$jobpref`[w_address][pub]" val=$job.w_address.pub}
          </div>
          <div style="margin-top: 20px; clear: both">
            {include file="geoloc/form.address.tpl" prefname="`$jobpref`[w_address]"
                     prefid=$jobid address=$job.w_address}
          </div>
        </div>
      </td>
    </tr>
    {include file="include/emails.combobox.tpl" name=$jobpref|cat:'[w_email]' val=$job.w_email
             class="pair" i=$i error=$job.w_email_error prefix="w_" pub=$job.w_email_pub id=$i}
    <tr class="pair">
      <td colspan="2">
        {foreach from=$job.w_phone key=t item=phone}
          <div id="{"`$jobid`_w_phone_`$t`"}" style="clear: both">
            {include file="profile/phone.tpl" prefname="`$jobpref`[w_phone]" prefid="`$jobid`_w_phone" telid=$t tel=$phone}
          </div>
        {/foreach}
        {if $job.w_phone|@count eq 0}
          <div id="{"`$jobid`_w_phone_0"}" style="clear: both">
            {include file="profile/phone.tpl" prefname="`$jobpref`[w_phone]" prefid="`$jobid`_w_phone" telid=0 tel=0}
          </div>
        {/if}
        <div id="{$jobid}_w_phone_add" class="center" style="clear: both; padding-top: 4px;">
          <a href="javascript:addTel('{$jobid}_w_phone','{$jobpref}[w_phone]')">
            {icon name=add title="Ajouter un numéro de téléphone"} Ajouter un numéro de téléphone
          </a>
        </div>
      </td>
    </tr>
  </table>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
