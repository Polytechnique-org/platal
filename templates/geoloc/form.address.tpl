    {if $adr.geoloc}
    <tr>
      <td class="cold" colspan="2">
        <input type="hidden" name="change{$adrid}" value="0"/>
	<span class="erreur">La geolocalisation n'a pas donné un résultat certain, vérifie la nouvelle adresse ou modifie l'ancienne pour que ton adresse puisse être prise en compte.</span>
	<div class="adresse">
        <textarea name="txt[{$adrid}]" cols="23" rows="3" onchange="form.change{$adrid}.value=1"
	{if !$adr.cityid}style="background:#FAA"{/if}
	>{$adr.txt}</textarea>
	</div>
	<div class="adresse">
	  <pre
	  style="border:2px inset threedface{if !$adr.geoloc_cityid};background:#FAA{/if}"
	>{$adr.geoloc}</pre>
	[<a href="{$smarty.server.PHP_SELF}?old_tab={$smarty.request.old_tab}&amp;parsevalid[{$adrid}]=1">Valider</a>]
	</div>
      </td>
    </tr>
    {else}
    <tr class="center">
      <td class="cold" colspan="2">
        <input type="hidden" name="change{$adrid}" />
        <textarea name="txt[{$adrid}]" cols="43" rows="3" onchange="form.change{$adrid}.value=1"
	{if $adr.nouvelle != 'new' && !$adr.cityid}style="background:#FAA"{/if}
	>{$adr.txt}</textarea>
      </td>
    </tr>
    {/if}
    <tr style="display:none">
      <td class="colg">
        &nbsp;
      </td>
      <td>
        <input type="hidden" name="cityid[{$adrid}]" value="{$adr.cityid}" />
        <input type="text" name="adr1[{$adrid}]" size="43" maxlength="88" value="{$adr.adr1}" />
      </td>
    </tr>
    <tr style="display:none">
      <td class="colg">
        &nbsp;
      </td>
      <td class="cold">
        <input type="text" name="adr2[{$adrid}]" size="43" maxlength="88" value="{$adr.adr2}" />
      </td>
    </tr>
    <tr style="display:none">
      <td class="colg">
        &nbsp;
      </td>
      <td class="cold">
        <input type="text" name="adr3[{$adrid}]" size="43" maxlength="88" value="{$adr.adr3}" />
      </td>
    </tr>
    <tr style="display:none">
      <td class="colg">
        <span class="titre">Code postal / Ville</span><br />
      </td>
      <td class="cold">
        <input type="text" name="postcode[{$adrid}]" value="{$adr.postcode}" size="7" maxlength="18" />
        &nbsp;
        <input type="text" name="city[{$adrid}]" value="{$adr.city}" size="32" maxlength="78" />
      </td>
    </tr>
    <tr style="display:none">
      <td class="colg">
        <span class="titre">Pays</span>
      </td>
      <td class="cold">
        <select name="country[{$adrid}]">
          {geoloc_country country=$adr.country}
        </select>
      </td>
    </tr>
    <tr style="display:none">
      <td class="colg">
        <span class="titre">Région ou département</span><br />
        <span class="comm">(selon pays)</span>
      </td>
      <td class="cold">
        <select name="region[{$adrid}]">
          {geoloc_region country=$adr.country region=$adr.region}
        </select>
      </td>
    </tr>
