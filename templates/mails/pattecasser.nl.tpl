{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: pattecasser.nl.tpl,v 1.2 2004-08-31 11:25:41 x2000habouzit Exp $
 ***************************************************************************}

{config_load file="mails.conf" section="pattecassee_nl"}
{subject text="Une de tes adresses de redirection Polytechnique.org ne marche plus !!"}
{from full=#from#}
{to addr=$dest}

  Bonjour !
	
  Nous t'écrivons car lors de l'envoi de la lettre d'information mensuelle de Polytechnique.org à ton adresse polytechnicienne {$dest}@polytechnique.org, l'adresse {$email}, sur laquelle tu rediriges ton courrier, ne fonctionnait pas.
  Estimant que cette information serait susceptible de t'intéresser, nous avons préféré t'en informer. Il n'est pas impossible qu'il ne s'agisse que d'une panne temporaire.
  Si tu souhaites changer la liste des adresses sur lesquelles tu reçois le courrier qui t'es envoyé à ton adresse polytechnicienne, il te suffit de te rendre sur la page :
  https://www.polytechnique.org/emails.php
  
  A bientôt sur Polytechnique.org !
  L'équipe d'administration <support@polytechnique.org>
  
  PS : si jamais tu ne disposes plus du mot de passe te permettant d'accéder au site, rends toi sur la page https://www.polytechnique.org/recovery.php ; elle te permettra de créer un nouveau mot de passe après avoir rentré ton login ({$dest}) et ta date de naissance !";
  
{* vim:set et sw=2 sts=2 sws=2: *}
