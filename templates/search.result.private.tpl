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
        $Id: search.result.private.tpl,v 1.11 2004-10-12 17:23:02 x2000habouzit Exp $
 ***************************************************************************}

<div class="bits">
  {if $result.inscrit==1}
  <a href="javascript:x()" onclick="popupWin('fiche.php?user={$result.forlife}', 'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=500')">
    <img src="images/loupe.gif" alt="Afficher les détails" />
  </a>
  <a href="vcard.php/{$result.forlife}.vcf?x={$result.forlife}">
    <img src="images/vcard.png" alt="Afficher la carte de visite" />
  </a>
  <a href="mescontacts.php?action={if $result.contact!=""}retirer{else}ajouter{/if}&amp;user={$result.forlife}&amp;mode=normal">
    <img src="images/{if $result.contact!=""}retirer{else}ajouter{/if}.gif" alt="{if $result.contact!=""}Retirer de{else}Ajouter parmi{/if} mes contacts" />
  </a>
  {/if}
  {perms level='admin'}
  <a href="javascript:x()" onclick="popWin('http://www.polytechniciens.com/index.php?page=AX_FICHE_ANCIEN&amp;anc_id={$result.matricule_ax}')">
    AX
  </a>
  {/perms}
  <span class="smaller"><strong>{$result.date|date_format:"%d-%m-%Y"}</strong></span>
</div>
{if $result.inscrit!=1}
{if $result.decede != 1}
<div style="float:right">
  <a href="javascript:x()" onclick="popWin('marketing/public.php?num={$result.matricule*2-100}')">
    clique ici si tu connais son adresse email !
  </a>
</div>
{/if}
{/if}
  <div class="long">
    <table cellspacing="0" cellpadding="0">
      {if $result.nat}
      <tr>
        <td class="lt">Nationalité:</td>
        <td class="rt">{$result.nat}</td>
      </tr>
      {/if}
      {if $result.web}
      <tr>
        <td class="lt">Page web:</td>
        <td class="rt"><a href="{$result.web}">{$result.web}</a></td>
      </tr>
      {/if}
      {if $result.pays || $result.ville || $result.pays}
      <tr>
        <td class="lt">Géographie:</td>
        <td class="rt">{implode sep=", " s1=$result.ville s2=$result.region s3=$result.pays}</td>
      </tr>
      {/if}
      {if $result.entreprise}
      <tr>
        <td class="lt">Profession:</td>
        <td class="rt">
          {$result.entreprise}
          {if $result.secteur}( {$result.secteur} ){/if}
          {if $result.fonction}<br />{$result.fonction} ){/if}
        </td>
      </tr>
      {/if}
    </table>
  </div>

{* vim:set et sw=2 sts=2 sws=2: *}
