#!/usr/bin/php5
<?php
require_once 'connect.db.inc.php';
require_once 'profil.func.inc.php';

$globals->debug = 0; //do not store backtraces

// Convert phone prefixes from varchar to int
$prefixes = XDB::iterRow('SELECT  iso_3166_1_a2, phonePrefix
                            FROM  geoloc_countries
                           WHERE  phonePrefix IS NOT NULL');
XDB::execute('ALTER TABLE  geoloc_countries
               ADD COLUMN  tmpPhonePrefix SMALLINT UNSIGNED NULL');
while (list($id, $pref) = $prefixes->next()) {
    $pref = preg_replace('/[^0-9]/', '', $pref);
    if ($pref[0] == '1') {
        $pref = '1';
    }
    if ($pref[0] == '7') {
        $pref = '7';
    }
    if ($pref != '' && strlen($pref) < 4) {
        XDB::execute('UPDATE  geoloc_countries
                         SET  tmpPhonePrefix = {?}
                       WHERE  iso_3166_1_a2 = {?}', $pref, $id);
    }
}

// geoloc_pays post operations
// Drops old prefix column
XDB::execute('ALTER TABLE  geoloc_countries
              DROP COLUMN  phonePrefix');
// Renames temporary column
XDB::execute('ALTER TABLE  geoloc_countries
            CHANGE COLUMN  tmpPhonePrefix phonePrefix SMALLINT UNSIGNED NULL AFTER nationality');
// Adds an index on phonePrefix column
XDB::execute('ALTER TABLE  geoloc_countries
                ADD INDEX  (phonePrefix)');
// Adds French phone prefix
XDB::execute('UPDATE  geoloc_countries
                 SET  phonePrefix = \'33\'
               WHERE  iso_3166_1_a2 = \'FR\'');
// Adds some phone formats
XDB::execute('UPDATE  geoloc_countries
                 SET  phoneFormat = \'0# ## ## ## ##\'
               WHERE  phonePrefix = \'33\'');    //France
XDB::execute('UPDATE  geoloc_countries
                 SET  phoneFormat = \'(+p) ### ### ####\'
               WHERE  phonePrefix = \'1\'');  //USA and NANP countries



//Phone number import

$warnings = 0;

// Import from auth_user_quick
echo "\nImporting mobile phone numbers from auth_user_quick...\n";
$phones = XDB::iterRow('SELECT  ap.pid, q.profile_mobile_pub, q.profile_mobile
                          FROM  #x4dat#.auth_user_quick AS q
                    INNER JOIN  account_profiles        AS ap ON (q.user_id = ap.uid AND FIND_IN_SET(\'owner\', ap.perms))
                         WHERE  q.profile_mobile <> \'\'');
while (list($pid, $pub, $phone) = $phones->next()) {
    $pub = ($pub == '' ? 'private' : $pub);
    $fmt_phone = format_phone_number($phone);
    if ($fmt_phone != '') {
        $display = format_display_number($fmt_phone, $error);
        if (!XDB::execute('INSERT INTO  profile_phones (pid, link_type, link_id, tel_id, tel_type, search_tel, display_tel, pub)
                                VALUES  ({?}, \'user\', 0, 0, \'mobile\', {?}, {?}, {?})', $pid, $fmt_phone, $display, $pub)) {
            echo "WARNING: insert of profile mobile phone number failed for profile $pid.\n";
            ++$warnings;
        }
    }
}


// Import from entreprises
echo "\nImporting professional phone numbers from entreprises...\n";
$phones = XDB::iterator('SELECT  ap.pid, e.entrid, e.tel, e.fax, e.mobile, e.tel_pub
                           FROM  #x4dat#.entreprises AS e
                     INNER JOIN  account_profiles    AS ap ON (e.uid = ap.uid AND FIND_IN_SET(\'owner\', ap.perms))
                       ORDER BY  ap.pid');
while ($row = $phones->next()) {
    $row['tel_pub'] = ($row['tel_pub'] == '' ? 'private' : $row['tel_pub']);
    $request = 'INSERT INTO  profile_phones (pid, link_type, link_id, tel_id, tel_type, search_tel, display_tel, pub)
                     VALUES  ({?}, \'pro\', {?}, {?}, {?}, {?}, {?}, {?})';
    $fmt_fixed   = format_phone_number($row['tel']);
    $fmt_mobile  = format_phone_number($row['mobile']);
    $fmt_fax     = format_phone_number($row['fax']);
    if ($fmt_fixed != '') {
        $disp_fixed  = format_display_number($fmt_fixed, $error);
        if (!XDB::execute($request, $row['pid'], $row['entrid'], 0, 'fixed', $fmt_fixed, $disp_fixed, $row['tel_pub'])) {
            echo 'WARNING: insert of professional fixed phone number failed for profile ' . $row['pid'] . ' and entreprise ' . $row['entrid'] . ".\n";
            ++$warnings;
        }
    }
    if ($fmt_mobile != '') {
        $disp_mobile = format_display_number($fmt_mobile, $error);
        if (!XDB::execute($request, $row['pid'], $row['entrid'], 1, 'mobile', $fmt_mobile, $disp_mobile, $row['tel_pub'])) {
            echo 'WARNING: insert of professional mobile number failed for profile ' . $row['pid'] . ' and entreprise ' . $row['entrid'] . ".\n";
            $warnings++;
        }
    }
    if ($fmt_fax != '') {
        $disp_fax    = format_display_number($fmt_fax, $error);
        if (!XDB::execute($request, $row['pid'], $row['entrid'], 2, 'fax', $fmt_fax, $disp_fax, $row['tel_pub'])) {
            echo 'WARNING: insert of professional fax number failed for profile ' . $row['pid'] . ' and entreprise ' . $row['entrid'] . ".\n";
            $warnings++;
        }
    }
}


//import from tels
echo "\nImporting personnal phone numbers from tels...\n";
$phones = XDB::iterator('SELECT  ap.pid, t.adrid, t.telid, t.tel_type, t.tel_pub, t.tel
                           FROM  #x4dat#.tels     AS t
                     INNER JOIN  account_profiles AS ap ON (t.uid = ap.uid AND FIND_IN_SET(\'owner\', ap.perms))');
$conversions = array();
$other_count = 0;
while ($row = $phones->next()) {
    $row['tel_pub'] = ($row['tel_pub'] == '' ? 'private' : $row['tel_pub']);
    $fmt_phone  = format_phone_number($row['tel']);
    if ($fmt_phone != '') {
        $display    = format_display_number($fmt_phone, $error);
        $guess_type = guess_phone_type($row['tel_type'], $fmt_phone);

        switch ($guess_type) {
        case 'fixed':
        case 'fax':
        case 'mobile':
            if (!XDB::execute('INSERT  INTO profile_phones (pid, link_type, link_id, tel_id, tel_type, search_tel, display_tel, pub)
                                     VALUES ({?}, \'address\', {?}, {?}, {?}, {?}, {?}, {?})',
                              $row['pid'], $row['adrid'], $row['telid'], $guess_type, $fmt_phone, $display, $row['tel_pub'])) {
                echo  'WARNING: insert of address phone number failed for profile ' . $row['pid'] . ', address ' . $row['adrid']
                    . ' and telephone id ' . $row['telid'] . ".\n";
                ++$warnings;
            } else {
                if ($row['tel_type'] == 'Autre') {
                    ++$other_count;
                } else if (!isset($conversions[$row['tel_type']])) {
                    $conversions[$row['tel_type']] = $guess_type;
                }
            }
            break;
        case 'conflict':
            echo  'WARNING: conflict for profile ' . $row['pid'] . ', address ' . $row['adrid']
                . ' and telephone id ' . $row['telid'] . ': type = "' . $row['tel_type']
                . '", number = "' .$fmt_phone . "\"\n";
            ++$warnings;
            break;
        case 'unknown':
        default:
            echo  'WARNING: unknown phone type (' . $row['tel_type'] . ') for profile ' . $row['pid'] . ', address ' . $row['adrid']
                . ' and telephone id ' . $row['telid'] . "\n";
            ++$warnings;
        }
    }
}

echo "\nSummary of automatic phone type conversion\n";
foreach ($conversions as $old => $new) {
    echo "* $old => $new\n";
}
echo "There was also $other_count conversions from old type 'Autre' to a new one determined by the phone number.\n";



//end of import
if ($warnings) {
    echo  "\n----------------------------------------------------------------------\n"
        . " There is $warnings phone numbers that couldn't be imported.\n"
        . " They need to be manually inserted.\n";
}
echo  "\nAfter solving any import problem and checking automatic conversions,\n"
    . "you can drop useless columns and tables by these requests:\n"
    . "DROP TABLE IF EXISTS tels;\n"
    . "ALTER TABLE auth_user_quick DROP COLUMN profile_mobile;\n"
    . "ALTER TABLE auth_user_quick DROP COLUMN profile_mobile_pub;\n"
    . "ALTER TABLE entreprises DROP COLUMN tel;\n"
    . "ALTER TABLE entreprises DROP COLUMN fax;\n"
    . "ALTER TABLE entreprises DROP COLUMN mobile;\n"
    . "ALTER TABLE entreprises DROP COLUMN tel_pub;\n";


// auxilliary functions

function guess_phone_type($str_type, $phone)
{
    $str_type = strtolower(trim($str_type));

    // special case for phone type 'autre', guessing by phone number
    if ($str_type == 'autre') {
        if (substr($phone, 3) == '336') {
            return 'mobile';
        } else {
            return 'fixed';
        }
    }

    if ((strpos($str_type, 'mob') !== false) || (strpos($str_type, 'cell') !== false)
        || (strpos($str_type, 'port') !== false) || (strpos($str_type, 'ptb') !== false)) {
        if (substr($phone, 3) == '336' || substr($phone, 2) != '33') {
            return 'mobile';      //for France check if number is a mobile one
        } else {
            return 'conflict';
        }
    }
    if (strpos($str_type, 'fax') !== false) {
        if (substr($phone, 3) == '336') {
            return 'conflict';
        } else {
            return 'fax';
        }
    }
    if ((strpos($str_type, 'fixe') !== false) || (strpos($str_type, 'tél') !== false)
        || (strpos($str_type, 'tel') !== false) || (strpos($str_type, 'free') !== false)) {
        if (substr($phone, 3) == '336') {
            return 'conflict';
        } else {
            return 'fixed';
        }
    }

    return 'unknown';
}

/* vim:set et sw=4 sts=4 ts=4: */
?>
