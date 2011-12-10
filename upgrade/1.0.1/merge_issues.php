#!/usr/bin/php5
<?php
// WARNING: this script takes a few minutes to be executed completly, thus run it into a screen.

require_once 'connect.db.inc.php';
require_once '../../classes/phone.php';
require_once '../../classes/address.php';
require_once '../../classes/visibility.php';

$globals->debug = 0; // Do not store backtraces.

$abbreviations = array(
    'commandant'    => 'cdt',
    'docteur'       => 'dr',
    'haut'          => 'ht',
    'haute'         => 'ht',
    'hauts'         => 'ht',
    'hts'           => 'ht',
    'general'       => 'gen',
    'gal '          => 'gen ',
    'grand'         => 'gd',
    'grande'        => 'gd',
    'grands'        => 'gd',
    'gde '          => 'gd ',
    'gds '          => 'gd ',
    'lieutenant'    => 'lt',
    'marechal'      => 'mal',
    'notre dame'    => 'n d',
    'nouveau'       => 'nouv',
    'president'     => 'pdt',
    'saint'         => 'st',
    'sainte'        => 'st',
    'saintes'       => 'st',
    'saints'        => 'st',
    'ste '          => 'st ',
    'appartement'   => 'app',
    'apt'           => 'app',
    'appt'          => 'app',
    'appart'        => 'app',
    'arrondissement'=> 'arr',
    'batiment'      => 'bat',
    'escalier'      => 'esc',
    'etage'         => 'etg',
    'et '           => 'etg',
    'immeuble'      => 'imm',
    'lieu dit'      => 'ld',
    ' lt '          => ' lt ',
    'porte'         => 'pte',
    'quartier'      => 'quart',
    'residence'     => 'res',
    'resi'          => 'res',
    'villa'         => 'vla',
    'village'       => 'vlge',
    'vil '          => 'vlge ',
    'allee'         => 'all',
    'avenue'        => 'av',
    'boulevard'     => 'bd',
    'bld'           => 'bd',
    'chemin'        => 'ch',
    'chem '         => 'ch ',
    'che '          => 'ch ',
    'cours'         => 'crs',
    'domaine'       => 'dom',
    'doma '         => 'dom ',
    'faubourg'      => 'fg',
    'fbg'           => 'fg',
    'hameau'        => 'ham',
    'hame '         => 'ham ',
    'impasse'       => 'imp',
    'impa '         => 'imp ',
    'lotissement'   => 'lot',
    'montee'        => 'mte',
    'passage'       => 'pass',
    'place'         => 'pl',
    'promenade'     => 'pro ',
    'prom '         => 'pro ',
    'quai'          => 'qu',
    'rue'           => 'r',
    'route'         => 'rte',
    ' rde '         => ' rte ',
    ' rle '         => ' rte ',
    'sentier'       => 'sen',
    'sent '         => 'sen ',
    'square'        => 'sq',
    'mount'         => 'mt',
    'road'          => 'rd',
    'street'        => 'st',
    'str '          => 'str',
    'bis'           => 'b',
    'ter'           => 't'
);
$patterns = array();
$replacements = array();
foreach ($abbreviations as $key => $abbreviation) {
    $patterns[] = '/' . $key . '/';
    $replacements[] = $abbreviation;
}

function check($address1, $address2)
{
    return $address1['short'] == $address2['short'] || $address1['short'] == $address2['long']
        || $address1['long'] == $address2['short'] || $address1['long'] == $address2['long'];
}

