{* $Id: x_inscrit.tpl,v 1.5 2004-08-29 16:02:40 x2000habouzit Exp $ *}

<div class="contact">
  <div class="nom">
    {$c.nom} {$c.prenom}
    {if $c.epouse}<br />({$c.epouse} {$c.nom}){/if}
    {if $c.dcd}(décédé){/if}
  </div>
  <div class="appli">
    {strip}
    (
    X{$c.promo}{if $c.app0text},
    {applis_fmt type=$c.app0type text=$c.app0text url=$c.app0url}
    {/if}{if $c.app1text},
    {applis_fmt type=$c.app1type text=$c.app1text url=$c.app1url}
    {/if}
    )
    {/strip}
  </div>
  <div class="bits">
    <a href="javascript:x()" onclick="popWin('fiche.php?user={$c.username}')">
      <img src="images/loupe.gif" alt="Afficher les détails" />
    </a>
    <a href="vcard.php/{$c.username}.vcf?x={$c.username}">
      <img src="images/vcard.png" alt="Afficher la carte de visite" />
    </a>
    <a href="mescontacts.php?action={$show_action}&amp;user={$c.username}">
      <img src="images/{$show_action}.gif" alt="{$show_action} aux/des contacts" />
    </a>
    {perms level='admin'}
    <a href="http://www.polytechniciens.com/index.php?page=AX_FICHE_ANCIEN&amp;anc_id={$c.matricule_ax}" onclick="return popup(this)">AX</a>
    {/perms}
    <span class="smaller"><strong>{$c.date|date_format:"%d-%m-%Y"}</strong></span>
  </div>
  <div class="long">
    <table cellspacing="0" cellpadding="0">
      {if $c.nat}
      <tr>
        <td class="lt">Nationalité:</td>
        <td class="rt">{$c.nat}</td>
      </tr>
      {/if}
      {if $c.web}
      <tr>
        <td class="lt">Page web:</td>
        <td class="rt"><a href="{$c.web}">{$c.web}</a></td>
      </tr>
      {/if}
      {if $c.pays || $c.ville || $c.pays}
      <tr>
        <td class="lt">Géographie:</td>
        <td class="rt">{implode sep=", " s1=$c.ville s2=$c.region s3=$c.pays}</td>
      </tr>
      {/if}
      {if $c.mobile}
      <tr>
        <td class="lt">Mobile:</td>
        <td class="rt">{$c.mobile}</td>
      </tr>
      {/if}
      {if $c.entreprise}
      <tr>
        <td class="lt">Profession:</td>
        <td class="rt">
          {$c.entreprise}
          {if $c.secteur}( {$c.secteur} ){/if}
          {if $c.fonction}<br />{$c.fonction} ){/if}
        </td>
      </tr>
      {/if}
      {if $c.libre}
      <tr>
        <td class="lt">Commentaire:</td>
        <td class="rt">{$c.libre|nl2br}</td>
      </tr>
      {/if}
    </table>
  </div>
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
