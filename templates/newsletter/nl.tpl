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

{config_load file="mails.conf" section="newsletter"}
{if $mail_part eq 'head'}
{from full=#from#}
{subject text=$nl->title(true)}
{if isset(#replyto#)}{add_header name='Reply-To' value=#replyto#}{/if}
{if isset(#retpath#)}{add_header name='Return-Path' value=#retpath#}{/if}
{elseif $mail_part eq 'text'}
{if !$is_mail}
<pre style="width : 72ex; margin: auto">
{/if}
====================================================================
{$nl->title()}
====================================================================

{$nl->head($prenom, $nom, $sexe, 'text')}


{foreach from=$nl->_arts key=cid item=arts name=cats}
{$smarty.foreach.cats.iteration} *{$nl->_cats[$cid]}*
{foreach from=$arts item=art}
- {$art->title()}
{/foreach}

{/foreach}

{foreach from=$nl->_arts key=cid item=arts}
--------------------------------------------------------------------
*{$nl->_cats[$cid]}*
--------------------------------------------------------------------

{foreach from=$arts item=art}
{$art->toText()}

{/foreach}
{/foreach}

--------------------------------------------------------------------
Cette lettre est envoyée à tous les Polytechniciens sur Internet par
l'intermédiaire de Polytechnique.org.

archives         : [https://www.polytechnique.org/nl]
écrire           : [https://www.polytechnique.org/nl/submit]
ne plus recevoir : [https://www.polytechnique.org/nl/out]

{if !$is_mail}
</pre>
{/if}
{elseif $mail_part eq 'html'}
{if $is_mail}
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> 
  <head>  
    <title>Lettre mensuelle de Polytechnique.org</title>
    <style type="text/css">
      {literal}
      body      { background-color: #ddd; color: #000; }
      {/literal}
    <!--
      {$nl->css()}
    -->
    </style>
  </head>
  <body>
    <div class='nl_background'>
{/if}
    <div class='nl'>
      <div class="title">{$nl->title()}</div>
      <div class="intro">{$nl->head($prenom, $nom, $sexe, 'html')|smarty:nodefaults}</div>
      <a id="top_lnk"></a>
      {foreach from=$nl->_arts key=cid item=arts name=cats}
      <div class="lnk">
        <a href="{$prefix}#cat{$cid}"><strong>{$smarty.foreach.cats.iteration}. {$nl->_cats[$cid]}</strong></a><br />
        {foreach from=$arts item=art}
        <a href="{$prefix}#art{$art->_aid}">&nbsp;&nbsp;- {$art->title()}</a><br />
        {/foreach}
      </div>
      {/foreach}

      {foreach from=$nl->_arts key=cid item=arts name=cats}
      <h1 class="xorg_nl"><a id="cat{$cid}"></a>
        {$nl->_cats[$cid]}
      </h1>
      {foreach from=$arts item=art}
        {$art->toHtml()|smarty:nodefaults}
        <div class="top_lnk"><a href="{$prefix}#top_lnk">Revenir au sommaire</a></div>
      {/foreach}
      {/foreach}
      <div class="foot1">
        Cette lettre est envoyée à tous les Polytechniciens sur Internet par l'intermédiaire de Polytechnique.org.
      </div>
      <div class="foot2">
        [<a href="https://www.polytechnique.org/nl">archives</a>&nbsp;|
         <a href="https://www.polytechnique.org/nl/submit">écrire dans la NL</a>&nbsp;|
         <a href="https://www.polytechnique.org/nl/out">ne plus recevoir</a>]
      </div>
      </div>
{if $is_mail}
    </div>
  </body>
</html>
{/if}
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
