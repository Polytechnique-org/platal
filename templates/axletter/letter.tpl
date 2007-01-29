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

{if !$html_version}
{if $is_mail}
{config_load file="mails.conf" section="mails_ax"}
{from full=#from#}
{subject text=$am->title(true)}
{if isset(#replyto#)}{add_header name='Reply-To' value=#replyto#}{/if}
{if isset(#retpath#)}{add_header name='Return-Path' value=#retpath#}{/if}
{else}
<pre style="width : 72ex; margin: auto">
{/if}
====================================================================
{$am->title()}
====================================================================

{$am->head($prenom, $nom, $sexe, 'text')}

{$am->body('text')}

{$am->signature('text')}

--------------------------------------------------------------------
Cette lettre est envoyée par l'AX grâce aux outils de Polytechnique.org.

archives         : [https://www.polytechnique.org/ax]
ne plus recevoir : [https://www.polytechnique.org/ax/out{if $hash}/{$hash}{/if}]

{if !$is_mail}
</pre>
{/if}
{else}
{if $is_mail}
<?xml version="1.0" encoding="iso-8859-15"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> 
  <head>  
    <title>Lettre d'information de l'AX</title>
    <style type="text/css">
    <!--
      {$am->css()}
    -->
    </style>
  </head>
  <body>
{/if}
    <div class='ax_mail'>
    <div class='ax_text'>
      <div class="title">{$am->title()}</div>
      <div class="intro">{$am->head($prenom, $nom, $sexe, 'html')|smarty:nodefaults}</div>
      <div class="body">{$am->body('html')|smarty:nodefaults}</div>
      <div class="signature">{$am->signature('html')|smarty:nodefaults}</div>
      <div class="foot1">
        Cette lettre est envoyée par l'AX grâce aux outils de Polytechnique.org.
      </div>
      <div class="foot2">
        [<a href="https://www.polytechnique.org/ax">archives</a>&nbsp;|
        <a href="https://www.polytechnique.org/ax/out{if $hash}/{$hash}{/if}">ne plus recevoir</a>]
      </div>
    </div>
    </div>
{if $is_mail}
  </body>
</html>
{/if}
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
