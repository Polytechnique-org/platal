{* $Id: profil.tpl,v 1.6 2004-08-30 11:35:37 x2000habouzit Exp $ *}

{config_load file="profil.conf"}
{dynamic}
{if $etat_naissance}
{include file="profil/naissance.tpl"}
{/if}
{if $etat_naissance == '' || $etat_naissance == 'ok'}

{if $profil_error}
<div class="erreur">
  {$profil_error}
</div>
{else}
<p>Tu peux consulter <a href="javascript:x()" onclick="popWin('fiche.php?user={$smarty.session.username}','_blank','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=620,height=370')">l'état actuel de ta fiche</a> tel qu'elle apparaîtra pour un camarade.</p>
{/if}
{* dessin des onglets *}

<form action="{$smarty.server.PHP_SELF}" method="post" id="prof_annu">
  <table class="cadre_a_onglet" cellpadding="0" cellspacing="0">
    <tr>
      <td>
        {draw_onglets}
        <input type="hidden" value="" name="binet_op" />
        <input type="hidden" value="" name="binet_id" />
        <input type="hidden" value="" name="groupex_op" />
        <input type="hidden" value="" name="groupex_id" />
        <input type="hidden" value="{$onglet}" name="old_tab" />
        <input type="hidden" value="" name="adresse_flag" />
      </td>
    </tr>
    <tr>
      <td>
        <div class="conteneur_tab">
          <table style="width:100%">
            <tr>
              <td colspan="2">
                {include file=$onglet_tpl}
              </td>
            </tr>
            <tr class="center">
              <td>
                <input type="submit" value="Valider ces modifications" name="modifier" />
              </td>
              {if $onglet != $onglet_last}
              <td>
                <input type="submit" value="Valider et passer au prochain onglet" name="modifier+suivant" />
              </td>
              {/if}
            </tr>
          </table>
        </div>
      </td>
    </tr>
  </table>
</form>
{/if}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
