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
        $Id: homonymes.tpl,v 1.5 2004-08-31 11:25:39 x2000habouzit Exp $
 ***************************************************************************}


<div class="rubrique">
  Gestion des homonymes
</div>

{dynamic}

{if $op eq 'mail'}
<p class="erreur">mail envoyé à {$username}</p>
{elseif $op eq 'correct'}
<p class="erreur">mail envoyé à {$username}, alias supprimé</p>
{/if}

{if $op eq 'list' || $op eq 'mail' || $op eq 'correct'}

<p>
  Les utilisateurs signalés en rouge sont ceux qui conservent actuellement
  l'alias prenom.nom et empêchent donc la mise en place du robot détrompeur.
</p>

<table class="bicol">
  <tr>
    <th>username</th>
    <th>date de l'alias</th>
    <th>op</th>
  </tr>
  {foreach from=$hnymes key=login item=row}
  <tr class="pair">
    <td colspan="3">
      <strong>{$login}</strong>
    </td>
  </tr>
  {foreach from=$row item=user}
  <tr class="impair">
    <td>&nbsp;&nbsp;
      {if $user.alias eq $login}
      <span class="erreur"><strong>{$user.username}</strong></span>
      {else}
      {$user.username}
      {/if}
    </td>
    <td>{$user.date}</td>
    <td>
      <a href="javascript:x()" onclick="popWin('../fiche.php?user={$user.username}')">fiche</a>
      <a href="javascript:x()" onclick="popWin('utilisateurs.php?login={$user.username}&amp;select=1')">edit</a>
      {if $user.alias eq $login}
      <a href="?op=mail-conf&amp;target={$user.user_id}">mailer</a>
      <a href="?op=correct-conf&amp;target={$user.user_id}">corriger</a>
      {/if}
    </td>
  </tr>
  {/foreach}
  {/foreach}
</table>

{elseif $op eq 'mail-conf'}

<form method="post" action="{$smarty.server.PHP_SELF}">
  <input type="hidden" name="target" value="{$target}" />
  <input type="hidden" name="op" value="mail" />
  <table class="bicol">
    <tr>
      <th>Envoyer un mail pour prévenir l'utilisateur</th>
    </tr>
    <tr>
      <td>
        <textarea cols="80" rows="20" name="mailbody">
{$prenom},


Comme nous t'en avons informé par mail il y a quelques temps,
pour respecter nos engagements en terme d'adresses e-mail devinables,
tu te verras bientôt attribuer de façon définitive l'adresse
{$username}@polytechnique.org.

Toute personne qui écrira à {$loginbis}@polytechnique.org recevra la
réponse d'un robot qui l'informera que {$loginbis}@polytechnique.org
est ambigu pour des raisons d'homonymie et signalera ton email exact.

L'équipe Polytechnique.org
{$baseurl}
        </textarea>
      </td>
    </tr>
    <tr>
      <td>
        <input type="submit" value="Envoyer" />
      </td>
    </tr>
  </table>
</form>

{elseif $op eq 'correct-conf'}

<form method="post" action="{$smarty.server.PHP_SELF}">
  <input type="hidden" name="target" value="{$target}" />
  <input type="hidden" name="op" value="correct" />
  <table class="bicol">
    <tr>
      <th>Mettre en place le robot {$loginbis}@polytechnique.org</th>
    </tr>
    <tr>
      <td>
        <textarea cols="80" rows="20" name="mailbody">
{$prenom},
          
Comme nous t'en avons informé par mail il y a quelques temps,
nous t'avons attribué de façon définitive l'adresse
{$username}@polytechnique.org.

Toute personne qui écrit à {$loginbis}@polytechnique.org reçoit la
réponse d'un robot qui l'informe que {$loginbis}@polytechnique.org
est ambigu pour des raisons d'homonymie et signale ton email exact

Tu peux faire l'essai toi-même en écrivant à {$loginbis}@polytechnique.org.

L'équipe Polytechnique.org
{$baseurl}
        </textarea>
      </td>
    </tr>
    <tr>
      <td>
        <input type="submit" value="Envoyer et corriger" />
      </td>
    </tr>
  </table>
</form>

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
