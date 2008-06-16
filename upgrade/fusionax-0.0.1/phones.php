#!/usr/bin/php5
<?php
require_once 'connect.db.inc.php';

//next two are required to include 'profil.func.inc.php'
require_once 'xorg.inc.php';
$page = new XorgPage(null);

require_once 'profil.func.inc.php';

$globals->debug = 0; //do not store backtraces

$warnings = 0;

// Import from auth_user_quick
echo "\nImporting mobile phone numbers from auth_user_quick...\n";
$phones = XDB::iterRow("SELECT user_id, profile_mobile_pub, profile_mobile FROM auth_user_quick WHERE profile_mobile <> ''");
while (list($uid, $pub, $phone) = $phones->next()) {
    $fmt_phone = format_phone_number($phone);
    if($fmt_phone != '')
    {
        $display   = format_display_number($fmt_phone, $error);
        if (!XDB::execute("INSERT INTO telephone (uid, link_type, link_id, tel_id, tel_type, search_tel, display_tel, pub)
                                VALUES ({?}, 'user', 0, 0, 'mobile', {?}, {?}, {?})", $uid, $fmt_phone, $display, $pub)) {
            echo "WARNING: insert of profile mobile phone number failed for user $uid.\n";
            $warnings++;
        }
    }
}


// Import from entreprises
echo "\nImporting professional phone numbers from entreprises...\n";
$phones = XDB::iterator("SELECT uid, entrid, tel, fax, mobile, tel_pub FROM entreprises ORDER BY uid");
while ($row = $phones->next()) {
    $request = "INSERT INTO telephone (uid, link_type, link_id, tel_id, tel_type, search_tel, display_tel, pub)
                     VALUES ({?}, 'pro', {?}, {?}, {?}, {?}, {?}, {?})";
    $fmt_fixed   = format_phone_number($row['tel']);
    $fmt_mobile  = format_phone_number($row['mobile']);
    $fmt_fax     = format_phone_number($row['fax']);
    if ($fmt_fixed != '')
    {
        $disp_fixed  = format_display_number($fmt_fixed, $error);
        if (!XDB::execute($request, $row['uid'], $row['entrid'], 0, 'fixed', $fmt_fixed, $disp_fixed, $row['tel_pub'])) {
            echo 'WARNING: insert of professional fixed phone number failed for user ' . $row['uid'] . ' and entreprise ' . $row['entrid'] . ".\n";
            $warnings++;
        }
    }
    if ($fmt_mobile != '')
    {
        $disp_mobile = format_display_number($fmt_mobile, $error);
        if (!XDB::execute($request, $row['uid'], $row['entrid'], 1, 'mobile', $fmt_mobile, $disp_mobile, $row['tel_pub'])) {
            echo 'WARNING: insert of professional mobile number failed for user ' . $row['uid'] . ' and entreprise ' . $row['entrid'] . ".\n";
            $warnings++;
        }
    }
    if ($fmt_fax != '')
    {
        $disp_fax    = format_display_number($fmt_fax, $error);
        if (!XDB::execute($request, $row['uid'], $row['entrid'], 2, 'fax', $fmt_fax, $disp_fax, $row['tel_pub'])) {
            echo 'WARNING: insert of professional fax number failed for user ' . $row['uid'] . ' and entreprise ' . $row['entrid'] . ".\n";
            $warnings++;
        }
    }
}


//import from tels
echo "\nImporting personnal phone numbers from tels...\n";
$phones = XDB::iterator("SELECT uid, adrid, telid, tel_type, tel_pub, tel FROM tels");
$conversions = array();
$autre_count = 0;
while ($row = $phones->next()) {
    $fmt_phone  = format_phone_number($row['tel']);
    if ($fmt_phone != '') {
        $display    = format_display_number($fmt_phone, $error);
        $guess_type = guess_phone_type($row['tel_type'], $fmt_phone);

        switch ($guess_type) {
        case 'fixed':
        case 'fax':
        case 'mobile':
            if (!XDB::execute("INSERT INTO telephone (uid, link_type, link_id, tel_id, tel_type, search_tel, display_tel, pub)
                                    VALUES ({?}, 'address', {?}, {?}, {?}, {?}, {?}, {?})",
                              $row['uid'], $row['adrid'], $row['telid'], $guess_type, $fmt_phone, $display, $row['tel_pub'])) {
                echo  'WARNING: insert of address phone number failed for user ' . $row['uid'] . ', address ' . $row['adrid']
                    . ' and telephone id ' . $row['telid'] . ".\n";
                $warnings++;
            } else {
                if ($row['tel_type'] == 'Autre') {
                    $autre_count++;
                } else if (!isset($conversions[$row['tel_type']])) {
                    $conversions[$row['tel_type']] = $guess_type;
                }
            }
            break;
        case 'conflict':
            echo  'WARNING: conflict for user ' . $row['uid'] . ', address ' . $row['adrid']
                . ' and telephone id ' . $row['telid'] . ': type = "' . $row['tel_type']
                . '", number = "' .$fmt_phone . "\"\n";
            $warnings++;
            break;
        case 'unknown':
        default:
            echo  'WARNING: unknown phone type (' . $row['tel_type'] . ') for user ' . $row['uid'] . ', address ' . $row['adrid']
                . ' and telephone id ' . $row['telid'] . "\n";
            $warnings++;
        }
    }
}

echo "\nSummary of automatic phone type conversion\n";
foreach ($conversions as $old => $new) {
    echo "* $old => $new\n";
}
echo "There was also $autre_count conversions from old type 'Autre' to a new one determined by the phone number.\n";



//end of import
if ($warnings) {
    echo  "\n----------------------------------------------------------------------\n"
        . " There is $warnings phone numbers that couldn't be imported.\n"
        . " They need to be manually inserted.\n";
}
echo  "\nAfter solving any import problem and checking automatic conversions,\n"
    . "you can drop useless columns and tables by these requests:\n"
    . "DROP TABLE IF EXISTS `tels`;\n"
    . "ALTER TABLE `auth_user_quick` DROP COLUMN `profile_mobile`;\n"
    . "ALTER TABLE `auth_user_quick` DROP COLUMN `profile_mobile_pub`;\n"
    . "ALTER TABLE `entreprises` DROP COLUMN `tel`;\n"
    . "ALTER TABLE `entreprises` DROP COLUMN `fax`;\n"
    . "ALTER TABLE `entreprises` DROP COLUMN `mobile`;\n"
    . "ALTER TABLE `entreprises` DROP COLUMN `tel_pub`;\n";


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

    if ((strpos($str_type, 'mob') !== false) || (strpos($str_type, 'cell') !== false) || (strpos($str_type, 'port') !== false)) {
        if (substr($phone, 3) == '336' || substr($phone, 2) != '33') {
            return 'mobile';      //for France check if number is a mobile one
        } else {
            return 'conflict';
        }
    }
    if (strpos($str_type, 'fax') !== false) {
        if(substr($phone, 3) == '336') {
            return 'conflict';
        } else {
            return 'fax';
        }
    }
    if ((strpos($str_type, 'fixe') !== false) || (strpos($str_type, 'tél') !== false) || (strpos($str_type, 'tel') !== false)) {
        if(substr($phone, 3) == '336') {
            return 'conflict';
        } else {
            return 'fixed';
        }
    }

    return 'unknown';
}

/* vim:set et sw=4 sts=4 ts=4: */
?>
