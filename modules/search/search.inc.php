<?php
/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
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
function getAddressJoin($table) {
    return 'u.user_id = ' . $table . '.pid' . (Env::v('only_current', false) ? ' AND FIND_IN_SET(\'current\', ' . $table . '.flags)' : '');
}
function advancedSearchFromInput()
{
    if ($with_soundex = Env::has('with_soundex')) {
        $nameField      = new RefWithSoundexSField('name', array('n.soundex'), 'search_name',
                                                   'n', 'u.user_id = n.uid');
    } else {
        $nameField      = new NameSField('name', array('n.token'), 'search_name', 'n', 'u.user_id = n.uid');
    }

    $promo1Field        = new PromoSField('promo1', 'egal1', array('u.promo'), '');
    $promo2Field        = new PromoSField('promo2', 'egal2', array('u.promo'), '');
    $womanField         = new RefSField('woman', array('FIND_IN_SET(\'femme\', u.flags) + 1'), '', '', '');
    $subscriberField    = new RefSField('subscriber', array('!(u.perms IN (\'admin\', \'user\')) + 1'), '', '', '');
    $aliveField         = new RefSField('alive', array('(u.deces != 0) + 1'), '', '', '');
    if (Env::v('only_referent') == 'on') {
        $referentField  = new RefSField('only_referent', array('"on"'), 'mentor', 'mt', 'mt.expertise != "" AND mt.uid = u.user_id');
    } else {
        $referentField  = null;
    }

    $townField          = new RefSField('city', array('av.localityId', 'av.postalCode'), 'profile_addresses',
                                        'av', getAddressJoin('av'));
    $countryField       = new RefSField('country', array('ap.countryId'), 'profile_addresses', 'ap', getAddressJoin('ap'));
    $regionField        = new RefSField('region',array('ar.administrativeAreaId'), 'profile_addresses', 'ar', getAddressJoin('ar'));

    $entrepriseField    = new RefSField('entreprise', array('je.name'), '', '','');
    $posteField         = new RefSField('poste', array('ep.description'), 'profile_job', 'ep', 'u.user_id = ep.uid', false);
    $fonctionField      = new RefSField('fonction', array('en.fonction_fr'), 'fonctions_def', 'en',
                                        'u.user_id = profile_job.uid AND fonctions_def.id = profile_job.functionid');
    $secteurField       = new RefSField('secteur', array('fm.sectorid'), 'profile_job', 'fm', 'u.user_id = fm.uid');
    $cvField            = new RefSField('cv', array('u.cv'), '', '', '', false);

    $natField           = new RefSField('nationalite', array('u.nationalite', 'u.nationalite2', 'u.nationalite3'), '', '', '');
    $binetField         = new RefSField('binet', array('b.binet_id'), 'binets_ins', 'b', 'u.user_id=b.user_id');
    $groupexField       = new RefSField('groupex', array('g.id'), array('groups', 'group_members'), array('g', 'gm'),
                                        array("(g.cat = 'GroupesX' OR g.cat = 'Institutions') AND g.pub = 'public'",
                                              'gm.asso_id = g.id AND u.user_id = gm.uid'));
    $sectionField       = new RefSField('section', array('u.section'), '', '', '');
    $schoolField        = new RefSField('school', array('edu.eduid'), 'profile_education', 'edu', 'u.user_id = edu.uid');
    $diplomaField       = new RefSField('diploma', array('edd.degreeid'), 'profile_education', 'edd', 'u.user_id = edd.uid');

    $freeField          = new RefSField('free', array('q.profile_freetext'), '', '', '', false);

    $nwAddressField     = new RefSField('networking_address', array('nw.address'), 'profile_networking', 'nw', 'nw.uid=u.user_id', false);
    if (Env::v('networking_address') == '') {
        $nwTypeField    = new IndexSField('networking_type', array('nwe.network_type'), array('profile_networking', 'profile_networking_enum'),
                                          array('nw', 'nwe'), array('nw.uid = u.user_id', 'nwe.network_type = nw.network_type'));
    } else {
        $nwTypeField    = new IndexSField('networking_type',
                                          array('nwe.network_type'), 'profile_networking_enum', 'nwe', 'nwe.network_type = nw.network_type');
    }
    $nwPhoneField       = new PhoneSField('phone_number', array('t.search_tel'), 'profile_phones', 't', 't.uid = u.user_id');
    return array(
                $nameField, $promo1Field,
                $promo2Field, $womanField, $subscriberField, $aliveField,
                $townField, $countryField, $regionField, $entrepriseField,
                $posteField, $secteurField, $cvField, $natField, $binetField,
                $groupexField, $sectionField, $schoolField, $diplomaField,
                $freeField, $fonctionField, $nwAddressField, $nwTypeField,
                $nwPhoneField, $referentField);
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
