{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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
{assign var=jobid value="jobs_"|cat:$i}
{assign var=jobpref value="jobs[`$i`]"}
{assign var=sector_text value="sector_text_"|cat:$i}
{assign var=sector value="sector_"|cat:$i}
{assign var=entreprise value="entreprise_"|cat:$i}
{if $isMe || hasPerm('admin')}
  {assign var=hiddenjob value=false}
  {assign var=hiddenaddr value=false}
  {assign var=hiddenemail value=false}
{else}
  {if hasPerm('directory_hidden') || ( ($job.pub neq 'hidden') && ($job.pub neq 'private')) || $new}
    {assign var=hiddenjob value=false}
  {elseif hasPerm('directory_private') && ($job.pub neq 'hidden')}
    {assign var=hiddenjob value=false}
  {else}
    {assign var=hiddenjob value=true}
  {/if}
  {if hasPerm('directory_hidden') || ( ($job.w_address.pub neq 'hidden') && ($job.w_address.pub neq 'private')) || empty($job.w_address.text|smarty:nodefaults)}
    {assign var=hiddenaddr value=false}
  {elseif hasPerm('directory_private') && ($job.w_address.pub neq 'hidden')}
    {assign var=hiddenaddr value=false}
  {else}
    {assign var=hiddenaddr value=true}
  {/if}
  {if hasPerm('directory_hidden') || ( ($job.w_email_pub neq 'hidden') && ($job.w_email_pub neq 'private')) || empty($job.w_email|smarty:nodefaults)}
    {assign var=hiddenemail value=false}
  {elseif hasPerm('directory_private') && ($job.w_email_pub neq 'hidden')}
    {assign var=hiddenemail value=false}
  {else}
    {assign var=hiddenemail value=true}
  {/if}
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
          {include file="include/flags.radio.tpl" name="`$jobpref`[pub]" val=$job.pub disabled=$hiddenjob
                   mainField='jobs' mainId=$i subField='w_address,w_email,w_phone' subId=-1}
        </div>
        Entreprise n°{$i+1}&nbsp;:
        {if $hiddenjob}
        (masquée)
        {if !t($job.tmp_name)}
        <input type="hidden" name="{$jobpref}[name]" value="{$job.name}" />
        {/if}
        {else}
        {if t($job.tmp_name)}{$job.tmp_name} <small>(en cours de validation)</small>{else}
        <input type="text" class="enterprise_name{if t($job.name_error)} error{/if}" size="35" maxlength="100"
               name="{$jobpref}[name]" value="{$job.name}" />
        {/if}
        {/if}
        <a href="javascript:removeJob('{$jobid}','{$jobpref}')">
          {icon name=cross title="Supprimer cet emploi"}
        </a>
      </th>
    </tr>
    {if !t($job.tmp_name)}{if !t($job.name)}
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
        <input type="text" maxlength="255" name="{$jobpref}[hq_email]" />
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
    {/if}{/if}

    <tr class="pair" {if $hiddenjob}style="display: none"{/if}>
      <td colspan="2" class="center" style="font-style: italic">Place dans l'entreprise</td>
    </tr>
    <tr class="pair" id="{$sector_text}" {if $hiddenjob}style="display: none"{/if}>
      <td class="titre">Mots-clefs</td>
      <td class="jobs_terms">
        <input type="text" class="term_search" size="35"/>
        <a href="javascript:toggleJobTermsTree({$i})">{icon name="table" title="Tous les mots-clefs"}</a>
        <script type="text/javascript">
        /* <![CDATA[ */
        $(function() {ldelim}
          {foreach from=$job.terms item=term}
          addJobTerm("{$i}", "{$term.jtid}", "{$term.full_name|replace:'"':'\\"'}");
          {/foreach}
          $('#jobs_{$i} .term_search').autocomplete(
            {ldelim}
              source: $.plURL('profile/jobterms'),
              select: function(event, ui) {ldelim}
                selectJobTerm(ui.item.id, ui.item.value, {$i});
              {rdelim},
              change: function(event, ui) {ldelim}
                $(this).val('');
              {rdelim}
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
    <tr class="pair" id="term_tree_comment" style="display: none">
      <td colspan="2" class="center"><small>La catégorie « Emplois » donne une liste de métiers, « Secteurs d'activité » décrit des secteurs.</small></td>
    </tr>
    <tr class="pair" {if $hiddenjob}style="display: none"{/if}>
      <td class="titre">Description</td>
      <td>
        <input type="text" size="35" maxlength="120" {if t($job.description_error)}class="error"{/if}
           name="{$jobpref}[description]" value="{$job.description}" />
      </td>
    </tr>
    <tr class="pair" {if $hiddenjob}style="display: none"{/if}>
      <td class="titre">Page&nbsp;perso</td>
      <td>
          <input type="text" size="35" maxlength="255" {if t($job.w_url_error)}class="error"{/if}
                 name="{$jobpref}[w_url]" value="{$job.w_url}" />
      </td>
    </tr>
    <tr class="pair" {if $hiddenjob}style="display: none"{/if}>
      <td class="titre">Année&nbsp;d'entrée</td>
      <td>
          <input type="text" size="4" maxlength="4" {if t($job.w_entry_year_error)}class="error"{/if}
                 name="{$jobpref}[w_entry_year]" value="{$job.w_entry_year}" />
          <small>(avec 4 chiffres, par exemple 1983)</small>
      </td>
    </tr>
    <tr id="{$jobid}_w_address" class="pair" {if $hiddenjob || $hiddenaddr}style="display: none"{/if}>
      <td class="titre">Adresse</td>
      <td class="flags">
        {include file="include/flags.radio.tpl" name="`$jobpref`[w_address][pub]" val=$job.w_address.pub
                 subField='w_address' mainField='jobs' mainId=$i subId=''}
      </td>
    </tr>
    {include file="geoloc/form.address.tpl" prefname="`$jobpref`[w_address]"
                     prefid=$jobid address=$job.w_address class="pair" hiddenaddr=$hiddenaddr}
    <tr class="pair" {if $hiddenjob || $hiddenaddr}style="display: none"{/if}>
      <td colspan="2">
        <label>
          <input type="checkbox" name="{$jobpref}[w_address][mail]" {if $job.w_address.mail}checked="checked"{/if} />
            on peut {if $isMe}m'{/if}y envoyer du courrier par la poste
        </label>
      </td>
    </tr>
    {if $hiddenaddr}
    <tr class="pair">
      <td class="titre" colspan="2">Adresse (masquée)</td>
    </tr>
    {/if}
    {if $hiddenjob || $hiddenemail}
    <tr class="pair" {if $hiddenjob}style="display: none"{/if}>
      <td class="titre" colspan="2">
        {if $hiddenemail}Email professionnel (masqué){/if}
        <input type="hidden" name="{$jobpref}[w_email]" value="{$job.w_email}" />
        <input type="hidden" name="{$jobpref}[w_email_pub]" value="{$job.w_email_pub}" />
      </td>
    </tr>
    {else}
    {include file="include/emails.combobox.tpl" name=$jobpref|cat:'[w_email]' val=$job.w_email
             class="pair" divId="`$jobid`_w_email" i=$i error=$job.w_email_error prefix="w_" pub=$job.w_email_pub id=$i
             subField='w_email' mainField='jobs' mainId=$i subId=''}
    {/if}
    <tr class="pair" {if $hiddenjob}style="display: none"{/if}>
      <td colspan="2">
        {foreach from=$job.w_phone key=t item=phone}
          <div id="{"`$jobid`_w_phone_`$t`"}" style="clear: both">
            {include file="profile/phone.tpl" prefname="`$jobpref`[w_phone]" prefid="`$jobid`_w_phone" telid=$t tel=$phone
                     subField='w_phone' mainField='jobs' mainId=$i}
          </div>
        {/foreach}
        {if $job.w_phone|@count eq 0}
          <div id="{"`$jobid`_w_phone_0"}" style="clear: both">
            {include file="profile/phone.tpl" prefname="`$jobpref`[w_phone]" prefid="`$jobid`_w_phone" telid=0 tel=0
                     subField='w_phone' mainField='jobs' mainId=$i}
          </div>
        {/if}
        <div id="{$jobid}_w_phone_add" class="center" style="clear: both; padding-top: 4px;">
          <a href="javascript:addTel('{$jobid}_w_phone','{$jobpref}[w_phone]','w_phone','jobs','{$i}')">
            {icon name=add title="Ajouter un numéro de téléphone"} Ajouter un numéro de téléphone
          </a>
        </div>
      </td>
    </tr>
  </table>
</div>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
