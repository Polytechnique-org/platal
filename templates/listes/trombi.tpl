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
        $Id: trombi.tpl,v 1.11 2004-11-07 14:22:49 x2000chevalier Exp $
 ***************************************************************************}

{dynamic}

{if $no_list}

<p class='erreur'>La liste n'existe pas ou tu n'as pas le droit d'en voir les détails</p>

{else}

{include file="listes/header_listes.tpl"}

<h1>
  Liste {$smarty.request.liste}
</h1>

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

<h1>
  modérateurs de la liste
</h1>

<table cellpadding="8" cellspacing="2" style="width:100%;">
  {foreach from=$owners item=xs key=promo}
    {foreach from=$xs item=x}
      {if $promo}
      {cycle values="1,2,3" assign="loop"}
      {if $loop eq "1"}<tr>{/if}
        <td class='center'>
          <img src="{"getphoto.php"|url}?x={$x.l}" width="110" alt=" [ PHOTO ] " />
          <br />
          <a href="javascript:x()" onclick="popWin('{"fiche.php"|url}?user={$x.l}')">
            {$x.n} ({$promo})
          </a>
        </td>
      {if $loop eq "3"}</tr>{/if}
      {/if}
    {/foreach}
  {/foreach}
  {if $loop eq "1"}
    {cycle values="1,2,3" assign="loop"}
    {cycle values="1,2,3" assign="loop"}
    <td></td><td></td></tr>
  {elseif $loop eq "2"}
    {cycle values="1,2,3" assign="loop"}
    <td></td></tr>
  {/if}
</table>

<h1>
  membres de la liste
</h1>

{$trombi->show()|smarty:nodefaults}

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
