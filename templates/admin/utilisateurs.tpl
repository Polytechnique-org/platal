{* $Id: utilisateurs.tpl,v 1.1 2004-02-11 20:00:38 x2000habouzit Exp $ *}

{if $smarty.session.suid}
<p class="erreur">
Attention, déjà en SUID !!!
</p>
{/if}

<div class="rubrique">
  Gestion des utilisateurs
</div>

{dynamic}

{if $smarty.post.u_kill_conf}
<div class="center">
  <form name="yes" method="post" action="{$smarty.server.PHP_SELF}">
    <input type="hidden" name="login" value="{$smarty.request.login}">
    Confirmer la suppression de {$smarty.request.login}&nbsp;&nbsp;
    <input type="submit" name="u_kill" value="continuer">
  </form>
</div>
{/if}

{/dynamic}

<form name="add" method="post" action="{$smarty.server.PHP_SELF}">
  <table class="tinybicol" border="0" cellspacing="0" cellpadding="3">
    <tr>
      <th>
        Administrer
      </th>
    </tr>
    <tr>
      <td class="center">
        <input type="text" name="login" size="40" maxlength="255" value="{$login}">
      </td>
    </tr>
    <tr>
      <td class="center">
        <input type="submit" name="select" value=" edit "> &nbsp;&nbsp;
        <input type="hidden" name="hashpass" value=""> 
        <input type="submit" name="suid_button" value=" su "> 
      </td>
    </tr>
  </table>
</form>

