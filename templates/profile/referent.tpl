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


{if $plset_count}
{include file="core/plset.tpl"}
{else}
<h1> 
  Rechercher un camarade pouvant m'aider à orienter mon parcours professionnel 
</h1> 

{if $recherche_trop_large} 
<p> 
Les critères de recherche que tu as rentrés n'ont pas produit de résultats, 
sans doute car ta requête était trop générale.
</p> 
{else}
<p class="erreur">
  Si tu utilises ce service pour la première fois, lis attentivement le texte
  qui suit.
</p>
<p>
En <a href="profile/edit">renseignant sa fiche dans l'annuaire</a>, chacun
d'entre nous a la possibilité de renseigner, dans la section "Mentoring",
s'il accepte de recevoir des messages de la part de camarades qui pourraient
souhaiter lui poser quelques questions et recevoir quelques conseils.<br />
Ces informations sont rentrées par chacun sur la base du volontariat et sont
totalement déclaratives. Chaque X qui complète cette rubrique accepte alors
de recevoir un courrier électronique des jeunes camarades qui sont en train
de bâtir leur projet professionnel, mais aussi des moins jeunes qui cherchent
à réorienter leur carrière. Bien entendu, chacun se réserve le droit de ne
pas donner suite à une sollicitation !<br />
Pour que ce système soit profitable, il est nécessaire que dans ta recherche
de conseils professionnels, tu agisses sagement, en évitant de contacter
un trop grand nombre de camarades. De même, pense bien que les quelques
personnes que tu vas éventuellement contacter suite à ta recherche
accepteront éventuellement de t'aider et de te guider <strong>sur la base du
  volontariat</strong>. Il va de soi que plus ton comportement lors de votre
contact sera éthique et reconnaissant, plus cette pratique de conseil
inter-générations sera encouragée et bien perçue par ceux qui la pratiquent.
<br />
Nous avons peiné à trouver un nom pour désigner ceux qui sont volontaires
pour guider les camarades qui en ressentent le besoin : nous avons finalement
retenu le terme de <em>mentors</em> pour désigner ceux qui sont prêts à aider de
manière suivie un camarade plus jeune, à plusieurs moments de sa carrière,
et avons appelé <em>référents</em> ceux qui s'impliquent plutôt en tant que
"relai d'informations", dans le sens où ils ont vécu des expériences
professionnelles susceptibles d'intéresser certains d'entre nous (expérience
de stage ou d'emploi à l'étranger), sans forcément souhaiter consacrer
autant de temps à quelqu'un que le ferait un mentor attentionné.
La recherche proposée ici permet de trouver les deux types d'aide.
</p>

<p>
Dans le formulaire ci-dessous, tu peux rechercher des avis en fonction des
domaines de compétence recherchés, des cultures professionnelles des pays
connues par le référent, et enfin une dernière case te permettra de faire
une recherche par mots-clefs.<br />
Nous t'incitons à prendre plutôt 2 ou 3 contacts qu'un seul, cela te
permettant certainement d'avoir une vision des choses plus complète.
</p>
{/if}
{/if}

<p>
Actuellement, {$mentors_number} mentors et référents se sont déclarés sur {#globals.core.sitename#}.
</p>

{javascript name=ajax}
<script type="text/javascript">//<![CDATA[

var baseurl = "{#globals.baseurl#}/referent/";
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
