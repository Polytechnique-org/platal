{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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
<div class="contact" {if $c.inscrit}{if $smarty.session.auth ge AUTH_COOKIE}title="Fiche mise à jour le {$c.date|date_format}"{/if}{/if}>
  <div class="nom">
    {if $c.sexe}&bull;{/if}
    {if !$c.dcd && $c.inscrit}<a href="profile/{$c.forlife}" class="popup2">{/if}
    {if $c.nom_usage}{$c.nom_usage} {$c.prenom}<br />({$c.nom}){else}{$c.nom} {$c.prenom}{/if}
    {if !$c.dcd && $c.inscrit}</a>{/if}
  </div>
  <div class="autre">
    {if $c.iso3166}
    <img src='images/flags/{$c.iso3166}.gif' alt='{$c.nat}' height='11' title='{$c.nat}' />&nbsp;
    {/if}
    (X {$c.promo})
    {if $c.dcd}décédé{if $c.sexe}e{/if} le {$c.deces|date_format}{/if}
    {if $smarty.session.auth ge AUTH_COOKIE}
    {if !$c.wasinscrit && !$c.dcd}
      {if $show_action eq ajouter}
        <a href="carnet/notifs/add_nonins/{$c.user_id}" target="_top">{*
        *}{icon name=add title="Ajouter à la liste de mes surveillances"}</a>
      {else}
        <a href="carnet/notifs/del_nonins/{$c.user_id}" target="_top">{*
        *}{icon name=cross title="Retirer de la liste de mes surveillances"}</a>
      {/if}
    {elseif $c.wasinscrit && !$c.dcd}
        <a href="vcard/{$c.forlife}.vcf">{*
        *}{icon name=vcard title="Afficher la carte de visite"}</a>
      {if $show_action eq ajouter}
        <a href="carnet/contacts?action={$show_action}&amp;user={$c.forlife}" target="_top">{*
        *}{icon name=add title="Ajouter à mes contacts"}</a>
      {else}
        <a href="carnet/contacts?action={$show_action}&amp;user={$c.forlife}" target="_top">{*
        *}{icon name=cross title="Retirer de mes contacts"}</a>
      {/if}
    {/if}
    {/if}
  </div>
  <div class="long">
  {if $c.wasinscrit}
    {if $c.mobile || $c.countrytxt || $c.city}
    <table cellspacing="0" cellpadding="0">
      {if $c.countrytxt || $c.city}
      <tr>
        <td class="lt">Géographie:</td>
        <td class="rt">{$c.city}{if $c.city && $c.countrytxt}, {/if}{$c.countrytxt}</td>
      </tr>
      {/if}
      {if $c.mobile && !$c.dcd}
      <tr>
        <td class="lt">Mobile:</td>
        <td class="rt">{$c.mobile}</td>
      </tr>
      {/if}
    </table>
    {/if}
  {/if}
  </div>
</div>
{if !$c.inscrit || $c.dcd}</div>{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
