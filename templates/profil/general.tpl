{* $Id: general.tpl,v 1.6 2004-08-24 23:06:06 x2000habouzit Exp $ *}

{include file="applis.js.tpl"}
<div class="blocunite_tab">
  <table class="bicol" cellspacing="0" cellpadding="0" 
    summary="Profil : Informations générales">
    <tr>
      <th colspan="2">
        Informations générales
      </th>
    </tr>
    <tr>
      <td colspan="2" class="pflags">
        <table class="flags" cellspacing="0" summary="Flags">
          <tr>
            <td class="vert">
              <input type="checkbox" disabled="disabled" checked="checked" />
            </td>
            <td class="texte">
              site public
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Identité</span>
      </td>
      <td class="cold">
        <span class="nom">{$prenom} {$nom} X{$promo}</span>
      </td>
    </tr>     
    {if $femme}
    <tr>
      <td class="colg">
        <span class="titre">Nom de mariage</span><br />
        <span class="comm">(si différent de {$nom} seulement)</span>
      </td>
      <td class="cold">
        <span class="nom">{$epouse|default:"Aucun"}</span>
        <span class="lien"><a href="epouse.php">modifier</a></span>
      </td>
    </tr>
    {/if}
    <tr>
      <td class="colg">
        <span class="titre">Nationalité</span>
      </td>
      <td class="cold">
        <select name="nationalite">
          {select_db_table table="nationalites" valeur=$nationalite}
        </select>
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Application</span><br />
        <span class="comm">(4ème année de l'X)</span>
      </td>
      <td class="cold">
        <select name="appli_id1" onchange="fillType(this.form.appli_type1, this.selectedIndex-1);">  
          {applis_options selected=$appli_id1}
        </select>
        <br />
        <select name="appli_type1">
          <option value=""></option>
        </select>
        <script type="text/javascript">
          <!--
          fillType(document.prof_annu.appli_type1, document.prof_annu.appli_id1.selectedIndex-1);
          selectType(document.prof_annu.appli_type1, '{dyn x=$appli_type1}');
          //-->
        </script>
      </td>
    </tr>
    <tr>
      <td class="dcolg">
        <span class="titre">Post-application</span>
      </td>
      <td class="dcold">
        <select name="appli_id2" onchange="fillType(this.form.appli_type2, this.selectedIndex-1);">   
          {applis_options selected=$appli_id2}
        </select>
        <br />
        <select name="appli_type2">
          <option value=""></option>
        </select>
        <script type="text/javascript">
          <!--
          fillType(document.prof_annu.appli_type2, document.prof_annu.appli_id2.selectedIndex-1);
          selectType(document.prof_annu.appli_type2, '{dyn x=$appli_type2}');
          //-->
        </script>
      </td>
    </tr>
  </table>
</div>

<div class="bolcunite">
  <table class="bicol" cellspacing="0" cellpadding="0"
    summary="Profil: Trombinoscope">
    <tr>
      <th colspan="3">
        Trombinoscope
      </th>
    </tr>
    <tr>
      <td class="col" colspan="3">
        Pour profiter de cette fonction intéressante, tu dois disposer 
        quelque part (sur ton ordinateur ou sur Internet) d'une photo
        d'identité (dans un fichier au format JPEG, PNG ou GIF).<br />
        <div class="center">
          <span class="lien"><a href="javascript:x()" onclick="popWin('trombino.php','trmb','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=750,height=480')">Éditer ta photo</a></span>
        </div>
      </td>
    </tr>
    <tr>
      <td class="col" colspan=3>
        <table cellspacing="0" cellpadding="0" summary="Trombinoscope">
          <tr>
            <td class="dcold">
              Voilà la photo qui apparaîtra sur la fiche de ton profil (si tu viens
              de changer ta photo, la photo affichée peut correspondre à ton ancien
              profil : c'est le cas si elle n'a pas encore été validée par un administrateur du site !
              <a href="javascript:x()" onclick="popWin('fiche.php?user={$smarty.session.username}&amp;modif=new','_blank','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=620,height=370')">nouvelle photo</a>).
            </td>
            <td class="dcolg">
              <img src="getphoto.php?x={$smarty.session.uid}{*{if $smarty.cookies|@count == 0}&amp;{php}echo SID;{/php}{/if}*}" alt=" [ PHOTO ] ">
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
<div class="blocunite">
  <table class="bicol" cellspacing="0" cellpadding="0"
    summary="Profil: Divers">
    <tr>
      <th colspan="2">
        Divers
      </th>
    </tr>
    <tr>
      <td colspan="2" class="pflags">
        <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
          <tr>
            <td class="vert">
              <input type="checkbox" name="mobile_public" value="V"
              {if $mobile_public}checked="checked"{/if} />
            </td>
            <td class="texte">
              site public
            </td>
            <td class="orange">
              <input type="checkbox" name="mobile_ax" value="O"
              {if $mobile_ax}checked="checked"{/if} />
            </td>
            <td class="texte">
              transmis à l'AX
            </td>
            <td class="texte">
              <a href="javascript:x()" onclick="popWin('aide.php#flags','remplissage','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=500')">Quelle couleur ??</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="colg">
        <span class="titre">Téléphone mobile</span>
      </td>
      <td class="cold">
        <input type="text" size="18" maxlength="18" name="mobile"
        value="{$mobile}" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="flags">
        <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
          <tr>
            <td class="vert">
              <input type="checkbox" name="web_public" value="V"
              {if $web_public}checked="checked"{/if} />
            </td>
            <td class="texte">
              site public
            </td>
            <td class="texte">
              <a href="javascript:x()" onclick="popWin('aide.php#flags','remplissage','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=500')">Quelle couleur ??</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="dcolg">
        <span class="titre">Page web Perso</span>
      </td>
      <td class="dcold">
        <input type="text" size="35" maxlength="95" name="web"  
        value="{$web}" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="pflags">
        <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
          <tr>
            <td class="vert">
              <input type="checkbox" name="libre_public" value="1"
              {if $libre_public}checked="checked"{/if} />
            </td>
            <td class="texte">
              site public
            </td>
            <td class="texte">
              <a href="javascript:x()" onclick="popWin('aide.php#flags','remplissage','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=500')">Quelle couleur ??</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="dcolg">
        <span class="titre">Complément libre</span>
        <span class="comm">Commentaire? ICQ? etc...</span>
      </td>
      <td class="dcold">
        <textarea name="libre" rows="3" cols="29" >{$libre}</textarea>
      </td>
    </tr>
  </table>
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
