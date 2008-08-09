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

<form action="marketing/promo/" method="post" onsubmit="this.action += this.promo.value">
  <div class="center">
    <a href="marketing/promo/{$promo-10}" title="-10"><img src="images/icons/resultset_first.gif" alt="[&lt;&lt;]" /></a>
    <a href="marketing/promo/{$promo-1}" title="-1"><img src="images/icons/resultset_previous.gif" alt="[&lt;]" /></a>

    &nbsp;
    Promo&nbsp;:<input type="text" name="promo" value="{$promo}" size="4" maxlength="4" /><input type="submit" value="GO" />
    &nbsp;

    <a href="marketing/promo/{$promo+1}" title="+1"><img src="images/icons/resultset_next.gif" alt="[&gt;]" /></a>
    <a href="marketing/promo/{$promo+10}" title="+10"><img src="images/icons/resultset_last.gif" alt="[&gt;&gt;]" /></a>
  </div>
</form>

<br />

<div class="center">
  <a href="javascript:void(window.open('stats/promos/{$promo}','','width=800,height=600,toolbar=no'))">
    Voir l'évolution des inscriptions de la promotion
  </a>
</div>

<br />

<form action="marketing/promo" method="post">
  <table class="bicol" summary="liste des inscriptions non confirmées">
    <tr>
      <th>Nom</th>
      <th>Dernière adresse connue</th>
      <th>Statut</th>
      <th>&nbsp;</th>
    </tr>
    {iterate from=$nonins item=it}
    <tr class="{cycle values="pair,impair"}">
      <td>{$it.nom} {$it.prenom}</td>
      <td>{if $it.last_known_email}{mailto address=$it.last_known_email}{/if}</td>
      <td class="center">
        {if $it.dern_rel && $it.dern_rel != '0000-00-00'}
        Relance le&nbsp;: {$it.dern_rel}
        {elseif $it.email}
        En cours&nbsp;: {$it.email}
        {else}
        -
        {/if}
      </td>
      <td class="center">
        <a href="marketing/private/{$it.user_id}">{icon name=wrench title="Marketing"}</a>
        <a href="http://www.polytechniciens.com/?page=AX_FICHE_ANCIEN&amp;anc_id={$it.matricule_ax}">{*
          *}{icon name=user_gray title="fiche AX"}</a>
      </td>
    </tr>
    {/iterate}
  </table>
</form>

<p>
{$nonins->total()} Polytechniciens de la promo {$promo} ne sont pas inscrits !
</p>


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
