{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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

<h1>{$asso->nom}&nbsp;: Evénement {$event.intitule}</h1>

<p class='descr'>
  {assign var=profile value=$event.organizer->profile()}
  Cet événement a lieu <strong>{$event.date}</strong> et a été proposé par
  <a href='https://www.polytechnique.org/profile/{$profile->hrpid}' class='popup2'>
    {$event.organizer->fullName('promo')}
  </a>.
</p>

<p class='descr'>
  {$event.descriptif|nl2br}
</p>

{if $form_sent}
Un email de confirmation vous à été envoyé à {$email}.
{else}
<form action="{$platal->ns}events/sub/{$eid}" method="post">
  {xsrf_token_field}
  <p style="text-align:center">
    Nom : <input type="text" name='nom' />
    Prénom : <input type="text" name='prenom' />
    E-mail : <input type="text" name='email' />
	<br/>
	<br/>
	<br/>
    <input type="submit" name='submit' value="Valider mes inscriptions" />
  </p>
</form>
{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}

