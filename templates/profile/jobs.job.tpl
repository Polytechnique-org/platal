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
{if !hasPerm('directory_private') && ($job.pub eq 'private') && !$new}
{assign var=hiddenjob value=true}
{else}
{assign var=hiddenjob value=false}
{/if}
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
        Restaurer l'entreprise n°{$i+1}&nbsp;:&nbsp;{if $hiddenjob}(masquée){else}<span id="{$jobid}_grayed_name"></span>{/if}
      </th>
    </tr>
  </table>

  <table id="{$jobid}_cont" class="bicol" summary="Entreprise n°{$i+1}" style="margin-bottom: 1em">
    <tr>
      <th colspan="2" style="text-align: right">
        <div class="flags" style="float: left; text-align: left">
          {include file="include/flags.radio.tpl" name="`$jobpref`[pub]" val=$job.pub disabled=$hiddenjob}
        </div>
        Entreprise n°{$i+1}&nbsp;:
        {if $hiddenjob}
        (masquée)
        {if !$job.tmp_name}
        <input type="hidden" name="{$jobpref}[name]" value="{$job.name}" />
        {/if}
        {else}
        {if $job.tmp_name}{$job.tmp_name} <small>(en cours de validation)</small>{else}
        <input type="text" class="enterpriseName{if $job.name_error} error{/if}" size="35" maxlength="100"
               name="{$jobpref}[name]" value="{$job.name}" />
        {/if}
        {/if}
        <a href="javascript:removeJob('{$jobid}','{$jobpref}')">
          {icon name=cross title="Supprimer cet emploi"}
        </a>
      </th>
    </tr>
    {if !$job.tmp_name && !$job.name}
    <tr class="{$entreprise}" {if $hiddenjob}style="display: none"{/if}>
      <td class="center" colspan="2">
        <small>Si l'entreprise ne figure pas dans la liste,
        <a href="javascript:addEntreprise({$i})">clique ici</a> et complète les informations la concernant.</small>
      </td>
    </tr>
    <tr class="{$entreprise}" style="display: none">
      <td class="titre">Acronyme</td>
      <td>
        <input type="text" size="35" maxlength="255" name="{$jobpref}[hq_acronym]" />
      </td>
    </tr>
    <tr class="{$entreprise}" style="display: none">
      <td class="titre">Page web</td>
      <td>
        <input type="text" size="35" maxlength="255" name="{$jobpref}[hq_url]" />
      </td>
    </tr>
    <tr class="{$entreprise}" style="display: none">
      <td class="titre">Email de contact</td>
      <td>
        <input type="text" maxlength="60" name="{$jobpref}[hq_email]" />
      </td>
    </tr>
    <tr class="{$entreprise}" style="display: none">
      <td class="titre">Adresse du siège</td>
      <td>
        <textarea name="{$jobpref}[hq_address]" cols="30" rows="4"></textarea>
      </td>
    </tr>
    <tr class="{$entreprise}" style="display: none">
      <td class="titre">Téléphone</td>
      <td>
        <input type="text" maxlength="28" name="{$jobpref}[hq_fixed]" />
      </td>
    </tr>
    <tr class="{$entreprise}" style="display: none">
      <td class="titre">Fax</td>
      <td>
        <input type="text" maxlength="28" name="{$jobpref}[hq_fax]" />
      </td>
    </tr>
    {/if}

    <tr class="pair" {if $hiddenjob}style="display: none"{/if}>
      <td colspan="2" class="center" style="font-style: italic">Place dans l'entreprise</td>
    </tr>
    <tr class="pair" id="{$sector_text}" {if $hiddenjob}style="display: none"{/if}>
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
    <tr class="pair" {if $hiddenjob}style="display: none"{/if}>
      <td colspan="2" class="term_tree">
      </td>
    </tr>
    <tr class="pair" {if $hiddenjob}style="display: none"{/if}>
      <td class="titre">Description</td>
      <td>
        <input type="text" size="35" maxlength="120" {if $job.description_error}class="error"{/if}
           name="{$jobpref}[description]" value="{$job.description}" />
      </td>
    </tr>
    <tr class="pair" {if $hiddenjob}style="display: none"{/if}>
      <td class="titre">Page&nbsp;perso</td>
      <td>
          <input type="text" size="35" maxlength="255" {if $job.w_rul}class="error"{/if}
                 name="{$jobpref}[w_url]" value="{$job.w_url}" />
      </td>
    </tr>
    <tr class="pair" {if $hiddenjob}style="display: none"{/if}>
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
    {if $hiddenjob}
    <tr class="pair" {if $hiddenjob}style="display: none"{/if}>
      <td colspan="2">
        <input type="hidden" name="{$jobpref}[w_email]" value="{$job.w_email}" />
        <input type="hidden" name="{$jobpref}[w_email_pub]" value="{$job.w_email_pub}" />
      </td>
    </tr>
    {else}
    {include file="include/emails.combobox.tpl" name=$jobpref|cat:'[w_email]' val=$job.w_email
             class="pair" i=$i error=$job.w_email_error prefix="w_" pub=$job.w_email_pub id=$i}
    {/if}
    <tr class="pair" {if $hiddenjob}style="display: none"{/if}>
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
