{dynamic}
<div class="boite">
  <div class="item" style="text-align:center;padding-left: 20px;padding-right: 20px;">
  <strong>{$prenom|htmlentities} {$nom|htmlentities}</strong><br />
  <span>X {$promo|htmlentities}</span><br />
  <span>Fiche mise à jour le {$date|date_format:"%d/%m/%Y"}</span><br />
  <span><a href="vcard.php/{$username}.vcf?x={$username}"><img src="images/vcard.png" alt="Afficher la carte de visite" /></a>&nbsp;
  {if !$is_contact}
  <a href="javascript:x()"  onclick="popWin('mescontacts.php?action=ajouter&amp;user={$username}')"><img src="images/ajouter.gif" alt="Ajouter parmi mes contacts" /></a>&nbsp;
  {/if}
  <a target="_blank" href="sendmail.php?contenu=Tu%20trouveras%20ci-apres%20la%20fiche%20de%20{$prenom}%20{$nom}%20https://www.polytechnique.org/fiche.php?x={$username}"><img src="images/mail.png" alt="Envoyer l'URL" /></a></span><br />
  <a href="mailto:{$username}@polytechnique.org">{$username}@polytechnique.org</a><br />
  <span><em>Section</em> : {$section|htmlentities}</span><br />
  <span><em>Binet(s)</em> : {$binets|htmlentities}</span><br />
  <span><em>Groupe(s) X</em> : {$groupes|htmlentities}</span><br />
  {if $mobile}<br /><span><em>Mobile</em> : {$mobile|htmlentities}</span><br />{/if}
  {if $libre}<br /><span><em>Commentaires</em> :</span><br /><span>{$libre|htmlentities|nl2br}</span>{/if}
  </div>
  <div class="item">
    <img alt="Photo de {$username}" src="{$photo_url}" width="{$size_x}" height="{$size_y}" />
  </div>
  <div class="spacer">&nbsp;</div>
</div>

{foreach from=$adr item="address" key="i"}
<div class="boite">
   <div class="titre">{$address.title}</div>
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
