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
{if $article->photo}
<div style="float: left; padding-right: 0.5em">
  <img src="{#globals.baseurl#}/events/photo/{$article->id}" alt="{$article->title|htmlentities}" />
</div>
{/if}
<div>{if $article->wiki}{$article->texte|miniwiki}{else}{$article->texte}{/if}</div>
{if $article->post_id neq -1}
<div style="clear: both">
  <br />
  <a href="{#globals.baseurl#}/banana/{#globals.banana.event_reply#|default:#globals.banana.event_forum#}/read/{$article->post_id}">
  {icon name=comments full=true} Suivre la discussion
  </a>
</div>
{/if}
{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
