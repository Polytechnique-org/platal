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


{if $ask_naissance}

{include file='include/form_naissance.tpl'}

{else}

<h1>
Bienvenue {$smarty.session.prenom}
{if $birthday}
  et joyeux anniversaire de {$birthday} ans de la part de toute l'équipe !
{else}
:o)
{/if}
</h1>

<div class="smaller">
  Ta connexion précédente date du
  <strong>{$smarty.session.lastlogin|date_format:"%x, %X"}</strong>
  depuis la machine <strong>{$smarty.session.host}</strong>
</div>
  
{if $fiche_incitation}
  <p>La dernière mise à jour de ta
  <a href="{rel}/fiche.php}?user={$smarty.session.forlife}" class="popup2">fiche</a>
  date du {$fiche_incitation|date_format}.
  Il est possible qu'elle ne soit pas à jour.
  Si tu souhaites la modifier, <a href="profil.php">clique ici !</a>
  </p>
{/if}

{if $photo_incitation}
  <p>
    Tu n'as pas mis de photo de toi sur ta fiche, c'est dommage.
    Clique <a href="{rel}/trombino.php">ici</a> si tu souhaites en ajouter une.
  </p>
{/if}

<br />

  <table class="bicol">
    <tr class="pair">
{foreach item=links from=$publicite}
      <td class="half">
{foreach key=url item=text from=$links}
        <a href="{$url}">{$text}</a><br />
{/foreach}
      </td>
{/foreach}
    </tr>
  </table>

  {iterate item=ev from=$evenement}
  <br />

  <table class="bicol">
    <tr>
      <th>
        <a id="newsid{$ev.id}"></a>
	 {tidy}
	   {$ev.titre|nl2br}
	 {/tidy}
      </th>
    </tr>
    <tr class="{cycle values="impair,pair"}">
      <td class="half">
        {tidy}
          {$ev.texte|smarty:nodefaults|nl2br}
        {/tidy}
        <br />
        <p class="smaller">Annonce proposée par
        <a href="{rel}/fiche.php?user={$ev.forlife}" class="popup2">
          {$ev.prenom} {$ev.nom} X{$ev.promo}
        </a>
        </p>
      </td>
    </tr>
  </table>
  {/iterate}

  <p class="smaller">
  Nota Bene : les informations présentées ici n'engagent que leurs auteurs
  respectifs et sont publiées à leur initiative. L'association Polytechnique.org
  ne pourrait en aucun cas être tenue responsable de la nature des propos relatés
  sur cet espace d'expression et d'information. Elle se réserve le droit de
  refuser ou de retirer toute information de nature diffamante ou pouvant être
  interprétée comme polémique par un membre de la communauté polytechnicienne.
  </p>

  <p>
  <a href="evenements.php">Proposer une information événementielle</a>
  </p>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
