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

{dynamic}

{if $no_list || ( !$details.own && $smarty.session.perms neq admin )}

<p class='erreur'>La liste n'existe pas ou tu n'as pas le droit de l'administrer</p>

{else}

{if !$details.own}
<p class='erreur'>
Tu n'es pas administrateur de la liste, mais du site.
</p>
{/if}

{include file="listes/header_listes.tpl" on=admin}

<p>
Pour inscrire un utilisateur, il faut remplir les champs prévus à cet effet en saisissant
son identifiant, de la forme "prenom.nom", ou "prenom.nom.promo" en cas d'homonymie.
L'icône <img src='{"images/retirer.gif"|url}' alt='retirer membre' title='retirer membre' /> permet de désinscrire de la liste quelqu'un
qui y était abonné.
</p>

{foreach from=$err item=e}
<p class='error'>{$e}</p>
{/foreach}

<h1>
  modérateurs de la liste
</h1>

<form method='post' action='{$smarty.server.REQUEST_URI}'>
  <table class='tinybicol' cellpadding='0' cellspacing='0'>
    {foreach from=$owners item=xs key=promo}
    <tr>
      <td class='titre'>{if $promo}{$promo}{else}non-X{/if}</td>
      <td>
        {foreach from=$xs item=x}
        {if $promo}
        <a href="{"fiche.php"|url}?user={$x.l}" class="popup2">{$x.n}</a>
        {else}
        {$x.l}
        {/if}
        <a href='?liste={$smarty.get.liste}&amp;del_owner={$x.a}'><img src='{"images/retirer.gif"|url}' alt='retirer modérateur' title='retirer modérateur' /></a>
        <br />
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


<h1>
  {$np_m|default:"0"} membre(s) dans la liste
</h1>

<form method='post' action='{$smarty.server.REQUEST_URI}'>
  <table class='bicol' cellpadding='0' cellspacing='0'>
    {foreach from=$members item=xs key=promo}
    <tr>
      <td class='titre'>{if $promo}{$promo}{else}non-X{/if}</td>
      <td>
        {foreach from=$xs item=x}
        {if $promo}
        <a href="{"fiche.php"|url}?user={$x.l}" class="popup2">{$x.n}</a>
        {else}
        {$x.l}
        {/if}
        <a href='?liste={$smarty.get.liste}&amp;del_member={$x.a}'><img src='{"images/retirer.gif"|url}' alt='retirer membre' title='retirer membre' /></a>
        <br />
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
