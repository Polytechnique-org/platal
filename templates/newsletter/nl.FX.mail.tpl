{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2013 Polytechnique.org                             *}
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

{config_load file="mails.conf" section="mails_fx"}
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
{if $is_mail}
Pour faire un don : <http://fondationx.fr/fond/paiement.php>
{else}
Pour faire un don : &lt;http://fondationx.fr/fond/paiement.php&gt;
{/if}
Cette lettre est envoyée par la FX grâce aux outils de Polytechnique.org.

{if $is_mail}
archives         : <https://www.polytechnique.org/fxletter>
ne plus recevoir : <https://www.polytechnique.org/fxletter/out/{if $hash}{$hash}{else}nohash{/if}/{$issue->id}>
{else}
archives         : &lt;https://www.polytechnique.org/fxletter&gt;
ne plus recevoir : &lt;https://www.polytechnique.org/fxletter/out/{if $hash}{$hash}{else}nohash{/if}/{$issue->id}&gt;
{/if}

{if !$is_mail}
</pre>
{/if}
{elseif $mail_part eq 'html'}
{if $is_mail}
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Lettre d'information de la Fondation de l'École polytechnique</title>
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
    <div class="fx_background">
{/if}
    <div class='fx_mail'>
      <div class="title">{$issue->title()}</div>
      <div class="intro">{$issue->head($user, 'html')|smarty:nodefaults}</div>
      {foreach from=$issue->arts key=cid item=arts name=cats}
      <h1 class="category"><a id="cat{$cid}"></a>
        {$issue->category($cid)}
      </h1>
      {foreach from=$arts item=art}
        {$art->toHtml($hash, $user->login())|smarty:nodefaults}
      {/foreach}
      {/foreach}
      <div class="signature">{$issue->signature('html')|smarty:nodefaults}</div>
      <div class="give"><a href="http://fondationx.fr/fond/paiement.php">Faire un don</a></div>
      <div class="foot1">
        Cette lettre est envoyée par la Fondation grâce aux outils de Polytechnique.org.
      </div>
      <div class="foot2">
        [<a href="https://www.polytechnique.org/fxletter">archives</a>&nbsp;|
        <a href="https://www.polytechnique.org/fxletter/out/{if $hash}{$hash}{else}nohash{/if}/{$issue->id}">ne plus recevoir</a>]
      </div>
    </div>
{if $is_mail}
    </div>
  </body>
</html>
{/if}
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}