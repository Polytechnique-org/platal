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

<script type="text/javascript">
{literal}
function xStateChange(box)
{
    var state = (box.checked ? '' : 'none');
    document.getElementById('xnom').style.display = state;
    document.getElementById('xprenom').style.display = state;
    document.getElementById('xpromo').style.display = state;
    document.getElementById('xsearch').style.display = state;
}

var nom;
var prenom;
var promo;
function searchX()
{
    if (document.getElementById('nom').value == nom
       && document.getElementById('prenom').value == prenom
       && document.getElementById('promo').value == promo) {
       return;
    }
    var nom = document.getElementById('nom').value;
    var prenom = document.getElementById('prenom').value;
    var promo = document.getElementById('promo').value;
    Ajax.update_html('xsearch',
      '{/literal}{$platal->ns}{literal}member/new/ajax?prenom=' + prenom + '&nom=' + nom + '&promo=' + promo);
}
{/literal}
</script>

<h1>{$asso.nom} : Ajout d'un membre</h1>

<form method="post" action="{$platal->ns}member/new/">
  <ul class='descr'>
    <li>
      Pour ajouter un X dans ton groupe, il suffit d'entrer ici une de ses
      adresses mail @polytechnique.org. Si il n'est pas inscrit à Polytechnique.org
      coche la case qui se trouve sous le formulaire et indique ses noms, prénoms et
      promotions.
    </li>
    <li>
      Pour ajouter un extérieur dans ton groupe, il suffit d'entrer ici son
      adresse mail, tu seras ensuite redirigé vers une page te permettant
      d'éditer son profil (nom, prenom, ...)
    </li>
  </ul>
  <table class="tinybicol">
    <tr>
      <td class="center" colspan="2">
        <input type="text" name="email" size="40" value="{$platal->argv[1]}" />
        <input type='submit' value='Ajouter'
          onclick='this.form.action += this.form.email.value' />
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <input type="checkbox" name="x" onchange="xStateChange(this);" />
        Coche cette case si il s'agit d'un X non inscrit à Polytechnique.org
      </td>
    </tr>
    <tr id="xnom" style="display: none">
      <td class="titre">Nom :</td>
      <td><input type="text" id="nom" name="nom" size="20" value="" onkeyup="searchX();" /></td>
    </tr>
    <tr id="xprenom" style="display: none">
      <td class="titre">Prénom :</td>
      <td><input type="text" id="prenom" name="prenom" size="20" value="" onkeyup="searchX();" /></td>
    </tr>
    <tr id="xpromo" style="display: none">
      <td class="titre">Promotion :</td>
      <td><input type="text" id="promo" name="promo" size="4" value="" onkeyup="searchX();" /></td>
    </tr>
    <tr id="xsearch" style="display: none" class="pair">
      {include file="xnet/groupe/membres-new-search.tpl"}
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
