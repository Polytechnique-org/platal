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
        $Id: admin.tpl,v 1.6 2004-09-24 16:28:06 x2000habouzit Exp $
 ***************************************************************************}

{dynamic}

{if $no_list || ( !$details.own && $smarty.session.perms neq admin )}

<p class='erreur'>La liste n'existe pas ou tu n'as pas le droit de l'administrer</p>

{else}

<div class='rubrique'>
  Abonnés de la liste {$details.addr}
</div>
{if !$details.own}
<p class='erreur'>
Tu n'es pas administrateur de la liste, mais du site.
</p>
{/if}

<p>
[<a href='index.php'>listes</a>] &gt;
[<a href='moderate.php?liste={$smarty.get.liste}'>modération</a>]
[abonnés]
[<a href='options.php?liste={$smarty.get.liste}'>options</a>]
{perms level=admin}
[<a href='soptions.php?liste={$smarty.get.liste}'>Soptions</a>]
{/perms}
</p>

<p>
Pour entrer un utilisateur, il faut remplir les champs prévus à cet effet par son login,
c'est-à-dire "prenom.nom" ou "prenom.nom.promo"
</p>

<div class='rubrique'>
  modérateurs de la liste
</div>

<form method='post' action='{$smarty.server.REQUEST_URI}'>
  <table class='tinybicol' cellpadding='0' cellspacing='0'>
    {foreach from=$owners item=xs key=promo}
    <tr>
      <td class='titre'>{if $promo}{$promo}{else}non-X{/if}</td>
      <td>
        {foreach from=$xs item=x}
        {if $promo}
        <a href="javascript:x()" onclick="popWin('{"fiche.php"|url}?user={$x.l}')">{$x.n}</a>
        {else}
        {$x.l}
        {/if}
        <a href='?liste={$smarty.get.liste}&amp;del_owner={$x.l}'>
          <img src='{"images/retirer.gif"|url}' alt='retirer utilisateur' />
        </a><br />
        {/foreach}
      </td>
    </tr>
    {/foreach}
    <tr>
      <td class='titre'>Ajouter ...</td>
      <td>
        <input type='text' name='add_owner' />
        &nbsp;
        <input type='submit' value='ajouter' />
      </td>
    </tr>
  </table>
</form>


<div class='rubrique'>
  membres de la liste
</div>

<form method='post' action='{$smarty.server.REQUEST_URI}'>
  <table class='bicol' cellpadding='0' cellspacing='0'>
    {foreach from=$members item=xs key=promo}
    <tr>
      <td class='titre'>{if $promo}{$promo}{else}non-X{/if}</td>
      <td>
        {foreach from=$xs item=x}
        {if $promo}
        <a href="javascript:x()" onclick="popWin('{"fiche.php"|url}?user={$x.l}')">{$x.n}</a>
        {else}
        {$x.l}
        {/if}
        <a href='?liste={$smarty.get.liste}&amp;del_member={$x.l}'>
          <img src='{"images/retirer.gif"|url}' alt='retirer utilisateur' />
        </a><br />
        {/foreach}
      </td>
    </tr>
    {/foreach}
    <tr>
      <td class='titre'>Ajouter ...</td>
      <td>
        <input type='text' size='32' name='add_member' />
        &nbsp;
        <input type='submit' value='ajouter' />
      </td>
    </tr>
  </table>
</form>

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
