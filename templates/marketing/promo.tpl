{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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
    Promo&nbsp;:<input type="text" name="promo" value="{$promo}" size="5" maxlength="12" /><input type="submit" value="GO" />
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
    {foreach from=$nonins item=it}
    <tr class="{cycle values="pair,impair"}">
      <td>{profile user=$it}</td>
      <td>{if $it->lastKnownEmail()}{mailto address=$it->lastKnownEmail()}{/if}</td>
      <td class="center">
        {if $it->pendingRegistrationDate() > $it->lastMarketingDate()}
        En cours&nbsp;: {$it->pendingRegistrationEmail()}
        {elseif $it->lastMarketingDate() && $it->lastMarketingDate() != '0000-00-00'}
        Relance le&nbsp;: {$it->lastMarketingDate()}
        {else}
        -
        {/if}
      </td>
      <td class="center">
        <a href="marketing/private/{$it->id()}">{icon name=wrench title="Marketing"}</a>
        <a href="profile/ax/{$it->login()}">{icon name=user_gray title="fiche AX"}</a>
      </td>
    </tr>
    {/foreach}
  </table>
</form>

<p>
{$nonins|@count} Polytechniciens de la promo {$promo} ne sont pas inscrits&nbsp;!
</p>


{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
