<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

class FusionAxModule extends PLModule{

    function handlers()
    {
        return array(
            'fusionax'          => $this->make_hook('index', AUTH_MDP, 'admin'),
            'fusionax/import'   => $this->make_hook('import', AUTH_MDP, 'admin'),
            'fusionax/ids'      => $this->make_hook('ids', AUTH_MDP, 'admin'),
            'fusionax/misc'     => $this->make_hook('misc', AUTH_MDP, 'admin'),
        );
    }
    
    function handler_index(&$page)
    {
        global $globals;
        $page->changeTpl('fusionax/index.tpl');
        $page->assign('xorg_title','Polytechnique.org - Fusion des annuaires');
        if (isset($globals->fusionax) && isset($globals->fusionax->LastUpdate)) {
            $page->assign('lastimport', date("d-m-Y",$globals->fusionax->LastUpdate));
        } 
    }
    
    function handler_import(&$page, $action = 'index', $fileSQL = '')
    {
        if ($action == 'index') {
            $page->changeTpl('fusionax/import.tpl');
            $page->addJsLink('jquery.js');
            global $globals;
            if (isset($globals->fusionax) && isset($globals->fusionax->LastUpdate)) {
                $page->assign('lastimport', "le ".date("d/m/Y à H:i",$globals->fusionax->LastUpdate));
            }
            if (!file_exists(dirname(__FILE__).'/../configs/ax_xorg_rsa')) {
                $page->assign('keymissing', realpath(dirname(__FILE__).'/../configs/').'/ax_xorg_rsa');
            }
            return;
        }
        $report = array();
        header("Content-type: text/javascript; charset=utf-8");
        if (Env::has('tmpdir')) {
            $tmpdir = Env::v('tmpdir');
        } else {
            exec('rm /tmp/fusionax* -rf');
            $tmpdir = tempnam('/tmp', 'fusionax');
            unlink($tmpdir);
            mkdir($tmpdir);
            chmod($tmpdir, 0777);
            if (!copy(dirname(__FILE__).'/../configs/ax_xorg_rsa',$tmpdir.'/ax_xorg_rsa'))
                $report[] = 'Impossible de copier la clef pour se logger sur le serveur AX';
            chmod($tmpdir.'/ax_xorg_rsa', 0600);
        }
        $modulepath = realpath(dirname(__FILE__).'/fusionax/').'/';
        $olddir = getcwd();
        chdir($tmpdir);
        if ($action == 'launch') {
            exec($modulepath.'import-ax.sh', $report);
            $report[] = utf8_decode('Récupération du fichier terminé.');
            $report[] = 'Import dans la base en cours...';
            $next = 'integrateSQL';
        } else if ($action == 'integrateSQL') {
            $filesSQL = array('Activites.sql', 'Adresses.sql', 'Anciens.sql', 'Formations.sql', 'Entreprises.sql');
            if ($fileSQL != '') {
                $trans = array_flip($filesSQL);
                $nextfile = $trans[$fileSQL] + 1;
                $queries = explode(';',file_get_contents($modulepath.$fileSQL));
                foreach ($queries as $q) if (trim($q)) {
                    if (substr($q,0,2) == '--') {
                        $lines = explode("\n",$q);
                        $l = $lines[0];
                        $report[] = addslashes(utf8_decode($l));
                    }
                    XDB::execute($q);
                }
            } else {
                $nextfile = 0;
            }
            if (!isset($filesSQL[$nextfile])) {
                $next = 'clean';
            } else {
                $next = 'integrateSQL/'.$filesSQL[$nextfile];
            }
        } else if ($action == 'clean') {
            chdir($olddir);
            exec("rm -rf $tmpdir", $report);
            $report[] = 'Fin de l\'import';
            global $globals;
            $globals->change_dynamic_config(array('LastUpdate' => time()), 'FusionAx');
        }
        $tmpdir = getcwd();
        chdir($olddir);
        foreach($report as $t)
            echo "$('#fusionax_import').append('".utf8_encode($t)."<br/>');\n";
        if (isset($next)) {
            echo "$.getScript('fusionax/import/".$next."?tmpdir=".urlencode($tmpdir)."');";
        }
        exit;
    }
    
