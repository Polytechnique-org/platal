{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

<script type="text/javascript">//<![CDATA[
{literal}
function ficheXorg(id)
{
  window.open('{/literal}{if !$annu}https://{#globals.core.secure_domain#}/{/if}{literal}profile/'+id,'_blank','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=840,height=600');
}
{/literal}
{if $annu}
var search_url = platal_baseurl + "{$platal->ns}{$annu}{$plset_search|smarty:nodefaults}";
{literal}
function clickOnCity(id)
{
    window.open(search_url + unescape('%26')+'cityid=' + id, '_blank', '');
}
var mapid = 0;
function goToCountry(id)
{
    mapid = id;
}
function searchMapId()
{
    window.open(search_url + unescape('%26')+'mapid=' + mapid, '_blank', '');
}
{/literal}
{/if}

//]]></script>

{if !$request_geodesix}

  <p class="center">
  {if $smarty.request.only_current neq 'on'}
    [<a href="{$platal->ns}{$plset_base}/geoloc{$plset_search}only_current=on">Ne voir que les adresses principales</a>]
  {else}
    [<a href="{$platal->ns}{$plset_base}">Voir toutes les adresses</a>]
  {/if}
  </p>

  <p class="center">
  <object
    type="application/x-shockwave-flash"
    data="{$platal->ns}{$plset_base}/geoloc/dynamap.swf"
    width="600"
    height="450">
      <param name="movie" value="{$platal->ns}{$plset_base}/geoloc/dynamap.swf"/>
      <param name="wmode" value="transparent"/>
      <param name="flashvars" value="initfile=init{$plset_search_enc}"/>
    </object>
  </p>
  {if $annu}
  <p class="center">
    <a href="javascript:searchMapId()">Lister les X présents sur cette carte</a>
  </p>
  {/if}
  <p class="smaller">Carte fournie gracieusement par <a href="http://www.geodesix.com/">Geodesix</a>.</p>
{else}
  <p>Le moteur de carte n'a pas été installé sur cette version de plat/al. Veuillez contacter <a href="http://www.geodesix.com/">Geodesix</a>.</p>
{/if} 
<p class="descr">Pour toute question, problème ou suggestion tu peux envoyer un mail à 
<a href="mailto:{#globals.geoloc.email#}">{#globals.geoloc.email#}</a>.</p>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
