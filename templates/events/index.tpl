{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

{if t($ask_naissance)}

{include file='include/form_naissance.tpl'}

{else}

<h1 id='pagetop'>
Bienvenue {$smarty.session.user->displayName()}{if t($birthday)}
  &nbsp;et joyeux anniversaire de la part de toute l'équipe&nbsp;!
{else},
{/if}
</h1>

{if $smarty.session.user->host}
<div class="smaller">
  {if t($birthday)}T{else}t{/if}a connexion précédente date du
  <strong>{$smarty.session.user->lastlogin|date_format:"%x, %X"}</strong>
  depuis la machine <strong>{$smarty.session.user->host}</strong>.
</div>
{/if}

{include file="include/migration.msg.tpl"}

{if t($reminder)}
{include file="reminder/base.tpl"}
{else}
{include file="include/tips.tpl" full=true}
{/if}

  <table class="tinybicol" id="menu-evts">
    {foreach from=$events name=events key=category item=evenement}
    <tr class="pair" style="height: 18px">
      <td class="half titre" style="height: 18px; padding-top: 1px; padding-bottom: 1px;">
        {if $smarty.foreach.events.first}
        {if $smarty.session.user->token}
        <a href="rss/{$smarty.session.hruid}/{$smarty.session.user->token}/rss.xml" style="display:block;float:right" title="Annonces">
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
          {icon name=magnifier} Mais encore&hellip;
        {/if}
      </td>
    </tr>
    {foreach item=ev from=$evenement}
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
    {/foreach}
    {/foreach}
    {if !$has_evts}
    <tr>
      <td class="half">
        {if $smarty.session.user->token}
        <a href="rss/{$smarty.session.hruid}/{$smarty.session.user->token}/rss.xml" style="display:block;float:right" title="Annonces">
          {icon name=feed title='fil rss'}
        </a>
        {else}
        <a href='prefs/rss?referer=events'  style="display:block;float:right">
          {icon name=feed_add title='Activer mon fil rss'}
        </a>
        {/if}
        Aucun article actuellement
      </td>
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
    $.xget('events/read/'+id);
  	return false;
  }
  -->
  {/literal}
  </script>

  {foreach from=$events key=category item=evenement}
  {foreach item=ev from=$evenement}
  {if $ev.nonlu}
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
          <a href="events/read/{$ev.id}{if t($previd)}/newsid{$previd}{/if}" onclick="return readEvent('{$ev.id}')">{icon name=cross title="Cacher cet article"}</a>
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
          <div style="float: {$position}; padding-{if $position eq 'right'}left{else}right{/if}: 0.5em">
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
          <a href="events#pagetop">
            <img alt="Sommaire" title="Remonter tout en haut" src="images/up.png"/>
          </a>
        </div>
        Annonce proposée par {profile user=$ev.uid sex=false promo=true}
      </td>
    </tr>
  </table>
  </div>
  {/if}
  {/foreach}
  {/foreach}

  <p class="smaller">
  Nota Bene&nbsp;: les informations présentées ici n'engagent que leurs auteurs
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

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
