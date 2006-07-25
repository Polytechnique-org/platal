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

<h1>Géolocalisation</h1>

<script type="text/javascript">
{literal}
function ficheXorg(id)
{
  window.open('{/literal}{if $no_annu}https://www.polytechnique.org/{/if}{literal}profile/'+id,'_blank','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=840,height=600');
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
{if $localises}
  <p class="descr">
    Aujourd'hui {$localises} de nos camarades sont localisés grâce à leurs adresses personnelles.
  </p>
{/if}
{if !$request_geodesix}
  <p class="center">
    <embed
      src="{$platal->ns}geoloc/dynamap.swf"
      quality="high"
      bgcolor="#ffffff"
      width="600"
      height="450"
      name="dynamap"
      id="dynamap"
      align="middle"
      flashvars="initfile={$platal->ns}geoloc%2Finit{$search}"
      type="application/x-shockwave-flash"
      menu="false"
      wmode="opaque"
      salign="tl"
      pluginspage="{$protocole}://www.macromedia.com/go/getflashplayer" />
  </p>
  <p class="smaller">Carte fournie gracieusement par <a href="http://www.geodesix.com/">Geodesix</a>.</p>
  {if !$no_annu}
    <form id="search_form" action="search/adv/?{$dynamap_vars|default:"only_current=on"}&amp;rechercher=1" method="post">
    <p>
    	<input type="button" value="Lister les camarades de la carte ci-dessus" onclick="searchMapId(this.form)"/>
    </p>
    </form>
  {/if}
{else}
  <p>Le moteur de carte n'a pas été installé sur cette version de plat/al. Veuillez contacter <a href="http://www.geodesix.com/">Geodesix</a>.</p>
{/if} 
<p class="descr">Pour toute question, problème ou suggestion tu peux envoyer un mail à <a href="mailto:contact+geoloc@polytechnique.org">contact+geoloc@polytechnique.org</a></p>

{* vim:set et sw=2 sts=2 sws=2: *}
