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
        $Id: marketing.thanks.tpl,v 1.2 2004-08-31 11:25:41 x2000habouzit Exp $
 ***************************************************************************}

{config_load file="mails.conf" section="marketing_thanks"}
{from full=#from#}
{to addr="$to"}
{subject text="$prenom $nom s'est inscrit à Polytechnique.org !"}
Bonjour,

Nous t'écrivons juste pour t'informer que {$prenom} {$nom} (X{$promo}), que tu avais incité à s'inscrire à Polytechnique.org, vient à l'instant de terminer son inscription !! :o)

Merci de ta participation active à la reconnaissance de ce site !!!

Bien cordialement,
L'équipe Polytechnique.org
"Le portail des élèves & anciens élèves de l'Ecole polytechnique"
{* vim:set et sw=2 sts=2 sws=2: *}
