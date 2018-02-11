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

require_once 'webservices/manageurs.inc.php';

function get_annuaire_infos($amicale, $id_assoce, $adresse){
    $url = '';

    $url = 'http://www.polytechniciens.org:80/manageurs.php';
    //decommenter pour ajouter un webservice chez l'AX :
    // return array('adresse' => array(0 => array('adr1' => 'test AX', 'city' => 'Trou perdu')));

    $client = new XmlrpcClient($url);

    global $globals;
    if($array = $client->get_annuaire_infos($globals->webservice->pass, $id_assoce, $adresse)){

        if( is_string($array) ){
            $erreur = xmlrpc_decode($array);
            echo $erreur['erreurstring']."\n";
            return $erreur['erreur'];
        }
        else{
            manageurs_encrypt_init($id_assoce);
            $reply = manageurs_decrypt_array($array);
            manageurs_encrypt_close();
            return $reply;
        }
    }
    else return false;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
