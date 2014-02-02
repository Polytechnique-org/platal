{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
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

<script type="text/javascript">
  $($.closeOnEsc);
</script>

{assign var=terms value=$profile->getMentoringTerms()}
{assign var=countries value=$profile->getMentoringCountries()}
{assign var=skills value=$profile->getSkills()}
{assign var=languages value=$profile->getLanguages()}
<div id="fiche">
<div id="fiche_referent">
  <div id="fiche_identite">
    <div class="civilite">
      <strong>{$profile->fullName()}</strong><br />
      <span>{$profile->promo()}&nbsp;-&nbsp;</span> <a href="mailto:{$profile->displayEmail()}">{$profile->displayEmail()}</a>
    </div>
  </div>
  <div class="spacer"></div>

  {if $skills|count || $languages|count}
  <div class="part">
    <h2>Compétences&nbsp;:</h2>
    {if $skills|count}
    <div style="float: left">
      <em>Professionnelles&nbsp;:</em><br />
      <ul>
        {foreach from=$skills item="skill"}
        <li>{$skill.text_fr} ({$skill.level})</li>
        {/foreach}
      </ul>
    </div>
    {/if}
    {if $languages|count}
    <div {if $skills|count}style="float: right"{/if}>
      <em>Linguistiques&nbsp;:</em><br />
      <ul>
        {foreach from=$languages item="language"}
        <li>{$language.language} ({$language.level})</li>
        {/foreach}
      </ul>
    </div>
    {/if}
    <div class="spacer">&nbsp;</div>
  </div>
  {/if}

  {if $profile->mentor_expertise != '' || $terms|count || $countries|count }
  <div class="part">
    <h2>Informations de référent&nbsp;:</h2>
    {if $profile->mentor_expertise}
    <div class="rubrique_referent">
      <em>Expertise&nbsp;: </em><br />
      <span>{$profile->mentor_expertise|nl2br}</span>
    </div>
    {/if}
    {if $terms|count}
    <div class="rubrique_referent">
      <em>Mots-clefs&nbsp;:</em><br />
      <ul>
        {foreach from=$terms item="term"}
        <li>{$term->full_name}</li>
        {/foreach}
      </ul>
    </div>
    {/if}
    {if $countries|count}
    <div class="rubrique_referent">
      <em>Pays&nbsp;:</em>
      <ul>
        {foreach from=$pays item="pays_i"}
        <li>{$pays_i}</li>
        {/foreach}
      </ul>
    </div>
    {/if}
    <div class="spacer">&nbsp;</div>
  </div>
  {/if}

  {assign var=jobs value=$profile->getJobs(2)}
  <div class="part">
    {foreach from=$jobs item="job"}
      <h2>{$job->company->name}</h2>
      {include file="include/emploi.tpl" job=$job}
      {if $job->address}
        {include file="geoloc/address.tpl" address=$job->address titre="Adresse&nbsp;: " for=$job->company->name phones=$job->phones pos="left"}
      {elseif $job->phones}
        {display_phones tels=$job->phones}
      {/if}
      <div class="spacer">&nbsp;</div>
    {/foreach}
  </div>

  {if $profile->cv}
  <div class="part">
    <h2>Curriculum Vitae&nbsp;: </h2>
    <div style="padding: 0 2ex">{$profile->cv|miniwiki:title|smarty:nodefaults}</div>
  </div>
  {/if}


  <div class="spacer"></div>
</div>
</div>
{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
