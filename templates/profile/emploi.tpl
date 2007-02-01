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


{section name=adresses_pro loop=2}
{assign var='i' value=$smarty.section.adresses_pro.index} 

<div class="blocunite{if !$i}tab{/if}">

  <table class="bicol" cellspacing="0" cellpadding="0" summary="Profil: Informations professionnelles - Entreprise n°{$i+1}">
    <tr>
      <th colspan="2">
        Informations professionnelles - Entreprise n°{$i+1}
      </th>
    </tr>
    {include file=include/flags.radio.tpl name="pubpro[$i]" val=$pubpro.$i}
    <tr>
      <td class="colg">
        <span class="titre">Entreprise ou organisme</span>
      </td>
      <td class="cold">
        <input type="text" size="35" maxlength="100" name="entreprise[{$i}]"
        value="{$entreprise.$i}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Secteur d'activité</span>
      </td>
      <td class="cold">
        <select name="secteur[{$i}]" onchange="this.form.submit();">
          {select_secteur secteur=$secteur.$i}
        </select>
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Sous-Secteur d'activité</span>
      </td>
      <td class="cold">
        <select name="ss_secteur[{$i}]">
          {select_ss_secteur secteur=$secteur.$i ss_secteur=$ss_secteur.$i}
        </select>
      </td> 
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Poste occupé</span>
      </td>
      <td class="cold">
        <input type="text" size="35" maxlength="120" name="poste[{$i}]"
        value="{$poste.$i}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Fonction occupée</span>
      </td>
      <td class="cold">
        <select name="fonction[{$i}]">
          {select_fonction fonction=$fonction.$i}
        </select>
      </td>
    </tr>
    {include file=include/flags.radio.tpl name="adr_pubpro[$i]" val=$adr_pubpro.$i}
    <tr>
      <td class="colg">
        <span class="titre">Adresse professionnelle</span>
      </td>
      <td class="cold">
        <input type="text" name="adrpro1[{$i}]" size="40" maxlength="88" value="{$adrpro1.$i}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        &nbsp;
      </td>
      <td class="cold">
        <input type="text" name="adrpro2[{$i}]" size="40" maxlength="88" value="{$adrpro2.$i}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        &nbsp;
      </td>
      <td class="cold">
        <input type="text" name="adrpro3[{$i}]" size="40" maxlength="88" value="{$adrpro3.$i}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Code postal</span><br />
      </td>
      <td class="cold">
        <input type="text" name="postcodepro[{$i}]" value="{$postcodepro.$i}" size="8" maxlength="8" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Ville</span><br />
      </td>
      <td class="cold">
        <input type="text" name="citypro[{$i}]" value="{$citypro.$i}" size="40" maxlength="50" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Pays</span>
      </td>
      <td class="cold">
        <select name="countrypro[{$i}]" onchange="this.form.submit();">
          {geoloc_country country=$countrypro.$i}
        </select>
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Région ou département</span><br />
        <span class="comm">(selon pays)</span>
      </td>
      <td class="cold">
        <select name="regionpro[{$i}]">
          {geoloc_region country=$countrypro.$i region=$regionpro.$i}
        </select>
      </td>
    </tr>
    {include file=include/flags.radio.tpl name="tel_pubpro[$i]" val=$tel_pubpro.$i}
    <tr>
      <td class="colg">
        <span class="titre">Téléphone professionnel</span>
      </td>
      <td>
        <input type="text" size="18" maxlength="18" name="telpro[{$i}]" value="{$telpro.$i}" />
        &nbsp;
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Fax</span>
      </td>
      <td>
        <input type="text" size="18" maxlength="18" name="faxpro[{$i}]" value="{$faxpro.$i}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Mobile</span>
      </td>
      <td>
        <input type="text" size="18" maxlength="18" name="mobilepro[{$i}]" value="{$mobilepro.$i}" />
      </td>
    </tr>
    {include file=include/flags.radio.tpl name="email_pubpro[$i]" val=$email_pubpro.$i}
    <tr>
      <td class="colg">
        <span class="titre">E-mail</span>
      </td>
      <td>
        <input type="text" size="30" maxlength="60" name="emailpro[{$i}]" value="{$emailpro.$i}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Page web</span>
      </td>
      <td>
        <input type="text" size="30" maxlength="255" name="webpro[{$i}]" value="{$webpro.$i}" />
      </td>
    </tr>
  </table>
</div>

{/section}

<div class="blocunite">
  <table class="bicol" cellspacing="0" cellpadding="0"
    summary="Profil: Informations professionnelles - CV">
    <tr>
      <th colspan="2">
        Informations professionnelles - CV
      </th>
    </tr>
    <tr>
      <td colspan="2" class="flags">
        <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
          <tr>
            <td class="rouge">
              <input type="checkbox" name="accesCV" checked="checked" disabled="disabled" />
            </td>
            <td class="texte">
              privé
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="dcolg">
        <span class="titre">Curriculum vitae</span><br />
        <span class="comm">Le CV n'est <strong>jamais</strong> public.<br />
          <a href="Xorg/FAQ?display=light#cv" class="popup_800x480">Comment remplir mon CV ?</a></span>
      </td>
      <td class="dcold">
        <textarea name="cv" rows="15" cols="33">{$cv}</textarea>
      </td>
    </tr>
  </table>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
