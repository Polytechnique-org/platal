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
        $Id: notifs.tpl,v 1.3 2004-11-04 18:24:01 x2000habouzit Exp $
 ***************************************************************************}

<h1>Notifications automatiques</h1>

<p>Les mails sont hebdomadaires (pour éviter une trop grosse charge du serveur de mails et de ta boite mail).
S'il n'y a rien à te signaler le mail ne t'est pas envoyé.</p>

<p>tu peux ici activer la surveillance de tes contacts, ce qui te permet :</p>
<ul>
  <li>d'être notifié lorsque tes contacts changent leur fiche</li>
  <li>d'être notifié lorsque un de tes contacts décède</li>
  <li>si tu le désires, lorsque tu es notifié du décès d'un de tes camarades, il peut être automatiquement retiré de ta liste de contact.
  (dans cec as ta liste de contact est vidée de tous les camarades qui sont décédés)
  </li>
</ul>

<form action="{$smarty.server.PHP_SELF}" method="post">
  <fieldset>
    <legend>Options</legend>
    <input type='checkbox' name='' /> Surveiller mes contacts<br />
    <input type='checkbox' name='' /> Supprimer les camarades décédés de mes contacts
  </fieldset>
  <div class='center'>
    <input type='submit' value='valider' />
  </div>
</form>

<br />
<h1>Surveiller des non inscrits</h1>

<p>
Pour les non-inscrits, tu es notifié lorsqu'il s'inscrit, ou lorsque ce camarade décède.
</p>

<p>
Si un non-inscrit que tu surveille s'inscrit, il sera automatiquement ajouté à tes contacts.
</p>

<form action="{$smarty.server.PHP_SELF}" method="post">
  <fieldset>
    <legend>Ajouter un non-inscrit</legend>
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
