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
        $Id: search.result.private.tpl,v 1.19 2004-10-22 12:05:47 x2000habouzit Exp $
 ***************************************************************************}

<div class="bits">
  {if $result.inscrit==1}
  <a href="javascript:x()" onclick="popupWin('fiche.php?user={$result.forlife}', 'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=500')">
    <img src="images/loupe.gif" alt="Afficher la fiche" title="Afficher la fiche" />
  </a>
  <a href="vcard.php/{$result.forlife}.vcf?x={$result.forlife}">
    <img src="images/vcard.png" alt="Afficher la carte de visite" title="Afficher la carte de visite" />
  </a>
  <a href="mescontacts.php?action={if $result.contact!=""}retirer{else}ajouter{/if}&amp;user={$result.forlife}&amp;mode=normal">
    {if $result.contact!=""}
    <img src="images/retirer.gif" alt="Retirer de mes contacts" title="Retirer de mes contacts" />
    {else}
    <img src="images/ajouter.gif" alt="Ajouter à mes contacts" title="Ajouter à mes contacts" />
    {/if}
  </a>
  {/if}
  {perms level='admin'}
  <a href="{"admin/utilisateurs.php"|url}?login={$result.forlife}">su</a>
  <a href="javascript:x()" onclick="popWin('http://www.polytechniciens.com/index.php?page=AX_FICHE_ANCIEN&amp;anc_id={$result.matricule_ax}')">
    ax
  </a>
  {/perms}
</div>
{if $result.inscrit!=1}
{if $result.decede != 1}
<div style="float:right">
  <a href="javascript:x()" onclick="popWin('marketing/public.php?num={$result.matricule}')">
    clique ici si tu connais son adresse email !
  </a>
</div>
<div class="long"></div>
{/if}
{else}
<div class="long">
  {if $result.nat || $result.web || $result.mobile || $result.pays || $result.ville || $result.region || $result.entreprise}
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
    {if $result.mobile}
    <tr>
      <td class="lt">Mobile:</td>
      <td class="rt">{$result.mobile}</td>
    </tr>
    {/if}
    {if $result.pays || $result.ville || $result.region}
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
        {if $result.fonction}<br />( {$result.fonction} ){/if}
      </td>
    </tr>
    {/if}
  </table>
  {/if}
</div>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
