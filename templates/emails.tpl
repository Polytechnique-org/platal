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
        $Id: emails.tpl,v 1.9 2004-10-24 14:41:11 x2000habouzit Exp $
 ***************************************************************************}


<h1>
  Gestion de mes courriers électroniques
</h1>

{dynamic}

<table class="bicol">
  <tr>
    <th>Mes adresses polytechniciennes à vie</th>
  </tr>
  <tr class="impair">
    <td>
      Tes adresses polytechniciennes sont :
      <ul>
        {foreach from=$aliases item=a}
        <li>
        {if $a.a_vie}(*){/if} <strong>{$a.alias}</strong>@polytechnique.org et @m4x.org 
        {if $a.expire}<span class='erreur'>(expire le {$a.expire|date_format:"%d %b %Y"})</span>{/if}
        </li>
        {/foreach}
      </ul>
    </td>
  </tr>
  <tr class="pair">
    <td>
      (M4X signifie <em>mail for X</em>, son intérêt est de te doter d'une adresse à vie
      moins "voyante" que l'adresse @polytechnique.org).
    </td>
  </tr>
  <tr class="impair">
    <td>
      Elles seront prochainement <strong>complétées d'une adresse @polytechnique.edu</strong>,
      plus lisible dans les pays du monde où "Polytechnique" n'évoque pas grand chose,
      .edu étant le suffixe propre aux universités et établissements d'enseignement supérieur.
    </td>
  </tr>
</table>

<p class="smaller">
(*) l'adresse email marquée d'une (*) t'est réservée pour une période 100 ans après ton entrée à l'X (dans ton cas, jusqu'en {$smarty.session.promo+100}).
Les autres te sont attribuées a priori à vie, sauf si tu venais à avoir un homonyme X. Dans ce cas, ni ton homonyme ni toi-même n'auriez d'autres adresses que celles de la forme prenom.nom.promo@polytechnique.org.
</p>


<br />

<table class="bicol">
  <tr>
    <th>Où est-ce que je reçois le courrier qui m'y est adressé ?</th>
  </tr>
  <tr class="pair">
    <td>
      Actuellement, tout courrier électronique qui t'y est adressé, est envoyé
      {if $nb_mails eq 1} à l'adresse {else} aux adresses {/if}
      <ul>
        {section name=mail loop=$mails}
        <li><strong>{$mails[mail].email}</strong>{if $smarty.section.mail.last}.{else}, {/if}</li>
        {/section}
      </ul>
      Si tu souhaites <strong>modifier ce reroutage de ton courrier,</strong>
      <a href="{"routage-mail.php"|url}">il te suffit de te rendre ici !</a>
    </td>
  </tr>
</table>

<br />

<table class="bicol">
  <tr>
    <th colspan="2">Antivirus, antispam</th>
  </tr>
  <tr class="impair">
    <td class="half">
      Tous les courriers qui te sont envoyés sur tes adresses polytechniciennes sont
      <strong>filtrés par un logiciel antivirus</strong> très performant. Il te protège de ces
      vers très gênants, qui se propagent souvent par le courrier électronique.
    </td>
    <td class="half">
      De même, un <strong>service antispam évolué</strong> est en place. Tu peux lui demander
      de te débarrasser des spams que tu reçois. Pour en savoir plus, et l'activer,
      <a href="antispam.php">c'est très simple, suis ce lien </a>!
      <br />
    </td>
  </tr>
</table>

<br />

<table class="bicol">
  <tr>
    <th>Un alias sympatique : melix !</th>
  </tr>
  <tr class="pair">
    <td>
      Tu peux ouvrir en supplément une adresse synonyme de ton adresse @polytechnique.org, 
      sur les domaines @melix.org et @melix.net (melix = Mél X).
    </td>
  </tr>
  <tr class="impair">
    <td>
      {if $melix}
      Tu disposes à l'heure actuelle des adresses <strong>{$melix}net</strong> et <strong>{$melix}org</strong>.
      Pour <strong>demander à la place un autre alias melix</strong>,
      <a href="alias.php">il te suffit de te rendre ici</a>
      {else}
      A l'heure actuelle <strong>tu n'as pas activé d'adresse melix</strong>.
      Si tu souhaites le faire, <a href="alias.php">il te suffit de venir ici</a>
      {/if}
    </td>
  </tr>
</table>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
