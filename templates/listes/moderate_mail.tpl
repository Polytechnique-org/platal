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
        $Id: moderate_mail.tpl,v 1.4 2004-09-24 16:43:53 x2000habouzit Exp $
 ***************************************************************************}

{dynamic}

{if $no_list}

<p class='erreur'>La liste n'existe pas ou tu n'as pas le droit de la modérer</p>

{else}

<div class='rubrique'>
  Propriétés du mail en attente
</div>

<table class='tinybicol' cellpadding='0' cellspacing='0'>
  <tr>
    <td class='titre'>émetteur</td>
    <td>{$mail.sender}</td>
  </tr>
  <tr>
    <td class='titre'>sujet</td>
    <td>{$mail.subj}</td>
  </tr>
  <tr>
    <td class='titre'>taille</td>
    <td>{$mail.size} octets</td>
  </tr>
  <tr>
    <td class='titre'>date</td>
    <td>{$mail.stamp|date_format:"%H:%M:%S le %d %b %Y"}</td>
  </tr>
</table>

<div class='rubrique'>
  Contenu du mail en attente
</div>

{if $mail.parts|@count}
<table class='bicol' cellpadding='0' cellspacing='0'>
  {foreach from=$mail.parts item=part key=i}
  <tr><th>Partie n°{$i}</th></tr>
  <tr class='{cycle values="impair,pair"}'>
    <td><pre>{$part|qpd|regex_replace:"!\\n-- *\\n(.*?)$!sm":"</pre><hr style='width:98%;margin:1%'/><pre>\\1"}</pre></td>
  </tr>
  {/foreach}
</table>
<br />
{/if}

<form method='post' action='?liste={$smarty.request.liste}'>
  <table class='tinybicol' cellpadding='0' cellspacing='0'>
    <tr>
      <th class='titre'>Modérer le mail de :</th>
    </tr>
    <tr>
      <td>raison (pour les refus) :
        <textarea cols='50' rows='10' name='reason'></textarea>
      </td>
    </tr>
    <tr>
      <td class='center'>
        <input type='hidden' name='mid' value='{$smarty.get.mid}' />
        <input type='submit' name='mok' value='Accepter !' />&nbsp;
        <input type='submit' name='mno' value='Refuser !' />&nbsp;
        <input type='submit' name='mdel' value='Rejeter !' style='color:red;' />
      </td>
    </tr>
  </table>
</form>

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
