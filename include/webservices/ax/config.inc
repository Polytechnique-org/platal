<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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
 ***************************************************************************/

	global $globals;
        //chemin de stockage de la clé privée  A MODIFIER
        $chemin_cle_privee = $globals->webservice->private_key_ax;
        //phrase cryptant la clé privée
        $phrase = $globals->webservice->pass_ax;
//	"Cryptage de la cle pour polytechnique.org";
            
        //paramètres serveurs  
        $nom_script_renvoi_challenge = $globals->webservice->host_ax."serveur_genere_challenge.php";
        $nom_script_renvoi_infos = $globals->webservice->host_ax."serveur_renvoie_infos_ancien_direct.php";
?>
