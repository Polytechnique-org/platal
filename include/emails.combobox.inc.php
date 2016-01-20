<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

function fill_email_combobox(PlPage $page, array $retrieve, $user = null)
{
    require_once 'emails.inc.php';
    if (is_null($user)) {
        $user = S::user();
    }
    /* Always refetch the profile. */
    $profile = $user->profile(true);

    $emails = array();
    if (in_array('source', $retrieve)) {
        $emails['Emails polytechniciens'] = XDB::fetchColumn('SELECT  CONCAT(s.email, \'@\', d.name)
                                                                FROM  email_source_account  AS s
                                                          INNER JOIN  email_virtual_domains AS m ON (s.domain = m.id)
                                                          INNER JOIN  email_virtual_domains AS d ON (d.aliasing = m.id)
                                                               WHERE  s.uid = {?}
                                                            ORDER BY  s.email, d.name',
                                                             $user->id());
    }

    if (in_array('redirect', $retrieve)) {
        $redirect = new Redirect($user);
        $emails['Redirections'] = array();
        foreach ($redirect->emails as $redirect_it) {
            if ($redirect_it->is_redirection()) {
                $emails['Redirections'][] = $redirect_it->email;
            }
        }
    }

    if ($profile) {
        if (in_array('job', $retrieve)) {
            $emails['Emails professionels'] = XDB::fetchColumn('SELECT  email
                                                                  FROM  profile_job
                                                                 WHERE  pid = {?} AND email IS NOT NULL AND email != \'\'',
                                                               $profile->id());
        }

        if ($profile->email_directory) {
            if (in_array('directory', $retrieve)) {
                foreach ($emails as &$email_list) {
                    foreach ($email_list as $key => $email) {
                        if ($profile->email_directory == $email) {
                            unset($email_list[$key]);
                        }
                    }
                }
                $emails['Email annuaire AX'] = array($profile->email_directory);
            } elseif (in_array('stripped_directory', $retrieve)) {
                if (User::isForeignEmailAddress($profile->email_directory)) {
                    $is_redirect = XDB::fetchOneCell('SELECT  COUNT(*)
                                                        FROM  email_redirect_account
                                                       WHERE  uid = {?} AND redirect = {?}',
                                                     $user->id(), $profile->email_directory);
                    if ($is_redirect == 0) {
                        $emails['Email annuaire AX'] = array($profile->email_directory);
                    }
                }
            }
        }

        if (isset($emails['Emails professionels']) && isset($emails['Redirections'])) {
            $intersect = array_intersect($emails['Emails professionels'], $emails['Redirections']);
            foreach ($intersect as $key => $email) {
                unset($emails['Emails professionels'][$key]);
            }
        }
    }

    $emails_count = 0;
    foreach ($emails as $email_list) {
        $emails_count += count($email_list);
    }
    $page->assign('emails_count', $emails_count);
    $page->assign('email_lists', $emails);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
