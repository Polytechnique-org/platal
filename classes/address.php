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

/** Class Address is meant to perform most of the access to the table profile_addresses.
 *
 * profile_addresses describes an Address, which can be related to either a
 * Profile, a Job or a Company:
 * - for a Profile:
 *   - `type` is set to 'home'
 *   - `pid` is set to the related profile pid (in profiles)
 *   - `id` is the id of the address in the list of those related to that profile
 *   - `jobid` is set to 0
 *
 * - for a Company:
 *   - `type` is set to 'hq'
 *   - `pid` is set to 0
 *   - `jobid` is set to the id of the company (in profile_job_enum)
 *   - `id` is set to 0 (only one address per Company)
 *
 * - for a Job:
 *   - `type` is set to 'job'
 *   - `pid` is set to the pid of the Profile of the related Job (in both profiles and profile_job)
 *   - `id` is the id of the job to which we refer (in profile_job)
 *   - `jobid` is set to 0
 *
 * - for a Group:
 *  - `type` is set to 'group'
 *  - `pid` is set to 0
 *  - `jobid` is set to 0
 *  - `groupid` is set to the group id
 *
 * Thus an Address can be linked to a Company, a Profile, or a Job.
 */
class Address
{
    const LINK_JOB     = 'job';
    const LINK_COMPANY = 'hq';
    const LINK_PROFILE = 'home';
    const LINK_GROUP   = 'group';

    // List of all available postal formattings.
    private static $formattings = array('FRANCE' => 'FR');

