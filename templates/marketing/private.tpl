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


<h1>Marketing de {$prenom} {$nom}</h1>

<h2>Matricules</h2>
Matricule &Eacute;cole (à rentrer lors de l'inscription) : <strong>{$matricule_X}</strong>
<br />
Matricule polytechnique.org : {$matricule}

{if $pending}

<h2>Inscription en cours</h2>

<p>
Cet utilisateur a une inscription en cours depuis le {$pending|date_format}.
</p>
<p>
{if $relance eq '0000-00-00'}
il n'a jamais été relancé.
{else}
sa dernière relance date du {$relance|date_format}
{/if}
</p>

<p>[<a href='?uid={$smarty.request.uid}relance=1'>le relancer</a>]</p>

{/if}

<h2>Adresses connues</h2>

<table class="bicol" cellpadding="0" cellspacing="0">
  <tr>
    <th>Adresse</th>
    <th>Marketeur</th>
    <th>Date</th>
    <th>Envois</th>
    <th>Nb.</th>
    <th>&nbsp;</th>
  </tr>
  {iterate from=$addr item=a}
  <tr class="{cycle values='impair,pair'}">
    <td>{$a.email}</td>
    <td><a href="{rel}/fiche.php?user={$a.alias}" class="popup2">{$a.alias}</a> {if $a.type eq user}(*){/if}</td>
    <td>{$a.date|date_format|default:'-'}</td>
    <td>{$a.last|date_format|default:'-'}</td>
    <td class='center'>{$a.nb|default:"-"}</td>
    <td class='action'>
      <a href='?uid={$smarty.request.uid}&amp;del={$a.email}'>del</a>
    </td>
  </tr>
  {/iterate}
  <tr>
    <td></td>
    <td colspan='5' class='smaller'>(*): mail perso</td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
