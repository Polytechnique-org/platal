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
        $Id: moderate.tpl,v 1.21 2004-11-27 16:01:04 x2000habouzit Exp $
 ***************************************************************************}

{dynamic}

{if $no_list}

<p class='erreur'>La liste n'existe pas ou tu n'as pas le droit de la modérer</p>

{else}

{include file="listes/header_listes.tpl" on=moderate}

<h1>
  Inscriptions en attente de modération
</h1>

{if $subs|@count}
<table class='bicol' cellpadding='0' cellspacing='0'>
  <tr>
    <th>Nom</th>
    <th>Adresse</th>
    <th></th>
  </tr>
  {foreach from=$subs item=s}
  <tr class='{cycle values="pair,impair"}'>
    <td>{$s.name}{if $s.login}
      <a href="{rel}/fiche.php?user={$s.login}" class="popup2">{*
        *}<img src="{rel}/images/loupe.gif" alt="Afficher la fiche" title="Afficher la fiche" /></a>
      {/if}
    </td>
    <td>{$s.addr}</td>
    <td class='action'>
      <a href='?liste={$smarty.request.liste}&amp;sadd={$s.id}'>ajouter</a>
      <a href='?liste={$smarty.request.liste}&amp;sid={$s.id}'>refuser</a>
    </td>
  </tr>
  {/foreach}
</table>
{else}
<p>pas d'inscriptions en attente de modération</p>
{/if}

<h1>
  Mails en attente de modération
</h1>

{if $mails|@count}
<ul>
  <li>
  <strong>accepter:</strong> le mail est immédiatement libéré, et envoyé à la
  liste.
  </li>
  <li>
  <strong>refuser:</strong> pour refuser un mail, suivre le lien [voir] et
  remplir le formulaire en bas de page.
  </li>
  <li>
  <strong>détruire:</strong> le mail est effacé sans autre forme de procès.
  N'utiliser <strong>QUE</strong> pour les virus et les courriers indésirables.
  </li>
</ul>

<table class='bicol' cellpadding='0' cellspacing='0'>
  <tr>
    <th>émetteur</th>
    <th>sujet</th>
    <th>taille</th>
    <th>date</th>
    <th></th>
  </tr>
  {foreach from=$mails item=m}
  <tr class='{cycle values="pair,impair"}'>
    <td>{$m.sender}</td>
    <td>{$m.subj|default:"[pas de sujet]"}</td>
    <td class='right'>{$m.size}o</td>
    <td class='right'>{$m.stamp|date_format:"%H:%M:%S<br />%d %b %Y"}</td>
    <td class='action'>
      <a href='?liste={$smarty.request.liste}&amp;mid={$m.id}'>voir</a>
      <a href='?liste={$smarty.request.liste}&amp;mid={$m.id}&amp;mok=1'>accepter</a><br />
      <a href='?liste={$smarty.request.liste}&amp;mid={$m.id}&amp;mdel=1'>détruire</a></td>
  </tr>
  {/foreach}
</table>
{else}
<p>pas de mails en attente de modération</p>
{/if}

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
