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
        $Id: fiche.tpl,v 1.19 2004-10-30 09:09:04 x2000bedo Exp $
 ***************************************************************************}

{literal}
<script type="text/javascript">
function chgMainWinLoc( strPage ) {
  parent.opener.document.location = strPage;
}
</script>
{/literal}
{dynamic}
<div id="fiche">

<div id="fiche_identite">
  <div class="civilite">
    {$prenom} {if $epouse eq ""}{$nom}{else}{$epouse} ({$nom}){/if}&nbsp;
    <a href="vcard.php/{$forlife}.vcf?x={$user_id}">
      <img src="images/vcard.png" alt="Afficher la carte de visite" title="Afficher la carte de visite"/>
    </a>
    {if !$is_contact}
    <a href="javascript:x()"  onclick="chgMainWinLoc('mescontacts.php?action=ajouter&amp;user={$forlife}')">
      <img src="images/ajouter.gif" alt="Ajouter à mes contacts" title="Ajouter à mes contacts" />
    </a>
    {else}
    <a href="javascript:x()"  onclick="chgMainWinLoc('mescontacts.php?action=retirer&amp;user={$forlife}')">
      <img src="images/retirer.gif" alt="Retirer de mes contacts" title="Retirer de mes contacts" />
    </a>
    {/if}
  </div>
  <div class='right' style="font-size: smaller; width:100%">(Fiche mise à jour le {$date|date_format:"%d %B %Y"})</div>
  <div>
    <a href="mailto:{$bestalias}@polytechnique.org">{$bestalias}@polytechnique.org</a><br />
    <a href="mailto:{$forlife}@polytechnique.org">{$forlife}@polytechnique.org</a>
  </div>
  <div><em>{$nationalite}</em> - X {$promo}&nbsp;-&nbsp;Formation&nbsp;: {$applis|smarty:nodefaults}</div>
  {if $mobile}<div><em class="intitule">Mobile : </em>{$mobile}</div>{/if}
  {if $is_referent}
    <div><a href="javascript:x()"  onclick="popWin('fiche_referent.php?user={$forlife}')">Ma fiche référent</a></div>
  {/if}
</div>

<div id="fiche_divers">
    <div><img alt="Photo de {$forlife}" src="{$photo_url}" width="{$size_x}" height="{$size_y}" /></div>
    {if $section}<em class="intitule">Section : </em><span>{$section}</span><br />{/if}
    {if $binets}<em class="intitule">Binet(s) : </em><span>{$binets}</span><br />{/if}
    {if $groupes}<em class="intitule">Groupe(s) X : </em><span>{$groupes|smarty:nodefaults}</span><br />{/if}
    {if $web}<em class="intitule">Site Web : </em><br /><a href="javascript:x()" onclick="popSimple('{$web}')">{$web}</a><br />{/if}
    {if $libre}<br /><em class="intitule">Commentaires : </em><br /><span>{$libre|nl2br}</span>{/if}
</div>
{if $adr|@count > 0}
<div style="clear: left;">&nbsp;</div>
<div class="categorie">Contact : </div>
<hr />

<div id="fiche_adresses">
{foreach from=$adr item="address" key="i"}
  <div class="adresse" {if $adr|@count == 1}style="width: 450px;"{/if}>
    <div class="titre" {if $adr|@count == 1}style="text-align: left;"{/if}>{$address.title}</div>
    {if $address.adr1 || $address.pays || $address.ville}
      <div>
          {if $address.adr1}<span>{$address.adr1}</span><br />{/if}
          {if $address.adr2}<span>{$address.adr2}</span><br />{/if}
          {if $address.adr3}<span>{$address.adr3}</span><br />{/if}
          {if $address.ville}<span>{$address.cp} {$address.ville}</span><br />{/if}
          {if $address.pays}
            <span>{$address.pays}{if $address.region} ({$address.region}){/if}</span>
          {/if}
      </div>
    {/if}

    {if $address.tel}
      <div>
       <em class="small">Tél : </em>
       <span class="value">{$address.tel}</span>
      </div>
    {/if}

    {if $address.fax}
    <div>
      <em class="small">Fax : </em>
      <span class="value">{$address.fax}</span>
    </div>
    {/if}
 </div>
{/foreach}
<div class="spacer">&nbsp;</div>
</div>

{/if}

{if $adr_pro|@count > 0}
<div class="categorie">Informations professionnelles : </div>
<hr />

<div id="fiche_infospro">
{foreach from=$adr_pro item="address" key="i"}
<div class="entreprise">
  {*<div class="titre">Entreprise n°{$i+1}</div>*}
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

{/if}

{if $cv}
<div class="spacer">&nbsp;</div>
<div class="categorie">Curriculum Vitae : </div>
<hr />
<div class="spacer">&nbsp;</div>
<div id="fiche_cv">
  <div>{$cv|nl2br}</div>
</div>
{/if}

</div>
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
