{* $Id: trombipromo.tpl,v 1.3 2004-08-26 14:44:43 x2000habouzit Exp $ *}

{dynamic}

{if !$smarty.request.xpromo || $erreur}
<div class="rubrique">
  Trombinoscope promo
</div>
<p>
Cette page te permet de voir l'album photo d'une promotion
donnée.
</p>
<p>
Fais attention, si tu as une connexion à internet lente (par
exemple si tu es sur un vieux modem), la page que tu vas télécharger
en validant peut être longue à afficher. Pour te donner une
idée de la taille, chaque photo est limitée à 30 ko, et
chaque page affiche au plus {$limit} photos.
</p>
{if $erreur}<p>{$erreur}</p>{/if}

<form action="{$smarty.server.PHP_SELF}" method="get">
  <table class="tinybicol" cellpadding="3" summary="Saisie promo" style="width: 30%; margin-left:35%">
    <tr>
      <th colspan="2">
        Trombinoscope
      </th>
    </tr>
    <tr>
      <td class="titre" style="vertical-align: middle;">
        Promotion
      </td>
      <td>
        <input type="text" name="xpromo" size="4" maxlength="4" />
        <input type="hidden" name="offset" value="0" />&nbsp;<input type="submit" value="Ok" />
      </td>
    </tr>
  </table>
</form>

{else}

<div class="rubrique">
  {if $smarty.request.xpromo eq 'all'}
  Album photo Polytechnique.org
  {else}
  Album photo promotion {$smarty.request.xpromo}
  {/if}
</div>

{if $pnb}
<p>
{$pnb} polytechnicien{if $pnb gt 1}s de la promotion {$smarty.request.xpromo} ont
{else} de la promotion {$smarty.request.xpromo} a {/if} une photo dans l'album photo :
</p>

<table cellpadding="8" cellspacing="2" style="width:100%;">
  {foreach from=$photos item=p}
  {cycle values="1,2,3" assign="loop"}
  {if $loop eq "1"}
  <tr>
  {/if}
    <td class="center">
      <a href="javascript:x()" onclick="popWin('x.php?x={$p.username}')">
        <img src="getphoto.php?x={$p.user_id}" width="110" alt=" [ PHOTO ] " />
      </a>
      {mailto address="`$p.username`@polytechnique.org" text="`$p.prenom`&nbsp;`$p.nom`"}
      {if $smarty.request.xpromo eq 'all'}{$p.promo}{/if}
      {if $smarty.session.perms eq 'admin'}<br /><a href="admin/admin_trombino.php?uid={$p.user_id}">[admin]</a>{/if}
    </td>
  {if $loop eq "3"}
  </tr>
  {/if}
  {/foreach}
  {if $loop eq "1"}
  <td></td><td></td></tr>
  {elseif $loop eq "2"}
  <td></td></tr>
  {/if}
</table>

{foreach from=$links item=l}
{if $l[0] eq $smarty.request.offset}
<span class="erreur">
  <a href="{$smarty.server.PHP_SELF}?xpromo={$smarty.request.xpromo}&amp;offset={$l[0]}">{$l[1]}</a>
</span>
{else}
<a href="{$smarty.server.PHP_SELF}?xpromo={$smarty.request.xpromo}&amp;offset={$l[0]}">{$l[1]}</a>
{/if}
{/foreach}

{else}

<div>
  Il n'y a aucune photo de camarade de cette promotion sur nos serveurs.
</div>

{/if}

{/if}

{/dynamic}


{* vim:set et sw=2 sts=2 sws=2: *}
