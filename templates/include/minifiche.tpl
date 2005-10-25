{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
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


{if !$c.inscrit || $c.dcd}<div class='grayed'>{/if}
<div class="contact" {if $c.inscrit}{min_auth level='cookie'}title="fiche mise à jour le {$c.date|date_format}"{/min_auth}{/if}>

  <div class="nom">
    {if $c.sexe}&bull;{/if}
    {if !$c.dcd && $c.inscrit}<a href="{"fiche.php"|url}?user={$c.user_id}" class="popup2">{/if}
    {if $c.nom_usage}{$c.nom_usage} {$c.prenom}<br />({$c.nom}){else}{$c.nom} {$c.prenom}{/if}
    {if !$c.dcd && $c.inscrit}</a>{/if}
  </div>

  <div class="appli">
    {if $c.iso3166}
    <img src='{"images/"|url}flags/{$c.iso3166}.gif' alt='{$c.nat}' height='14' title='{$c.nat}' />&nbsp;
    {/if}
    (X {$c.promo}{if $c.app0text}, {applis_fmt type=$c.app0type text=$c.app0text url=$c.app0url}
    {/if}{if $c.app1text}, {applis_fmt type=$c.app1type text=$c.app1text url=$c.app1url}{/if})
    {if $c.dcd}décédé{if $c.sexe}e{/if} le {$c.deces|date_format}{/if}
    {min_auth level="cookie"}
    {if !$c.dcd && !$c.wasinscrit}
    <a href="{rel}/marketing/public.php?num={$c.user_id}" class='popup'>clique ici si tu connais son adresse email !</a>
    {/if}
    {/min_auth}
  </div>

  <div class="bits">
    {min_auth level="cookie"}
    {if !$c.wasinscrit && !$c.dcd}
      {if $show_action eq ajouter}
        <a href="{rel}/carnet/notifs.php?add_nonins={$c.user_id}">{*
        *}<img src="{rel}/images/ajouter.gif" alt="Ajouter à la liste de mes surveillances" title="Ajouter à la liste de mes surveillances" /></a>
      {else}
        <a href="{rel}/carnet/notifs.php?del_nonins={$c.user_id}">{*
        *}<img src="{rel}/images/retirer.gif" alt="Retirer de la liste de mes surveillances" title="Retirer de la liste de mes surveillances" /></a>
      {/if}
    {elseif $c.wasinscrit}
        <a href="{rel}/fiche.php?user={$c.forlife}" class="popup2">{*
        *}<img src="{rel}/images/loupe.gif" alt="Afficher la fiche" title="Afficher la fiche" /></a>
      {if !$c.dcd}
        <a href="{rel}/vcard.php/{$c.forlife}.vcf?x={$c.forlife}">{*
        *}<img src="{rel}/images/vcard.png" alt="Afficher la carte de visite" title="Afficher la carte de visite" /></a>
        <a href="{rel}/carnet/mescontacts.php?action={$show_action}&amp;user={$c.forlife}">{*
        *}<img src="{rel}/images/{$show_action}.gif" alt="{if $show_action eq "ajouter"}Ajouter à mes{else}Retirer de mes{/if} contacts"
            title="{if $show_action eq "ajouter"}Ajouter à mes{else}Retirer de mes{/if} contacts" /></a>
      {/if}
    {/if}
    {/min_auth}

    {perms level='admin'}
      {if !$c.wasinscrit && !$c.dcd}
        <a href="{rel}/marketing/private.php?uid={$c.user_id}">{*
          *}<img src="{rel}/images/admin.png" alt='admin' title="marketter user" /></a>
      {elseif $c.wasinscrit}
        <a href="{rel}/admin/utilisateurs.php?login={$c.forlife}">{*
          *}<img src="{rel}/images/admin.png" alt='admin' title="administrer user" /></a>
      {/if}
      <a href="http://www.polytechniciens.com/index.php?page=AX_FICHE_ANCIEN&amp;anc_id={$c.matricule_ax}">{*
      *}<img src="{rel}/images/ax.png" alt='AX' title="fiche AX" /></a>
    {/perms}
  </div>

  <div class="long">
  {if $c.wasinscrit}
    {if $c.web || $c.mobile || $c.countrytxt || $c.city || $c.region || $c.entreprise || $c.freetext}
    <table cellspacing="0" cellpadding="0">
      {if $c.web}
      <tr>
        <td class="lt">Page web:</td>
        <td class="rt"><a href="{$c.web}">{$c.web}</a></td>
      </tr>
      {/if}
      {if $c.countrytxt || $c.city}
      <tr>
        <td class="lt">Géographie:</td>
        <td class="rt">{$c.city}{if $c.city && $c.countrytxt}, {/if}{$c.countrytxt}</td>
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
      {if $c.freetext}
      <tr>
        <td class="lt">Commentaire:</td>
        <td class="rt">{$c.freetext|nl2br}</td>
      </tr>
      {/if}
    </table>
    {/if}
  {/if}
  </div>

</div>
{if !$c.inscrit || $c.dcd}</div>{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
