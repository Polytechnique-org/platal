<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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
 ***************************************************************************
        $Id: mescontacts_pdf.php,v 1.3 2004-11-22 11:15:31 x2000habouzit Exp $
 ***************************************************************************/

#
# $Id: mescontacts_pdf.php,v 1.3 2004-11-22 11:15:31 x2000habouzit Exp $
#

require("xorg.inc.php");
new_skinned_page('index.tpl', AUTH_COOKIE);
require("texify.inc.php");
require("applis.func.inc.php");

$sql = "SELECT  a.*,c.*,e.alias as epouse
	  FROM  auth_user_md5 AS a
    INNER JOIN  contacts      AS c ON ( a.user_id = c.contact )
    LEFT  JOIN  aliases       AS e ON ( a.user_id = e.id and FIND_IN_SET('epouse',e.flags) )
         WHERE  c.uid = {$_SESSION['uid']}";
// choix de l'ordre des réponses (par promo ou par noms)
if(isset($_GET['order']) && $_GET['order']=="promo") {
    $sql .= " ORDER BY  a.promo, a.nom, a.prenom";
} else {
    $sql .= " ORDER BY a.nom, a.prenom, a.promo";
}
    
$req = $globals->db->query($sql);

// génération des en-têtes
$texsrc = contacts_headers();

// on traite ensuite les contacts un par un
while ($myrow = mysql_fetch_array($req)) {

    /* affichage de l'identité */
    ereg( "[0-9]{2}([0-9]{2})-([0-9]{1,2})-([0-9]{1,2})", $myrow["date"], $regs );
    $texsrc .= contact_tbl_hdr($myrow["prenom"], $myrow["nom"], $myrow["epouse"], $myrow["promo"], "$regs[3].$regs[2].$regs[1]");


    /* affichage de l'école d'appli et de la post-appli */
    $tmpreq=$globals->db->query(
       "SELECT applis_def.text,applis_def.url,applis_ins.type,applis_ins.ordre
        FROM applis_ins
	INNER JOIN applis_def ON applis_def.id=applis_ins.aid
	WHERE uid=".$myrow["user_id"]." ORDER by ordre"
	    );
    while (list($rapp_txt,$rapp_url,$rapp_type,$rapp_ordre)=mysql_fetch_row($tmpreq)) {
      if ($rapp_ordre==0)
        $texsrc .= contact_tbl_entry("Appli", applis_fmt($rapp_type,$rapp_txt,""));
      if ($rapp_ordre==1)
        $texsrc .= contact_tbl_entry("Post-appli", applis_fmt($rapp_type,$rapp_txt,""));
    }
    mysql_free_result($tmpreq);

    /* affichage des différentes adresses persos */
    $i = 0;
    $tmpreq = $globals->db->query(
     "SELECT adr1,adr2,adr3,cp,ville,gp.pays,gr.name,tel,fax
      FROM adresses AS adr
      LEFT JOIN geoloc_pays AS gp ON(adr.pays=gp.a2)
      LEFT JOIN geoloc_region AS gr ON(adr.pays=gr.a2 AND adr.region=gr.region)
      WHERE adr.uid = ".$myrow["user_id"]
              );
    while (list($adr1,$adr2,$adr3,$cp,$ville,$pays,$region,$tel,$fax)
            = mysql_fetch_row($tmpreq)) {
        $i++;
	$ch_adr = "";
	if (! empty($adr1)) $ch_adr .= "\n$adr1";
	if (! empty($adr2)) $ch_adr .= "\n$adr2";
	if (! empty($adr3)) $ch_adr .= "\n$adr3";
	if (! empty($cp) || ! empty($ville)) $ch_adr .= "\n$cp $ville";
	if (! empty($region)) $ch_adr .= "\n$region, ";
	else if (! empty($pays)) $ch_adr .= "\n";
	if (! empty($pays)) $ch_adr .= "$pays";
	$texsrc .= contact_tbl_entry("Adresse $i", substr($ch_adr, 1));
	if (! empty($tel)) $texsrc .= contact_tbl_entry("Téléphone $i", $tel);
	if (! empty($fax)) $texsrc .= contact_tbl_entry("Fax $i", $fax);
	
    }
    mysql_free_result($tmpreq);

    /* affichage du téléphone mobile */
    if ($myrow["mobile"])
    	$texsrc .= contact_tbl_entry("Mobile", $myrow["mobile"]);

    /* affichage des infos professionnelles (poste, adresse, tel) */
    $res_pro = $globals->db->query(
       "SELECT  entreprise, s.label, ss.label, f.fonction_fr, poste,
                adr1, adr2, adr3, cp, ville, gp.pays, gr.name, tel, fax
	  FROM  entreprises       AS e
     LEFT JOIN  emploi_secteur    AS s  ON e.secteur = s.id
     LEFT JOIN  emploi_ss_secteur AS ss ON (e.secteur = ss.secteur AND e.ss_secteur = ss.id)
     LEFT JOIN  fonctions_def     AS f  ON f.id = e.fonction
     LEFT JOIN  geoloc_pays       AS gp ON gp.a2 = e.pays
    INNER JOIN  geoloc_region     AS gr ON (gr.a2 = e.pays AND gr.region = e.region)
	WHERE e.uid = ".$myrow["user_id"]
    );
    while (list($entreprise, $secteur, $ss_secteur, $fonction, $poste, $adr1, $adr2, $adr3, $cp, $ville, $pays, $region, $tel, $fax) = mysql_fetch_row($res_pro)) {
        if (!empty($entreprise))
    	    $texsrc .= contact_tbl_entry("Entreprise", $entreprise . ($secteur ? " ($secteur - $ss_secteur)" : ""));

        if (!empty($poste))
    	    $texsrc .= contact_tbl_entry("Poste / Fonction", $poste." / ".(($fonction) ? $fonction : "-"));
	$ch_adr = "";
	if (! empty($adr1)) $ch_adr .= "\n$adr1";
	if (! empty($adr2)) $ch_adr .= "\n$adr2";
	if (! empty($adr3)) $ch_adr .= "\n$adr3";
	if (! empty($cp) || ! empty($ville)) $ch_adr .= "\n$cp $ville";
	if (! empty($region)) $ch_adr .= "\n$region, ";
	else if (! empty($pays)) $ch_adr .= "\n";
	if (! empty($pays)) $ch_adr .= "$pays";
	$texsrc .= contact_tbl_entry("Adresse pro.", substr($ch_adr, 1));
	if (! empty($tel)) $texsrc .= contact_tbl_entry("Téléphone pro.", $tel);
	if (! empty($fax)) $texsrc .= contact_tbl_entry("Fax pro.", $fax);
    }

    /* fin du contact */
    $texsrc .= contact_tbl_ftr();

}

// enfin on rajoute le footer
$texsrc .= contacts_footer();
mysql_free_result($req);

// puis on convertit le fichier tex en pdf !
tex_to_pdf($texsrc);

?>
