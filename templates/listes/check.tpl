{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http ://opensource.polytechnique.org/                                   *
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
        $Id : admin.tpl,v 1.4 2004/09/23 18:47:00 x2000habouzit Exp $
 ***************************************************************************}

{dynamic}

{if $no_list || $smarty.session.perms neq admin }

<p class='erreur'>La liste n'existe pas ou tu n'as pas le droit de l'administrer</p>

{else}

<p>
[<a href='index.php'>listes</a>] »
[<a href='members.php?liste={$smarty.request.liste}'>{$smarty.request.liste}</a>]
[<a href='trombi.php?liste={$smarty.request.liste}'>trombino</a>] »
[<a href='moderate.php?liste={$smarty.get.liste}'>modération</a>]
[<a href='admin.php?liste={$smarty.get.liste}'>membres</a>]
[<a href='options.php?liste={$smarty.get.liste}'>options</a>]
{perms level=admin} »
[<a href='soptions.php?liste={$smarty.get.liste}'>Soptions</a>]
[check]
{/perms}
</p>

{if $options|@count}
<table class='bicol' cellpadding='2' cellspacing='0'>
  <tr><th colspan='3'>Options incorrectres pour {$details.addr}</th></tr>
  <tr><th>champs</th><th>devrait être:</th><th>est ...</th></tr>
  {foreach from=$options key=k item=o}
  <tr>
    <td class='titre'>{$k}</td>
    <td>{if $o[0] === false}False{elseif $o[0] === true}True{elseif $o[0] === ''}" "{else}{$o[0]}{/if}</td>
    <td>{if $o[1] === false}False{elseif $o[1] === true}True{elseif $o[1] === ''}" "{else}{$o[1]}{/if}</td>
  </tr>
  {/foreach}
</table>
<form action='{$smarty.server.REQUEST_URI}' method='post'>
  <div class='center'>
    <br />
    <input type='submit' name='correct' value='Corriger les valeurs !' />
  </div>
</form>
{else}
<p>Liste correcte !</p>
{/if}

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
