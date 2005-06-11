    <tr>
      <td class="colg">
        <a name="jump_adr{$adrid}" />
        <span class="titre">{$titre}</span>
	<br />
        {if $adr.nouvelle != 'new' && !$smarty.request.detail[$adrid]}
          [<a href="{$url}&detail%5B{$adrid}%5D=1#jump_adr{$adrid}">corriger</a>]
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
        <input type="hidden" name="region[{$adrid}]" value="{$adr.region}" />
        <input type="hidden" name="country[{$adrid}]" value="{$adr.country}" />
        <textarea name="txt[{$adrid}]" cols="43" rows="3" onclick="form.nochange{$adrid}.value=0;select()">
{if $adr.adr1}{$adr.adr1}
{/if}
{if $adr.adr2}{$adr.adr2}
{/if}
{if $adr.adr3}{$adr.adr3}
{/if}
{if $adr.postcode || $adr.city}
{if $adr.country eq 'US' || $adr.country eq 'CA'}
{assign var='tmp' value="%v,\r\n%r %p"}
{else}
{assign var='tmp' value="%p %v"}
{/if}
{$tmp|replace:"%p":$adr.postcode|replace:"%v":$adr.city|replace:"%r":$adr.region}
{/if}
{if $adr.pays}{$adr.pays}{/if}</textarea>
      {else}
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
          {geoloc_pays pays=$adr.country}
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
          {geoloc_region pays=$adr.country region=$adr.region}
        </select>
        {/if}
      </td>
    </tr>