    // Abbreviations to be used to format French postal addresses.
    private static $streetAbbreviations = array(
        'ALLEE'                        => 'ALL',
        'AVENUE'                       => 'AV',
        'BOULEVARD'                    => 'BD',
        'CENTRE'                       => 'CTRE',
        'CENTRE COMMERCIAL'            => 'CCAL',
        'IMMEUBLE'                     => 'IMM',
        'IMMEUBLES'                    => 'IMM',
        'IMPASSE'                      => 'IMP',
        'LIEU-DIT'                     => 'LD',
        'LOTISSEMENT'                  => 'LOT',
        'PASSAGE'                      => 'PAS',
        'PLACE'                        => 'PL',
        'RESIDENCE'                    => 'RES',
        'ROND-POINT'                   => 'RPT',
        'ROUTE'                        => 'RTE',
        'SQUARE'                       => 'SQ',
        'VILLAGE'                      => 'VLGE',
        'ZONE D\'ACTIVITE'             => 'ZA',
        'ZONE D\'AMENAGEMENT CONCERTE' => 'ZAC',
        'ZONE D\'AMENAGEMENT DIFFERE'  => 'ZAD',
        'ZONE INDUSTRIELLE'            => 'ZI'
    );
    private static $otherAbbreviations = array(
        'ADJUDANT'               => 'ADJ',
        'AERODROME'              => 'AERD',
        'AEROGARE'               => 'AERG',
        'AERONAUTIQUE'           => 'AERN',
        'AEROPORT'               => 'AERP',
        'AGENCE'                 => 'AGCE',
        'AGRICOLE'               => 'AGRIC',
        'ANCIEN'                 => 'ANC',
        'ANCIENNEMENT'           => 'ANC',
        'APPARTEMENT'            => 'APP',
        'APPARTEMENTS'           => 'APP',
        'ARMEMENT'               => 'ARMT',
        'ARRONDISSEMENT'         => 'ARR',
        'ASPIRANT'               => 'ASP',
        'ASSOCIATION'            => 'ASSOC',
        'ASSURANCE'              => 'ASSUR',
        'ATELIER'                => 'AT',
        'BARAQUEMENT'            => 'BRQ',
        'BAS'                    => 'BAS',
        'BASSE'                  => 'BAS',
        'BASSES'                 => 'BAS',
        'BATAILLON'              => 'BTN',
        'BATAILLONS'             => 'BTN',
        'BATIMENT'               => 'BAT',
        'BATIMENTS'              => 'BAT',
        'BIS'                    => 'B',
        'BOITE POSTALE'          => 'BP',
        'CABINET'                => 'CAB',
        'CANTON'                 => 'CANT',
        'CARDINAL'               => 'CDL',
        'CASE POSTALE'           => 'CP',
        'CHAMBRE'                => 'CHBR',
        'CITADELLE'              => 'CTD',
        'COLLEGE'                => 'COLL',
        'COLONEL'                => 'CNL',
        'COLONIE'                => 'COLO',
        'COMITE'                 => 'CTE',
        'COMMANDANT'             => 'CDT',
        'COMMERCIAL'             => 'CIAL',
        'COMMUNE'                => 'COM',
        'COMMUNAL'               => 'COM',
        'COMMUNAUX'              => 'COM',
        'COMPAGNIE'              => 'CIE',
        'COMPAGNON'              => 'COMP',
        'COMPAGNONS'             => 'COMP',
        'COOPERATIVE'            => 'COOP',
        'COURSE SPECIALE'        => 'CS',
        'CROIX'                  => 'CRX',
        'DELEGATION'             => 'DELEG',
        'DEPARTEMENTAL'          => 'DEP',
        'DEPARTEMENTAUX'         => 'DEP',
        'DIRECTEUR'              => 'DIR',
        'DIRECTECTION'           => 'DIR',
        'DIVISION'               => 'DIV',
        'DOCTEUR'                => 'DR',
        'ECONOMIE'               => 'ECO',
        'ECONOMIQUE'             => 'ECO',
        'ECRIVAIN'               => 'ECRIV',
        'ECRIVAINS'              => 'ECRIV',
        'ENSEIGNEMENT'           => 'ENST',
        'ENSEMBLE'               => 'ENS',
        'ENTREE'                 => 'ENT',
        'ENTREES'                => 'ENT',
        'ENTREPRISE'             => 'ENTR',
        'EPOUX'                  => 'EP',
        'EPOUSE'                 => 'EP',
        'ETABLISSEMENT'          => 'ETS',
        'ETAGE'                  => 'ETG',
        'ETAT MAJOR'             => 'EM',
        'EVEQUE'                 => 'EVQ',
        'FACULTE'                => 'FAC',
        'FORET'                  => 'FOR',
        'FORESTIER'              => 'FOR',
        'FRANCAIS'               => 'FR',
        'FRANCAISE'              => 'FR',
        'FUSILIER'               => 'FUS',
        'GENDARMERIE'            => 'GEND',
        'GENERAL'                => 'GAL',
        'GOUVERNEMENTAL'         => 'GOUV',
        'GOUVERNEUR'             => 'GOU',
        'GRAND'                  => 'GD',
        'GRANDE'                 => 'GDE',
        'GRANDES'                => 'GDES',
        'GRANDS'                 => 'GDS',
        'HAUT'                   => 'HT',
        'HAUTE'                  => 'HTE',
        'HAUTES'                 => 'HTES',
        'HAUTS'                  => 'HTS',
        'HOPITAL'                => 'HOP',
        'HOPITAUX'               => 'HOP',
        'HOSPICE'                => 'HOSP',
        'HOSPITALIER'            => 'HOSP',
        'HOTEL'                  => 'HOT',
        'INFANTERIE'             => 'INFANT',
        'INFERIEUR'              => 'INF',
        'INFERIEUR'              => 'INF',
        'INGENIEUR'              => 'ING',
        'INSPECTEUR'             => 'INSP',
        'INSTITUT'               => 'INST',
        'INTERNATIONAL'          => 'INTERN',
        'INTERNATIONALE'         => 'INTERN',
        'LABORATOIRE'            => 'LABO',
        'LIEUTENANT'             => 'LT',
        'LIEUTENANT DE VAISSEAU' => 'LTDV',
        'MADAME'                 => 'MME',
        'MADEMOISELLE'           => 'MLLE',
        'MAGASIN'                => 'MAG',
        'MAISON'                 => 'MAIS',
        'MAITRE'                 => 'ME',
        'MARECHAL'               => 'MAL',
        'MARITIME'               => 'MAR',
        'MEDECIN'                => 'MED',
        'MEDICAL'                => 'MED',
        'MESDAMES'               => 'MMES',
        'MESDEMOISELLES'         => 'MLLES',
        'MESSIEURS'              => 'MM',
        'MILITAIRE'              => 'MIL',
        'MINISTERE'              => 'MIN',
        'MONSEIGNEUR'            => 'MGR',
        'MONSIEUR'               => 'M',
        'MUNICIPAL'              => 'MUN',
        'MUTUEL'                 => 'MUT',
        'NATIONAL'               => 'NAL',
        'NOTRE DAME'             => 'ND',
        'NOUVEAU'                => 'NOUV',
        'NOUVEL'                 => 'NOUV',
        'NOUVELLE'               => 'NOUV',
        'OBSERVATOIRE'           => 'OBS',
        'PASTEUR'                => 'PAST',
        'PETIT'                  => 'PT',
        'PETITE'                 => 'PTE',
        'PETITES'                => 'PTES',
        'PETITS'                 => 'PTS',
        'POLICE'                 => 'POL',
        'PREFET'                 => 'PREF',
        'PREFECTURE'             => 'PREF',
        'PRESIDENT'              => 'PDT',
        'PROFESSEUR'             => 'PR',
        'PROFESSIONNEL'          => 'PROF',
        'PROFESSIONNELE'         => 'PROF',
        'PROLONGE'               => 'PROL',
        'PROLONGEE'              => 'PROL',
        'PROPRIETE'              => 'PROP',
        'QUATER'                 => 'Q',
        'QUINQUIES'              => 'C',
        'RECTEUR'                => 'RECT',
        'REGIMENT'               => 'RGT',
        'REGION'                 => 'REG',
        'REGIONAL'               => 'REG',
        'REGIONALE'              => 'REG',
        'REPUBLIQUE'             => 'REP',
        'RESTAURANT'             => 'REST',
        'SAINT'                  => 'ST',
        'SAINTE'                 => 'STE',
        'SAINTES'                => 'STES',
        'SAINTS'                 => 'STS',
        'SANATORIUM'             => 'SANA',
        'SERGENT'                => 'SGT',
        'SERVICE'                => 'SCE',
        'SOCIETE'                => 'SOC',
        'SOUS COUVERT'           => 'SC',
        'SOUS-PREFET'            => 'SPREF',
        'SUPERIEUR'              => 'SUP',
        'SUPERIEURE'             => 'SUP',
        'SYNDICAT'               => 'SYND',
        'TECHNICIEN'             => 'TECH',
        'TECHNICIENNE'           => 'TECH',
        'TECHNICIQUE'            => 'TECH',
        'TER'                    => 'T',
        'TRI SERVICE ARRIVEE'    => 'TSA',
        'TUNNEL'                 => 'TUN',
        'UNIVERSITAIRE'          => 'UNVT',
        'UNIVERSITE'             => 'UNIV',
        'VELODROME'              => 'VELOD',
        'VEUVE'                  => 'VVE',
        'VIEILLE'                => 'VIEL',
        'VIEILLES'               => 'VIEL',
        'VIEUX'                  => 'VX'
    );
    private static $entrepriseAbbreviations = array(
        'COOPERATIVE D\'UTILISATION DE MATERIEL AGRICOLE EN COMMUN' => 'CUMA',
        'ETABLISSEMENT PUBLIC A CARACTERE INDUSTRIEL ET COMMERCIAL' => 'EPIC',
        'ETABLISSEMENT PUBLIC ADMINISTRATIF'                        => 'EPA',
        'GROUPEMENT AGRICOLE D\'EXPLOITATION EN COMMUN'             => 'GAEC',
        'GROUPEMENT D\'INTERET ECONOMIQUE'                          => 'GIE',
        'GROUPEMENT D\'INTERET PUBLIC'                              => 'GIP',
        'GROUPEMENT EUROPEEN D\'INTERET ECONOMIQUE'                 => 'GEIE',
        'OFFICE PUBLIC D\'HABITATION A LOYER MODERE'                => 'OPHLM',
        'SOCIETE A RESPONSABILITE LIMITEE'                          => 'SARL',
        'SOCIETE ANONYME'                                           => 'SA',
        'SOCIETE CIVILE DE PLACEMENT COLLECTIF IMMOBILIER'          => 'SCPI',
        'SOCIETE CIVILE PROFESSIONNELLE'                            => 'SCP',
        'SOCIETE COOPERATIVE OUVRIERE DE PRODUCTION ET DE CREDIT'   => 'SCOP',
        'SOCIETE D\'AMENAGEMENT FONCIER ET D\'EQUIPEMENT RURAL'     => 'SAFER',
        'SOCIETE D\'ECONOMIE MIXTE'                                 => 'SEM',
        'SOCIETE D\'INTERET COLLECTIF AGRICOLE'                     => 'SICA',
        'SOCIETE D\'INVESTISSEMENT A CAPITAL VARIABLE'              => 'SICAV',
        'SOCIETE EN NOM COLLECTIF'                                  => 'SNC',
        'SOCIETE IMMOBILIERE POUR LE COMMERCE ET L\'INDUSTRIE'      => 'SICOMI',
        'SOCIETE MIXTE D\'INTERET AGRICOLE'                         => 'SMIA',
        'SYNDICAT INTERCOMMUNAL A VOCATION MULTIPLE'                => 'SIVOM',
        'SYNDICAT INTERCOMMUNAL A VOCATION UNIQUE'                  => 'SIVU'
    );

