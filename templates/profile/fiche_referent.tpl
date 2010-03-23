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

{javascript name=ajax}
{assign var=sectors value=$profile->getMentoringSectors()}
{assign var=countries value=$profile->getMentoringCountries()}
<div id="fiche">
<div id="fiche_referent">
  <div id="fiche_identite">
    <div class="civilite">
      <strong>{$profile->fullName()}</strong><br />
      <span>{$profile->promo()}&nbsp;-&nbsp;</span> <a href="mailto:{$profile->displayEmail()}">{$profile->displayEmail()}</a>
    </div>
  </div>
  <div class="spacer"></div>

  {if $profile->expertise != '' || $sectors|count || $countries|count }
  <div id="part">
    <h2>Informations de référent&nbsp;:</h2>
    {if $profile->expertise}
    <div class="rubrique_referent">
      <em>Expertise&nbsp;: </em><br />
      <span>{$profile->expertise|nl2br}</span>
    </div>
    {/if}
    {if $sectors|count}
    <div class="rubrique_referent">
      <em>Secteurs&nbsp;:</em><br />
      <ul>
        {foreach from=$sectors item="sector" key="i"}
        <li>{$sector.sector}{if $sector.subsector != ''} ({$sector.subsector}){/if}</li>
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
      {if $job->address()}
        {include file="geoloc/address.tpl" address=$job->address titre="Adresse&nbsp;: " for=$job->company->name}
      {/if}
      <div class="spacer">&nbsp;</div>
    {/foreach}
  </div>

  {if $profile->cv}
  <div class="part">
    <h2>Curriculum Vitae&nbsp;: </h2>
    <div style="padding: 0 2ex">{$profile->cv|smarty:nodefaults}</div>
  </div>
  {/if}


  <div class="spacer"></div>
</div>
</div>
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
