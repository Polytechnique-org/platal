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
        $Id: fiche.tpl,v 1.7 2004-09-02 23:25:31 x2000habouzit Exp $
 ***************************************************************************}


{dynamic}
<div class="boite">
  <div class="item" style="text-align:center;padding-left: 20px;padding-right: 20px; max-width: 250px;">
    <strong>{$prenom} {$nom}</strong><br />
    <span>X {$promo}&nbsp;-&nbsp;{$applis|smarty:nodefaults}</span><br />
    <span style="font-size: small;">(Fiche mise à jour le {$date|date_format:"%d/%m/%Y"})</span><br />
    <span>
      <a href="vcard.php/{$forlife}.vcf?x={$user_id}"><img src="images/vcard.png" alt="Afficher la carte de visite" /></a>&nbsp;
      {if !$is_contact}
      <a href="javascript:x()"  onclick="popWin('mescontacts.php?action=ajouter&amp;user={$forlife}')"><img src="images/ajouter.gif" alt="Ajouter parmi mes contacts" /></a>&nbsp;
      {/if}
      <a
        href="sendmail.php?contenu=Tu%20trouveras%20ci-apres%20la%20fiche%20de%20{$prenom}%20{$nom}%20https://www.polytechnique.org/fiche.php?user={$forlife}"  onclick="return popup(this)">
        <img src="images/mail.png" alt="Envoyer l'URL" />
      </a>
    </span>
    <br />
    <a href="mailto:{$forlife}@polytechnique.org">{$forlife}@polytechnique.org</a><br />
    <span><em>Section</em> : {$section}</span><br />
    <span><em>Binet(s)</em> : {$binets}</span><br />
    <span><em>Groupe(s) X</em> : {$groupes}</span><br />
    {if $mobile}<br /><span><em>Mobile</em> : {$mobile}</span><br />{/if}
    {if $web}<br /><span><em>Site Web</em> :</span><br /><span><a href="{$web}" onclick="return popup(this)">{$web}</a></span><br />{/if}
    {if $libre}<br /><span><em>Commentaires</em> :</span><br /><span>{$libre|nl2br}</span>{/if}
  </div>
  <div class="item">
    <img alt="Photo de {$forlife}" src="{$photo_url}" width="{$size_x}" height="{$size_y}" />
  </div>
  <div class="spacer">&nbsp;</div>
</div>

{foreach from=$adr item="address" key="i"}
<div class="boite">
  <div class="titre">{$address.title}</div>
  {if $address.adr1 || $address.pays || $address.ville}
  <div class="item">
    <div class="title">Adresse :</div>
    <div class="value">
      {if $address.adr1}<span>{$address.adr1}</span><br />{/if}
      {if $address.adr2}<span>{$address.adr2}</span><br />{/if}
      {if $address.adr3}<span>{$address.adr3}</span><br />{/if}
      {if $address.ville}<span>{$address.cp} {$address.ville}</span><br />{/if}
      {if $address.pays}
      <span>{$address.pays}{if $address.region} ({$address.region}){/if}</span>
      {/if}
    </div>
  </div>
  {/if}
  <div class="spacer">&nbsp;</div>

  {if $address.tel}
  <div class="item">
    <div class="title">Tél :</div>
    <div class="value">{$address.tel}</div>
  </div>
  {/if}

  {if $address.fax}
  <div class="item">
    <div class="title">Fax :</div>
    <div class="value">{$address.fax}</div>
  </div>
  {/if}
  <div class="spacer">&nbsp;</div>
</div>
{/foreach}

{foreach from=$adr_pro item="address" key="i"}
<div class="boite">
  <div class="titre">Infos professionnelles - Entreprise n°{$i+1}</div>
  {if $address.entreprise}
  <div class="item">
    <div class="title">Entreprise/Organisme :</div>
    <div class="value">{$address.entreprise}</div>
  </div>
  {/if}
  {if $address.secteur}
  <div class="item">
    <div class="title">Secteur :</div>
    <div class="value">{$address.secteur}{if $address.ss_secteur} ({$address.ss_secteur}){/if}</div>
  </div>
  {/if}
  <div class="spacer">&nbsp;</div>

  {if $address.adr1 || $address.pays || $address.ville}
  <div class="item">
    <div class="title">Adresse :</div>
    <div class="value">
      {if $address.adr1}<span>{$address.adr1}</span><br />{/if}
      {if $address.adr2}<span>{$address.adr2}</span><br />{/if}
      {if $address.adr3}<span>{$address.adr3}</span><br />{/if}
      {if $address.ville}<span>{$address.cp} {$address.ville}</span><br />{/if}
      {if $address.pays}
      <span>{$address.pays}{if $address.region} ({$address.region}){/if}</span>
      {/if}
    </div>
  </div>
  {/if}
  <div class="spacer">&nbsp;</div>

  {if $address.fonction}
  <div class="item">
    <div class="title">Fonction :</div>
    <div class="value">{$address.fonction}</div>
  </div>
  {/if}
  {if $address.poste}
  <div class="item">
    <div class="title">Poste :</div>
    <div class="value">{$address.poste}</div>
  </div>
  {/if}
  <div class="spacer">&nbsp;</div>

  {if $address.tel}
  <div class="item">
    <div class="title">Tél :</div>
    <div class="value">{$address.tel}</div>
  </div>
  {/if}

  {if $address.fax}
  <div class="item">
    <div class="title">Fax :</div>
    <div class="value">{$address.fax}</div>
  </div>
  {/if}
  <div class="spacer">&nbsp;</div>
</div>
{/foreach}

{if $cv}
<div class="boite">
  <div class="titre">CV</div>
  <div class="item">{$cv|nl2br}</div>
  <div class="spacer">&nbsp;</div>
</div>
{/if}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
