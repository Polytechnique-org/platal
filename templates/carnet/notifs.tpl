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
        $Id: notifs.tpl,v 1.2 2004-11-04 17:47:24 x2000habouzit Exp $
 ***************************************************************************}

<h1>Notifications automatiques</h1>

<form action="{$smarty.server.PHP_SELF}" method="post">
  <fieldset>
    <legend>Contacts</legend>
    <input type='checkbox' name='' /> Surveiller les changements de fiche de tes contacts<br />
    <input type='checkbox' name='' /> Surveiller les décès parmis tes contacts
  </fieldset>
  
  <fieldset>
    <legend>Surveillance des promos</legend>
    <input type='checkbox' name='' /> Surveiller les inscriptions<br />
    <input type='checkbox' name='' /> Surveiller les décès<br />
  </fieldset>
  
  <fieldset>
    <legend>Surveillance des non-inscrits</legend>
    <input type='checkbox' name='' /> Surveiller les inscriptions<br />
    <input type='checkbox' name='' /> Surveiller les décès<br />  
  </fieldset>
  
  <div class='center'>
    <input type='submit' value='valider' />
  </div>
</form>

<br />
<h1>Promotions à surveiller</h1>

<form action="{$smarty.server.PHP_SELF}" method="post">
  <fieldset>
    <legend>Ajouter une promotion</legend>
    Ajouter une surveillance sur la promotion (YYYY) :
    <input type='text' name='' maxlength='4' size='4' />
    <input type='submit' value='ajouter' />
  </fieldset>
</form>

<table class='tinybicol' cellpadding="0" cellspacing="0">
  <tr>
    <td>
      {if !$promos|@count}
      Tu ne surveilles actuellement aucune promo.<br />
      {elseif $promos|@count}
      Tu surveilles {if $promos|@count eq 1}la promo{else}les promos{/if} :
      {foreach from=$promos item=p}{$p} {/foreach}<br />
      {/if}
    </td>
  </tr>
</table>

<br />
<h1>Surveiller des non inscrits</h1>

<form action="{$smarty.server.PHP_SELF}" method="post">
  <fieldset>
    <legend>Ajouter un non-inscrit</legend>
    Si la personne est en fait inscrite, elle sera ajoutée à tes contacts.<br />
    <input type='text' name='' />
    <input type='submit' value='ajouter' />
    <span class='smaller'>Il faut entrer le "login" (prenom.nom ou prenom.nom.promo).</span>
  </fieldset>
</form>

<table class='tinybicol' cellpadding="0" cellspacing="0">
  <tr>
    <td>
      {if !$nonins|@count}
      Tu ne surveilles actuellement aucun non-inscrit.<br />
      {elseif $promos|@count}
      Tu surveilles {if $promos|@count eq 1}le non-inscrit{else}les non-inscrits{/if} :
      {foreach from=$nonins item=p}
      {$p}<br />
      {/foreach}
      {/if}
    </td>
  </tr>
</table>


{* vim:set et sw=2 sts=2 sws=2: *}
