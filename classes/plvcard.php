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
 **************************************************************************/

/** Describe a field.
 */
class PlVcardField
{
    /** Default encoding for the different fields.
     */
    private static $defaultEncoding = array('BEGIN'       => 'limited',
                                            'END'         => 'limited',
                                            'SOURCE'      => 'uri',
                                            'NAME'        => 'text',
                                            'PROFILE'     => 'limited',
                                            'FN'          => 'text',
                                            'N'           => 'structured',
                                            'NICKNAME'    => 'text*',
                                            'PHOTO'       => 'binary',
                                            'BDAY'        => 'date',
                                            'ADR'         => 'structured',
                                            'LABEL'       => 'text',
                                            'TEL'         => 'phone-number',
                                            'EMAIL'       => 'text',
                                            'MAILER'      => 'text',
                                            'TZ'          => 'utc-offset',
                                            'GEO'         => 'structured',
                                            'TITLE'       => 'text',
                                            'ROLE'        => 'text',
                                            'LOGO'        => 'binary',
                                            'AGENT'       => 'vcard',
                                            'ORG'         => 'structured',
                                            'CATEGORIES'  => 'text*',
                                            'NOTE'        => 'text',
                                            'PRODID'      => 'text',
                                            'REV'         => 'date-time',
                                            'SORT-STRING' => 'text',
                                            'SOUND'       => 'binary',
                                            'UID'         => 'text',
                                            'URL'         => 'uri',
                                            'VERSION'     => 'limited',
                                            'CLASS'       => 'text',
                                            'KEY'         => 'binary');

    /** Field group.
     */
    public $group    = null;

    /** Field name.
     */
    public $name     = null;

    /** Field value.
     */
    public $value    = null;


    /* RFC2425 parameters */

    /** ENCODING: encoding of the field.
     * default is 8bit, only 'b' is supported for binary fields
     */
    public $ENCODING = null;

    /** VALUE: type of the value of the field.
     * available types from RFC2425 are:
     * -uri: one uri
     * -text*: one or more text entry
     * -date*: one or more date entry
     * -time*: one or more time entry
     * -date-time: one or more date-time entry
     * -integer: one or more integer
     * -boolean: (TRUE|FALSE)
     * -float: one ore more float entry
     * -x-username: user-defined type
     * -(iana-token)
     *
     * available types from RFC2426 are:
     * -binary: (encoding type must be specified)
     * -vcard: inlined vcard (encoded as text)
     * -phone-number
     * -utc-offset
     * -structured
     */
    public $VALUE    = null;

    /** CHARSET: charset of the value of the field.
     */
    public $CHARSET  = null;

    /** LANGUAGE: lanugage of the field.
     */
    public $LANGUAGE = null;

    /** CONTEXT: context of the value.
     */
    public $CONTEXT  = null;


    /* RFC2426 parameters */

    /** TYPE: variants of the type.
     */
    public $TYPE     = null;


    public function __construct($group, $name, $value)
    {
        $this->group = $group;
        $this->name  = $name;
        $this->value = $value;

        $type = @self::$defaultEncoding[$name];
        if (is_null($type)) {
            $type = 'text';
        }
        if ($type == 'binary') {
            $this->ENCODING = 'b';
        } else if ($type == 'text' || $type == 'text*' || $type == 'structured') {
            $this->CHARSET = PlVCard::$charset;
        }
    }

    public function show()
    {
        $params = array();
        foreach ($this as $pk => $pv) {
            if ($pk != 'value' && $pk != 'group' && $pk != 'name') {
                if ($pv instanceof PlFlagset) {
                    $params[$pk] = $pv->flags();
                } else if (!is_null($pv)) {
                    $params[$pk] = $pv;
                }
            }
        }
        $encoding = $this->VALUE;
        if (is_null($encoding)) {
            $encoding = @self::$defaultEncoding[$this->name];
        }
        if (is_null($encoding)) {
            // let say default encoding is 'text'
            $encoding = 'text';
        }
        self::output($this->group, $this->name, $params, self::format($this->value, $encoding));
    }

