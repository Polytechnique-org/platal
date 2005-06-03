<h1>{$asso.nom} : <a href="{$smarty.server.PHP_SELF}">Evénements</a></h1>

<h2>{$evt.intitule}</h2>

<form method='post' action='{$smarty.server.PHP_SELF}'>
 <table>
    	<tr><td><i>Evénement annoncé par :</i></td><td>{$evt.prenom} {$evt.nom} (X{$evt.promo})</td></tr>
    	<tr><td><i>Description :</i></td><td>{$evt.descriptif}</td></tr>
    	<tr><td><i>Date :</i></td><td>{$evt.deb}{if $evt.fin} - {$evt.fin}{/if}</td></tr>
</table>

<br /><br />

<table border=1 width='100%'>
	<input type="hidden" name="eid" value="{$evt.eid}" />
	<tr><td></td><td>Participation</td></tr>
	{iterate from=$moments item=m}
		<input type="hidden" name="item_id{counter}" value="{$m.item_id}" />
	{if $m.titre | $m.montant}
		<tr><td colspan='2'><b>{$m.titre} - {if $m.montant > 0}{$m.montant}{else}gratuit{/if}</b></td></tr>
	{/if}
       	<tr><td>{$m.details}</td><td>
	         <input name='item_{$m.item_id}' value='0' type='radio' {if $m.nb eq 0}checked{/if}> je ne participe pas<br />
		 <input name='item_{$m.item_id}' value='1' type='radio' {if $m.nb eq 1}checked{/if}> je participe, seul<br />
		 <input name='item_{$m.item_id}' value='+' type='radio' {if $m.nb > 1}checked{/if}> je viens, et serai accompagné de <input type='text' name='itemnb_{$m.item_id}' value='{if $m.nb < 2}0{else}{$m.nb-1}{/if}' size=1 maxlength=1> personnes
              </td></tr>
    	{/iterate}
</table>
<br />
<center><input type='submit' name='ins' value='valider ma participation'>
<input type='reset' value='annuler'></center>
</form>

