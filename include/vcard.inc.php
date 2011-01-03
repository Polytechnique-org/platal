<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

    public function __construct($photos = true, $freetext = null)
    {
        PlVCard::$folding = false;
        $this->freetext = $freetext;
        $this->photos   = $photos;
    }

    public function addProfile($profile)
    {
        $profile = Profile::get($profile);
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
            $entry->addMail(null, $user->bestalias_alternate);
            if ($user->forlife != $user->bestalias) {
                $entry->addMail(null, $user->forlife);
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
                // TODO : find a way to fetch only the "street" part of the address
                $group = $entry->addHome($adr->text, null, null, $adr->postalCode,
                                $adr->locality, $adr->administrativeArea, $adr->country,
                                $adr->hasFlag('current'), $adr->hasFlag('mail'), $adr->hasFlag('mail'));
            }
            foreach ($adr->phones() as $phone) {
                if ($phone->linkType() == Phone::TYPE_FIXED) {
                    $entry->addTel($group, $phone->display, false, true, true, false, false,
                                   $adr->hasFlag('current') && empty($pf->mobile));
                } else if ($phone->linkType() == Phone::TYPE_FAX) {
                    $entry->addTel($group, $phone->display, true, false, false, false, false, false);
                }
            }
        }

        // Pro
        $adrs = $pf->iterAddresses(Profile::ADDRESS_PRO);
        while ($adr = $adrs->next()) {
            if (!$adr->postalCode || !$adr->locality || !$adr->country) {
                $group = $entry->addWork(null, null, null, null,
                                         $adr->text, null, null, null,
                                         null, $adr->administrativeArea, null);
            } else {
                // TODO : link address to company
                $group = $entry->addWork(null, null, null, null,
                                         $adr->text, null, null, $adr->postalCode,
                                         $adr->locality, $adr->administrativeArea, $adr->country);
            }
            foreach ($adr->phones() as $phone) {
                if ($phone->linkType() == Phone::TYPE_FIXED) {
                    $entry->addTel($group, $phone->display);
                } else if ($phone->linkType() == Phone::TYPE_FAX) {
                    $entry->addTel($group, $phone->display, true);
                }
            }
        }

        // Melix
        if (!is_null($user)) {
            $alias = $user->emailAlias();
            if (!is_null($alias) && $pf->alias_pub == 'pub') {
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
                       FROM  profile_photos AS p
                      WHERE  p.pid = {?}", $pf->id());
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