    static public function format($value, $format)
    {
        if (substr($format, -1) == '*') {
            $format = substr($format, 0, -1);
            if (is_array($value)) {
                $vals = array();
                foreach ($value as $v) {
                    $vals[] = self::format($v, $format);
                }
                return implode(',', $vals);
            }
        }
        if (is_null($value)) {
            return '';
        }
        switch ($format) {
          case 'float':
            return str_replace(',', '.', $value);

          case 'boolean':
            if ($value == 'TRUE' || $value == 'FALSE') {
                return $value;
            }
            return $value ? 'TRUE' : 'FALSE';

          case 'binary':
            if (!PlVCard::$escapeBinary) {
                return base64_encode($value);
            }
            $value = base64_encode($value);

          case 'limited':
          case 'vcard':
          case 'text':
            if (PlVCard::$charset != 'UTF-8' && $format != 'binary') {
                $value = iconv('UTF-8', PlVCard::$charset, $value);
            }
            return str_replace(array('\\',   ',',   "\r\n", "\r",  "\n"),
                               array('\\\\', '\\,', '\\n',  '\\n', '\\n'),
                               $value);

          case 'structured':
            $vals = array();
            foreach ($value as $k => $v) {
                if ($k{0} == '_') {
                    continue;
                }
                $enc = isset($value->_encoding[$k]) ? $value->_encoding[$k] : $value->_encoding['@@EXTRA@@'];
                $vals[] = str_replace(';', '\\;', self::format($v, $enc));
            }
            return implode(';', $vals);

          case 'uri':
          case 'phone-number':
          case 'utc-offset':
          case 'integer':
          default:
            return $value;
        }
    }

    static public function output($group, $name, $params, $value)
    {
        $str = '';
        if (!is_null($group)) {
            $str .= $group . '.';
        }
        $str .= $name;
        if (!is_null($params)) {
            foreach ($params as $pn => $pv) {
                $str .= ';' . $pn . '=' . $pv;
            }
        }
        $str .= ':' . $value;

        // Folding
        if (PlVCard::$folding && strlen($str) > 75) {
            $str = chunk_split($str, 75, "\r\n ");
            if (substr($str, -3) == "\r\n ") {
                $str = substr($str, 0, -3);
            }
        }
        echo $str . "\r\n";
    }
}


/** Structure of the N type as described in RFC2426.
 */
class N_Field
{
    public $_encoding = array('familyName'        => 'text*',
                              'givenName'         => 'text*',
                              'additionalName'    => 'text*',
                              'honorificPrefixes' => 'text*',
                              'honorificSuffixes' => 'text*');

    /** The family name
     * -type: text-list
     */
    public $familyName        = null;

    /** The given name
     * -type: text-list
     */
    public $givenName         = null;

    /** The additional names
     * -type: text-list
     */
    public $additionalName    = null;

    /** Honorific prefixes
     * -type: text-list
     */
    public $honorificPrefixes = null;

    /** Honorific suffixes
     * -type: text-list
     */
    public $honorificSuffixes = null;

    public function __construct($family, $given, $additional, $prefix, $suffix)
    {
        $this->familyName     = $family;
        $this->givenName      = $given;
        $this->additionalName = $additional;

        $this->honorificPrefixes = $prefix;
        $this->honorificSuffixes = $suffix;
    }
}


/** Structure of the ADR type as described in RFC2426.
 */
class ADR_Field
{
    public $_encoding = array('postOfficeBox'   => 'text',
                              'extendedAddress' => 'text*',
                              'streetAddress'   => 'text*',
                              'locality'        => 'text',
                              'region'          => 'text',
                              'postalCode'      => 'text',
                              'countryName'     => 'text');

    /** The post office box
     * -type: text
     */
    public $postOfficeBox    = null;

    /** Extended address.
     * -type: text
     */
    public $extendedAddress  = null;

    /** Street address.
     * -type: text
     */
    public $streetAddress    = null;

    /** Locality name.
     * -type: text
     */
    public $locality         = null;

    /** Region name.
     * -type: text
     */
    public $region           = null;

    /** Postal code.
     * -type: text
     */
    public $postalCode       = null;

    /** Country name.
     * -type: text
     */
    public $countryName      = null;


