{* $Id: skins.tpl,v 1.2 2004-01-28 17:19:47 x2000habouzit Exp $ *}

<div class="rubrique">
  Skins Polytechnique.org
</div>
<p class="normal">
Tu n'aimes pas les couleurs ou l'apparence de Polytechnique.org ? Normal, les goûts et les
couleurs, ça ne se discute pas. Certains préfèrent une page sérieuse, d'autres plus
fantaisiste. A toi de voir :)
</p>
<p class="normal">
Note aux utilisateurs du navigateur Netscape 4.x ou équivalent.
La fonctionalité "skins" n'est hélas pas compatible avec ces navigateurs
qui ne respectent pas les standards du web. <br />
Pour profiter de toutes les fonctionnalités de Polytechnique.org,
nous te conseillons de télécharger une version récente de ton navigateur.
</p>
<p class="normal">
Pour toute information complémentaire, n'hésite pas à écrire à
{mailto address='support@polytechnique.org' subject='navigateurs' encode='javascript'}
</p>

<form action="{$smarty.server.REQUEST_URI}" method="POST">
  <div style="text-align:center; margin: 1em;">
    <input type="submit" value="Enregistrer" name="submit">
  </div>
  <table id="skin" cellpadding="0" cellspacing="0" summary="Choix de skins">
{dynamic}
{foreach item=skin from=$skins}    
    <tr>
      <td class="skigauche">
        <input type="radio" name="newskin" value="{$skin.id}" {if $smarty.session.skin_id eq $skin.id}checked="checked"{/if}>
      </td>
      <td class="skimilieu">
        <strong>{$skin.name}</strong>
        ajoutée le {$skin.date|date_format:"%x"}<br />
        {$skin.comment}
        <br /><br />
        Créée par <strong>{$skin.auteur}</strong>
        <br /><br />
        Utilisée par <strong>{$skin.nb}</strong> inscrit{if $skin.nb>1}s{/if}
      </td>
      <td class="skidroite">
        <img src="images/{$skin.snapshot}" width=171 height=162 alt=" [ CAPTURE D'ECRAN ] ">
      </td>
    </tr>
{/foreach}
{/dynamic}
  </table>
  <div style="text-align:center; margin: 1em;">
    <input type="submit" value="Enregistrer" name="submit">
  </div>

</form>

{* vim:set et sw=2 sts=2 sws=2: *}
