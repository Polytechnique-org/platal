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

<h1>Validation</h1>
 

{if $vit->total()}

{iterate item=valid from=$vit|smarty:nodefaults}
<br />
<form action="{$smarty.server.PHP_SELF}" method="post">
  <table class="bicol" {popup caption="Règles de validation" text=$valid->rules}>
    <tr>
      <th colspan="2">{$valid->type}</th>
    </tr>
    <tr>
      <td class="titre" style="width: 20%">Demandeur&nbsp;:</td>
      <td>
        <a href="{rel}/fiche.php?user={$valid->bestalias}" class="popup2">
          {$valid->prenom} {$valid->nom} (X{$valid->promo})
        </a>
      </td>
    </tr>
    {include file=$valid->formu()}
    {if $valid->comments}
    <tr><th colspan='2'>Commentaires</th></tr>
    {/if}
    {foreach from=$valid->comments item=c}
    <tr class="{cycle values="impair,pair"}">
      <td class="titre">
        <a href="{rel}/fiche.php?user={$c[0]}" class="popup2">{$c[0]}</a>
      </td>
      <td>{$c[1]}</td>
    </tr>
    {/foreach}
    <tr>
      <td colspan='2' class='center'>
        Commentaire:<br />
        <textarea rows="5" cols="50" name="comm"></textarea><br />

        <input type="hidden" name="uid"    value="{$valid->uid}" />
        <input type="hidden" name="type"   value="{$valid->type}" />
        <input type="hidden" name="stamp"  value="{$valid->stamp}" />
        <input type="submit" name="accept" value="Accepter" />
        <input type="submit" name="hold"   value="Commenter" />
        {if $valid->refuse}<input type="submit" name="refuse" value="Refuser" />{/if}
        <input type="submit" name="delete" value="Supprimer" />
      </td>
    </tr>
  </table>
</form>
{/iterate}

{else}

<p>Rien à valider</p>

{/if}


{* vim:set et sw=2 sts=2 sws=2: *}
