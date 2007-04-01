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

<script type="text/javascript">
{literal}
function ficheXorg(id)
{
  window.open('{/literal}{if $no_annu}https://{#globals.core.secure_domain#}/{/if}{literal}profile/'+id,'_blank','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=840,height=600');
}
{/literal}
{if !$no_annu}
{literal}
function clickOnCity(id)
{
	var f = document.getElementById('search_form');
	var url = f.action;
	f.action += unescape('%26')+'cityid=' + id; 
	f.submit();
	f.action = url;
	document.getElementById('search_results').style.height = '250px';
}
var mapid = 0;
function goToCountry(id)
{
	mapid = id;
}
function searchMapId(f)
{
	var url = f.action;
	f.action += unescape('%26')+'mapid=' + mapid; 
	f.submit();
	f.action = url;
	document.getElementById('search_results').style.height = '250px';
}
{/literal}
{/if}

</script>

{if !$request_geodesix}
  
  {if $smarty.request.only_current neq 'on'}
  <p class="center">
    [<a href="{$plset_base}/geoloc{$search_nourlencode|smarty:nodefaults}&amp;only_current=on">Ne voir que les adresses principales</a>]
  </p>
  {/if}
  
  <p class="center">
  <object
    classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
    codebase="{$protocole}://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0"
    width="600"
    height="450"
    align="middle">
      <param name="movie" value="{$plset_base}/geoloc/dynamap.swf"/>
      <param name="bgcolor" value="#ffffff"/>
      <param name="wmode" value="opaque"/>
      <param name="quality" value="high"/>
      <param name="flashvars" value="initfile={$plset_base|urlencode}%2Fgeoloc%2Finit{$search|smarty:nodefaults}"/>
      <embed
        src="{$plset_base}/geoloc/dynamap.swf"
        quality="high"
        bgcolor="#ffffff"
        width="600"
        height="450"
        name="dynamap"
        id="dynamap"
        align="middle"
        flashvars="initfile={$plset_base|urlencode}%2Fgeoloc%2Finit{$search|smarty:nodefaults}"
        type="application/x-shockwave-flash"
        menu="false"
        wmode="opaque"
        salign="tl"
        pluginspage="{$protocole}://www.macromedia.com/go/getflashplayer"/>
    </object>
  </p>
  <p class="smaller">Carte fournie gracieusement par <a href="http://www.geodesix.com/">Geodesix</a>.</p>
{else}
  <p>Le moteur de carte n'a pas été installé sur cette version de plat/al. Veuillez contacter <a href="http://www.geodesix.com/">Geodesix</a>.</p>
{/if} 
<p class="descr">Pour toute question, problème ou suggestion tu peux envoyer un mail à <a href="mailto:geoloc@staff.polytechnique.org">geoloc@staff.polytechnique.org</a></p>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
