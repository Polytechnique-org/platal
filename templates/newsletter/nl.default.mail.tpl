{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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

{config_load file="mails.conf" section="group_newsletter"}
{if $mail_part eq 'head'}
{from full=#from#}
{subject text=$issue->title(true)}
{if isset(#replyto#)}{add_header name='Reply-To' value=#replyto#}{/if}
{if isset(#retpath#)}{add_header name='Return-Path' value=#retpath#}{/if}
{elseif $mail_part eq 'text'}
{if !$is_mail}
<pre style="width : 72ex; margin: auto">
{/if}
====================================================================
{$issue->title()}
====================================================================

{$issue->head($user, 'text')}


{foreach from=$issue->arts key=cid item=arts name=cats}
{$smarty.foreach.cats.iteration} *{$issue->category($cid)}*
{foreach from=$arts item=art}
- {$art->title()}
{/foreach}

{/foreach}

{foreach from=$issue->arts key=cid item=arts}
--------------------------------------------------------------------
*{$issue->category($cid)}*
--------------------------------------------------------------------

{foreach from=$arts item=art}
{$art->toText($hash, $user->login())}

{/foreach}
{/foreach}

{$issue->signature('text')}

--------------------------------------------------------------------
Cette lettre est envoyée aux membres du groupe {$nl->group} par
l'intermédiaire de Polytechnique.org.

{if $is_mail}
archives         : <http://www.polytechnique.net/{$nl->prefix()}>
ne plus recevoir : <http://www.polytechnique.org/{$nl->prefix()}/out/nohash/{$issue->id}>
{else}
archives         : &lt;http://www.polytechnique.net/{$nl->prefix()}&gt;
ne plus recevoir : &lt;http://www.polytechnique.org/{$nl->prefix()}/out/nohash/{$issue->id}&gt;
{/if}
{if !$is_mail}
</pre>
{/if}
{elseif $mail_part eq 'html'}
{if $is_mail}
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>{$nl->name}</title>
    <style type="text/css">
      {literal}
      body      { background-color: #ddd; color: #000; }
      {/literal}
    <!--
      {$issue->css()}
    -->
    </style>
  </head>
  <body>
    <div class='nl_background'>
{/if}
    <div class='nl'>
      <div class="title" style="background-image: url({$globals->baseurl}/{$nl->group}/logo);"><a href="{$globals->baseurl}">{$issue->title()}</a></div>
      <div class="intro">{$issue->head($user, 'html')|smarty:nodefaults}</div>
      <a id="top_lnk"></a>
      {foreach from=$issue->arts key=cid item=arts name=cats}
      <div class="lnk">
        <a href="{$prefix}#cat{$cid}"><strong>{$smarty.foreach.cats.iteration}. {$issue->category($cid)}</strong></a><br />
        {foreach from=$arts item=art}
        <a href="{$prefix}#art{$art->aid}">&nbsp;&nbsp;- {$art->title()}</a><br />
        {/foreach}
      </div>
      {/foreach}

      {foreach from=$issue->arts key=cid item=arts name=cats}
      <h1 class="xorg_nl"><a id="cat{$cid}"></a>
        {$issue->category($cid)}
      </h1>
      {foreach from=$arts item=art}
        {$art->toHtml($hash, $user->login())|smarty:nodefaults}
        <div class="top_lnk"><a href="{$prefix}#top_lnk">Revenir au sommaire</a></div>
      {/foreach}
      {/foreach}
      <div class="signature">{$issue->signature('html')|smarty:nodefaults}</div>
      <div class="foot1">
        Cette lettre est envoyée à tous les membres du groupe {$nl->group}
        par l'intermédiaire de Polytechnique.org.
      </div>
      <div class="foot2">
        [<a href="http://www.polytechnique.net/{$nl->prefix()}">archives</a>&nbsp;|
         <a href="http://www.polytechnique.net/{$nl->prefix()}/out/nohash/{$issue->id}">ne plus recevoir</a>]
      </div>
      </div>
{if $is_mail}
    </div>
  </body>
</html>
{/if}
{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}