    public function __construct($box, $extend, $street, $locality, $region,
                                $postcode, $country) {
        $this->postOfficeBox   = $box;
        $this->extendedAddress = $extend;
        $this->streetAddress   = $street;
        $this->locality        = $locality;
        $this->region          = $region;
        $this->postalCode      = $postcode;
        $this->countryName     = $country;
    }
}

/** Structure of the ORG type as described in RFC2426.
 */
class ORG_Field
{
    public $_encoding = array('name'      => 'text',
                              '@@EXTRA@@' => 'text');


    /** Organisation name.
     * -type: text
     */
    public $name             = null;

    /** Unit level
     * -type: several entries
     *
     * Use dynamic PHP members to distinguish
     * multi-fields from text-list.
     */

    public function __construct($org, $units)
    {
        $this->name = $org;
        if (!is_null($units)) {
            if (is_array($units)) {
                foreach ($units as $k => $v) {
                    $f = 'unit_' . $k;
                    $this->$f = $v;
                }
            } else {
                $this->unit_0 = $units;
            }
        }
    }
}

/** Structure of the GEO type as described in RFC2426.
 */
class GEO_Field
{
    public $_encoding = array('latitude'  => 'float',
                              'longitude' => 'float');

    /** Latitude.
     * -type: float
     */
    public $latitude;

    /** Longitude.
     * -type: float
     */
    public $longitude;


    public function __construct($lat, $lon)
    {
        $this->latitude  = $lat;
        $this->longitude = $lon;
    }
}

class PlVCardEntry
{
    /* RFC2425 fields */

    /** SOURCE: source of the vCard.
     * -type: uri
     * -optional
     */
    public $SOURCE   = null;

    /** NAME: name of the entry.
     * -type: text
     * -optional
     */
    public $NAME     = null;

    /** PROFILE: profile type.
     * -type: a registered profile name (vCard)
     * -optional
     */
    public $PROFILE  = null;

    /* RFC2426 fields */

    /* Identification fields */

    /** FN: Formatted name.
     * -type: text
     * -mandatory
     */
    public $FN       = null;

    /** N: Name structure.
     * -type: n structure
     * -mandatory
     */
    public $N        = null;

    /** NICKNAME: List of nick names.
     * -type: text-list
     */
    public $NICKNAME = null;

    /** PHOTO: Photo of the object identified by the vcard.
     * -type: binary, can be reset to URL
     */
    public $PHOTO    = null;

    /** BDAY: Birthday
     * -type: date, can be reset to date-time
     */
    public $BDAY     = null;


    /* Delivery addressing */

    /** ADR: delivery address by components.
     * -type: adr structure
     * -variant flags: dom, intl, postal, parcel, home, work, pref (default: intl,postal,parcel,work)
     */
    public $ADR      = array();

    /** LABEL: formatted text representing a delivery address.
     * -type: text
     * -variant flags: dom, intl, postal, parcel, home, work, pref (default: intl,postal,parcel,work)
     */
    public $LABEL    = array();


    /* Telecommunication addressing */

    /** TEL: telephone number.
     * -type: phone-number
     * -variant flags: home, msg, work, pref, voice, fax, cell, video, pager, bbs, modem, car, isdn, pcs (default: voice)
     */
    public $TEL      = array();

    /** EMAIL: electroning mail address.
     * -type: text
     * -variant flags: internet, x400, pref (default: internet)
     */
    public $EMAIL    = array();

    /** MAILER: type of mailer used...
     * -type: text
     */
    public $MAILER   = array();


    /* Geographical */

    /** TZ: timezone.
     * -type: utc-offset (can be reset to a text value)
     */
    public $TZ       = null;

    /** GEO: Geographical coordinates.
     * -type: geo structure
     */
    public $GEO      = null;


    /* Organizational */

    /** TITLE: job title, functional position or function.
     * -type: text
     */
    public $TITLE   = array();

    /** ROLE: role, occupation, business category.
     * -type: text
     */
    public $ROLE    = array();

    /** LOGO: logo of the organization.
     * -type: binary (can be reset to uri)
     */
    public $LOGO    = array();

    /** AGENT: define information about another person.
     * -type: vcard (can be reset to a uri)
     */
    public $AGENT   = array();

    /** ORG: Organizational name and units.
     * -type: org structure
     */
    public $ORG     = array();


    /* Explanatory */

