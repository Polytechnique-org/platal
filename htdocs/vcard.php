<?php
require("auto.prepend.inc.php");
new_nonhtml_page('vcard.tpl', AUTH_COOKIE);

function quoted_printable_encode($input, $line_max = 76) {
    $hex = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
    $lines = preg_split("/(?:\r\n|\r|\n)/", $input);
    $eol = "\r\n";
    $linebreak = "\n";
    $escape = "=";
    $output = "";

    for ($j=0;$j<count($lines);$j++) {
        $line = $lines[$j];
        $linlen = strlen($line);
        $newline = "";
        for($i = 0; $i < $linlen; $i++) {
            $c = substr($line, $i, 1);
            $dec = ord($c);
            if ( ($dec == 32) && ($i == ($linlen - 1)) ) { // convert space at eol only
                $c = "=20";
            } elseif ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) ) { // always encode "\t", which is *not* required
                $h2 = floor($dec/16); $h1 = floor($dec%16);
                $c = $escape.$hex["$h2"].$hex["$h1"];
            }
            if ( (strlen($newline) + strlen($c)) >= $line_max ) { // CRLF is not counted
                $output .= $newline.$escape."\n"; // soft line break; " =\r\n" is okay
                $newline = "    ";
            }
            $newline .= $c;
        } // end of for
        $output .= $newline;
        if ($j<count($lines)-1) $output .= $linebreak;
    }
    return trim($output);
}

function format_adr($params, &$smarty) {
    // $adr1, $adr2, $adr3, $cp, $ville, $region, $pays
    extract($params['adr']);
    $res = ";;";
    if (! empty($adr1)) $res .= "$adr1\n";
    if (! empty($adr2)) $res .= "$adr2\n";
    if (! empty($adr3)) $res .= "$adr3\n";
    if (! empty($adr1) || ! empty($adr2) || ! empty($adr3))
        $res = substr($res, 0, -1);
    $res .= ";";
    if (! empty($ville)) $res .= "$ville;"; else $res .= ";";
    if (! empty($region)) $res .= "$region;"; else $res .= ";";
    if (! empty($cp)) $res .= "$cp;"; else $res .= ";";
    if (! empty($pays)) $res .= "$pays";
    return quoted_printable_encode($res);
}

$page->register_modifier('qp_enc', 'quoted_printable_encode');
$page->register_function('format_adr', 'format_adr');

$myquery = 
    "SELECT prenom, nom, epouse, username, mobile, web, libre, promo, alias, user_id, date
    FROM auth_user_md5 AS a
    WHERE username='{$_REQUEST['x']}'";
    $result=mysql_query($myquery);
if (mysql_num_rows($result)!=1) {
    echo 'erreur';
    exit;
}
$vcard = mysql_fetch_assoc($result);
$page->assign_by_ref('vcard', $vcard);
mysql_free_result($result);

$adr = mysql_query(
        "SELECT statut,adr1,adr2,adr3,cp,ville,gp.pays,gr.name,tel,fax,
        FIND_IN_SET('courrier', a.statut) AS courrier
        FROM adresses as a
        LEFT JOIN geoloc_pays AS gp ON(a.pays = gp.a2)
        LEFT JOIN geoloc_region AS gr
        ON(a.pays = gr.a2 AND a.region = gr.region)
        WHERE uid = {$vcard['user_id']}
        ORDER BY FIND_IN_SET('active', a.statut),
        NOT FIND_IN_SET('res-secondaire', a.statut)"
);
$home = Array();
while($home[] = mysql_fetch_assoc($adr));
array_pop($home);
mysql_free_result($adr);
$page->assign_by_ref('home', $home);


$adr = mysql_query(
        "SELECT adr1,adr2,adr3,cp,ville,gp.pays,gr.name,tel,fax,poste,entreprise,f.label as fonction
        FROM entreprises as e
        LEFT JOIN emploi_naf AS f ON(e.fonction = f.id)
        LEFT JOIN geoloc_pays AS gp ON(e.pays = gp.a2)
        LEFT JOIN geoloc_region AS gr
        ON(e.pays = gr.a2 AND e.region = gr.region)
        WHERE uid = {$vcard['user_id']}"
);
if(mysql_num_rows($adr))
    $work = mysql_fetch_assoc($adr);
mysql_free_result($adr);
$page->assign_by_ref('work', $work);


header("Pragma: ");
header("Cache-Control: ");
header("Content-type: text/x-vcard\n");
header("Content-Transfer-Encoding: Quoted-Printable\n");

$page->run();
?>
