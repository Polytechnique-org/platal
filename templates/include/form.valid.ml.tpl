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


<form action="{$smarty.server.PHP_SELF}" method="post">
  <table class="bicol">
    <tr>
      <td>Demandeur&nbsp;:</td>
      <td>
        <a href="{"fiche.php"|url}?user={$valid->bestalias}" class="popup2">
          {$valid->prenom} {$valid->nom}
        </a>
      </td>
    </tr>
    <tr>
      <td>Motif :</td>
      <td>{$valid->comment|nl2br}
      </td>
    </tr>
    <tr>
      <td style="border-top:1px dotted inherit">
        Alias :
      </td>
      <td style="border-top:1px dotted inherit">
        <input type="text" name="alias" value="{$valid->alias}" />@polytechnique.org
      </td>
    </tr>
    <tr>
      <td>Topic :</td>
      <td><input type="text" name="topic" size="60" value="{$valid->topic}" />
      </td>
    </tr>
    <tr>
      <td>Propriétés :</td>
      <td>
        <input type="checkbox" name="publique" {if $valid->publique}checked="checked"{/if} />Publique
        <input type="checkbox" name="libre" {if $valid->libre}checked="checked"{/if} />Libre
        <input type="checkbox" name="freeins" {if $valid->freeins}checked="checked"{/if} />Freeins
        <input type="checkbox" name="archive" {if $valid->archive}checked="checked"{/if} />Archive
      </td>
    </tr>
    <tr>
      <td style="border-top:1px dotted inherit">Modéros :</td>
      <td style="border-top:1px dotted inherit">{$valid->moderos_txt}</td>
    </tr>
    <tr>
      <td>Membres :</td>
      <td>{$valid->membres_txt}</td>
    </tr>
    <tr>
      <td class="middle" style="border-top:1px dotted inherit">
        <input type="hidden" name="uid" value="{$valid->uid}" />
        <input type="hidden" name="type" value="{$valid->type}" />
        <input type="hidden" name="stamp" value="{$valid->stamp}" />
        <input type="submit" name="submit" value="Accepter" />
        <br /><br />
        <input type="submit" name="submit" value="Refuser" />
      </td>
      <td style="border-top:1px dotted inherit">
        <p>Explication complémentaire (refus ou changement de config, ...)</p>
        <textarea rows="5" cols="74" name=motif></textarea>
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
