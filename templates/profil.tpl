{* $Id: profil.tpl,v 1.2 2004-07-17 11:23:09 x2000habouzit Exp $ *}

{config_load file="profil.conf"}
{literal}
<style>
  <!--
  div.blocunite {margin: 1em 0em 2em 0em;}
  div.blocunite_tab {margin: 0em 0em 2em 0em;}
  div.bloc {margin: 0em 0.5em 0.5em 0.5em;}
  div.erreur {background-color: #FF3300; padding-left=0.5em; margin: 2px;}
  table.bicol td.cold,td.col {padding-right: 0.5em;}
  table.bicol td.colm {}
  table.bicol td.colg,td.col {padding-left: 0.5em;}
  table.bicol td.dcolm, td.dcolg, td.dcold {padding-bottom: 0.5em;}
  table.bicol td.dcolg {padding-left: 0.5em;}
  table.bicol td.dcold {padding-right: 0.5em;}
  table.bicol td.pflags {}
  table.bicol td.flags {padding-top: 0.5em;}
  table.bicol tr.top {vertical-align: top;}
  table.bicol span.titre {font-weight: bold;}
  table.bicol span.comm {font-size: smaller;}
  table.bicol span.nom {}
  table.bicol span.valeur {font-weight: bold;}
  table.bicol span.lien {font-size: smaller;}
  table.flags td.texte {font-size: smaller; font-weight: bold; padding-left: 0.5em;}
  table.flags td.vert {background-color: green; width: 1.5em; height: 1.5em; text-align: center;}
  table.flags td.orange {background-color: #ff9900; width: 1.5em; height: 1.5em; text-align: center;}
  table.flags td.rouge {background-color: red; width: 1.5em; height: 1.5em; text-align: center;}
  -->
</style>
{/literal}

{dynamic}
{if $etat_naissance}
{include file="profil/naissance.tpl"}
{/if}
{if $etat_naissance == '' || $etat_naissance == 'ok'}

{if $profil_error}
<div class="erreur">
  {$profil_error}
</div>
{/if}
{* dessin des onglets *}

<form action="{$smarty.server.PHP_SELF}" method="POST" name="prof_annu">
  <input type="hidden" value="" name="binet_op" />
  <input type="hidden" value="" name="binet_id" />
  <input type="hidden" value="" name="groupex_op" />
  <input type="hidden" value="" name="groupex_id" />
  <input type="hidden" value="{$onglet}" name="old_tab" />
  <input type="hidden" value="" name="adresse_flag" />
  <table class="cadre_a_onglet" cellpadding="0" cellspacing="0">
    <tr>
      <td>
        {draw_onglets}
      </td>
    </tr>
    <tr>
      <td>
        <div class="conteneur_tab">
          <table style="width:100%">
            <tr>
              <td colspan=2>
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
