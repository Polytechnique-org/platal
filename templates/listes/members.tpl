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
        $Id: members.tpl,v 1.6 2004-10-09 12:58:28 x2000habouzit Exp $
 ***************************************************************************}

{dynamic}

{if $no_list}

<p class='erreur'>La liste n'existe pas ou tu n'as pas le droit d'en voir les détails</p>

{else}

<p>
[<a href='index.php'>listes</a>] »
[{$smarty.request.liste}]
[<a href='trombi.php?liste={$smarty.request.liste}'>trombino</a>]
{if $details.own || $smarty.session.perms eq admin}
»
[<a href='moderate.php?liste={$smarty.get.liste}'>modération</a>]
[<a href='admin.php?liste={$smarty.get.liste}'>abonnés</a>]
[<a href='options.php?liste={$smarty.get.liste}'>options</a>]
{/if}
{perms level=admin} »
[<a href='soptions.php?liste={$smarty.get.liste}'>Soptions</a>]
[<a href='check.php?liste={$smarty.get.liste}'>check</a>]
{/perms}
</p>

<div class="rubrique">
  Liste {$smarty.request.liste}
</div>

<table class='tinybicol' cellpadding='0' cellspacing='0'>
  <tr>
    <td class='titre'> Adresse </td>
    <td>{mailto address=$details.addr}</td>
  </tr>
  <tr>
    <td class='titre'> Sujet </td>
    <td>{$details.desc}</td>
  </tr>
  <tr>
    <td class='titre'> Visibilité </td>
    <td>{if $details.priv eq 0}publique{elseif $details.priv eq 1}privée{else}admin{/if}</td>
  </tr>
  <tr>
    <td class='titre'> Diffusion </td>
    <td>{if $details.diff eq 2}modérée{elseif $details.diff}restreinte{else}libre{/if}</td>
  </tr>
  <tr>
    <td class='titre'> Inscription </td>
    <td>{if $details.ins}modérée{else}libre{/if}</td>
  </tr>
</table>
{if $details.info}
<br />
<table class='tinybicol' cellpadding='0' cellspacing='0'>
  <tr><th colspan='2'>Informations sur la liste</th></tr>
  <tr>
    <td colspan='2'>{$details.info|nl2br}</td>
  </tr>
</table>
{/if}

<div class='rubrique'>
  modérateurs de la liste
</div>

{if $owners|@count}
<table class='tinybicol' cellpadding='0' cellspacing='0'>
  {foreach from=$owners item=xs key=promo}
  <tr>
    <td class='titre'>{if $promo}{$promo}{else}non-X{/if}</td>
    <td>
      {foreach from=$xs item=x}
      {if $promo}
      <a href="javascript:x()" onclick="popWin('{"fiche.php"|url}?user={$x.l}')">{$x.n}</a><br />
      {else}
      {$x.l}<br />
      {/if}
      {/foreach}
    </td>
  </tr>
  {/foreach}
</table>
{/if}

<div class='rubrique'>
  membres de la liste
</div>

{if $members|@count}
<table class='bicol' cellpadding='0' cellspacing='0'>
  {foreach from=$members item=xs key=promo}
  <tr>
    <td class='titre'>{if $promo}{$promo}{else}non-X{/if}</td>
    <td>
      {foreach from=$xs item=x}
      {if $promo}
      <a href="javascript:x()" onclick="popWin('{"fiche.php"|url}?user={$x.l}')">{$x.n}</a><br />
      {else}
      {$x.l}<br />
      {/if}
      {/foreach}
    </td>
  </tr>
  {/foreach}
</table>
{/if}

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
