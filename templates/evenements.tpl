{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
{*  http://opensource.polytechnique.org/                                  *}
{*                                                                        *}
{*  This program is free software; you can redistribute it and/or modify  *}
{*  it under the terms of the GNU General Public License as published by  *}
{*  the Free Software Foundation; either version 2 of the License, or     *}
{*  (at your option) any later version.                                   *}
{*                                                                        *}
{*  This program is distributed in the hope that it will be useful,       *}
{*  but WITHOUT ANY WARRANTY; without even the implied warranty of        *}
{*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *}
{*  GNU General Public License for more details.                          *}
{*                                                                        *}
{*  You should have received a copy of the GNU General Public License     *}
{*  along with this program; if not, write to the Free Software           *}
{*  Foundation, Inc.,                                                     *}
{*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA               *}
{*                                                                        *}
{**************************************************************************}

<h1>Proposition d'information événementielle</h1>

{if $action eq "proposer"}

<p>
Voici ton annonce :
</p>

<table class="bicol">
  <tr><th>{$titre|nl2br}</th></tr>
  <tr><td>{$texte|nl2br}</td></tr>
</table>

<p>
Ce message est à destination
{if $promo_min || $promo_max}
des promotions {if $promo_min}X{$promo_min}{/if} {if $promo_max}jusqu'à X{$promo_max}{else} et plus{/if}
{else}
de toutes les promotions
{/if}
et sera affiché sur la page d'accueil jusqu'au {$peremption|date_format}.
</p>

{if $valid_mesg}
<p>
Tu as ajouté le message suivant à l'intention du validateur : {$valid_mesg|nl2br}
</p>
{/if}

<form action="events/submit" method="post">
  <div>
    <input type="hidden" name="titre"      value="{$titre}" />
    <input type="hidden" name="texte"      value="{$texte}" />
    <input type="hidden" name="promo_min"  value="{$promo_min}" />
    <input type="hidden" name="promo_max"  value="{$promo_max}" />
    <input type="hidden" name="peremption" value="{$peremption}" />
    <input type="hidden" name="valid_mesg" value="{$valid_mesg}" />
    <input type="submit" name="action"     value="Confirmer" />
    <input type="submit" name="action"     value="Modifier" />
  </div>
</form>


{elseif $action eq "confirmer"}

{if $ok}
<p>
Ta proposition a bien été enregistrée, un administrateur va se charger de la valider aussi rapidement que possible.
</p>
<p>
Merci pour ta contribution à la vie du site!
</p>
<p>
<a href="events">Retour à la page d'accueil</a>
</p>
{else}
<p class="erreur">
Une erreur s'est produite pendant l'enregistrement de ta proposition.
Merci de nous <a href="Docs/NousContacter">contacter</a>!
</p>
{/if}

{else}

{include file="include/form.evenement.tpl"}

{/if}


{* vim:set et sw=2 sts=2 sws=2: *}
