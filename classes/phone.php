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

/** Class Phone is meant to perform most of the access to the table profile_phones.
 *
 * profile_phone describes a Phone, which can be related to an Address,
 * a Job, a Profile or a Company:
 * - for a Profile:
 *   - `link_type` is set to 'user'
 *   - `link_id` is set to 0
 *   - `pid` is set to the id of the related Profile (in profiles)
 *
 * - for a Company:
 *   - `link_type` is set to 'hq'
 *   - `link_id` is set to the id of the related Company (in profile_job_enum)
 *   - `pid` is set to 0
 *
 * - for an Address (this only applies to a personal address)
 *   - `link_type` is set to 'address'
 *   - `link_id` is set to the related Address `id` (in profile_addresses)
 *   - `pid` is set to the related Address `pid` (in both profiles and profile_addresses)
 *
 * - for a Job:
 *   - `link_type` is set to 'pro'
 *   - `link_id` is set to the related Job `id` (not `jobid`) (in profile_job)
 *   - `pid` is set to the related Job `pid` (in both profiles and profile_job)
 *
 * Thus a Phone can be linked to a Company, a Profile, a Job, or a Profile-related Address.
*/
class Phone
{
    const TYPE_FAX    = 'fax';
    const TYPE_FIXED  = 'fixed';
    const TYPE_MOBILE = 'mobile';

    const LINK_JOB     = 'pro';
    const LINK_ADDRESS = 'address';
    const LINK_PROFILE = 'user';
    const LINK_COMPANY = 'hq';
    const LINK_GROUP   = 'group';

    /** The following fields, but $error, all correspond to the fields of the
     * database table profile_phones.
     */
    public $id = 0;
    public $pid = 0;
    public $search = '';
    public $link_type = 'user';
    public $link_id = 0;
    // The following fields are the fields of the form in the profile edition.
    public $type = 'fixed';
    public $display = '';
    public $pub = 'ax';
    public $comment = '';
    public $error = false;

