{* $Id: adresses.tpl,v 1.3 2004-08-24 11:45:19 x2000habouzit Exp $ *}

<div class="blocunite_tab">
  <table class="bicol" cellspacing="0" cellpadding="0" summary="Profil: Adresses personnelles">
    <tr>
      <th colspan="5">
        Adresses personnelles
      </th>
    </tr>

    {section name=i loop=$nb_adr start=1 max=$nb_adr}
    {*
    $adrid = $ordre_adrid[$i];
    $adr = &$adresses[$adrid];
    *}
    {assign var='adrid' value=$ordre_adrid[i]}
    {assign var='adr' value=$adresses.$adrid}
    <tr>
      <th colspan="5">
        {if $adr.nouvelle != 'new'}Adresse n°{$smarty.section.i.index}{else}Rentre ici une nouvelle adresse{/if}
        <input type="hidden" name="adrid[{$adrid}]" value="{$adrid}" />
        {if $adr.nouvelle == 'new'}
        <input type="hidden" name="numero_formulaire[{$adrid}]" value="0" />
        {else}
        <input type="hidden" name="numero_formulaire[{$adrid}]" value="{$smarty.section.i.index}" />
        {/if}
      </th>
    </tr>
    <tr>
      <td class="left">
        {if $adr.nouvelle != 'new'}
        <input type="submit" value="La supprimer !" name="adrid_del[{$adrid}]" />
        {/if}
        &nbsp;
      </td>
      <td colspan="4" class="right">
        <em>c'est à cette adresse que je suis actuellement</em>
        <input name="adrid_active" type="radio" value="{$adrid}" {if $adr.active}checked="checked"{/if} />
      </td>
    </tr>
    <tr>
      <td colspan="5" class="pflags">
        <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
          <tr>
            <td class="vert">
              <input type="checkbox" name="adr_public[{$adrid}]" value="1" {if $adr.adr_public}checked="checked"{/if} />
            </td>
            <td class="texte">
              site public
            </td>
            <td class="orange">
              <input type="checkbox" name="adr_ax[{$adrid}]" value="1" {if $adr.adr_ax}checked="checked"{/if} />
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
        <span class="titre">{if $adr.nouvelle != 'new'}Adresse n°{$smarty.section.i.index}{else}Nouvelle adresse{/if}</span><br />
      </td>
      <td class="cold">
        <input type="text" name="adr1[{$adrid}]" size="43" maxlength="88" value="{$adr.adr1}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        &nbsp;
      </td>
      <td class="cold">
        <input type="text" name="adr2[{$adrid}]" size="43" maxlength="88" value="{$adr.adr2}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        &nbsp;
      </td>
      <td class="cold">
        <input type="text" name="adr3[{$adrid}]" size="43" maxlength="88" value="{$adr.adr3}" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Code postal / Ville</span><br />
      </td>
      <td class="cold">
        <input type="text" name="cp[{$adrid}]" value="{$adr.cp}" size="7" maxlength="18" />
        &nbsp;
        <input type="text" name="ville[{$adrid}]" value="{$adr.ville}" size="32" maxlength="78" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Pays</span>
      </td>
      <td class="cold">
        <select name="pays[{$adrid}]" onChange="this.form.submit();">
          {geoloc_pays pays=$adr.pays}
        </select>
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Région ou département</span><br />
        <span class="comm">(selon pays)</span>
      </td>
      <td class="cold">
        <select name="region[{$adrid}]">
          {geoloc_region pays=$adr.pays region=$adr.region}
        </select>
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Cette adresse est :</span>
      </td>
      <td class="cold">
        <input type="radio" name="temporaire[{$adrid}]" value="0" {if !$adr.temporaire}checked="checked"{/if} />
        permanente
        <input type="radio" name="temporaire[{$adrid}]" value="1" {if $adr.temporaire}checked="checked"{/if} />
        temporaire
      </td>
    </tr>
    <tr>
      <td class="colg">
        &nbsp;
      </td>
      <td class="cold">
        <input type="radio" name="secondaire[{$adrid}]" value="0" {if !$adr.secondaire}checked="checked"{/if} />
        ma résidence principale
        <input type="radio" name="secondaire[{$adrid}]" value="1" {if $adr.secondaire}checked="checked"{/if} />
        une résidence secondaire
      </td>
      <tr>
        <td class="colg">
          &nbsp;
        </td>
        <td class="cold">
          <input type="checkbox" name="courrier[{$adrid}]" value="1" {if $adr.courrier}checked="checked"{/if} /> on peut m'y envoyer du courrier par la poste
        </td>
      </tr>
      <tr>
        <td colspan="2" class="flags">
          <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
            <tr>
              <td class="vert">
                <input type="checkbox" name="tel_public[{$adrid}]" value="1" {if $adr.tel_public}checked="checked"{/if} />
              </td>
              <td class="texte">
                site public
              </td>
              <td class="orange">
                <input type="checkbox" name="tel_ax[{$adrid}]" value="1" {if $adr.tel_ax}checked="checked"{/if} />
              </td>
              <td class="texte">
                transmis à l'AX
              </td>
              <td class="texte">
                <a href="javascript:x()" onclick="popWin('aide.php#flags','remplissage','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=500')">Quelle couleur ??</a>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td class="colg">
          <span class="titre">Téléphone associé</span>
        </td>
        <td>
          <input type="text" size="19" maxlength="28" name="tel[{$adrid}]" value="{$adr.tel}" />
          &nbsp;
          <span class="titre">Fax</span>
          <input type="text" size="19" maxlength="28" name="fax[{$adrid}]" value="{$adr.fax}" />
        </td>
      </tr>
      <tr><td colspan="5">&nbsp;</td></tr>
      {/section}
      <tr><td colspan="5">&nbsp;</td></tr>
    </table>
  </div>

  {* vim:set et sw=2 sts=2 sws=2: *}