    // Primary key fields: the quadruplet ($pid, $jobid, $type, $id) defines a unique address.
    public $pid = 0;
    public $jobid = 0;
    public $groupid = 0;
    public $type = Address::LINK_PROFILE;
    public $id = 0;

    // Geocoding fields.
    public $text = '';
    public $postalText = '';
    public $postal_code_fr = null;
    public $types = '';
    public $formatted_address = '';
    public $components = array();
    public $latitude = null;
    public $longitude = null;
    public $southwest_latitude = null;
    public $southwest_longitude = null;
    public $northeast_latitude = null;
    public $northeast_longitude = null;
    public $location_type = '';
    public $partial_match = false;
    public $componentsIds = '';
    public $request = false;
    public $geocoding_date = null;
    public $geocoding_calls = 0;

    // Database's field required for both 'home' and 'job' addresses.
    public $pub = 'ax';

    // Database's fields required for 'home' addresses.
    public $flags = null; // 'current', 'temporary', 'secondary', 'mail', 'cedex', 'deliveryIssue'
    public $comment = null;
    public $current = null;
    public $temporary = null;
    public $secondary = null;
    public $mail = null;
    public $deliveryIssue = null;

    // Remaining fields that do not belong to profile_addresses.
    public $phones = array();
    public $error = false;
    public $changed = 0;
    public $removed = 0;

