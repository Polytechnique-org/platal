<td>
  {if $result.inscrit==1}
    <a href="javascript:x()"  onclick="popWin('x.php?x={$result.username}')">
      <img src="images/loupe.gif" border=0 ALT="Afficher les détails">
    </a>
    <a href="vcard.php/{$result.username}.vcf?x={$result.username}">
      <img src="images/vcard.png" border=0 ALT="Afficher la carte de visite">
    </a>
    <a href="mescontacts.php?action={if $result.contact!=""}retirer{else}ajouter{/if}&amp;user={$result.username}&amp;mode=normal">
      <img src="images/{if $result.contact!=""}retirer{else}ajouter{/if}.gif" border=0 ALT="{if $result.contact!=""}Retirer de{else}Ajouter parmi{/if} mes contacts">
    </a>
  {else}
    {if $result.decede != 1}
      <a href="marketing.php?num={$result.matricule*2-100}" target="_blank">
        clique ici si tu connais son adresse email !
      </a>
    {/if}
  {/if}
  {if $is_admin==1}
    <a href="http://www.polytechniciens.com/index.php?page=AX_FICHE_ANCIEN&amp;anc_id={$result.matricule_ax}" target="_blank">
      AX
    </a>
  {/if}
</td>
