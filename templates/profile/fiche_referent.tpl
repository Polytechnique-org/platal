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

{literal}
<script type="text/javascript" src="javascript/close_on_esc.js">
</script>
{/literal}

<div id="fiche">
  
  <div class="center">
    <strong>{$prenom} {$nom}</strong><br />
    <span>X{$promo}&nbsp;-&nbsp;</span> <a href="mailto:{$bestalias}@{#globals.mail.domain#}">{$bestalias}@{#globals.mail.domain#}</a>
  </div>

  {if $expertise != '' || $secteurs|count || $pays|count }

  <h2>Informations de référent :</h2>
  
  <div id="fiche_referent">
    {if $expertise}
    <div class="rubrique_referent">
      <em>Expertise : </em><br />
      <span>{$expertise|nl2br}</span>
    </div>
    {/if}
    {if $secteurs|count}
    <div class="rubrique_referent">
      <em>Secteurs :</em><br />
      <ul>
        {foreach from=$secteurs item="secteur" key="i"}
        <li>{$secteur}{if $ss_secteurs.$i != ''} ({$ss_secteurs.$i}){/if}</li>
        {/foreach}
      </ul>
    </div>
    {/if}
    {if $pays|count}
    <div class="rubrique_referent">
      <em>Pays :</em>
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

  {foreach from=$adr_pro item="address" key="i"}
  <h2>{$address.entreprise}</h2>
  {include file="include/emploi.tpl" address=$address}
  {include file="geoloc/address.tpl" address=$address titre="Adresse : "}
  
  <div class="spacer">&nbsp;</div>
  {/foreach}

  {if $cv}
  <h2>Curriculum Vitae : </h2>
  <div>{$cv|nl2br}</div>
  {/if}


  
</div>
{* vim:set et sw=2 sts=2 sws=2: *}