    public function __construct(array $data = array())
    {
        if (count($data) > 0) {
            foreach ($data as $key => $val) {
                $this->$key = $val;
            }
        }

        if (!is_null($this->flags)) {
            $this->flags = new PlFlagSet($this->flags);
        } else {
            static $flags = array('current', 'temporary', 'secondary', 'mail', 'deliveryIssue');

            $this->flags = new PlFlagSet();
            foreach ($flags as $flag) {
                if (!is_null($this->$flag) && ($this->$flag == 1 || $this->$flag == 'on')) {
                    $this->flags->addFlag($flag, 1);
                    $this->$flag = null;
                }
                $this->flags->addFlag('cedex', (strpos(strtoupper(preg_replace(array("/[0-9,\"'#~:;_\- ]/", "/\r\n/"),
                                                                               array('', "\n"), $this->text)), 'CEDEX')) !== false);
            }
        }
        $this->request = ($this->request || !is_null(AddressReq::get_request($this->pid, $this->jobid, $this->groupid, $this->type, $this->id)));
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function phones()
    {
        return $this->phones;
    }

    public function addPhone(Phone $phone)
    {
        if ($phone->link_type == Phone::LINK_ADDRESS && $phone->pid == $this->pid) {
            $this->phones[$phone->uniqueId()] = $phone;
        }
    }

    public function hasFlag($flag)
    {
        return ($this->flags != null && $this->flags->hasFlag($flag));
    }

    public function addFlag($flag)
    {
        $this->flags->addFlag($flag);
    }

    /** Auxilary function for formatting postal addresses.
     * If the needle is found in the haystack, it notifies the substitution's
     * success, modifies the length accordingly and returns either the matching
     * substitution or the needle.
     */
    private function substitute($needle, $haystack, &$length, &$success, $trim = false)
    {
        if (array_key_exists($needle, $haystack)) {
            $success = true;
            $length -= (strlen($needle) - strlen($haystack[$needle]));
            return $haystack[$needle];
        } elseif ($trim) {
            $success = true;
            if (strlen($needle) > 4) {
                $length -= (strlen($needle) - 4);
                $needle = $needle{4};
            }
        }
        return $needle;
    }

    /** Checks if the line corresponds to a French street line.
     * A line is considered a French street line if it starts by between 1 and 4 numbers.
     */
    private function isStreetFR($line)
    {
        return preg_match('/^\d{1,4}\D/', $line);
    }

    /** Retrieves a French street number and slit the rest of the line into an array.
     * @param $words: array containing the rest of the line (a word per cell).
     * @param $line: line to consider.
     * Returns the street number.
     */
    private function getStreetNumberFR(&$line)
    {
        // First we define numbers and separators.
        $numberReq = '(\d{1,4})\s*(BIS|TER|QUATER|[A-Z])?';
        $separatorReq = '\s*(?:\\\\|\/|-|&|A|ET)?\s*';

        // Then we retrieve the number(s) and the rest of the line.
        // $matches contains:
        //  -0: the full patern, here the given line,
        //  -1: the number,
        //  -2: its optionnal quantifier,
        //  -3: an optionnal second number,
        //  -4: the second number's optionnal quantifier,
        //  -5: the rest of the line.
        preg_match('/^' . $numberReq . '(?:' . $separatorReq . $numberReq . ')?\s+(.*)/', $line, $matches);
        $number = $matches[1];
        $line = $matches[5];

        // If there is a precision on the address, we concatenate it to the number.
        if ($matches[2] != '') {
            $number .= $matches[2]{0};
        } elseif ($matches[4] != '') {
            $number .= $matches[4]{0};
        }

        return $number;
    }

    /** Checks if the line corresponds to a French locality line.
     * A line is considered a French locality line if it starts by exactly a
     * postal code of exactly 5 numbers.
     */
    private function isLocalityFR($line)
    {
        return preg_match('/^\d{5}\D/', $line);
    }

    /** Retrieves a French postal code and slit the rest of the line into an array.
     * @param $words: array containing the rest of the line (a word per cell).
     * @param $line: line to consider.
     * Returns the postal code, and cuts it out from the line.
     */
    private function getPostalCodeFR(&$line)
    {
        $number = substr($line, 0, 5);
        $line = trim(substr($line, 5));
        return $number;
    }

    /** Returns the address formated for French postal use (cf AFNOR XPZ 10-011).
     * A postal addresse containts at most 6 lines of at most 38 characters each:
     *  - addressee's identification ("MONSIEUR JEAN DURAND", "DURAND SA"…),
     *  - delivery point identification ("CHEZ TOTO APPARTEMENT 2", "SERVICE ACHAT"…),
     *  - building localisation complement ("ENTREE A BATIMENT DES JONQUILLES", "ZONE INDUSTRIELLE OUEST"…),
     *  - N° and street name ("25 RUE DES FLEURS", "LES VIGNES"…),
     *  - delivery service, street localisation complement ("BP 40122", "BP 40112 AREYRES"…),
     *  - postal code and locality or cedex code and cedex ("33500 LIBOURNE", "33506 LIBOURNE CEDEX"…).
     * Punctuation must be removed, all leters must be uppercased.
     * Both locality and street name must not take more than 32 characters.
     *
     * @param $arrayText: array containing the address to be formated, one
     * address line per array line.
     * @param $count: array size.
     */
    private function formatPostalAddressFR($arrayText)
    {
        // First removes country if any.
        $count = count($arrayText);
        if ($arrayText[$count - 1] == 'FRANCE') {
            unset($arrayText[$count - 1]);
            --$count;
        }

        $postal_code = null;
        // All the lines must have less than 38 characters but street and
        // locality lines whose limit is 32 characters.
        foreach ($arrayText as $lineNumber => $line) {
            if ($isStreetLine = $this->isStreetFR($line)) {
                $formattedLine = $this->getStreetNumberFR($line) . ' ';
                $limit = 32;
            } elseif ($this->isLocalityFR($line)) {
                $postal_code = $this->getPostalCodeFR($line);
                $formattedLine = $postal_code . ' ';
                $limit = 32;
            } else {
                $formattedLine = '';
                $limit = 38;
            }

            $words = explode(' ', $line);
            $count = count($words);
            $length = $count - 1;
            foreach ($words as $word) {
                $length += strlen($word);
            }

            // Checks is length is ok. Otherwise, we try to shorten words and
            // update the length of the current line accordingly.
            for ($i = 0; $i < $count && $length > $limit; ++$i) {
                $success = false;
                if ($isStreetLine) {
                    $sub = $this->substitute($words[$i], Address::$streetAbbreviations, $length, $success, ($i == 0));
                }
                // Entreprises' substitution are only suitable for the first two lines.
                if ($lineNumber <= 2 && !$success) {
                    $sub = $this->substitute($words[$i], Address::$entrepriseAbbreviations, $length, $success);
                }
                if (!$success) {
                    $sub = $this->substitute($words[$i], Address::$otherAbbreviations, $length, $success);
                }

                $formattedLine .= $sub . ' ';
            }
            for (; $i < $count; ++$i) {
                $formattedLine .= $words[$i] . ' ';
            }
            $arrayText[$lineNumber] = trim($formattedLine);
        }

        $this->postal_code_fr = $postal_code;
        return implode("\n", $arrayText);
    }

    // Formats postal addresses.
    // First erases punctuation, accents… Then uppercase the address and finally
    // calls the country's dedicated formatting function.
    public function formatPostalAddress()
    {
        // Performs rough formatting.
        $text = mb_strtoupper(replace_accent($this->text));
        $text = str_replace(array(',', ';', '.', ':', '!', '?', '"', '«', '»'), '', $text);
        $text = preg_replace('/( |\t)+/', ' ', $text);
        $arrayText = explode("\n", $text);
        $arrayText = array_map('trim', $arrayText);

        // Formats according to country rules. Thus we first identify the
        // country, then apply corresponding formatting or translate country
        // into default language.
        $count = count($arrayText);
        list($countryId, $country) = XDB::fetchOneRow('SELECT  gc.iso_3166_1_a2, gc.country
                                                         FROM  geoloc_countries AS gc
                                                   INNER JOIN  geoloc_languages AS gl ON (gc.iso_3166_1_a2 = gl.iso_3166_1_a2)
                                                        WHERE  gl.countryPlain = {?} OR gc.countryPlain = {?}',
                                                      $arrayText[$count - 1], $arrayText[$count - 1]);
        if (is_null($countryId)) {
            $text = $this->formatPostalAddressFR($arrayText);
        } elseif (in_array(strtoupper($countryId), Address::$formattings)) {
            $text = call_user_func(array($this, 'formatPostalAddress' . strtoupper($countryId)), $arrayText);
        } else {
            $arrayText[$count - 1] = mb_strtoupper(replace_accent($country));
            $text = implode("\n", $arrayText);
        }

        $this->postalText = $text;
    }

    public function format()
    {
        $this->text = trim($this->text);
        $this->phones = Phone::formatFormArray($this->phones, $this->error, $this->pub);
        if ($this->removed == 1) {
            if (!S::user()->checkPerms('directory_private') && Phone::hasPrivate($this->phones)) {
                Platal::page()->trigWarning("L'adresse ne peut être supprimée car elle contient des informations pour lesquelles vous n'avez le droit d'édition.");
            } else  {
                $this->text = '';
                return true;
            }
        }

        $this->formatPostalAddress();
        if ($this->changed == 1) {
            $gmapsGeocoder = new GMapsGeocoder();
            $gmapsGeocoder->getGeocodedAddress($this);

            $componants = array();
            foreach ($this->components as $component) {
                $componants[] = Geocoder::getComponentId($component);
            }
            $this->componentsIds = implode(',', $componants);
        }
        if ($this->componentsIds == '') {
            $this->latitude = null;
            $this->longitude = null;
        }

        return true;
    }

    public function toFormArray()
    {
        $address = array(
            'text'                => $this->text,
            'postalText'          => $this->postalText,
            'types'               => $this->types,
            'formatted_address'   => $this->formatted_address,
            'latitude'            => $this->latitude,
            'longitude'           => $this->longitude,
            'southwest_latitude'  => $this->southwest_latitude,
            'southwest_longitude' => $this->southwest_longitude,
            'northeast_latitude'  => $this->northeast_latitude,
            'northeast_longitude' => $this->northeast_longitude,
            'location_type'       => $this->location_type,
            'partial_match'       => $this->partial_match,
            'componentsIds'       => $this->componentsIds,
            'geocoding_date'      => $this->geocoding_date,
            'geocoding_calls'     => $this->geocoding_calls,
            'request'             => $this->request
        );

        if ($this->type == self::LINK_JOB) {
            $address['pub']  = $this->pub;
            $address['mail'] = $this->flags->hasFlag('mail');
        }
        if ($this->type == self::LINK_PROFILE) {
            static $flags = array('current', 'temporary', 'secondary', 'mail', 'cedex', 'deliveryIssue');
            foreach ($flags as $flag) {
                $address[$flag] = $this->flags->hasFlag($flag);
            }
            $address['pub']     = $this->pub;
            $address['comment'] = $this->comment;
            $address['phones']  = Phone::formatFormArray($this->phones);
        }

        return $address;
    }

    private function toString()
    {
        $address = $this->text;
        if ($this->type == self::LINK_PROFILE || $this->type == self::LINK_JOB) {
            static $pubs = array('public' => 'publique', 'ax' => 'annuaire papier', 'private' => 'privé', 'hidden' => 'administrateurs');
            $address .= ' (affichage ' . $pubs[$this->pub];
        }
        if ($this->type == self::LINK_PROFILE) {
            static $flags = array(
                'current'       => 'actuelle',
                'temporary'     => 'temporaire',
                'secondary'     => 'secondaire',
                'mail'          => 'conctactable par courier',
                'deliveryIssue' => 'n\'habite pas à l\'adresse indiquée',
                'cedex'         => 'type cédex',
            );

            if (!$this->flags->hasFlag('temporary')) {
                $address .= ', permanente';
            }
            if (!$this->flags->hasFlag('secondary')) {
                $address .= ', principale';
            }
            foreach ($flags as $flag => $flagName) {
                if ($this->flags->hasFlag($flag)) {
                    $address .= ', ' . $flagName;
                }
            }
            if ($this->comment) {
                $address .= ', commentaire : ' . $this->comment;
            }
            if ($phones = Phone::formArrayToString($this->phones)) {
                $address .= ', ' . $phones;
            }
        } elseif ($this->type == self::LINK_JOB) {
            $address .= ')';
        }
        return $address;
    }

    private function isEmpty()
    {
        return (!$this->text || $this->text == '');
    }

    public function save($notify_ungeocoded = true)
    {
        if (!$this->isEmpty()) {
            XDB::execute('INSERT IGNORE INTO  profile_addresses (pid, jobid, groupid, type, id, flags, text, postalText, pub, comment,
                                                                 types, formatted_address, location_type, partial_match, latitude, longitude,
                                                                 southwest_latitude, southwest_longitude, northeast_latitude, northeast_longitude,
                                                                 geocoding_date, geocoding_calls, postal_code_fr)
                                      VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?},
                                               {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, NOW(), {?}, {?})',
                         $this->pid, $this->jobid, $this->groupid, $this->type, $this->id, $this->flags, $this->text, $this->postalText, $this->pub, $this->comment,
                         $this->types, $this->formatted_address, $this->location_type, $this->partial_match, $this->latitude, $this->longitude,
                         $this->southwest_latitude, $this->southwest_longitude, $this->northeast_latitude, $this->northeast_longitude, $this->geocoding_calls, $this->postal_code_fr);

            if ($this->componentsIds) {
                foreach (explode(',', $this->componentsIds) as $component_id) {
                    XDB::execute('INSERT IGNORE INTO  profile_addresses_components (pid, jobid, groupid, type, id, component_id)
                                              VALUES  ({?}, {?}, {?}, {?}, {?}, {?})',
                                 $this->pid, $this->jobid, $this->groupid, $this->type, $this->id, $component_id);
                }
            } elseif ($notify_ungeocoded) {
                // If the address was not geocoded, notifies it to the appropriate ML.
                $mailer = new PlMailer('profile/no_geocoding.mail.tpl');
                $mailer->assign('text', $this->text);
                $mailer->assign('primary_key', $this->pid . '-' . $this->jobid . '-' . $this->groupid . '-' . $this->type . '-' . $this->id);
                $mailer->send();
            }

            if ($this->type == self::LINK_PROFILE) {
                Phone::savePhones($this->phones, $this->pid, Phone::LINK_ADDRESS, $this->id);
            }

            if ($this->request) {
                $req = new AddressReq(S::user(), $this->toFormArray(), $this->pid, $this->jobid, $this->groupid, $this->type, $this->id);
                $req->submit();
            }

            if ($this->pid != 0) {
                self::updateBestMail($this->pid);
            }
        }
    }

    /**
     * Upate the denormalized flag which is used to mark the best mail to use
     * when sending postal mail
     *
     * Call with $fake to true to only get which address would be selected,
     * without updating anything in the database.
     * Returns an array describing the selected profile address
     */
    static public function updateBestMail($pid, $fake=false)
    {
        if (!$fake) {
            XDB::execute("UPDATE  profile_addresses
                             SET  flags = REPLACE(flags, 'dn_best_mail', '')
                           WHERE  pid = {?}",
                         $pid);
        }

        /* Following order is selected to find the best mail:
         *  * Use addresses without the deliveryIssue flag if possible.
         *  * Among these, use addresses flagged as "current".
         *  * If there is no such addresses or several ones, prefer those
         *    without "secondary" flag.
         *  * If there are still several addresses in the selection, try not
         *    to select the ones with "job" type.
         */
        $best_mail = XDB::fetchOneAssoc("SELECT  pid, jobid, groupid, type, id, flags
                                         FROM  profile_addresses
                                        WHERE  FIND_IN_SET('mail', flags) AND pid = {?}
                                     ORDER BY  FIND_IN_SET('deliveryIssue', flags),
                                               NOT FIND_IN_SET('current', flags),
                                               FIND_IN_SET('secondary', flags), type = 'job'
                                        LIMIT  1",
                                      $pid);

        if (!$fake && $best_mail) {
            XDB::execute("UPDATE  profile_addresses
                             SET  flags = CONCAT(flags, ',dn_best_mail')
                           WHERE  pid = {?} AND jobid = {?} AND groupid = {?} AND type = {?} AND id = {?}",
                         $best_mail['pid'], $best_mail['jobid'], $best_mail['groupid'], $best_mail['type'], $best_mail['id']);
        }
        return $best_mail;
    }

    public function updateGeocoding()
    {
        XDB::execute('UPDATE  profile_addresses
                         SET  text = {?}, postalText = {?}, types = {?}, formatted_address = {?},
                              location_type = {?}, partial_match = {?}, latitude = {?}, longitude = {?},
                              southwest_latitude = {?}, southwest_longitude = {?}, northeast_latitude = {?}, northeast_longitude = {?},
                              geocoding_date = NOW(), geocoding_calls = {?}
                       WHERE  pid = {?} AND jobid = {?} AND groupid = {?} AND type = {?} AND id = {?}',
                     $this->text, $this->postalText, $this->types, $this->formatted_address,
                     $this->location_type, $this->partial_match, $this->latitude, $this->longitude,
                     $this->southwest_latitude, $this->southwest_longitude, $this->northeast_latitude, $this->northeast_longitude, $this->geocoding_calls,
                     $this->pid, $this->jobid, $this->groupid, $this->type, $this->id);

        XDB::execute('DELETE FROM  profile_addresses_components
                            WHERE  pid = {?} AND jobid = {?} AND groupid = {?} AND type = {?} AND id = {?}',
                     $this->pid, $this->jobid, $this->groupid, $this->type, $this->id);
        if ($this->componentsIds) {
            foreach (explode(',', $this->componentsIds) as $component_id) {
                XDB::execute('INSERT IGNORE INTO  profile_addresses_components (pid, jobid, groupid, type, id, component_id)
                                          VALUES  ({?}, {?}, {?}, {?}, {?}, {?})',
                             $this->pid, $this->jobid, $this->groupid, $this->type, $this->id, $component_id);
            }
        }
    }

    public function delete()
    {
        XDB::execute('DELETE FROM  profile_addresses_components
                            WHERE  pid = {?} AND jobid = {?} AND groupid = {?} AND type = {?} AND id = {?}',
                     $this->pid, $this->jobid, $this->groupid, $this->type, $this->id);
        XDB::execute('DELETE FROM  profile_addresses
                            WHERE  pid = {?} AND jobid = {?} AND groupid = {?} AND type = {?} AND id = {?}',
                     $this->pid, $this->jobid, $this->groupid, $this->type, $this->id);

        return XDB::affectedRows();
    }

    static public function deleteAddresses($pid, $type, $jobid = null, $groupid = null, $deletePrivate = true)
    {
        $where = '';
        if (!is_null($pid)) {
            $where = XDB::format(' AND pid = {?}', $pid);
        }
        if (!is_null($jobid)) {
            $where = XDB::format(' AND jobid = {?}', $jobid);
        }
        if (!is_null($groupid)) {
            $where = XDB::format(' AND groupid = {?}', $groupid);
        }
        XDB::execute('DELETE FROM  profile_addresses
                            WHERE  type = {?}' . $where . (($deletePrivate) ? '' : ' AND pub IN (\'public\', \'ax\')'),
                     $type);
        if ($type == self::LINK_PROFILE) {
            Phone::deletePhones($pid, Phone::LINK_ADDRESS, null, $deletePrivate);
        }
    }

    /** Saves addresses into the database.
     * @param $data: an array of form formatted addresses.
     * @param $pid, $type, $linkid: pid, type and id concerned by the update.
     */
    static public function saveFromArray(array $data, $pid, $type = self::LINK_PROFILE, $linkid = null, $savePrivate = true)
    {
        foreach ($data as $id => $value) {
            if ($value['pub'] != 'private' || $savePrivate) {
                if (!is_null($linkid)) {
                    $value['id'] = $linkid;
                } else {
                    $value['id'] = $id;
                }
                if (!is_null($pid)) {
                    $value['pid'] = $pid;
                }
                if (!is_null($type)) {
                    $value['type'] = $type;
                }
                $address = new Address($value);
                $address->save();
            }
        }
    }

    static private function formArrayWalk(array $data, $function, &$success = true, $requiresEmptyAddress = false)
    {
        $addresses = array();
        foreach ($data as $item) {
            $address = new Address($item);
            $success = ($address->format() && $success);
            if (!$address->isEmpty()) {
                $addresses[] = call_user_func(array($address, $function));
            }
        }
        if (count($address) == 0 && $requiresEmptyAddress) {
            $address = new Address();
            $addresses[] = call_user_func(array($address, $function));
        }
        return $addresses;
    }

    // Compares two addresses. First sort by publicity, then place primary
    // addresses before secondary addresses.
    static private function compare(array $a, array $b)
    {
        $value = Visibility::comparePublicity($a, $b);
        if ($value == 0) {
            if ($a['secondary'] != $b['secondary']) {
                $value = $a['secondary'] ? 1 : -1;
            }
        }
        return $value;
    }

    // Formats an array of form addresses into an array of form formatted addresses.
    static public function formatFormArray(array $data, &$success = true)
    {
        $addresses = self::formArrayWalk($data, 'toFormArray', $success, true);

        // Only a single address can be the profile's current address and she must have one.
        $hasCurrent = false;
        foreach ($addresses as $key => &$address) {
            if (isset($address['current']) && $address['current']) {
                if ($hasCurrent) {
                    $address['current'] = false;
                } else {
                    $hasCurrent = true;
                }
            }
        }
        if (!$hasCurrent && count($value) > 0) {
            foreach ($value as &$address) {
                $address['current'] = true;
                break;
            }
        }

        usort($addresses, 'Address::compare');
        return $addresses;
    }

    static public function formArrayToString(array $data)
    {
        return implode(', ', self::formArrayWalk($data, 'toString'));
    }

    static public function hasPrivate(array $addresses)
    {
        foreach ($addresses as $address) {
            if ($address['pub'] == 'private') {
                return true;
            }
        }
        return false;
    }

    static public function iterate(array $pids = array(), array $types = array(),
                                   array $jobids = array(), $visibility = null, $where = null)
    {
        return new AddressIterator($pids, $types, $jobids, $visibility, $where);
    }
}

