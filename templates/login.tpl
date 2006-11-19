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

{if $ask_naissance}

{include file='include/form_naissance.tpl'}

{else}

<h1 id='pagetop'>
Bienvenue {$smarty.session.prenom}
{if $birthday}
  et joyeux anniversaire de {$birthday} ans de la part de toute l'équipe !
{else}
:o)
{/if}
</h1>

<div class="smaller">
  Ta connexion précédente date du
  <strong>{$lastlogin|date_format:"%x, %X"}</strong>
  depuis la machine <strong>{$smarty.session.host}</strong>
</div>
  
{if $fiche_incitation}
  <p>La dernière mise à jour de ta
  <a href="profile/{$smarty.session.forlife}" class="popup2">fiche</a>
  date du {$fiche_incitation|date_format}.
  Il est possible qu'elle ne soit pas à jour.
  Si tu souhaites la modifier, <a href="profile/edit">clique ici !</a>
  </p>
{/if}

{if $photo_incitation}
  <p>
    Tu n'as pas mis de photo de toi sur ta fiche, c'est dommage.
    Clique <a href="photo/change">ici</a> si tu souhaites en ajouter une.
  </p>
{/if}

{if $geoloc_incitation > 0}
  <p>
    Parmi tes adresses, il y en a {$geoloc_incitation} que nous n'avons pas pu localiser.
    Clique <a href="profile/edit/adresses">ici</a> pour rectifier.
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

  <br/>
  
  <table class="bicol">
    <tr>
      <th>
        {if $smarty.session.core_rss_hash}
        <a href='rss/{$smarty.session.forlife}/{$smarty.session.core_rss_hash}/rss.xml' style="display:block;float:right">
          {icon name=feed title='fil rss'}
        </a>
        {else}
        <a href='prefs/rss?referer=events'  style="display:block;float:right">
          {icon name=feed_add title='Activer mon fil rss'}
        </a>
       {/if}
        Sommaire des informations événementielles
      </th>
    </tr>
    {iterate item=ev from=$evenement_summary}
    <tr class="{cycle values="impair,pair"}">
      <td class="half">
        &bull;
        <a href="events{if !$ev.nonlu}/unread/{$ev.id}{else}#newsid{$ev.id}{/if}">
        {if $ev.nonlu}<strong>{/if}
      	 {tidy}
      	   {$ev.titre|nl2br}
      	 {/tidy}
        {if $ev.nonlu}</strong>{/if}
        </a>
      </td>
    </tr>
    {assign var="has_evts" value=true}
    {/iterate}
    {if !$has_evts}
    <tr>
      <td class="half">Aucun article actuellement</td>
    </tr>
    {/if}
  </table>

  {iterate item=ev from=$evenement}
  <br />

  <table class="bicol">
    <tr>
      <th>
        <div style="float:right">
          {if $smarty.session.perms eq 'admin'}
          <a href="admin/events/edit/{$ev.id}">{icon name=page_edit title="Editer cet article"}</a>
          {/if}
          <a href="events/read/{$ev.id}{if $previd}/newsid{$previd}{/if}">{icon name=cross title="Cacher cet article"}</a>
        </div>
        {assign var="previd" value=$ev.id}
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
        <p class="smaller"><a href="events#pagetop" style="display:block;float:right"><img alt="Sommaire" title="Remonter tout en haut" src="images/up.png"/></a>Annonce proposée par
        <a href="profile/{$ev.forlife}" class="popup2">
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
  <a href="events/submit">Proposer une information événementielle</a>
  </p>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