    private static function link_by_ids($user_id, $matricule_ax)
    {
        if (!XDB::execute("UPDATE fusionax_import AS i INNER JOIN auth_user_md5 AS u 
            SET u.matricule_ax = i.id_ancien, i.user_id = u.user_id, i.date_match_id = NOW()
            WHERE
            i.id_ancien = {?} AND u.user_id = {?} AND (
                u.matricule_ax != {?} OR u.matricule_ax IS NULL OR
                i.user_id != {?} OR i.user_id IS NULL)", $matricule_ax, $user_id, $matricule_ax, $user_id))
        {
            return 0;
        }
        return XDB::affectedRows() / 2;  
    }
    
    private static function find_easy_to_link($limit = 10)
    {
        return XDB::iterator("SELECT 
            xorg.prenom, xorg.nom, xorg.promo, xorg.user_id, ax.id_ancien
            FROM fusionax_anciens AS ax
            INNER JOIN fusionax_import AS i ON (i.id_ancien = ax.id_ancien AND i.user_id IS NULL)
            INNER JOIN auth_user_md5 AS xorg
            WHERE 
                xorg.matricule_ax IS NULL AND 
                xorg.promo = ax.promotion_etude AND 
                xorg.prenom LIKE ax.prenom AND 
                xorg.nom LIKE ax.Nom_complet
            ".($limit?('LIMIT '.$limit):''));
    }
    
    function handler_ids(&$page, $part = 'main')
    {
        global $globals;
        $globals->change_dynamic_config(array('LastUpdate' => time()), 'FusionAX');
        
        $page->assign('xorg_title','Polytechnique.org - Fusion des annuaires - Mise en correspondance simple');
        if ($part == 'missingInAX')
        {
            // locate all persons from this database that are not in AX's
            $page->changeTpl('fusionax/idsMissingInAx.tpl');
            $missingInAX = XDB::iterator("SELECT *
                FROM auth_user_md5 AS u
                    LEFT JOIN aliases AS a  ON(a.id = u.user_id AND FIND_IN_SET('bestalias', a.flags))
                    WHERE u.matricule_ax IS NULL
                    LIMIT 20");
            $page->assign('missingInAX', $missingInAX);
            return;
        }
        if ($part == 'missingInXorg')
        {
            // locate all persons from AX's database that are not here
            $page->changeTpl('fusionax/idsMissingInXorg.tpl');
            $missingInXorg = XDB::iterator("SELECT promotion_etude AS promo, prenom, Nom_usuel AS nom, id_ancien 
                FROM fusionax_import 
                    INNER JOIN fusionax_anciens AS a USING (id_ancien)
                    WHERE fusionax_import.user_id IS NULL
                    LIMIT 20");
            $page->assign('missingInXorg', $missingInXorg);
            return;
        }
        if ($part == 'link')
        {
            FusionAxModule::link_by_ids(Env::i('user_id'),Env::v('matricule_ax'));
        }
        if ($part == 'linknext')
        {
            $linksToDo = FusionAxModule::find_easy_to_link(10);
            while ($l = $linksToDo->next())
            {
                FusionAxModule::link_by_ids($l['user_id'],$l['id_ancien']);
            }
        }
        if ($part == 'linkall')
        {
            $linksToDo = FusionAxModule::find_easy_to_link(0);
            while ($l = $linksToDo->next())
            {
                FusionAxModule::link_by_ids($l['user_id'],$l['id_ancien']);
            }
        }
        {
            $page->changeTpl('fusionax/ids.tpl');
            $missingInAX = XDB::query("SELECT COUNT(*) FROM auth_user_md5 WHERE matricule_ax IS NULL");
            if ($missingInAX)
            {
                $page->assign('nbMissingInAX', $missingInAX->fetchOneCell());
            }
            $missingInXorg = XDB::query("SELECT COUNT(*) FROM fusionax_import WHERE user_id IS NULL");
            if ($missingInXorg)
            {
                $page->assign('nbMissingInXorg', $missingInXorg->fetchOneCell());
            }
            $easyToLink = FusionAxModule::find_easy_to_link(10);
            if ($easyToLink->total() > 0)
            { 
                $page->assign('easyToLink', $easyToLink);
            }
        }
    }
    function handler_misc(&$page)
    {
        $page->changeTpl('fusionax/misc.tpl');
        // deceased
        $deceasedErrorsSql = XDB::query('SELECT COUNT(*) FROM fusionax_deceased');
        $page->assign('deceasedErrors',$deceasedErrorsSql->fetchOneCell());
        $page->assign('deceasedMissingInXorg',XDB::iterator('SELECT user_id,id_ancien,nom,prenom,promo,Date_décès FROM fusionax_deceased WHERE deces = "0000-00-00" LIMIT 10'));
        $page->assign('deceasedMissingInAX',XDB::iterator('SELECT user_id,id_ancien,nom,prenom,promo,deces FROM fusionax_deceased WHERE Date_décès = "0000-00-00" LIMIT 10'));
        $page->assign('deceasedDifferent',XDB::iterator('SELECT user_id,id_ancien,nom,prenom,promo,Date_décès,deces FROM fusionax_deceased WHERE deces != "0000-00-00" AND Date_décès != "0000-00-00" LIMIT 10'));
    }
}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:?>
