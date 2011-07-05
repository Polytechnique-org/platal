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

class VCard extends PlVCard
{
    private $profile_list = array();
    private $count     = 0;
    private $freetext  = null;
    private $photos    = true;
    private $visibility;

    public function __construct($photos = true, $freetext = null)
    {
        PlVCard::$folding = false;
        $this->visibility = Visibility::defaultForRead(Visibility::VIEW_PRIVATE);
        $this->freetext = $freetext;
        $this->photos   = $photos;
    }

    public function addProfile($profile)
    {
        $profile = Profile::get($profile, Profile::FETCH_ALL, $this->visibility);
        if ($profile) {
            $this->profile_list[] = $profile;
            $this->count++;
        }
    }

    public function addProfiles(array $profiles) {
        foreach ($profiles as $profile) {
            $this->addProfile($profile);
        }
    }

    protected function fetch()
    {
        return PlIteratorUtils::fromArray($this->profile_list);
    }

    protected function buildEntry($pf)
    {
        global $globals;
        $pf = $pf['value'];

        $entry = new PlVCardEntry($pf->firstNames(), $pf->lastNames(), null, null, $pf->nickname);

        $user = $pf->owner();

        // Free text
        $freetext = '(' . $pf->promo . ')';
        if ($this->freetext) {
            $freetext .= "\n" . $this->freetext;
        }
        $entry->set('NOTE', $freetext);
        if ($pf->mobile) {
            $entry->addTel(null, $pf->mobile, false, true, true, false, true, true);
        }

        // Emails
        if (!is_null($user)) {
            $entry->addMail(null, $user->bestalias, true);
            if ($user->forlife != $user->bestalias) {
                $entry->addMail(null, $user->forlife);
            }
            if ($user->forlife_alternate != $user->bestalias) {
                $entry->addMail(null, $user->forlife_alternate);
            }
        }

        // Homes
        $adrs = $pf->iterAddresses(Profile::ADDRESS_PERSO);
        while ($adr = $adrs->next()) {
            if (!$adr->postalCode || !$adr->locality || !$adr->country) {
                $group = $entry->addHome($adr->text, null, null, null,
                                null, $adr->administrativeArea, null,
                                $adr->hasFlag('current'), $adr->hasFlag('mail'), $adr->hasFlag('mail'));
            } else {
                $group = $entry->addHome(trim(Geocoder::getFirstLines($adr->text, $adr->postalCode, 4)), null, null, $adr->postalCode,
                                $adr->locality, $adr->administrativeArea, $adr->country,
                                $adr->hasFlag('current'), $adr->hasFlag('mail'), $adr->hasFlag('mail'));
            }
            foreach ($adr->phones() as $phone) {
                if ($phone->link_type == Phone::TYPE_FIXED) {
                    $entry->addTel($group, $phone->display, false, true, true, false, false,
                                   $adr->hasFlag('current') && empty($pf->mobile));
                } else if ($phone->link_type == Phone::TYPE_FAX) {
                    $entry->addTel($group, $phone->display, true, false, false, false, false, false);
                }
            }
        }

        // Pro
        $jobs = $pf->getJobs();
        foreach ($jobs as $job) {
            $terms_array = array();
            foreach ($job->terms as $term) {
               $terms_array[] = $term->full_name;
            }
            $terms = implode(', ', $terms_array);
            if ($job->address) {
                if (!$job->address->postalCode || !$job->address->locality || !$job->address->country) {
                    $group = $entry->addWork($job->company->name, null, $job->description, $terms,
                                             $job->address->text, null, null, null,
                                             null, $job->address->administrativeArea, null);
                } else {
                    $group = $entry->addWork($job->company->name, null, $job->description, $terms,
                                             trim(Geocoder::getFirstLines($job->address->text, $job->address->postalCode, 4)),
                                             null, null, $job->address->postalCode,
                                             $job->address->locality, $job->address->administrativeArea, $job->address->country);
                }
            } else {
                $group = $entry->addWork($job->company->name, null, $job->description, $terms,
                                         null, null, null, null,
                                         null, null, null);
            }
            if ($job->user_email) {
                $entry->addMail($group, $job->user_email);
            }
            foreach ($job->phones as $phone) {
                if ($phone->type == Phone::TYPE_MOBILE) {
                    $entry->addTel($group, $phone->display, false, true, true, false, true);
                } else if ($phone->type == Phone::TYPE_FAX) {
                    $entry->addTel($group, $phone->display, true);
                } else {
                    $entry->addTel($group, $phone->display, false, true, true);
                }
            }
        }

        // Melix
        if (!is_null($user)) {
            $alias = $user->emailAlias();
            if (!is_null($alias) && $pf->alias_pub == 'public') {
                $entry->addMail(null, $alias);
            }
        }

        // Custom fields
        if (!is_null($user)) {
            $groups = $user->groups(true, true);
            if (count($groups)) {
                $gn = DirEnum::getOptions(DirEnum::GROUPESX);
                $gns = array();
                foreach (array_keys($groups) as $gid) {
                    $gns[$gid] = $gn[$gid];
                }
                $entry->set('X-GROUPS', join(', ', $gns));
            }
        }

        $binets = $pf->getBinets();

        if (count($binets)) {
            $bn = DirEnum::getOptions(DirEnum::BINETS);
            $bns = array();
            foreach ($binets as $bid) {
                $bns[$bid] = $bn[$bid];
            }
            $entry->set('X-BINETS', join(', ', $bns));
        }
        if (!empty($pf->section)) {
            $entry->set('X-SECTION', $pf->section);
        }

        // Photo
        if ($this->photos) {
            $res = XDB::query(
                    "SELECT  attach, attachmime
                       FROM  profile_photos
                      WHERE  pid = {?} AND pub IN ('public', {?})",
                    $pf->id(), $this->visibility->level());
            if ($res->numRows()) {
                list($data, $type) = $res->fetchOneRow();
                $entry->setPhoto($data, strtoupper($type));
            }
        }
        return $entry;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
