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
        $Id: notifs.tpl,v 1.1 2004-11-04 16:59:31 x2000habouzit Exp $
 ***************************************************************************}

<h1>Notifications automatiques</h1>

<h2>Surveiller tes contacts</h2>

<form action="{$smarty.server.PHP_SELF}" method="post">
  <fieldset>
    <legend>Contacts</legend>
    <input type='checkbox' name='' /> Surveiller les changements de fiche de tes contacts (y compris celles de photo)<br />
    <input type='checkbox' name='' /> Surveiller les décès parmis tes contacts
  </fieldset>
  <div class='center'>
    <input type='submit' value='valider' />
  </div>
</form>

<h2>Surveiller des promotions</h2>

<form action="{$smarty.server.PHP_SELF}" method="post">
  <fieldset>
    <legend>Ajouter une promotion</legend>
    Ajouter une surveillance sur la promotion :
    <input type='text' name='' maxlength='4' size='4' />
    <input type='submit' value='ajouter' />
  </fieldset>
</form>

{if $promos|@count}
<form action="{$smarty.server.PHP_SELF}" method="post">
  {foreach from=$promos item=p}
  <fieldset>
    <legend>Promo {$p.promo}</legend>
    <input type='checkbox' name='' /> Surveiller les inscriptions<br />
    <input type='checkbox' name='' /> Surveiller les décès
  </fieldset>
  {/foreach}
  
  <div class='center'>
    <input type='submit' value='tout valider' />
  </div>
</form>
{/if}

<h2>Surveiller des non inscrits</h2>

<table style="width: 100%">
  <tr>
    <td class='half'>
      <form action="{$smarty.server.PHP_SELF}" method="post">
        <fieldset>
          <legend>Surveiller des inscriptions</legend>
          <input type='text' name='' />
          <input type='submit' value='ajouter' />
          <br />
          {foreach from=$watch_ins item=w}
          {$w.forlife}
          {/foreach}
        </fieldset>
      </form>
    </td>
    <td class='half'>
      <form action="{$smarty.server.PHP_SELF}" method="post">
        <fieldset>
          <legend>Surveiller des décès</legend>
          <input type='text' name='' />
          <input type='submit' value='ajouter' />
          <br />
          {foreach from=$watch_dcd item=w}
          {$w.forlife}
          {/foreach}
        </fieldset>
      </form>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
