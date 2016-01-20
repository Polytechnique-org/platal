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

<div>
  Plat/al <a href="changelog">{#globals.version#}</a> - Copyright © 1999-2016 <a href="http://x-org.polytechnique.org/">Polytechnique.org</a>
  &nbsp;-&nbsp;
  <a href="Reference/Convention-AX">Lien avec l'AX</a>
  &nbsp;-&nbsp;
  <a href="Equipe/APropos">À propos de ce site et ses équipes</a>
  {if hasPerm('payment')}
  &nbsp;-&nbsp;
  <a href="payment">Faire un don</a>
  {/if}
<br />
  <a href="Docs/Ethique">Services et éthique</a>
  | <a href="Reference/Charte">Charte</a>
{if $smarty.session.auth ge AUTH_COOKIE}
  | <a href="stats/coupures">Disponibilité</a>
  | <a href="stats">Statistiques</a>
{/if}
</div>
<div class="pem">
  <a href="{$globals->baseurl}/pem/{$platal->pl_self()|replace:'/':'_'}/200">Liste1</a>
  <a href="{$globals->baseurl}/pem/{$platal->pl_self()|replace:'/':'_'}/400">Liste2</a>
  <!--
  {poison count=20}
  -->
</div>

<script type="text/javascript">//<![CDATA[
  {literal}
  (function($) {
    $.extend({
      xsrf_token: {/literal}'{$smarty.session.xsrf_token}'{literal}
    });
  }(jQuery));
  {/literal}
//]]></script>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