    public function __construct(array $data = array())
    {
        if (count($data) > 0) {
            foreach ($data as $key => $val) {
                $this->$key = $val;
            }
        }
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /** Returns the unique ID of a phone.
     * This ID will allow to link it to an address, a user or a job.
     * The format is address_addressId_phoneId (where phoneId is the id
     * of the phone in the list of those associated with the address).
     */
    public function uniqueId() {
        return $this->link_type . '_' . $this->link_id . '_' . $this->id;
    }

    public function hasFlags($flags) {
        return $this->hasType($flags) && $this->hasLink($flags);
    }

    /** Returns true if this phone's type matches the flags.
     */
    public function hasType($flags) {
        $flags = $flags & Profile::PHONE_TYPE_ANY;
        return (
            ($flags == Profile::PHONE_TYPE_ANY)
            ||
            (($flags & Profile::PHONE_TYPE_FAX) && $this->type == self::TYPE_FAX)
            ||
            (($flags & Profile::PHONE_TYPE_FIXED) && $this->type == self::TYPE_FIXED)
            ||
            (($flags & Profile::PHONE_TYPE_MOBILE) && $this->type == self::TYPE_MOBILE)
        );
    }

    /** User-friendly accessible version of the type.
     */
    public function displayType($short = false)
    {
        switch ($this->type) {
          case Phone::TYPE_FIXED:
            return $short ? 'Tél' : 'Fixe';
          case Phone::TYPE_FAX:
            return 'Fax';
          case Phone::TYPE_MOBILE:
            return $short ? 'Mob' : 'Mobile';
          default:
            return $this->type;
        }
    }

    /** Returns true if this phone's link matches the flags.
     */
    public function hasLink($flags)
    {
        $flags = $flags & Profile::PHONE_LINK_ANY;
        return (
            ($flags == Profile::PHONE_LINK_ANY)
            ||
            (($flags & Profile::PHONE_LINK_COMPANY) && $this->link_type == self::LINK_COMPANY)
            ||
            (($flags & Profile::PHONE_LINK_JOB) && $this->link_type == self::LINK_JOB)
            ||
            (($flags & Profile::PHONE_LINK_ADDRESS) && $this->link_type == self::LINK_ADDRESS)
            ||
            (($flags & Profile::PHONE_LINK_PROFILE) && $this->link_type == self::LINK_PROFILE)
        );
    }

    /* Properly formats the search phone, based on actual display phone.
     *
     * Computes a base form of the phone number with the international prefix.
     * This number only contains digits, thus does not begin with the '+' sign.
     * Numbers starting with 0 (or '(0)') are considered as French.
     * This assumes that non-French numbers have their international prefix.
     */
    private function formatSearch()
    {
        $tel = trim($this->display);
        // Number starting with "(0)" is a French number.
        if (substr($tel, 0, 3) === '(0)') {
            $tel = '33' . $tel;
        }
        // Removes all "(0)" often used as local prefix.
        $tel = str_replace('(0)', '', $tel);
        // Removes all non-digit chars.
        $tel = preg_replace('/[^0-9]/', '', $tel);

        if (substr($tel, 0, 2) === '00') {
            // Removes prefix for international calls.
            $tel = substr($tel, 2);
        } else if (substr($tel, 0, 1) === '0') {
            // Number starting with 0 is a French number.
            $tel = '33' . substr($tel, 1);
        }
        $this->search = $tel;
    }

    // Properly formats the display phone, it requires the search phone to be already formatted.
    private function formatDisplay($format = array())
    {
        $tel = $this->search;
        $ret = '';
        $telLength = strlen($tel);
        // Try to find the country by trying to find a matching prefix of 1, 2 or 3 digits.
        if ((!isset($format['phoneprf'])) || ($format['phoneprf'] == '')) {
            $res = XDB::query('SELECT  phonePrefix AS phoneprf, phoneFormat AS format
                                 FROM  geoloc_countries
                                WHERE  phonePrefix = SUBSTRING({?}, 1, LENGTH(phonePrefix))
                             ORDER BY  LENGTH(phonePrefix) DESC
                                LIMIT  1',
                              $tel);
            if ($res->numRows() == 0) {
                // No country found, does not format more than prepending a '+' sign.
                $this->error = true;
                $this->display = '+' . $tel;
                return;
            }
            $format = $res->fetchOneAssoc();
        }
        if ($format['format'] == '') {
            // If the country does not have a phone number format, the number will be displayed
            // as "+prefix ## ## ## ##...".
            $format['format'] = '(+p)';
        }

        /* Formats the phone number according t the template with these rules:
         *  - p is replaced by the international prefix,
         *  - # is replaced by one digit,
         *  - other chars are left intact.
         * If the number is longer than the format, remaining digits are
         * appended by blocks of two digits separated by spaces.
         * The last block can have 3 digits to avoid a final single-digit block.
         */
        $j = 0;
        $i = strlen($format['phoneprf']);
        $lengthFormat = strlen($format['format']);
        while (($i < $telLength) && ($j < $lengthFormat)) {
            if ($format['format'][$j] == '#') {
                $ret .= $tel[$i];
                ++$i;
            } else if ($format['format'][$j] == 'p') {
                $ret .= $format['phoneprf'];
            } else {
                $ret .= $format['format'][$j];
            }
            ++$j;
        }
        for (; $i < $telLength - 1; $i += 2) {
            $ret .= ' ' . substr($tel, $i, 2);
        }
        // Appends last left alone numbers to the last block.
        if ($i < $telLength) {
            $ret .= substr($tel, $i);
        }
        $this->display = $ret;
    }


    public function format($format = array())
    {
        if (!($this->type == Phone::TYPE_FIXED
              || $this->type == Phone::TYPE_MOBILE
              || $this->type == Phone::TYPE_FAX)) {
            $this->type = Phone::TYPE_FIXED;
        }
        $this->formatSearch();
        $this->formatDisplay($format);
        return !$this->error;
    }

    public function toFormArray()
    {
        return array(
            'type'    => $this->type,
            'display' => $this->display,
            'pub'     => $this->pub,
            'comment' => $this->comment,
            'error'   => $this->error
        );
    }

    private function toString()
    {
        static $pubs = array('public' => 'publique', 'ax' => 'annuaire AX', 'private' => 'privé');
        static $types = array('fax' => 'fax', 'fixed' => 'fixe', 'mobile' => 'mobile');
        return $this->display . ' (' . $types[$this->type] . (($this->comment) ? ', commentaire : « ' . $this->comment . ' »' : '')
            . ', affichage ' . $pubs[$this->pub] . ')';
    }

    private function isEmpty()
    {
        return (!$this->search || $this->search == '');
    }

    public function save()
    {
        $this->format();
        if (!$this->isEmpty()) {
            XDB::execute('INSERT IGNORE INTO  profile_phones (pid, link_type, link_id, tel_id, tel_type,
                                                              search_tel, display_tel, pub, comment)
                                      VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})',
                         $this->pid, $this->link_type, $this->link_id, $this->id, $this->type,
                         $this->search, $this->display, $this->pub, $this->comment);
        }
    }

    public function delete()
    {
        XDB::execute('DELETE FROM  profile_phones
                            WHERE  pid = {?} AND link_type = {?} AND link_id = {?} AND tel_id = {?}',
                     $this->pid, $this->link_type, $this->link_id, $this->id);
    }

    static public function deletePhones($pid, $link_type, $link_id = null, $deletePrivate = true)
    {
        $where = '';
        if (!is_null($link_id)) {
            $where = XDB::format(' AND link_id = {?}', $link_id);
        }
        XDB::execute('DELETE FROM  profile_phones
                            WHERE  pid = {?} AND link_type = {?}' . $where . (($deletePrivate) ? '' : ' AND pub IN (\'public\', \'ax\')'),
                     $pid, $link_type);
    }

    /** Saves phones into the database.
     * @param $data: an array of form formatted phones.
     * @param $pid, $link_type, $link_id: pid, link_type and link_id concerned by the update.
     */
    static public function savePhones(array $data, $pid, $link_type, $link_id = null)
    {
        foreach ($data as $id => $value) {
            $value['id'] = $id;
            if (!is_null($pid)) {
                $value['pid'] = $pid ;
            }
            if (!is_null($link_type)) {
                $value['link_type'] = $link_type ;
            }
            if (!is_null($link_id)) {
                $value['link_id'] = $link_id ;
            }
            $phone = new Phone($value);
            $phone->save();
        }
    }

    static private function formArrayWalk(array $data, $function, &$success = true, $requiresEmptyPhone = false, $maxPublicity = null)
    {
        $phones = array();
        if (!is_null($data)) {
            foreach ($data as $item) {
                $phone = new Phone($item);
                $success = (!$phone->error && ($phone->format() || $phone->isEmpty()) && $success);
                if (!$phone->isEmpty()) {
                    // Restrict phone visibility to $maxPublicity
                    if (!is_null($maxPublicity) && Visibility::isLessRestrictive($maxPublicity, $phone->pub)) {
                        $phone->pub = $maxPublicity;
                    }
                    $phones[] = call_user_func(array($phone, $function));
                }
            }
        }
        if (count($phones) == 0 && $requiresEmptyPhone) {
            $phone = new Phone();
            if (!is_null($maxPublicity) && Visibility::isLessRestrictive($maxPublicity, $phone->pub)) {
                // Restrict phone visibility to $maxPublicity
                $phone->pub = $maxPublicity;
            }
            $phones[] = call_user_func(array($phone, $function));
        }
        return $phones;
    }

    // Formats an array of form phones into an array of form formatted phones.
    static public function formatFormArray(array $data, &$success = true, $maxPublicity = null)
    {
        $phones = self::formArrayWalk($data, 'toFormArray', $success, true, $maxPublicity);
        usort($phones, 'Visibility::comparePublicity');
        return $phones;
    }

    static public function formArrayToString(array $data)
    {
        return implode(', ', self::formArrayWalk($data, 'toString'));
    }

    static public function hasPrivate(array $phones)
    {
        foreach ($phones as $phone) {
            if ($phone['pub'] == 'private') {
                return true;
            }
        }
        return false;
    }

    static public function iterate(array $pids = array(), array $link_types = array(),
                                   array $link_ids = array(), $visibility = null)
    {
        return new PhoneIterator($pids, $link_types, $link_ids, $visibility);
    }
}