{dynamic on="0`$smarty.request.select`"}
<table border="0" cellspacing="0" cellpadding="0" class="admin">
  <p class="smaller">
  Derniére connexion le <strong>{$lastlogin|date_format:"%d %B %Y, %T"}</strong>
  depuis <strong>{$mr.host}</strong>
  </p>
  <form name="edit" method="post" action="{$smarty.server.PHP_SELF}">
    <tr valign=top align=left> 
      <th class="login">
        Login
      </th>
      <input type="hidden" name="hashpass" value="">
      <th class="password"> 
        Password
      </th>
      <th class="perms"> 
        Perms
      </th>
    </tr>
    <tr> 
      <td class="login"> 
        <input type="hidden" name="hashpass" value="">
        <input type="text" name="login" size=20 maxlength=50 value="{$mr.username}">
      </td>
      <td class="password"> 
        <input type="text" name="newpass_clair" size=10 maxlength=10 value="********">
        <input type="hidden" name="passw" size=32 maxlength=32 value="{$mr.password}">
      </td>
      <td class="perms"> 
        <select name="permsN">
          <option value="user" {if $mr.perms eq "user"}selected="selected"{/if}>user</option>
          <option value="admin" {if $mr.perms eq "admin"}selected="selected"{/if}>admin</option>
        </select>
      </td>
    </tr>
    <tr> 
      <td class="loginr"> 
        if (login!=prenom.nom)&nbsp;
      </td>
      <td class="login"> 
        <select name="homonyme">
        {if $mr.loginbis && ($mr.loginbis neq $mr.username)}
          <option value="1" selected="selected"> OUI </option>
          <option value="0"> NON </option>
        {else}
          <option value="1"> OUI </option>
          <option value="0" selected="selected"> NON </option>
        {/if}
        </select>
        /* pour homonymes */
      </td>
      <th class="action">
        &nbsp;
      </th>
    </tr>
    <tr> 
      <td class="loginr">
        then prenom.nom=
      </td>
      <td class="login">
        <input type="text" name="loginbis" size=24 maxlength=255 value="{$mr.loginbis}">
      </td>
      <th class="action">
        Action
      </th>
    </tr>
    <tr> 
      <th>UID</th>
      <td>
        {$mr.user_id}
        <input type="hidden" name="olduid" size=6 maxlength=6 value="{$mr.user_id}">
        <input type="hidden" name="oldlogin" size=100 maxlength=100 value="{$mr.username}">
      </td>
      <td class="action">
        <input type="submit" name="u_kill_conf" value="DELETE">
      </td>
    </tr>
    <tr> 
      <th class="detail">
        Matricule
      </th>
      <td class="detail"> 
        {$mr.matricule}
      </td>
      <td class="action"> 
        <input onClick="doEditUser(); return true;" type="submit" name="u_edit" value="UPDATE">
      </td>
    </tr>
    <tr> 
      <th class="detail">
        Date de naissance
      </th>
      <td class="detail"> 
        <input type="text" name="naissanceN" size=10 maxlength=10 value="{$mr.naissance}">
      </td>
      <td class="action">
        &nbsp;
      </td>
    </tr>
    <tr> 
      <th class="detail">
        Promo
      </th>
      <td class="detail"> 
        <input type="text" name="promoN" size=4 maxlength=4 value="{$mr.promo}">
      </td>
      <td class="action">
        &nbsp;
      </td>
    </tr>
    <tr> 
      <th class="detail">
        Nom
      </th>
      <td class="detail">
        <input type="text" name="nomN" size=20 maxlength=255 value="{$mr.nom}">
      </td>
      <td class="action">
        <a href="javascript:x()" onclick="popWin('{"x.php?x=`$mr.username`"|url}')">[Voir fiche]</a>
      </td>
    </tr>
    <tr> 
      <th class="detail">
        Prénom
      </th>
      <td class="detail">
        <input type="text" name="prenomN" size=20 maxlength=30 value="{$mr.prenom}">
      </td>
      <td class="action">
        <a href="admin_trombino.php?uid={$mr.user_id}">[Trombino]</a>
      </td>
    </tr>
    <tr> 
      <th class="alias">
        Alias e-mail
      </th>
      <td class="alias"> 
        <input type="text" name="alias" size=20 maxlength=255 value="{$mr.alias}">@m4x.org
      </td>
      <td class="action">
        &nbsp;
      </td>
    </tr>
{if $db_edu}
    {foreach item=alias from=$alias_edu}
    <tr>
      <th class="polyedu">Alias polyedu
        {if !$alias.email}
          <br /><span="erreur">Attention, email indéfini !</span>
          {assign var="edu_err" value=1}
        {/if}
        {if $alias.email neq "`$mr.username`@m4x.org"}
          <br /><span="erreur">Attention, email mal défini !</span>
          {assign var="edu_err" value=1}
        {/if}
        {if $alias.email && !$alias.act}
          <br /><span="erreur">Attention, email inactif !</span>
          {assign var="edu_err" value=1}
        {/if}
      </th>
      {if $alias.alias}
      <td class="polyedu">
        <input type="text" name="alias_edu" size=20 maxlength=255 value="{$alias.alias}" />
      </td>
      <td class="polyedu">  
        <form name="rmedu" method="post" action="{$smarty.server.PHP_SELF}">
          <input type="hidden" name="id_edu" value="{$alias.id}">
          <input type="hidden" name="alias_edu" value="{$alias.alias}">
          <input type="hidden" name="login" value="{$mr.username}">
          <input type="hidden" name="select" value="edit">
          <input type="submit" name="remove_polyedu_alias" value="Supprimer">
        </form>
      </td>
      {else}
      <td class="polyedu">
        Erreur : entrée dans la table x mais pas dans la table alias
      </td>
      <td class="polyedu">
        <form name="addedu" method="post" action="{$smarty.server.PHP_SELF}">
          <input type="hidden" name="user_id" value="{$mr.user_id}">
          <input type="hidden" name="matricule" value="{$mr.matricule}">
          <input type="hidden" name="login" value="{$mr.username}">
          <input type="hidden" name="select" value="edit">
          <input type="hidden" name="alias_edu" value="">
          <input type="submit" name="add_polyedu_alias" value="Réparer">
        </form>
      </td>
      {/if}
    </tr>
    {/foreach}
    {if $edu_err}
    <tr> 
      <th class="polyedu">
        Alias polyedu
      </th>
      <td class="polyedu"> 
        Un problème existe !!!
      </td>
      <td class="polyedu">
        <form name="addedu" method="post" action="{$smarty.server.PHP_SELF}">
          <input type="hidden" name="user_id" value="{$mr.user_id}">
          <input type="hidden" name="matricule" value="{$mr.matricule}">
          <input type="hidden" name="login" value="{$mr.username}">
          <input type="hidden" name="select" value="edit">
          <input type="hidden" name="alias_edu" value="">
          <input type="submit" name="add_polyedu_alias" value="Réparer">
        </form>
      </td>
    </tr>
    {/if}
    <form name="addedu" method="post" action="{$smarty.server.PHP_SELF}">
      <input type="hidden" name="user_id" value="{$mr.user_id}">
      <input type="hidden" name="matricule" value="{$mr.matricule}">
      <input type="hidden" name="login" value="{$mr.username}">
      <input type="hidden" name="select" value="edit">
      <tr> 
        <th class="polyedu">
          Ajouter un alias polyedu
        </th>
        <td class="polyedu"> 
          <input type="text" name="alias_edu" size=29 maxlength=60 value="">
        </td>
        <td class="polyedu">
          <input type="submit" name="add_polyedu_alias" value="Ajouter">
        </td>
      </tr>
    </form>
{else}{* db_edu *}
    <tr> 
      <th class="polyedu" colspan="3">
        Polyedu non joignable
      </th>
    </tr>
{/if}
    {foreach item=mail from=$xorgmails}
    <form name="remove" method="post" action="{$smarty.server.PHP_SELF}">
      <tr> 
        <th class="detail"> 
          e-mail forward {$mail.num} ({$mail.flags})
        </th>
        <td class="detail"> 
          <input type="text" name="fwd" size=29 maxlength=255 value="{$mail.email}">
        </td>
        <td class="action"> 
          <input type="hidden" name="user_id" value="{$mr.user_id}">
          <input type="hidden" name="login" value="{$mr.username}">
          <input type="hidden" name="email" value="{$mail.email}">
          <input type="hidden" name="select" value="edit">
          <input type="submit" name="remove_email" value="Supprimer">
        </td>
      </tr>
    </form>
    {/foreach}
    <form name="add" method="post" action="{$smarty.server.PHP_SELF}">
      <input type="hidden" name="user_id" value="{$mr.user_id}">
      <input type="hidden" name="login" value="{$mr.username}">
      <input type="hidden" name="select" value="edit">
      <input type="hidden" name="num" value="{$next_num}">
      <tr> 
        <th class="detail">
          Ajouter un email
        </td>
        <td class="detail"> 
          <input type="text" name="email" size=29 maxlength=60 value="">
        </td>
        <td class="action">
          <input type="submit" name="add_email" value="Ajouter">
        </td>
      </tr>
    </form>
  </table>
</form>
<p class="erreur">
{$email_panne}
</p>
{/dynamic}
{* vim:set et sw=2 sts=2 sws=2: *}
