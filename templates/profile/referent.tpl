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


{if $plset_count}
{include file="core/plset.tpl"}
{else}
{include wiki=Docs.Emploi}
{/if}

<p>
Actuellement, {$mentors_number} mentors et référents se sont déclarés sur {#globals.core.sitename#}.
</p>

{javascript name=ajax}
<script type="text/javascript">//<![CDATA[

var baseurl = platal_baseurl + "referent/";
{literal}
var Ajax2 = new AjaxEngine();

function setSecteur(secteur)
{
    if (secteur == '') {
        document.getElementById('scat').style.display = 'none';
        document.getElementById('country').style.display = 'none';
        document.getElementById('keywords').style.display = 'none';
    } else {
        Ajax.update_html('ssect_chg', baseurl + 'ssect/' + secteur);
        Ajax2.update_html('country_chg', baseurl + 'country/' + secteur);
        document.getElementById('scat').style.display = ''; 
        document.getElementById('country').style.display = ''; 
        document.getElementById('keywords').style.display = ''; 
    }
}

function setSSecteurs()
{
    var sect  = document.getElementById('sect_field').value;
    var ssect = document.getElementById('ssect_field').value;
    Ajax2.update_html('country_chg', baseurl + 'country/' + sect + '/' + ssect);
}

{/literal}
//]]></script>

<form action="{$smarty.server.REQUEST_URI}" method="get">
  <table cellpadding="0" cellspacing="0" summary="Formulaire de recherche de referents" class="bicol">
    <tr class="impair">
      <td class="titre">
        Secteur de compétence <br /> du référent
      </td>
      <td>
        <select name="secteur" id="sect_field" onchange="setSecteur(this.value)">
          {html_options options=$secteurs selected=$secteur_sel}
        </select>
      </td>
    </tr>
    <tr class="impair" style="display: none" id="scat">
      <td class="titre">
        Sous-Secteur
      </td>
      <td id="ssect_chg">
      </td>
    </tr>
    <tr class="pair" style="display: none" id="country">
      <td class="titre">
        Pays bien connu du référent
      </td>
      <td id="country_chg">
      </td>
    </tr>
    <tr class="impair" style="display: none" id="keywords">
      <td class="titre">
        Expertise (rentre un ou plusieurs mots clés)
      </td>
      <td >
        <input type="text" name="expertise" size="30" value="{$expertise_champ}" />
      </td>
    </tr>
  </table>
  <div class="center" style="margin-top: 1em;">
    <input type="submit" value="Chercher" name="Chercher" />
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