    /** CATEGORIES: list of categories.
     * -type: text-list
     */
    public $CATEGORIES = null;

    /** NOTE: supplemental information or comment.
     * -type: text
     */
    public $NOTE       = null;

    /** PRODID: Identifier of the product that created the card.
     * -type: text (ISO 9070)
     */
    public $PRODID     = null;

    /** REV: revision information about the card.
     * -type: date-time (can be reset to a simple date)
     */
    public $REV        = null;

    /** SORT-STRING: informations on how to sort this card
     * -type: text
     */
    public $SORT_STRING = null;

    /** SOUND: digital sound content that annotates the card.
     * -type: binary (can be reset to a uri)
     */
    public $SOUND       = null;

    /** UID: globaly unique identifier corresponding to the object.
     * -type: text
     * -variant: IANA standard format identifier (optionnal)
     */
    public $UID         = null;

    /** URL: url describing the object the vcard refers to.
     * -type: uri
     */
    public $URL         = null;

    /** VERSION: format version of the vcard
     * -type: text
     * MUST BE "3.0"
     */
    public $VERSION     = null;


    /* Security types */

    /** CLASS: access classification.
     * -type: text (eg.: PUBLIC, PRIVATE, CONFIDENTIAL...)
     */
    public $CLASS       = null;

    /** KEY: public key or authentication certificate associated with the object.
     * -type: binary (can be overloaded to text
     */
    public $KEY         = null;


    public function __construct($firstname, $lastname, $displayname = null, $sortname = null, $nickname = null)
    {
        $this->set('VERSION', '3.0');
        $this->setName($firstname, $lastname, $displayname, $sortname, $nickname);
    }

    public function &set($name, $value)
    {
        $field = new PlVcardField(null, $name, $value);
        $name = str_replace('-', '_', $name);
        $this->$name = $field;
        return $field;
    }

    public function &add($name, $value)
    {
        $field = new PlVcardField(null, $name, $value);
        array_push($this->$name, $field);
        return $field;
    }

    public function &addInGroup($group, $name, $value)
    {
        $field = new PlVcardField($group, $name, $value);
        array_push($this->$name, $field);
        return $field;
    }

    public function setName($firstname, $lastname, $displayname = null, $sortname = null, $nickname = null)
    {
        $additional = array();
        if (is_array($firstname)) {
            $given = array_shift($firstname);
            $additional = $firstname;
        } else {
            $given = $firstname;
        }
        if (is_array($lastname)) {
            $l = array_shift($lastname);
            $additional = array_merge($additional, $lastname);
            $lastname = $l;
        }
        if (is_null($displayname)) {
            $displayname = $given . ' ' . $lastname;
        }
        if (is_null($sortname)) {
            $sortname = $lastname;
        }
        $this->set('N', new N_Field($lastname, $given, $additional, null, null));
        $this->set('FN', $displayname);
        if (!is_null($nickname)) {
            $this->set('NICKNAME', $nickname);
        }
        $this->set('SORT-STRING', $sortname);
    }

    public function addHome($street, $extra, $postBox, $postCode, $city,
                            $region, $country, $pref = false, $postal = true,
                            $parcel = true)
    {
        $group = 'HOME' . count($this->ADR);
        $field =& $this->addInGroup($group, 'ADR',
                                    new ADR_Field($postBox, $extra, $street, $city,
                                                  $region, $postCode, $country));
        $field->TYPE = new PlFlagset();
        $field->TYPE->addFlag('home');
        $field->TYPE->addFlag('dom');
        $field->TYPE->addFlag('intl');
        if ($pref) {
            $field->TYPE->addFlag('pref');
        }
        if ($postal) {
            $field->TYPE->addFlag('postal');
        }
        if ($parcel) {
            $field->TYPE->addFlag('parcel');
        }
        return $group;
    }

    public function addWork($organisation, $units, $title, $role,
                            $street, $extra, $postBox, $postCode, $city,
                            $region, $country)
    {
        $group = 'WORK' . count($this->ORG);
        $this->addInGroup($group, 'ORG',
                          new ORG_Field($organisation, $units));
        if (!is_null($title)) {
            $this->addInGroup($group, 'TITLE', $title);
        }
        if (!is_null($role)) {
            $this->addInGroup($group, 'ROLE', $role);
        }
        $field =& $this->addInGroup($group, 'ADR',
                                    new ADR_Field($postBox, $extra, $street, $city,
                                                  $region, $postCode, $country));
        $field->TYPE = new PlFlagset();
        $field->TYPE->addFlag('work');
        return $group;
    }

