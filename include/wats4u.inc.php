<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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

define('WATS4U_SCHOOL_NAME', "École polytechnique");

function _map_profile(Profile $profile)
{
    return array(
        'hrid' => $profile->hrid(),
        'school' => WATS4U_SCHOOL_NAME,
        'diploma' => $profile->mainGrade(),
        'promo' => $profile->yearpromo(),
        'first_name' => $profile->firstname_ordinary,
        'last_name' => $profile->lastname_ordinary,
        'birth_name' => $profile->lastname,
        'female' => $profile->isFemale(),
        'email' => '',
        'paying' => 1,
    );
}

function _filter_profile(Profile $profile)
{
    return !$profile->isDead();
}

function generate_wats4u_extract()
{
    $pf = new ProfileFilter(new PFC_True());
    // For debug: replace with iterProfiles(new PlLimit(100));
    $profiles = $pf->iterProfiles();
    $alive_profiles = PlIteratorUtils::filter($profiles, '_filter_profile');
    return PlIteratorUtils::map($alive_profiles, '_map_profile');
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
