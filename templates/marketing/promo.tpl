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
 ***************************************************************************}



<table style="margin-left: auto; margin-right: auto">
  <tr>
    <td>
      <form action="?" method="get">
        <div>
          <input type="hidden" name="promo" value="{$promo-10}" />
          <input type="submit" value="&lt;&lt;" />
        </div>
      </form>
    </td>
    <td>
      <form action="?" method="get">
        <div>
          <input type="hidden" name="promo" value="{$promo-1}" />
          <input type="submit" value="&lt;" />
        </div>
      </form>
    </td>
    <td>
      <form action="?" method="get">
        <div>
          Promotion : <input type="text" name="promo" value="{$promo}" size="4" maxlength="4" />
          <input type="submit" value="GO" />
        </div>
      </form>
    </td>
    <td>
      <form action="?" method="get">
        <div>
          <input type="hidden" name="promo" value="{$promo+1}" />
          <input type="submit" value="&gt;" />
        </div>
      </form>
    </td>
    <td>
      <form action="?" method="get">
        <div>
          <input type="hidden" name="promo" value="{$promo+10}" />
          <input type="submit" value="&gt;&gt;" />
        </div>
      </form> 
    </td>
  </tr>
</table>

<br />

<div class="center">
  <a href="javascript:void(window.open('{"stats/stats_promo.php"|url}?promo={$promo}','','width=800,height=600,toolbar=no'))">
    Voir l'évolution des inscriptions de la promotion
  </a>
</div>

<form action="{$smarty.server.PHP_SELF}" method="post">
  <table class="bicol" summary="liste des inscriptions non confirmées">
    <tr>
      <th>Nom</th>
      <th>Dernière adresse connue</th>
      <th>Statut</th>
      <th>AX</th>
      <th>&nbsp;</th>
    </tr>
    {iterate from=$nonins item=it}
    <tr class="{cycle values="pair,impair"}">
      <td>{$it.nom} {$it.prenom}</td>
      <td>{if $it.last_known_email}{mailto address=$it.last_known_email}{/if}</td>
      <td class="center">
        {if $it.dern_rel && $it.dern_rel != '0000-00-00'}
        Relance le: {$it.dern_rel}
        {elseif $it.email}
        En cours : {$it.email}
        {else}
        -
        {/if}
      </td>
      <td class="center">
        <a href="http://www.polytechniciens.com/index.php?page=AX_FICHE_ANCIEN&amp;anc_id={$it.matricule_ax}">{*
          *}<img src="{rel}/images/ax.png" alt='AX' title="fiche AX" /></a>
      </td>
      <td>
        <a href="utilisateurs_marketing.php?xmat={$it.user_id}">Marketing</a>
      </td>
    </tr>
    {/iterate}
  </table>
</form>

<p>
{$nonins->total()} Polytechniciens de la promo {$promo} ne sont pas inscrits !
</p>


{* vim:set et sw=2 sts=2 sws=2: *}
