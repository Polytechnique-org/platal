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
        $Id: x_inscrit.tpl,v 1.6 2004-08-31 11:25:40 x2000habouzit Exp $
 ***************************************************************************}


<div class="contact">
  <div class="nom">
    {$c.nom} {$c.prenom}
    {if $c.epouse}<br />({$c.epouse} {$c.nom}){/if}
    {if $c.dcd}(décédé){/if}
  </div>
  <div class="appli">
    {strip}
    (
    X{$c.promo}{if $c.app0text},
    {applis_fmt type=$c.app0type text=$c.app0text url=$c.app0url}
    {/if}{if $c.app1text},
    {applis_fmt type=$c.app1type text=$c.app1text url=$c.app1url}
    {/if}
    )
    {/strip}
  </div>
  <div class="bits">
    <a href="javascript:x()" onclick="popWin('fiche.php?user={$c.username}')">
      <img src="images/loupe.gif" alt="Afficher les détails" />
    </a>
    <a href="vcard.php/{$c.username}.vcf?x={$c.username}">
      <img src="images/vcard.png" alt="Afficher la carte de visite" />
    </a>
    <a href="mescontacts.php?action={$show_action}&amp;user={$c.username}">
      <img src="images/{$show_action}.gif" alt="{$show_action} aux/des contacts" />
    </a>
    {perms level='admin'}
    <a href="http://www.polytechniciens.com/index.php?page=AX_FICHE_ANCIEN&amp;anc_id={$c.matricule_ax}" onclick="return popup(this)">AX</a>
    {/perms}
    <span class="smaller"><strong>{$c.date|date_format:"%d-%m-%Y"}</strong></span>
  </div>
  <div class="long">
    <table cellspacing="0" cellpadding="0">
      {if $c.nat}
      <tr>
        <td class="lt">Nationalité:</td>
        <td class="rt">{$c.nat}</td>
      </tr>
      {/if}
      {if $c.web}
      <tr>
        <td class="lt">Page web:</td>
        <td class="rt"><a href="{$c.web}">{$c.web}</a></td>
      </tr>
      {/if}
      {if $c.pays || $c.ville || $c.pays}
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
          {$c.entreprise}
          {if $c.secteur}( {$c.secteur} ){/if}
          {if $c.fonction}<br />{$c.fonction} ){/if}
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
  </div>
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
