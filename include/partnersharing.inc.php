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

// {{{ class PartnerSharing
// Holds data about a "directory partner".
class PartnerSharing
{
    public $id;
    public $shortname;
    public $name;
    public $url;
    public $has_directory = false;
    public $has_bulkmail = false;
    public $default_sharing_level = Visibility::VIEW_NONE;
    protected $api_uid = null;

    protected function __construct(array $data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    public function apiUser()
    {
        return User::getSilentWithUID($this->api_uid);
    }

    public static function fetchByAPIUser(User $user)
    {
        $res = XDB::fetchOneAssoc('SELECT  id, shortname, name, url,
                                           has_directory, has_bulkmail,
                                           default_sharing_level, api_uid
                                     FROM  profile_partnersharing_enum
                                    WHERE  api_uid = {?}', $user->uid);
        if ($res == null) {
            return null;
        } else {
            return new PartnerSharing($res);
        }
    }

    public static function fetchById($id)
    {
        $res = XDB::fetchOneAssoc('SELECT  id, shortname, name, url,
                                           has_directory, has_bulkmail,
                                           default_sharing_level, api_uid
                                     FROM  profile_partnersharing_enum
                                    WHERE  id = {?}', $id);
        if ($res == null) {
            return null;
        } else {
            return new PartnerSharing($res);
        }
    }
}
// }}}
// {{{ class PartnerSettings
class PartnerSettings
{
    public $exposed_uid;
    public $sharing_level;
    public $allow_email = false;
    protected $partner_id;
    public $partner = null;

    public function __construct(array $data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
        $this->partner = PartnerSharing::fetchById($this->partner_id);
        $this->sharing_visibility = Visibility::get($this->sharing_level);
    }

    public static function getEmpty($partner_id)
    {
        $data = array(
            'partner_id' => $partner_id,
            'exposed_uid' => 0,
            'sharing_level' => Visibility::VIEW_NONE,
            'allow_email' => false,
        );
        return new PartnerSettings($data);
    }
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
