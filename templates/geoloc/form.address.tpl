    <tr>
      <td class="colg">
        <span class="titre">{$titre}</span>
        {if $adr.nouvelle != 'new' && !$smarty.request.detail[$adrid]}
	<br />
          [<a href="{$url}&amp;detail[{$adrid}]=1">corriger</a>]
        {/if}
	{if $adr.nouvelle != 'new' && !$adr.cityid}
	<br />
	<span class="erreur">non géolocalisée</span>
	{/if}
      </td>
      <td class="cold">
        {if $smarty.request.detail[$adrid] neq 1}
        <input type="hidden" name="nochange{$adrid}" value="1" />
        <input type="hidden" name="adr1[{$adrid}]" value="{$adr.adr1}" />
        <input type="hidden" name="adr2[{$adrid}]" value="{$adr.adr2}" />
        <input type="hidden" name="adr3[{$adrid}]" value="{$adr.adr3}" />
        <input type="hidden" name="postcode[{$adrid}]" value="{$adr.postcode}"/>
        <input type="hidden" name="city[{$adrid}]" value="{$adr.city}" />
        <input type="hidden" name="cityid[{$adrid}]" value="{$adr.cityid}" />
        <input type="hidden" name="region[{$adrid}]" value="{$adr.region}" />
        <input type="hidden" name="country[{$adrid}]" value="{$adr.country}" />
        <textarea name="txt[{$adrid}]" cols="43" rows="3" onclick="form.nochange{$adrid}.value=0;select()">{$adr.txt}</textarea>
      {else}
        <input type="hidden" name="cityid[{$adrid}]" value="{$adr.cityid}" />
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
        <input type="text" name="postcode[{$adrid}]" value="{$adr.postcode}" size="7" maxlength="18" />
        &nbsp;
        <input type="text" name="city[{$adrid}]" value="{$adr.city}" size="32" maxlength="78" />
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Pays</span>
      </td>
      <td class="cold">
        <select name="country[{$adrid}]" onchange="this.form.submit();">
          {geoloc_country country=$adr.country}
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
          {geoloc_region country=$adr.country region=$adr.region}
        </select>
        {/if}
      </td>
    </tr>
