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

{if $ajaxdeco}
<?xml version="1.0" encoding="utf-8"?>
{/if}
<div id="medal_{$id}" style="clear: both; margin-top: 1em; height: 50px; vertical-align: middle">
  <div style="float: left; margin-right: 0.3em">
    <img src="profile/medal/{$id}" height="50" />
  </div>
  <div style="float: left; width: 70%">
    <div><b id="medal_name_{$id}"></b>
    {if !$medal.valid}(en attente de {if $ajaxdeco}validation{else}modération{/if}){/if}</div>
    <div id="medal_grade_{$id}"><input type="hidden" name="medals[{$id}][valid]" value="{$medal.valid}" /></div>
  </div>
  <a href="javascript:remove({$id})" style="vertical-align: middle">{icon name="cross" title="Supprimer cette médaille"}</a>
  {if !$ajaxdeco}
  <script type="text/javascript">
  getMedalName({$id});
  buildGrade({$id}, {$medal.grade});
  </script>
  {/if}
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
