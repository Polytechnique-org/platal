{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: fiche_referent.tpl,v 1.7 2004-10-24 14:41:11 x2000habouzit Exp $
 ***************************************************************************}
 
<div id="fiche">
  {dynamic}

  <div id="fiche_identite">
    <div class="civilite">{$prenom} {$nom}</div>
    <span>X{$promo}&nbsp;-&nbsp;</span>
    <a href="mailto:{$forlife}@polytechnique.org">{$forlife}@polytechnique.org</a>
  </div>

  {**a-t-il bien des infos de referents ? **}
  {if $expertise != '' || ($nb_secteurs > 0)  || ($nb_pays > 0) }

  <div style="clear: left;">&nbsp;</div>
  <div class="categorie">Informations de référent : </div>
  <hr />

  <div id="fiche_referent">
    {if $expertise}
    <div class="rubrique_referent">
      <em>Expertise : </em><br />
      <span>{$expertise|nl2br}</span>
    </div>
    {/if}
    {if $nb_secteurs > 0}
    <div class="rubrique_referent">
      <em>Secteurs :</em><br />
      <div>
        <ul>
          {foreach from=$secteurs item="secteur" key="i"}
          <li>{$secteur}{if $ss_secteurs.$i != ''} ({$ss_secteurs.$i}){/if}</li>
          {/foreach}
        </ul>
      </div>
    </div>
    {/if}
    {if $nb_pays > 0}
    <div class="rubrique_referent">
      <em>Pays :</em>
      <div>
        <ul>
          {foreach from=$pays item="pays_i"}
          <li>{$pays_i}</li>
          {/foreach}
        </ul>
      </div>
    </div>
    {/if}
    <div class="spacer">&nbsp;</div>
  </div>
  {/if}

  <div style="clear: left;">&nbsp;</div>
  <div class="categorie">Informations professionnelles : </div>
  <hr />

  <div id="fiche_infospro">
    {foreach from=$adr_pro item="address" key="i"}
    <div class="entreprise">
      <div class="titre">Entreprise n°{$i+1}</div>
      <div class="details">
        {if $address.entreprise}
        <div>
          <em>Entreprise/Organisme : </em>
          <span>{$address.entreprise}</span>
        </div>
        {/if}
        {if $address.secteur}
        <div>
          <em>Secteur : </em>
          <span>{$address.secteur}{if $address.ss_secteur} ({$address.ss_secteur}){/if}</span>
        </div>
        {/if}

        {if $address.fonction}
        <div>
          <em>Fonction : </em>
          <span>{$address.fonction}</span>
        </div>
        {/if}
        {if $address.poste}
        <div>
          <em>Poste : </em>
          <span>{$address.poste}</span>
        </div>
        {/if}
      </div>

      <div class="adr_pro">
        {if $address.adr1 || $address.pays || $address.ville}
        <em>Adresse : </em><br />
        {if $address.adr1}<span>{$address.adr1}</span><br />{/if}
        {if $address.adr2}<span>{$address.adr2}</span><br />{/if}
        {if $address.adr3}<span>{$address.adr3}</span><br />{/if}
        {if $address.ville}<span>{$address.cp} {$address.ville}</span><br />{/if}
        {if $address.pays}
        <span>{$address.pays}{if $address.region} ({$address.region}){/if}</span>
        {/if}
        {/if}

        {if $address.tel}
        <div>
          <em>Tél : </em>
          <span>{$address.tel}</span>
        </div>
        {/if}

        {if $address.fax}
        <div>
          <em>Fax : </em>
          <span>{$address.fax}</span>
        </div>
        {/if}
      </div>
      <div class="spacer">&nbsp;</div>
    </div>
    {/foreach}
  </div>

  {if $cv}
  <div class="spacer">&nbsp;</div>
  <div class="categorie">Curriculum Vitae : </div>
  <hr />
  <div class="spacer">&nbsp;</div>
  <div id="fiche_cv">
    <div>{$cv|nl2br}</div>
  </div>
  {/if}


  {/dynamic}

</div>
{* vim:set et sw=2 sts=2 sws=2: *}