print "Deletes duplicated addresses. (1/3)\n";
$pids = XDB::rawFetchColumn("SELECT  DISTINCT(pid)
                               FROM  profile_addresses AS a1
                              WHERE  type = 'home' AND EXISTS (SELECT  *
                                                                 FROM  profile_addresses AS a2
                                                                WHERE  a2.type = 'home' AND a2.pid = a1.pid AND a2.id != a1.id)
                           ORDER BY  pid");
$total = count($pids);
$done = 0;
$aux = 0;
$deleted = 0;
$addresses = array();
$rawAddresses = array();
$duplicates = array();
foreach ($pids as $pid) {
    $count = 0;
    $it = Address::iterate(array($pid), array(Address::LINK_PROFILE), array(0));
    while ($item = $it->next()) {
        $addresses[$count] = $item;
        $rawAddress = preg_replace('/[^a-z0-9]/', ' ', mb_strtolower(replace_accent($item->text)));
        $rawAddresses[$count] = array(
            'long'  => preg_replace('/\s+/', '', $rawAddress),
            'short' => preg_replace('/\s+/', '', preg_replace($patterns, $replacements, $rawAddress)),
        );
        ++$count;
    }
    for ($i = 0; $i < $count; ++$i) {
        for ($j = $i + 1; $j < $count; ++$j) {
            if (check($rawAddresses[$i], $rawAddresses[$j])) {
                $duplicates[$j] = true;
                if (Visibility::isLessRestrictive($addresses[$i]->pub, $addresses[$j]->pub)) {
                    $addresses[$i]->pub = $addresses[$j]->pub;
                }
                if ($addresses[$j]->hasFlag('mail') && !$addresses[$i]->hasFlag('mail')) {
                    $addresses[$i]->addFlag('mail');
                }
            }
        }
    }
    if (count($duplicates)) {
        foreach ($duplicates as $key => $bool) {
            unset($addresses[$key]);
        }
    }
    if (count($addresses) != $count) {
        $deleted += ($count - count($addresses));
        Address::deleteAddresses($pid, 'home');
        $id = 0;
        foreach ($addresses as $address) {
            $address->setId($id);
            $address->save();
            ++$id;
        }
        XDB::execute('UPDATE IGNORE  profile_merge_issues
                                SET  issues = REPLACE(issues, \'address\', \'\')
                              WHERE  pid = {?}', $pid);
    }
    unset($rawAddresses);
    unset($addresses);
    unset($duplicates);

    ++$done;
    ++$aux;
    if ($aux == 100) {
        $aux = 0;
        printf("\r%u / %u",  $done, $total);
    }
}
printf("\r%u / %u",  $done, $total);
print "\n$deleted addresses deleted.\n\n";

print "Formats non formated phones. (2/3)\n";
$it = XDB::rawIterator("SELECT  search_tel AS search, display_tel AS display, comment, link_id,
                                tel_type AS type, link_type, tel_id AS id, pid, pub
                          FROM  profile_phones
                         WHERE  search_tel = '' OR search_tel IS NULL
                      ORDER BY  pid, link_id, tel_id");
$total = $it->total();
$i = 0;
$j = 0;
while ($item = $it->next()) {
    $phone = new Phone($item);
    $phone->delete();
    $phone->save();

    ++$i;
    ++$j;
    if ($j == 100) {
        $j = 0;
        printf("\r%u / %u",  $i, $total);
    }
}
printf("\r%u / %u",  $i, $total);
print "\nFormating done.\n\n";

print "Deletes duplicated phones. (3/3)\n";
$pids = XDB::rawFetchColumn("SELECT  DISTINCT(pid)
                               FROM  profile_phones AS p1
                              WHERE  link_type = 'user' AND EXISTS (SELECT  *
                                                                      FROM  profile_phones AS p2
                                                                     WHERE  p2.link_type = 'user' AND p2.pid = p1.pid AND p2.tel_id != p1.tel_id)
                           ORDER BY  pid");
$total = count($pids);
$done = 0;
$aux = 0;
$deleted = 0;
$phones = array();
$duplicates = array();
foreach ($pids as $pid) {
    $count = 0;
    $it = Phone::iterate(array($pid), array(Phone::LINK_PROFILE), array(0));
    while ($item = $it->next()) {
        $phones[] = $item;
        ++$count;
    }
    for ($i = 0; $i < $count; ++$i) {
        for ($j = $i + 1; $j < $count; ++$j) {
            if ($phones[$i]->search == $phones[$j]->search) {
                $duplicates[$j] = true;
                if (Visibility::isLessRestrictive($phones[$i]->pub, $phones[$j]->pub)) {
                    $phones[$i]->pub = $phones[$j]->pub;
                }
            }
        }
    }
    if (count($duplicates)) {
        foreach ($duplicates as $key => $bool) {
            unset($phones[$key]);
        }
    }
    if (count($phones) != $count) {
        $deleted += ($count - count($phones));
        Phone::deletePhones($pid, 'user');
        $id = 0;
        foreach ($phones as $phone) {
            $phone->setId($id);
            $phone->save();
            ++$id;
        }
        XDB::execute('UPDATE IGNORE  profile_merge_issues
                                SET  issues = REPLACE(issues, \'phone\', \'\')
                              WHERE  pid = {?}', $pid);
    }
    unset($duplicates);
    unset($phones);

    ++$done;
    ++$aux;
    if ($aux == 10) {
        $aux = 0;
        printf("\r%u / %u",  $done, $total);
    }
}
printf("\r%u / %u",  $done, $total);
print "\n$deleted phones deleted.\n\n";

print "That's all folks!\n";

/* vim:set et sw=4 sts=4 ts=4: */
?>
