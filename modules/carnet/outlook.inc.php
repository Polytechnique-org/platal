<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

class Outlook {

    static $contact_fields = array(
        'fr' => array("Nom","Titre","Prénom","Deuxième prénom","Nom","Suffixe","Surnom","Société ","Service ","Titre","Rue (bureau)","Rue (bureau) 2","Rue (bureau) 3","Ville (bureau)","Dép/Région (bureau)","Code postal (bureau)","Pays (bureau)","Rue (domicile)","Rue (domicile) 2","Rue (domicile) 3","Ville (domicile)","Dép/Région (domicile)","Code postal (domicile)","Pays (domicile)","Rue (autre)","Rue (autre) 2","Rue (autre) 3","Ville (autre)","Dép/Région (autre)","Code postal (autre)","Pays (autre)","Téléphone de l'assistant(e)","Télécopie (bureau)","Téléphone (bureau)","Téléphone 2 (bureau)","Rappel","Téléphone (voiture)","Téléphone société","Télécopie (domicile)","Téléphone (domicile)","Téléphone 2 (domicile)","RNIS","Tél. mobile","Télécopie (autre)","Téléphone (autre)","Récepteur de radiomessagerie","Téléphone principal","Radio téléphone","Téléphone TDD/TTY","Télex","Adresse de messagerie","Type de messagerie","Nom complet de l'adresse de messagerie","Adresse de messagerie 2","Type de messagerie 2","Nom complet de l'adresse de messagerie 2","Adresse de messagerie 3","Type de messagerie 3","Nom complet de l'adresse de messagerie 3","Anniversaire","Anniversaire de mariage ou fête","Autre boîte postale","B.P. professionnelle","Boîte postale du domicile","Bureau","Catégories","Code gouvernement","Compte","Conjoint(e)","Critère de diffusion","Disponibilité Internet","Emplacement","Enfants","Informations facturation","Initiales","Kilométrage","Langue","Mots clés","Nom de l'assistant(e)","Notes","Numéro d'identification de l'organisation","Page Web","Passe-temps","Priorité","Privé","Profession","Recommandé par","Responsable","Serveur d'annuaire","Sexe","Utilisateur 1","Utilisateur 2","Utilisateur 3","Utilisateur 4"),
        );

    private static function add_address(&$adr, &$contact, $adr_type = 'autre') {
        $contact['Rue ('.$adr_type.')'] = $adr->text;
        $contact['Code postal ('.$adr_type.')'] = $adr->postalCode;
        $contact['Ville ('.$adr_type.')'] = $adr->locality;
        $contact['Dép/Région ('.$adr_type.')'] = $adr->administrativeArea;
        $contact['Pays ('.$adr_type.')'] = $adr->country;
        $phones = $adr->phones();
        foreach ($phones as $p) {
            if ($p->hasType(Profile::PHONE_TYPE_FIXED)) {
                $contact['Téléphone ('.$adr_type.')'] = $p->display;
            }
            if ($p->hasType(Profile::PHONE_TYPE_FAX)) {
                $contact['Télécopie ('.$adr_type.')'] = $p->display;
            }
        }
    }

    private static function profile_to_contact(&$p) {
        $contact = array(
            'Prénom' => $p->firstName(),
            'Nom' => $p->lastName(),
            'Notes' => '('.$p->promo.')',
            'Tél. mobile' => $p->mobile,
            'Anniversaire' => $p->birthdate,
            'Surnom' => $p->nickname,
        );
        // Homes
        $adrs = $p->iterAddresses(Profile::ADDRESS_PERSO);
        if ($adr = $adrs->next()) {
            Outlook::add_address(&$adr, &$contact, 'domicile');
        }
        if ($adr = $adrs->next()) {
            Outlook::add_address(&$adr, &$contact, 'autre');
        }
        // Pro
        $adrs = $p->iterAddresses(Profile::ADDRESS_PRO);
        if ($adr = $adrs->next()) {
            Outlook::add_address(&$adr, &$contact, 'bureau');
        }
        $mainjob = $p->getMainJob();
        if ($mainjob && $mainjob->company) {
            $contact['Société '] = $mainjob->company->name;
        }
        if (!empty($p->section)) {
            $contact['Utilisateur 2'] = 'Section : '. $p->section;
        }
        if ($p->isFemale()) {
            $contact['Sexe'] = 'Féminin';
        } else {
            $contact['Sexe'] = 'Masculin';
        }
        $binets = $p->getBinets();
        if (count($binets)) {
            $bn = DirEnum::getOptions(DirEnum::BINETS);
            $bns = array();
            foreach (array_keys($binets) as $bid) if (!empty($bn[$bid])) {
                $bns[$bid] = $bn[$bid];
            }
            if (count($bns) > 0) {
                $contact['Utilisateur 3'] = 'Binets : '.join(', ', $bns);
            }
        }
        $user = $p->owner();
        if ($user) {
            $contact['Adresse de messagerie'] = $user->bestalias;
            $contact['Nom complet de l\'adresse de messagerie'] = $p->fullName().' <'.$user->bestalias.'>';
            $contact['Adresse de messagerie 2'] = $user->bestalias_alternate;
            $contact['Nom complet de l\'adresse de messagerie 2'] = $p->fullName().' <'.$user->bestalias_alternate.'>';
            if ($user->bestalias != $user->forlife) {
                $contact['Adresse de messagerie 3'] = $user->forlife;
                $contact['Nom complet de l\'adresse de messagerie 3'] = $p->fullName().' <'.$user->forlife.'>';
            }
            $groups = $user->groups();
            if (count($groups)) {
                $gn = DirEnum::getOptions(DirEnum::GROUPESX);
                $gns = array();
                foreach (array_keys($groups) as $gid) if (!empty($gn[$gid])) {
                    $gns[$gid] = $gn[$gid];
                }
                if (count($gns) > 0) {
                    $contact['Utilisateur 1'] = 'Groupes X : '. join(', ', $gns);
                }
            }
        }
        return $contact;
    }

    private static function protect(&$t) {
        if (empty($t)) {
            return '""';
        }
        $t = preg_replace("/\r?\n/", ", ", $t);
        return '"'.strtr(utf8_decode($t),'"', '\\"').'"';
    }

    public static function output_profiles($profiles, $lang) {
        pl_content_headers("text/plain", "iso8859-15");
        $fields =& Outlook::$contact_fields[$lang];
        foreach ($fields as $i => $k) {
            if ($i != 0) {
                echo ',';
            }
            echo Outlook::protect($k);
        }
        echo "\r\n";
        foreach ($profiles as &$p) {
            $values = Outlook::profile_to_contact(&$p);
            foreach ($fields as $i => $k) {
                if ($i != 0) {
                    echo ',';
                    echo Outlook::protect($values[$k]);
                } else {
                    // HACK to fix fullname
                    $fullname = $p->firstName()." ".$p->lastName();
                    echo Outlook::protect($fullname);
                }
        }
            echo "\r\n";
        }
    }
}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