/** Iterator over a set of Addresses
 *
 * @param $pid, $type, $jobid, $pub
 *
 * The iterator contains the addresses that correspond to the value stored in
 * the parameters' arrays.
 */
class AddressIterator implements PlIterator
{
    private $dbiter;
    private $visibility;

    public function __construct(array $pids, array $types, array $jobids, $visibility, $_where)
    {
        $where = array();
        if (!is_null($_where)) {
            $where[] = $_where;
        }
        if (count($pids) != 0) {
            $where[] = XDB::format('(pa.pid IN {?})', $pids);
        }
        if (count($types) != 0) {
            $where[] = XDB::format('(pa.type IN {?})', $types);
        }
        if (count($jobids) != 0) {
            $where[] = XDB::format('(pa.jobid IN {?})', $jobids);
        }
        if ($visibility == null || !($visibility instanceof Visibility)) {
            $visibility = Visibility::defaultForRead();
        }
        $where[] = 'pve.best_display_level+0 <= pa.pub+0';

        $sql = 'SELECT  pa.pid, pa.jobid, pa.groupid, pa.type, pa.id, pa.flags, pa.text, pa.postalText, pa.pub, pa.comment,
                        pa.types, pa.formatted_address, pa.location_type, pa.partial_match, pa.latitude, pa.longitude,
                        pa.southwest_latitude, pa.southwest_longitude, pa.northeast_latitude, pa.northeast_longitude,
                        pa.geocoding_date, pa.geocoding_calls,
                        GROUP_CONCAT(DISTINCT pc.component_id SEPARATOR \',\') AS componentsIds,
                        GROUP_CONCAT(pace1.long_name) AS postalCode, GROUP_CONCAT(pace2.long_name) AS locality,
                        GROUP_CONCAT(pace3.long_name) AS administrativeArea, GROUP_CONCAT(pace4.long_name) AS country
                  FROM  profile_addresses                 AS pa
             LEFT JOIN  profile_addresses_components      AS pc    ON (pa.pid = pc.pid AND pa.jobid = pc.jobid AND pa.groupid = pc.groupid
                                                                       AND pa.type = pc.type AND pa.id = pc.id)
             LEFT JOIN  profile_addresses_components_enum AS pace1 ON (FIND_IN_SET(\'postal_code\', pace1.types) AND pace1.id = pc.component_id)
             LEFT JOIN  profile_addresses_components_enum AS pace2 ON (FIND_IN_SET(\'locality\', pace2.types) AND pace2.id = pc.component_id)
             LEFT JOIN  profile_addresses_components_enum AS pace3 ON (FIND_IN_SET(\'administrative_area_level_1\', pace3.types) AND pace3.id = pc.component_id)
             LEFT JOIN  profile_addresses_components_enum AS pace4 ON (FIND_IN_SET(\'country\', pace4.types) AND pace4.id = pc.component_id)
             LEFT JOIN  profile_visibility_enum AS pve ON (pve.access_level = {?})
                 WHERE  ' . implode(' AND ', $where) . '
              GROUP BY  pa.pid, pa.jobid, pa.groupid, pa.type, pa.id
              ORDER BY  pa.pid, pa.jobid, pa.id';
        $this->dbiter = XDB::iterator($sql, $visibility->level());
        $this->visibility = $visibility;
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
        // Adds phones to addresses.
        $it = Phone::iterate(array($data['pid']), array(Phone::LINK_ADDRESS), array($data['id']), $this->visibility);
        while ($phone = $it->next()) {
            $data['phones'][$phone->id] = $phone->toFormArray();
        }
        return new Address($data);
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

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
