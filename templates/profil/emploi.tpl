{* $Id: emploi.tpl,v 1.3 2004-07-31 13:37:19 x2000coic Exp $ *}

{section name=adresses_pro loop=2}
{assign var='i' value=$smarty.section.adresses_pro.index} 

<div class="blocunite{if !$i}tab{/if}">

  <table class="bicol" cellspacing="0" cellpadding="0" summary="Profil: Informations professionnelles - Entreprise n°{$i+1}">
    <tr>
      <th colspan="2">
        Informations professionnelles - Entreprise n°{$i+1}
      </th>
    </tr>
    <tr>
      <td colspan="5" class="pflags">
        <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
          <tr>
            <td class="vert">
              <input type="checkbox" name="entreprise_public[{$i}]" value="1" {if $entreprise_public.$i}checked="checked"{/if} />
            </td>
            <td class="texte">
              site public
            </td>
            <td class="orange">
              <input type="checkbox" name="entreprise_ax[{$i}]" value="1" {if $entreprise_ax.$i}checked="checked"{/if} />
            </td>
            <td class="texte">
              transmis à l'AX
            </td>
            <td class="texte">
              <a href="javascript:x()" onclick="popWin('aide.php#flags','remplissage','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=500')">
                Quelle couleur ??</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Entreprise ou organisme</span>
      </td>
      <td class="cold">
        <input type="text" size="35" maxlength="100" name="entreprise[{$i}]"
        value="{$entreprise.$i|print_html}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Secteur d'activité</span>
      </td>
      <td class="cold">
        <select name="secteur[{$i}]" onChange="this.form.submit();">
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
        value="{$poste.$i|print_html}" />
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
    <tr>
      <td colspan="5" class="pflags">
        <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
          <tr>
            <td class="vert">
              <input type="checkbox" name="adrpro_public[{$i}]" value="1" {if $adrpro_public.$i}checked="checked"{/if} />
            </td>
            <td class="texte">
              site public
            </td>
            <td class="orange">
              <input type="checkbox" name="adrpro_ax[{$i}]" value="1" {if $adrpro_ax.$i}checked="checked"{/if} />
            </td>
            <td class="texte">
              transmis à l'AX
            </td>
            <td class="texte">
              <a href="javascript:x()" onclick="popWin('aide.php#flags','remplissage','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=500')">
                Quelle couleur ??</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Adresse professionnelle</span>
      </td>
      <td class="cold">
        <input type="text" name="adrpro1[{$i}]" size="40" maxlength="88" value="{$adrpro1.$i|print_html}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        &nbsp;
      </td>
      <td class="cold">
        <input type="text" name="adrpro2[{$i}]" size="40" maxlength="88" value="{$adrpro2.$i|print_html}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        &nbsp;
      </td>
      <td class="cold">
        <input type="text" name="adrpro3[{$i}]" size="40" maxlength="88" value="{$adrpro3.$i|print_html}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Code postal</span><br />
      </td>
      <td class="cold">
        <input type="text" name="cppro[{$i}]" value="{$cppro.$i|print_html}" size="8" maxlength="8" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Ville</span><br />
      </td>
      <td class="cold">
        <input type="text" name="villepro[{$i}]" value="{$villepro.$i|print_html}" size="40" maxlength="50" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Pays</span>
      </td>
      <td class="cold">
        <select name="payspro[{$i}]" onChange="this.form.submit();">
          {geoloc_pays pays=$payspro.$i}
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
          {geoloc_region pays=$payspro.$i region=$regionpro.$i}
        </select>
      </td>
    </tr>
    <tr>
      <td colspan="5" class="pflags">
        <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
          <tr>
            <td class="vert">
              <input type="checkbox" name="telpro_public[{$i}]" value="1" {if $telpro_public.$i}checked="checked"{/if} />
            </td>
            <td class="texte">
              site public
            </td>
            <td class="orange">
              <input type="checkbox" name="telpro_ax[{$i}]" value="1" {if $telpro_ax.$i}checked="checked"{/if} />
            </td>
            <td class="texte">
              transmis à l'AX
            </td>
            <td class="texte">
              <a href="javascript:x()" onclick="popWin('aide.php#flags','remplissage','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=500')">
                Quelle couleur ??</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Téléphone professionnel</span>
      </td>
      <td>
        <input type="text" size="18" maxlength="18" name="telpro[{$i}]" value="{$telpro.$i|print_html}" />
        &nbsp;
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Fax</span>
      </td>
      <td>
        <input type="text" size="18" maxlength="18" name="faxpro[{$i}]" value="{$faxpro.$i|print_html}" />
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
              ne peut être ni public ni transmis à l'AX
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="dcolg">
        <span class="titre">Curriculum vitae</span><br />
        <span class="comm">Le CV n'est <strong>jamais</strong> public.<br />
          <a href="javascript:x()" onclick="popWin('aide.php#cv','aide_cv','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=500')">
            Comment remplir mon CV ?</a></span>
      </td>
      <td class="dcold">
        <textarea name="cv" rows="15" cols="33">{$cv|print_html}</textarea>
      </td>
    </tr>
  </table>
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
