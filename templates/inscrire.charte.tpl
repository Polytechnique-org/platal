{* $Id: inscrire.charte.tpl,v 1.3 2004-08-26 14:44:43 x2000habouzit Exp $ *}

<form action="{$smarty.server.REQUEST_URI}" method="post">
  <div class="rubrique">
    Conditions générales
  </div>
  <p>
  L'enregistrement se déroule <strong>en deux étapes</strong>. La pré-inscription te prendra moins
  de 5 minutes. La seconde étape est une phase de validation où c'est nous qui te
  recontactons pour te fournir un mot de passe et te demander de le changer.
  </p>
  {include file="docs/charte.tpl"}
  <div class="center">
    <input type="hidden" value="OUI" name="charte" />
    <input type="submit" value="J'accepte ces conditions" name="submit" />
  </div>
</form>


{* vim:set et sw=2 sts=2 sws=2: *}
