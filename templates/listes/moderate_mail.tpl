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
        $Id: moderate_mail.tpl,v 1.9 2004-10-28 09:30:20 x2000habouzit Exp $
 ***************************************************************************}

{dynamic}

{if $no_list}

<p class='erreur'>La liste n'existe pas ou tu n'as pas le droit de la modérer</p>

{else}

<h1>
  Propriétés du mail en attente
</h1>

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

<h1>
  Contenu du mail en attente
</h1>

{if $mail.parts|@count}
<table class='bicol' cellpadding='0' cellspacing='0'>
  {foreach from=$mail.parts item=part key=i}
  <tr><th>Partie n°{$i}</th></tr>
  <tr class='{cycle values="impair,pair"}'>
    <td><tt>{$part|qpd|nl2br}</tt></td>
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
  <ul>
    <li>« Refuser » rejette le mail avec un message à son auteur (celui que tu tapes dans le cadre)</li>
    <li>
    Rejeter efface le mail sans autre forme de procès, et c'est à utiliser UNIQUEMENT pour les
    virus et les courriers indésirables
    </li>
  </ul>
</form>
<p>
En cas de refus, le mail envoyé à l'auteur du mail que tu modères actuellement sera de la forme suivante :
</p>
<pre>{$msg}</pre>

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
