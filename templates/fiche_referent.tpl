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
       <span>{$expertise|nl2br}</span>
      </div>
      <div class="spacer">&nbsp;</div>
    {/if}
   {if $nb_secteurs > 0}
      <div class="item">
        <div class="title">Secteurs :</div>
        <div class="value">
          <ul>
     {foreach from=$secteurs item="secteur" key="i"}
            <li>{$secteur}{if $ss_secteurs.$i != ''} ({$ss_secteurs.$i}){/if}</li>
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
          <li>{$pays_i}</li>
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
             <div class="value">{$address.entreprise}</div>
	    </div>
	  {/if}
	  {if $address.secteur}
	    <div class="item">
	     <div class="title">Secteur :</div>
	     <div class="value">{$address.secteur}{if $address.ss_secteur} ({$address.ss_secteur}){/if}</div>
	    </div>
	  {/if}
	<div class="spacer">&nbsp;</div>

	  {if $address.adr1 || $address.pays || $address.ville}
	    <div class="item">
	     <div class="title">Adresse :</div>
	     <div class="value">
	       {if $address.adr1}<span>{$address.adr1}</span><br />{/if}
	       {if $address.adr2}<span>{$address.adr2}</span><br />{/if}
	       {if $address.adr3}<span>{$address.adr3}</span><br />{/if}
	       {if $address.ville}<span>{$address.cp} {$address.ville}</span><br />{/if}
             {if $address.pays}
	       <span>{$address.pays}{if $address.region} ({$address.region}){/if}</span>
             {/if}
	    </div>
	   </div>
	  {/if}
	<div class="spacer">&nbsp;</div>
	  
	  {if $address.fonction}
	    <div class="item">
             <div class="title">Fonction :</div>
             <div class="value">{$address.fonction}</div>
	    </div>
	  {/if}
	  {if $address.poste}
	    <div class="item">
             <div class="title">Poste :</div>
             <div class="value">{$address.poste}</div>
	    </div>
	  {/if}
	<div class="spacer">&nbsp;</div>

	  {if $address.tel}
	    <div class="item">
             <div class="title">Tél :</div>
             <div class="value">{$address.tel}</div>
	    </div>
	  {/if}

	  {if $address.fax}
	    <div class="item">
             <div class="title">Fax :</div>
             <div class="value">{$address.fax}</div>
	    </div>
	  {/if}
	<div class="spacer">&nbsp;</div>
</div>
{/foreach}

{if $cv}
<div class="boite">
  <div class="titre">CV</div>
  <div class="item">{$cv|nl2br}</div>
  <div class="spacer">&nbsp;</div>
</div>
{/if}
{/dynamic}
