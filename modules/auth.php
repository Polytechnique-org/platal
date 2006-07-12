<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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

class AuthModule extends PLModule
{
    function handlers()
    {
        return array(
            'groupex/done-chall.php'        => $this->make_hook('chall', AUTH_PUBLIC),
            'groupex/export-econfiance.php' => $this->make_hook('econf', AUTH_PUBLIC),

            'auth-redirect.php' => $this->make_hook('redirect', AUTH_COOKIE),
            'auth-groupex.php'  => $this->make_hook('groupex',  AUTH_COOKIE),
        );
    }

    function chall(&$page)
    {
        $_SESSION["chall"] = uniqid(rand(), 1);
        echo $_SESSION["chall"] . "\n" . session_id();
        exit;
    }

    function econf(&$page)
    {
        global $globals;

        require_once 'platal/xmlrpc-client.inc.php';
        require_once 'lists.inc.php';

        $cle = $globals->core->econfiance;

        if (Session::get('chall') && $_GET['PASS'] == md5(Session::get('chall').$cle)) {

            $res  = $globals->xdb->query("SELECT password FROM auth_user_md5 WHERE user_id=10154");
            $pass = $res->fetchOneCell();

            $client =& lists_xmlrpc(10154, $pass, "x-econfiance.polytechnique.org");
            $members = $client->get_members('membres');
            if (is_array($members)) {
                $membres = Array();
                foreach($members[1] as $member) {
                    if (preg_match('/^([^.]*.[^.]*.(\d\d\d\d))@polytechnique.org$/',
                                   $member[1], $matches))
                    {
                        $membres[] = "a.alias='{$matches[1]}'";
                    }
                }
            }

            $where = join(' OR ',$membres);

            $all = $globals->xdb->iterRow(
                    "SELECT  u.prenom,u.nom,a.alias
                       FROM  auth_user_md5 AS u
                 INNER JOIN  aliases       AS a ON ( u.user_id = a.id AND a.type!='homonyme' )
                      WHERE  $where
                   ORDER BY  nom");

            $res = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n\n<membres>\n\n";

            while (list ($prenom1,$nom1,$email1) = $all->next()) {
                    $res .= "<membre>\n";
                    $res .= "\t<nom>$nom1</nom>\n";
                    $res .= "\t<prenom>$prenom1</prenom>\n";
                    $res .= "\t<email>$email1</email>\n";
                    $res .= "</membre>\n\n";
            }

            $res .= "</membres>\n\n";

            echo $res;
        }
        exit;
    }

    function handler_redirect(&$page)
    {
        redirect(Env::get('dest', '/'));
    }

    function handler_groupex(&$page)
    {
        global $globals;

        require_once dirname(__FILE__).'/auth/methods.inc.php';

        $gpex_pass = $_GET["pass"];
        $gpex_url  = urldecode($_GET["url"]);
        if (strpos($gpex_url, '?') === false) {
            $gpex_url .= "?PHPSESSID=" . $_GET["session"];
        } else {
            $gpex_url .= "&PHPSESSID=" . $_GET["session"];
        }

        /* a-t-on besoin d'ajouter le http:// ? */
        if (!preg_match("/^(http|https):\/\/.*/",$gpex_url))
            $gpex_url = "http://$gpex_url";
        $gpex_challenge = $_GET["challenge"];

        // mise à jour de l'heure et de la machine de dernier login sauf quand on est en suid
        if (!isset($_SESSION['suid'])) {
            $logger = (isset($_SESSION['log']) && $_SESSION['log']->uid == $uid)
                      ? $_SESSION['log']
                      : new DiogenesCoreLogger($uid);
            $logger->log('connexion_auth_ext', $_SERVER['PHP_SELF']);
        }

        /* on parcourt les entrees de groupes_auth */
        $res = $globals->xdb->iterRow('select privkey,name,datafields from groupesx_auth');

        while (list($privkey,$name,$datafields) = $res->next()) {
            if (md5($gpex_challenge.$privkey) == $gpex_pass) {
                $returl = $gpex_url.gpex_make_params($gpex_challenge,$privkey,$datafields);
                redirect($returl);
            }
        }

        /* si on n'a pas trouvé, on renvoit sur x.org */
        redirect('https://www.polytechnique.org/');
    }
}

?>
