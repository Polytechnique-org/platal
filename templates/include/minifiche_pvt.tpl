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
        $Id: minifiche_pvt.tpl,v 1.14 2004-11-13 15:22:06 x2000habouzit Exp $
 ***************************************************************************}


{if !$inscrit}
  {if !$c.dcd}
  <div class='bits'>
    {if $show_action eq ajouter}
    <a href="{"carnet/notifs.php"|url}?add_nonins={$c.user_id}">{*
      *}<img src="{"images"|url}/ajouter.gif" alt="Ajouter à la liste de mes surveillances" title="Ajouter à la liste de mes surveillances" />{*
    *}</a>
    {else}
    <a href="{"carnet/notifs.php"|url}?del_nonins={$c.user_id}">{*
      *}<img src="{"images"|url}/retirer.gif" alt="Retirer de la liste de mes surveillances" title="Retirer de la liste de mes surveillances" />{*
    *}</a>
    {/if}
    {perms level='admin'}
    <a href="javascript:x()" onclick="popWin('http://www.polytechniciens.com/index.php?page=AX_FICHE_ANCIEN&amp;anc_id={$c.matricule_ax}')">{*
      *}<img src="{"images/ax.png"|url}" alt='AX' title="fiche AX" />{*
    *}</a>
    {/perms}
  </div>
  {/if}
  <div class="long"></div>
{else}
  <div class="bits">
    <a href="javascript:x()"
      onclick="popupWin('{"fiche.php"|url}?user={$c.forlife}','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=500')">{*
      *}<img src="{"images/loupe.gif"|url}" alt="Afficher la fiche" title="Afficher la fiche" />{*
    *}</a>
    <a href="vcard.php/{$c.forlife}.vcf?x={$c.forlife}">{*
      *}<img src="{"images/vcard.png"|url}" alt="Afficher la carte de visite" title="Afficher la carte de visite" />{*
    *}</a>
    <a href="{"carnet/mescontacts.php"|url}?action={$show_action}&amp;user={$c.forlife}">{*
      *}<img src="{"images"|url}/{$show_action}.gif" alt="{if $show_action eq "ajouter"}Ajouter à mes{else}Retirer de mes{/if} contacts"
        title="{if $show_action eq "ajouter"}Ajouter à mes{else}Retirer de mes{/if} contacts" />{*
    *}</a>
    {if $c.dcd neq 1}
    {perms level='admin'}
    <a href="{"admin/utilisateurs.php"|url}?login={$c.forlife}">{*
      *}<img src="{"images/admin.png"|url}" alt='admin' title="administrer user" />{*
    *}</a>
    <a href="javascript:x()" onclick="popWin('http://www.polytechniciens.com/index.php?page=AX_FICHE_ANCIEN&amp;anc_id={$c.matricule_ax}')">{*
      *}<img src="{"images/ax.png"|url}" alt='AX' title="fiche AX" />{*
    *}</a>
    {/perms}
    {/if}
  </div>
  <div class="long">
    {if $c.web || $c.mobile || $c.pays || $c.ville || $c.region || $c.entreprise || $c.libre}
    <table cellspacing="0" cellpadding="0">
      {if $c.web}
      <tr>
        <td class="lt">Page web:</td>
        <td class="rt"><a href="{$c.web}">{$c.web}</a></td>
      </tr>
      {/if}
      {if $c.pays || $c.ville || $c.region}
      <tr>
        <td class="lt">Géographie:</td>
        <td class="rt">{implode sep=", " s1=$c.ville s2=$c.region s3=$c.pays}</td>
      </tr>
      {/if}
      {if $c.mobile}
      <tr>
        <td class="lt">Mobile:</td>
        <td class="rt">{$c.mobile}</td>
      </tr>
      {/if}
      {if $c.entreprise}
      <tr>
        <td class="lt">Profession:</td>
        <td class="rt">
          {$c.entreprise} {if $c.secteur}({$c.secteur}){/if}
          {if $c.fonction}<br />{$c.fonction}{/if}
        </td>
      </tr>
      {/if}
      {if $c.libre}
      <tr>
        <td class="lt">Commentaire:</td>
        <td class="rt">{$c.libre|nl2br}</td>
      </tr>
      {/if}
    </table>
    {/if}
  </div>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
