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
{javascript name="jquery"}
<script type="text/javascript">//<![CDATA[
  {if ($stateless || $id gt $current) && $wiz_ajax}
  {literal}
  function changePage(obj, id)
  {
    var myUrl = obj.href;
    $.ajax({ url: myUrl + "/ajax",
             global: false,
             dataTye: 'html',
             error: function(request, error) {
                      document.location = myUrl;
                    },
             success: function(data) {
                        $(".wiz_content").fadeOut('normal',
                            function() {
                              $(".wiz_tab").removeClass("active");
                              $("#wiz_tab_" + id).addClass("active");
                              $(".wiz_content").html(data).fadeIn('normal');
                            });
                      }
          });
    return false;
  }
  {/literal}
  {else}
  {literal}
  function changePage(obj)
  {
    return true;
  }
  {/literal}
  {/if}
//]]></script>

<div class="wizard" style="clear: both">
  <div class="wiz_header">
    {foreach from=$pages item=title key=id}
    {if $stateless || $id gt $current}
    {assign var=tag value="a"}
    {else}
    {assign var=tag value="div"}
    {/if}
    <{$tag} class="wiz_tab {if $id eq $current}active{/if} {if !$stateless && $current gt $id}grayed{/if}"
            style="display: block; float: left; width: {$tab_width}%; vertical-align: middle"
            id="wiz_tab_{$lookup[$id]}"
            {if $tag eq "a"}
            href="{$wiz_baseurl}/{$lookup[$id]}"
            onclick="return changePage(this, '{$lookup[$id]}')"
            {/if}
            >
      <span style="vertical-align: middle">{$title}</span>
    </{$tag}>
    {/foreach}
    <div style="clear: both"></div>
  </div>
  <div class="wiz_content" style="clear: both">
    {foreach from=$xorg_errors item=err}
    <div class="erreur">{$err|smarty:nodefaults}</div>
    {/foreach}
    {include file=$wiz_page}
  </div>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
