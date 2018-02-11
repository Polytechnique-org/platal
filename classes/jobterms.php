<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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

class JobTerms {

    const ALL = 'all';
    const ONLY_JOBS = 'jobs';
    const ONLY_MENTORS = 'mentors';

    /** Retreives subterms of a term to display in a tree
     * @param $parent_jtid the id of the parent jobterm
     * @param $filter enable filtering on jobterms that have been used in jobs or in mentors, in
     *  that case, each row as an 'nb' field that counts all profiles that match
     * @param $token_filter a text (usually ending by %) the retreived subterms to jobterms that
     *  contains this tokens or that one of their children contains it.
     * @return an iterator over jobterms with jtid, name, full_name and nb
     */
    static public function getSubTerms($parent_jtid, $filter = self::ALL, $token_filter = null) {
        switch ($filter) {
          case self::ALL:
          default:
            $table = '';
            break;
          case self::ONLY_JOBS:
            $table = 'profile_job_term';
            break;
          case self::ONLY_MENTORS:
            $table = 'profile_mentor_term';
            break;
        }
        // we are in a tree looking for certain job terms:
        $countingterms = false;
        if ($table) {
            $count = ', COUNT(DISTINCT j.pid) AS nb';
            $join = ' INNER JOIN  profile_job_term_relation AS r2 ON (r2.jtid_1 = e.jtid)
                      INNER JOIN  '.$table.' AS j ON (j.jtid = r2.jtid_2)';
            $countingterms = 'j.jtid';
        } else {
            $count = $join = '';
        }
        if (!empty($token_filter)) {
            $join .= ' INNER JOIN  profile_job_term_relation AS rtf ON (rtf.jtid_1 = e.jtid) '.
                self::token_join_query(self::tokenize($token_filter), 'rtf', 'jtid_2');
            if (!$countingterms) {
                $countingterms = 'rtf.jtid_2';
            }
        }
        if (!$countingterms) {
            $select_leaf = 'IF(r_subbranch.jtid_1 IS NULL,1,0)';
            $join .= ' LEFT JOIN  profile_job_term_relation AS r_subbranch ON (r_subbranch.jtid_1 = e.jtid AND r_subbranch.computed = "original") ';
        } else {
            // branches that have counting terms different that
            // main branch will have subbranches
            $select_leaf = 'MIN('.$countingterms.' = e.jtid)';
        }
        return XDB::iterator('SELECT  e.jtid, e.name, e.full_name'.$count.', '.$select_leaf.' AS leaf
                                FROM  profile_job_term_enum AS e
                          INNER JOIN  profile_job_term_relation AS r ON (r.jtid_2 = e.jtid)'.$join.'
                               WHERE  r.jtid_1 = {?} AND r.computed = "original"
                            GROUP BY  e.jtid
                            ORDER BY  e.name',
                       $parent_jtid);
    }

    /**
     * Display a JSon page containing the sub-branches of a branch in the job terms tree.
     * @param $page the Platal page
     * @param $filter filter helps to display only jobterms that are contained in jobs or in mentors
     *
     * @param Env::i('jtid') job term id of the parent branch, if none trunk will be used
     * @param Env::v('attrfunc') the name of a javascript function that will be called when a branch
     * is chosen
     * @param Env::v('treeid') tree id that will be given as first argument of attrfunc function
     * the second argument will be the chosen job term id and the third one the chosen job's full name.
     * @param Env::v('text_filter') a string (usually ending by %) that will be used to filter
     * subbranches, keeping only the one containing this text in its title or in the title of one of
     * its subbranches.
     */
    static public function ajaxGetBranch($page, $filter = self::ALL) {
        pl_content_headers('application/json');
        $page->changeTpl('include/jobterms.branch.tpl', NO_SKIN);
        $subTerms = self::getSubTerms(Env::v('jtid'), $filter, Env::v('text_filter'));
        $page->assign('subTerms', $subTerms);
        switch ($filter) {
          case self::ONLY_JOBS:
            $page->assign('filter', 'camarade');
            break;
          case self::ONLY_MENTORS:
            $page->assign('filter', 'mentor');
            break;
        }
        $page->assign('jtid', Env::v('jtid'));
        $page->assign('text_filter', Env::v('text_filter'));
        $page->assign('attrfunc', Env::v('attrfunc'));
        $page->assign('treeid', Env::v('treeid'));
    }

    static public function jsAddTree($platalpage, $domElement = '.term_tree', $treeid = '', $attrfunc = '') {
        return '$("'.addslashes($domElement).'").jstree({
            "core" : {"strings":{"loading":"Chargement ..."}},
            "plugins" : ["themes","json_data"],
            "themes" : { "url" : $.plURL("css/jstree.css") },
            "json_data" : { "ajax" : {
                "url" : $.plURL("'.addslashes($platalpage).'"),
                "data" : function(nod) {
                    var jtid = 0;
                    if (nod != -1) {
                        jtid = nod.attr("id").replace(/^.*_([0-9]+)$/, "$1");
                    }
                    return {
                        "jtid" : jtid,
                        "treeid" : "'.addslashes($treeid).'",
                        "attrfunc" : "'.addslashes($attrfunc).'"
                     }
                }
            }} });';
    }

    /**
     * Extract search token from term
     * @param $term a utf-8 string that can contain any char
     * @return an array of elementary tokens
     */
    static public function tokenize($term)
    {
        $term = mb_strtoupper(replace_accent($term));
        $term = str_replace(array('/', ',', '(', ')', '"', '&', '»', '«'), ' ', $term);
        $tokens = explode(' ', $term);
        static $not_tokens = array('ET','AND','DE','DES','DU','D\'','OU','L\'','LA','LE','LES','PAR','AU','AUX','EN','SUR','UN','UNE','IN');
        foreach ($tokens as &$t) {
            $t = preg_replace(array('/^-+/','/-+$/'), '', $t);
            if (substr($t, 1, 1) == '\'' && in_array(substr($t, 0, 2), $not_tokens)) {
                $t = substr($t, 2);
            }
            if (strlen($t) == 1 || in_array($t, $not_tokens)) {
                $t = false;
                continue;
            }
        }
        return array_filter($tokens);
    }

    /**
     * Create the INNER JOIN query to restrict search to some job terms
     * @param $tokens an array of the job terms to look for (LIKE comp)
     * @param $table_alias the alias or name of the table with a jtid field to restrict
     * @param $table_field the name of the field to restrict in table_alias, usually jtid
     * @return a partial SQL query
     */
    static public function token_join_query(array $tokens, $table_alias, $table_field = 'jtid') {
        $joins = '';
        $i = 0;
        foreach ($tokens as $t) {
            ++$i;
             $joins .= ' INNER JOIN  profile_job_term_search AS s'.$i.' ON(s'.$i.'.jtid = '.$table_alias.'.'.$table_field.' AND s'.$i.'.search LIKE '.XDB::escape($t).')';
        }
        return $joins;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
