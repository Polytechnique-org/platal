<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

require_once dirname(__FILE__).'/classes.inc.php';

// {{{ function advancedSearchFromInput
function getadr_join($table) {
    return 'u.user_id='.$table.'.uid'.(Env::v('only_current',false)?' AND FIND_IN_SET(\'active\','.$table.'.statut)':'');
}
function advancedSearchFromInput()
{
    if ($with_soundex = Env::has('with_soundex')) {
        $nameField      = new RefWithSoundexSField('name',array('rn.nom1_soundex','rn.nom2_soundex','rn.nom3_soundex'),'recherche_soundex','rn','u.matricule = rn.matricule');
        $firstnameField = new RefWithSoundexSField('firstname',array('rp.prenom1_soundex','rp.prenom2_soundex'),'recherche_soundex','rp','u.matricule = rp.matricule');
    } else {
        $nameField      = new NameSField('name',array('u.nom','u.nom_usage'),'');
        $firstnameField = new StringSField('firstname',array('u.prenom'),'');
    }
    $nicknameField   = new StringSField('nickname',array('q.profile_nick'),'');

    $promo1Field     = new PromoSField('promo1','egal1',array('u.promo'),'');
    $promo2Field     = new PromoSField('promo2','egal2',array('u.promo'),'');
    $womanField      = new RefSField('woman',array('FIND_IN_SET(\'femme\',u.flags)+1'),'','','');
    $subscriberField = new RefSField('subscriber',array('!(u.perms IN (\'admin\',\'user\'))+1'),'','','');
    $aliveField      = new RefSField('alive',array('(u.deces!=0)+1'),'','','');
    if (Env::v('only_referent') == 'on') {
        $referentField = new RefSField('only_referent', array('"on"'), 'mentor', 'mt', 'mt.expertise != "" AND mt.uid=u.user_id');
    } else {
        $referentField = null;
    }

    if (!Env::i('cityid')) {
        $townField      = new RefSField('city',array('ac.city'),'adresses','ac',getadr_join('ac'),false);
    } else {
        $townField    = new RefSField('cityid',array('av.cityid'),'adresses','av',getadr_join('av'));
    }
    $countryField   = new RefSField('country',array('ap.country'),'adresses','ap',getadr_join('ap'));
    $regionField    = new RefSField('region',array('ar.region'),'adresses','ar',getadr_join('ar'));
    $mapField       = new MapSField('mapid', array('sgcim.map_id'), array('adresses','geoloc_city_in_maps'), array('amp','sgcim'), array(getadr_join('amp'), 'amp.cityid = sgcim.city_id'));

    $entrepriseField = new RefSField('entreprise',array('ee.entreprise'),'entreprises','ee','u.user_id=ee.uid',false);
    $posteField      = new RefSField('poste',array('ep.poste'),'entreprises','ep','u.user_id=ep.uid', false);
    $fonctionField = new RefSField('fonction',array('en.fonction'),'entreprises','en','u.user_id=en.uid');
    $secteurField    = new RefSField('secteur',array('fm.secteur'),'entreprises','fm','u.user_id=fm.uid');
    $cvField         = new RefSField('cv',array('u.cv'),'','','',false);

    $natField        = new RefSField('nationalite',array('u.nationalite', 'u.nationalite2', 'u.nationalite3'),'','','');
    $binetField      = new RefSField('binet',array('b.binet_id'),'binets_ins','b','u.user_id=b.user_id');
    $groupexField    = new RefSField('groupex',array('g.id'),array('groupex.asso', 'groupex.membres'),array('g', 'gm'),
                                     array("(g.cat = 'GroupesX' OR g.cat = 'Institutions') AND g.pub = 'public'",
                                           'gm.asso_id = g.id AND u.user_id=gm.uid'));
    $sectionField    = new RefSField('section',array('u.section'),'','','');
    $schoolField     = new RefSField('school',array('as.aid'),'applis_ins','`as`','u.user_id=as.uid');
    $diplomaField    = new RefSField('diploma',array('ad.type'),'applis_ins','ad','u.user_id=ad.uid');

    $freeField       = new RefSField('free',array('q.profile_freetext'),'','','',false);

    $nwAddressField  = new RefSField('networking_address', array('nw.address'), 'profile_networking', 'nw', 'nw.uid=u.user_id', false);
    if (Env::v('networking_address') == '') {
        $nwTypeField     = new IndexSField('networking_type', array('nwe.network_type'), array('profile_networking', 'profile_networking_enum'), array('nw', 'nwe'), array('nw.uid = u.user_id', 'nwe.network_type = nw.network_type'));
    } else {
        $nwTypeField     = new IndexSField('networking_type', array('nwe.network_type'), 'profile_networking_enum', 'nwe', 'nwe.network_type = nw.network_type');
    }
    $nwPhoneField  = new PhoneSField('phone_number', array('t.search_tel'), 'profile_phones', 't', 't.uid=u.user_id');
    return array(
                $nameField, $firstnameField, $nicknameField, $promo1Field,
                $promo2Field, $womanField, $subscriberField, $aliveField,
                $townField, $countryField, $regionField, $mapField, $entrepriseField,
                $posteField, $secteurField, $cvField, $natField, $binetField,
                $groupexField, $sectionField, $schoolField, $diplomaField,
                $freeField, $fonctionField, $nwAddressField, $nwTypeField,
                $nwPhoneField, $referentField);
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