/** Iterator over a set of Phones
 *
 * @param $pid, $link_type, $link_id, $pub
 *
 * The iterator contains the phones that correspond to the value stored in the
 * parameters' arrays.
 */
class PhoneIterator implements PlIterator
{
    private $dbiter;

    public function __construct(array $pids, array $link_types, array $link_ids, $visibility)
    {
        $where = array();
        if (count($pids) != 0) {
            $where[] = XDB::format('(pid IN {?})', $pids);
        }
        if (count($link_types) != 0) {
            $where[] = XDB::format('(link_type IN {?})', $link_types);
        }
        if (count($link_ids) != 0) {
            $where[] = XDB::format('(link_id IN {?})', $link_ids);
        }
        if ($visibility == null || !($visibility instanceof Visibility)) {
            $visibility = Visibility::defaultForRead();
        }
        $where[] = 'pve.best_display_level+0 <= pub+0';

        $sql = 'SELECT  search_tel AS search, display_tel AS display, comment, link_id,
                        tel_type AS type, link_type, tel_id AS id, pid, pub
                  FROM  profile_phones
             LEFT JOIN  profile_visibility_enum AS pve ON (pve.access_level = {?})
                 WHERE  ' . implode(' AND ', $where) . '
              ORDER BY  pid, link_id, tel_id';
        $this->dbiter = XDB::iterator($sql, $visibility->level());
    }

    public function next()
    {
        if (is_null($this->dbiter)) {
            return null;
        }
        $data = $this->dbiter->next();
        if (is_null($data)) {
            return null;
        }
        return new Phone($data);
    }

    public function total()
    {
        return $this->dbiter->total();
    }

    public function first()
    {
        return $this->dbiter->first();
    }

    public function last()
    {
        return $this->dbiter->last();
    }

    public function value()
    {
        return $this->dbiter;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
