{dynamic}
<div class="boite" style="text-align:center;">
  <span>{$prenom} {$nom}</span><br />
  <span>X{$promo}&nbsp;-&nbsp;</span>
  <a href="mailto:{$username}@polytechnique.org">{$username}@polytechnique.org</a><br />
</div>

{**a-t-il bien des infos de referents ? **}
{if $expertise != '' || ($nb_secteurs > 0)  || ($nb_pays > 0) }

<div class="boite">
    <div class="titre">Informations de référent</div>
    {if $expertise}
      <div class="item">
       <div class="title">Expertise :</div>
       <span>{$expertise|htmlentities|nl2br}</span>
      </div>
      <div class="spacer">&nbsp;</div>
    {/if}
   {if $nb_secteurs > 0}
      <div class="item">
        <div class="title">Secteurs :</div>
        <div class="value">
          <ul>
     {foreach from=$secteurs item="secteur" key="i"}
            <li>{$secteur|htmlentities}{if $ss_secteurs.$i != ''} ({$ss_secteurs.$i|htmlentities}){/if}</li>
     {/foreach}
          </ul>
        </div>
      </div>
   {/if}
   {if $nb_pays > 0}
    <div class="item">
      <div class="title">Pays :</div>
      <div class="value">
        <ul>
     {foreach from=$pays item="pays_i"}
          <li>{$pays_i|htmlentities}</li>
     {/foreach}
        </ul>
      </div>
    </div>
   {/if}
    <div class="spacer">&nbsp;</div>
</div>
{/if}
     
{foreach from=$adr_pro item="address" key="i"}
<div class="boite">
   <div class="titre">Infos professionnelles - Entreprise n°{$i+1}</div>
          {if $address.entreprise}
	    <div class="item">
	     <div class="title">Entreprise/Organisme :</div>
             <div class="value">{$address.entreprise|htmlentities}</div>
	    </div>
	  {/if}
	  {if $address.secteur}
	    <div class="item">
	     <div class="title">Secteur :</div>
	     <div class="value">{$address.secteur|htmlentities}{if $address.ss_secteur} ({$address.ss_secteur|htmlentities}){/if}</div>
	    </div>
	  {/if}
	<div class="spacer">&nbsp;</div>

	  {if $address.adr1 || $address.pays || $address.ville}
	    <div class="item">
	     <div class="title">Adresse :</div>
	     <div class="value">
	       {if $address.adr1}<span>{$address.adr1|htmlentities}</span><br />{/if}
	       {if $address.adr2}<span>{$address.adr2|htmlentities}</span><br />{/if}
	       {if $address.adr3}<span>{$address.adr3|htmlentities}</span><br />{/if}
	       {if $address.ville}<span>{$address.cp|htmlentities} {$address.ville|htmlentities}</span><br />{/if}
             {if $address.pays}
	       <span>{$address.pays|htmlentities}{if $address.region} ({$address.region|htmlentities}){/if}</span>
             {/if}
	    </div>
	   </div>
	  {/if}
	<div class="spacer">&nbsp;</div>
	  
	  {if $address.fonction}
	    <div class="item">
             <div class="title">Fonction :</div>
             <div class="value">{$address.fonction|htmlentities}</div>
	    </div>
	  {/if}
	  {if $address.poste}
	    <div class="item">
             <div class="title">Poste :</div>
             <div class="value">{$address.poste|htmlentities}</div>
	    </div>
	  {/if}
	<div class="spacer">&nbsp;</div>

	  {if $address.tel}
	    <div class="item">
             <div class="title">Tél :</div>
             <div class="value">{$address.tel|htmlentities}</div>
	    </div>
	  {/if}

	  {if $address.fax}
	    <div class="item">
             <div class="title">Fax :</div>
             <div class="value">{$address.fax|htmlentities}</div>
	    </div>
	  {/if}
	<div class="spacer">&nbsp;</div>
</div>
{/foreach}

{if $cv}
<div class="boite">
  <div class="titre">CV</div>
  <div class="item">{$cv|htmlentities|nl2br}</div>
  <div class="spacer">&nbsp;</div>
</div>
{/if}
{/dynamic}
