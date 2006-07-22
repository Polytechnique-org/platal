{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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
      <a href="profile/{$s.login}" class="popup2">{*
        *}<img src="images/loupe.gif" alt="Afficher la fiche" title="Afficher la fiche" /></a>
      {/if}
    </td>
    <td>{$s.addr}</td>
    <td class='action'>
      <a href='{$platal->pl_self(1)}?sadd={$s.id}'>ajouter</a>
      <a href='{$platal->pl_self(1)}?sid={$s.id}'>refuser</a>
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
  N'utiliser <strong>QUE</strong> pour les virus et les courriers indésirables. <br/>
  S'il y a trop d'indésirables, il est probablement plus rapide pour la suite de les
  <a href="{$platal->ns}lists/options/{$platal->argv[1]}#antispam">jeter directement</a> et non de les modérer. 
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
    <td class='right'>{$m.stamp|date_format:"%X<br />%x"}</td>
    <td class='action'>
      <a href='{$platal->pl_self(1)}?mid={$m.id}'>voir</a><br/>
      <a href='{$platal->pl_self(1)}?mid={$m.id}&amp;mok=1'>accepter</a>&nbsp;<a href='{$platal->pl_self(1)}?mid={$m.id}&amp;mdel=1'>détruire</a></td>
  </tr>
  {/foreach}
</table>
{else}
<p>pas de mails en attente de modération</p>
{/if}


{* vim:set et sw=2 sts=2 sws=2: *}
