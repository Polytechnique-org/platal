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

{if $ask_naissance}

{include file='include/form_naissance.tpl'}

{else}

<h1 id='pagetop'>
Bienvenue {$smarty.session.prenom}{if $birthday}
  &nbsp;et joyeux anniversaire de la part de toute l'équipe !
{else},
{/if}
</h1>

{if $smarty.session.host}
<div class="smaller">
  {if $birthday}T{else}t{/if}a connexion précédente date du
  <strong>{$smarty.session.lastlogin|date_format:"%x, %X"}</strong>
  depuis la machine <strong>{$smarty.session.host}</strong>.
</div>
{/if}

{if $smarty.session.no_redirect}
<p class="erreur">
  Tu n'as plus de redirection valide ce qui rend ton adresse Polytechnique.org
  inutilisable. Rends-toi au plus vite sur <a href="emails/redirect">la page de 
  gestion des emails</a> pour corriger ce problème.
</p>
{/if}

{if $smarty.session.mx_failures|@count}
<fieldset>
  <legend>{icon name=error}Des problèmes sont actuellement recontrés sur tes redirections suivantes</legend>
  {foreach from=$smarty.session.mx_failures item=mail}
  <div>
    <span class="erreur">{$mail.mail}</span>
    <div class="explication">{$mail.text}</div>
  </div>
{/foreach}
</fieldset>
{/if}

  
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

{include file="include/tips.tpl" full=true}
  
  <table class="tinybicol" id="menu-evts">
    {foreach from=$events name=events key=category item=evenement}
    <tr class="pair" style="height: 18px">
      <td class="half titre" style="height: 18px; padding-top: 1px; padding-bottom: 1px;">
        {if $smarty.foreach.events.first}
        {if $smarty.session.core_rss_hash}
        <a href='rss/{$smarty.session.forlife}/{$smarty.session.core_rss_hash}/rss.xml' style="display:block;float:right">
          {icon name=feed title='fil rss'}
        </a>
        {else}
        <a href='prefs/rss?referer=events'  style="display:block;float:right">
          {icon name=feed_add title='Activer mon fil rss'}
        </a>
        {/if}
        {/if}
        {if $category eq 'important'}
          {icon name=error} Informations prioritaires&nbsp;:
        {elseif $category eq 'news'}
          {icon name=bell} Nouvelles annonces&nbsp;:
        {elseif $category eq 'end'}
          {icon name=clock} Dernières minutes&nbsp;:
        {else}
          {icon name=magnifier} Mais encore...
        {/if}
      </td>
    </tr>
    {iterate item=ev from=$evenement.summary}
    <tr class="impair">
      <td class="half">
        &bull;
        <a href="events{if !$ev.nonlu}/unread/{$ev.id}{else}#newsid{$ev.id}{/if}" id="link-evt{$ev.id}">
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
    {/foreach}
    {if !$has_evts}
    <tr>
      <td class="half">Aucun article actuellement</td>
    </tr>
    {/if}
  </table>

  <script type="text/javascript">
  {literal}
  <!--
  function readEvent(id) {
  	document.getElementById('content-evt'+id).style.display='none';
  	var link = document.getElementById('link-evt'+id);
  	link.setAttribute('href','events/unread/'+id);
  	for (var i=0; i < link.childNodes.length; i++)
  	if (link.childNodes[i].nodeName == 'STRONG') {
  		link.replaceChild(link.childNodes[i].firstChild,link.childNodes[i]);
  	}
  	Ajax.update_html(null, 'events/read/'+id);
  	return false;
  }
  -->
  {/literal}
  </script>
 
  {foreach from=$events key=category item=evenement}
  {iterate item=ev from=$evenement.events}
  <div id="content-evt{$ev.id}">
  <br />

  <table class="bicol">
    <tr>
      <th>
        <div style="float: left">
          {if $category eq 'important'}
            {icon name=error title="Important"}
          {elseif $category eq 'news'}
            {icon name=bell title="Nouvelle annonce"}
          {elseif $category eq 'end'}
            {icon name=clock title="Bientôt fini"}
          {else}
            {icon name=magnifier title="Annonce"}
          {/if}
        </div>
        <div style="float:right">
          {if hasPerm('admin')}
          <a href="admin/events/edit/{$ev.id}">{icon name=page_edit title="Editer cet article"}</a>
          {/if}
          <a href="events/read/{$ev.id}{if $previd}/newsid{$previd}{/if}" onclick="return readEvent('{$ev.id}')">{icon name=cross title="Cacher cet article"}</a>
        </div>
        {assign var="previd" value=$ev.id}
        <a id="newsid{$ev.id}"></a>
	 {tidy}
	   {$ev.titre|nl2br}
	 {/tidy}
      </th>
    </tr>
    {cycle values="left,right" assign=position}
    <tr class="impair">
      <td class="half">
        <div>
          {if $ev.img}
          <div style="float: {$position}; padding-{if $position eq right}left{else}right{/if}: 0.5em">
            <img src="events/photo/{$ev.id}" alt="{$ev.title}" />
          </div>
          {/if}
          <div style="text-align: justify">
            {if !$ev.wiki}
            {$ev.texte|smarty:nodefaults|nl2br}
            {else}
            {$ev.texte|miniwiki|smarty:nodefaults}
            {/if}
          </div>
        </div>
      </td>
    </tr>
    <tr class="pair">
      <td class="half smaller">
        <div style="display:block; float: right; padding-left:1em">
          {if $ev.post_id}
          <a href="banana/{#globals.banana.event_reply#|default:#globals.banana.event_forum#}/read/{$ev.post_id}">
            {icon name="comments" title="Discussion"}Suivre la discussion
          </a> &bull;
          {/if}
          <a href="events#pagetop">
            <img alt="Sommaire" title="Remonter tout en haut" src="images/up.png"/>
          </a>
        </div>
        Annonce proposée par
        <a href="profile/{$ev.forlife}" class="popup2">
          {$ev.prenom} {$ev.nom} X{$ev.promo}
        </a>
      </td>
    </tr>
  </table>
  </div>
  {/iterate}
  {/foreach}

  <p class="smaller">
  Nota Bene : les informations présentées ici n'engagent que leurs auteurs
  respectifs et sont publiées à leur initiative. L'association Polytechnique.org
  ne pourrait en aucun cas être tenue responsable de la nature des propos relatés
  sur cet espace d'expression et d'information. Elle se réserve le droit de
  refuser ou de retirer toute information de nature diffamante ou pouvant être
  interprétée comme polémique par un membre de la communauté polytechnicienne.
  </p>

  <p class="center">
    {icon name=page_edit}&nbsp;
    <a href="events/submit">Proposer une information événementielle</a>&nbsp;&bull;
    <a href="nl/submit">Proposer un article pour la Lettre mensuelle</a>
  </p>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