    public function addTel($group, $tel, $fax = false, $msg = false, $voice = true,
                           $video = false, $cell = false, $pref = false)
    {
        $home = is_null($group) || substr($group, 0, 4) == 'HOME';
        $work = !$home;

        $field =& $this->addInGroup($group, 'TEL', $tel);
        $field->TYPE = new PlFlagset();
        foreach (array('home', 'work', 'fax', 'msg', 'voice', 'video', 'cell', 'pref')
                 as $f) {
            if ($$f) {
                $field->TYPE->addFlag($f);
            }
        }
    }

    public function addMail($group, $mail, $pref = false)
    {
        $field =& $this->addInGroup($group, 'EMAIL', $mail);
        $field->TYPE = new PlFlagset();
        $field->TYPE->addFlag('internet');
        if ($pref) {
            $field->TYPE->addFlag('pref');
        }
    }

    public function setPhoto($data, $format = 'JPEG')
    {
        $field =& $this->set('PHOTO', $data);
        $field->TYPE = $format;
    }

    public function show()
    {
        if (is_null($this->FN) || is_null($this->N) || is_null($this->VERSION)) {
            trigger_error('Missing mandatoring field in vcard', E_USER_ERROR);
            return;
        }
        PlVcardField::output(null, 'BEGIN', null, 'VCARD');
        foreach ($this as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $entry) {
                    $entry->show();
                }
            } else if (!is_null($value)) {
                $value->show();
            }
        }
        PlVcardField::output(null, 'END', null, 'VCARD');
    }
}


/** Abstract representation of a vcard.
 * A VCard file can contain several 'physical' vcards. So, this class
 * handle a vcard as a set of 'PlVCardEntry', each entry describes a
 * profile.
 *
 * To use this tool, you MUST define a new class that inherists this class
 * and implements fetch() and buildEntry(). Fetch build an iterator that
 * list a sequence of object (there is no constraint on the type of object).
 * This objects are given to buildEntry() that MUST use the object to
 * build a PlVCardEntry object.
 *
 * Example:
 *
 * <code>
 * protected function fetch() {
 *  return new PlArrayIterator(array(id1, id2, id3));
 * }
 *
 * protected function buildEntry($object) {
 *   $profile = fetchProfile($object['value']);
 *   $entry = new PlVCardEntry($profile['firstname'], $profile['name'], ...);
 *   for ($adr in $profile) {
 *      $entry->addHome($street, $ext, $postCode, $city, ...);
 *   }
 *   ...
 *   return $entry;
 * }
 * </code>
 */
abstract class PlVCard
{
    /* VCard parameters */

    /** Charset of the text fields
     */
    static public $charset      = 'UTF-8';

    /** Is line folding activated.
     * Line folding consists in breaking too long logical lines
     * into several physical lines.
     *
     * RFC2425 and 2426 indicates that folding SHOULD be used
     * on lines longer than 75 characters, but it seems to fail
     * on some systems.
     */
    static public $folding      = true;

    /** Do we escape binary (base64) content like text content.
     *
     * RFC2426 does not mention escaping on binary values, but this
     * seems to bee required for some clients.
     */
    static public $escapeBinary = false;

    /** Build an iterator that will be used to build the entries.
     */
    protected abstract function fetch();

    /** Build a entry from an object.
     */
    protected abstract function buildEntry($item);

    /** Output a VCard
     */
    public function show()
    {
        header("Pragma: ");
        header("Cache-Control: ");

        /* XXX: RFC2425 defines the mime content-type text/directory.
         * VCard inherits this type as a profile type. Maybe test/x-vcard
         * could be better. To be checked.
         */
        header("Content-type: text/directory; profile=vCard; charset=" . self::$charset);

        $it = $this->fetch();
        while ($item = $it->next()) {
            $entry = $this->buildEntry($item);
            $entry->show();
        }
        exit;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
