{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
{*  http://opensource.polytechnique.org/                                  *}
{*                                                                        *}
{*  This program is free software; you can redistribute it and/or modify  *}
{*  it under the terms of the GNU General Public License as published by  *}
{*  the Free Software Foundation; either version 2 of the License, or     *}
{*  (at your option) any later version.                                   *}
{*                                                                        *}
{*  This program is distributed in the hope that it will be useful,       *}
{*  but WITHOUT ANY WARRANTY; without even the implied warranty of        *}
{*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *}
{*  GNU General Public License for more details.                          *}
{*                                                                        *}
{*  You should have received a copy of the GNU General Public License     *}
{*  along with this program; if not, write to the Free Software           *}
{*  Foundation, Inc.,                                                     *}
{*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA               *}
{*                                                                        *}
{**************************************************************************}

<h1>Gestion des homonymes</h1>

{if $op eq 'mail'}
<p class="erreur">mail envoyé à {$forlife}</p>
{elseif $op eq 'correct'}
<p class="erreur">mail envoyé à {$forlife}, alias supprimé</p>
{/if}

{if $op eq 'list' || $op eq 'mail' || $op eq 'correct'}

<p>
  Les utilisateurs signalés en rouge sont ceux qui conservent actuellement
  l'alias prenom.nom et empêchent donc la mise en place du robot détrompeur.
</p>

<table class="bicol">
  <tr>
    <th>alias concerné</th>
    <th>date de péremption de l'alias</th>
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
      {if $user.type eq 'alias'}
      <span class="erreur"><strong>{$user.forlife}</strong></span>
      {else}
      {$user.forlife}
      {/if}
    </td>
    <td>{$user.expire|date_format}</td>
    <td>
      <a href="profile/{$user.forlife}" class='popup2'>fiche</a>
      <a href="admin/user/{$user.forlife}">edit</a>
      {if $user.type eq 'alias'}
      <a href="admin/homonyms/mail-conf/{$user.user_id}">mailer</a>
      <a href="admin/homonyms/correct-conf/{$user.user_id}">corriger</a>
      {/if}
    </td>
  </tr>
  {/foreach}
  {/foreach}
</table>

{elseif $op eq 'mail-conf'}

<form method="post" action="admin/homonyms/mail/{$target}">
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
tu te verras bientôt retirer l'alias {$loginbis}@{#globals.mail.domain#} pour
ne garder que {$forlife}@{#globals.mail.domain#}.

Toute personne qui écrira à {$loginbis}@{#globals.mail.domain#} recevra la
réponse d'un robot qui l'informera que {$loginbis}@{#globals.mail.domain#}
est ambigu pour des raisons d'homonymie et signalera ton email exact.

L'équipe Polytechnique.org
{#globals.baseurl#}
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

<form method="post" action="admin/homonyms/correct/{$target}">
  <table class="bicol">
    <tr>
      <th>Mettre en place le robot {$loginbis}@{#globals.mail.domain#}</th>
    </tr>
    <tr>
      <td>
        <textarea cols="80" rows="20" name="mailbody">
{$prenom},

Comme nous t'en avons informé par mail il y a quelques temps,
nous t'avons retiré de façon définitive l'adresse
{$loginbis}@{#globals.mail.domain#}.

Toute personne qui écrit à {$loginbis}@{#globals.mail.domain#} reçoit la
réponse d'un robot qui l'informe que {$loginbis}@{#globals.mail.domain#}
est ambigu pour des raisons d'homonymie et indique ton email exact.

Tu peux faire l'essai toi-même en écrivant à {$loginbis}@{#globals.mail.domain#}.

L'équipe Polytechnique.org
{#globals.baseurl#}
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


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
