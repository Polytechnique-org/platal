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


{if $formulaire eq 0 and !$xorg_errors|count}
  {if !$simple}
    {if !$advanced}
    {include file=search/quick.form.tpl show_js=1}
    {else}
    {include file=search/adv.links.tpl do_title=1 with_soundex=$with_soundex}
    {/if}
  {/if}
  
  {include file='core/plset.tpl'}

  {if $search_pages_nb > 1}
  <p>
    {foreach from=$search_pages_link item=l}
    {if $l.i eq $search_page}
    <span class="erreur">{$l.text}</span>
    {else}
    <a href="{$l.u}">{$l.text}</a>
    {/if}
    {/foreach}
  </p>
  {/if}

  {if $search_results_nb eq 1}{literal}
    <script type="text/javascript">
      // popup automatically if only one result
      var alinks = document.getElementById('content').getElementsByTagName('a');
      for (i = 0; i < alinks.length; i++) {
        if (alinks[i].className == 'popup2') {
          popWin(alinks[i], 840, 600);
          break;
        }
      }
    </script>
  {/literal}{/if}

  {if $smarty.session.auth ge AUTH_COOKIE}
  <p class="noprint">
    <strong>{icon name=lightbulb title=Astuce}Astuce&nbsp;:</strong>
    {if $search_results_nb}
    Si tu survoles une fiche, tu sauras quand elle a été mise à jour la dernière fois&nbsp;!
    {elseif $advanced && $with_soundex && ($smarty.request.name || $smarty.request.firstname)}
    Si tu n'es pas sûr de l'orthographe d'un nom, tu peux essayer la <a href="{$with_soundex}">recherche par
    proximité sonore</a>.
    {elseif $advanced}
    Essaye d'élargir tes critères de recherche.
    {elseif $smarty.session.auth ge AUTH_COOKIE}
    Essaye la <a href="search/adv">recherche avancée</a>.
    {else}
    Pour les X inscrits à Polytechnique.org, un module de recherche avancée est disponible permettant de réaliser
    des recherches fines dans l'annuaire. Si vous êtes un X et que vous n'êtes pas encore inscrit, commencez dès
    maintenant la <a href="register">procédure</a>.
    {/if}
  </p>
  {/if}
{else}
  {if $advanced}
  {include file=search/adv.form.tpl}
  {else}
  {include file=search/quick.tpl}
  {/if}
{/if}


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
