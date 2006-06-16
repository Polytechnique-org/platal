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

require_once('diogenes/diogenes.core.session.inc.php');
require_once('diogenes/diogenes.misc.inc.php');

// {{{ function check_perms()

/** verifie si un utilisateur a les droits pour voir une page
 ** si ce n'est pas le cas, on affiche une erreur
 * @return void
 */
function check_perms()
{
    global $page;
    if (!has_perms()) {
        if ($_SESSION['log']) {
            require_once('diogenes/diogenes.core.logger.inc.php');
            $_SESSION['log']->log("noperms",$_SERVER['PHP_SELF']);
        }
	$page->kill("Tu n'as pas les permissions nécessaires pour accéder à cette page.");
    }
}

// }}}
// {{{ function has_perms()

/** verifie si un utilisateur a les droits pour voir une page
 ** soit parce qu'il est admin, soit il est dans une liste
 ** supplementaire de personnes utilisées
 * @return BOOL
 */
    
function has_perms()
{
    return logged() && Session::get('perms')==PERMS_ADMIN;
}

// }}}
// {{{ function logged()

/** renvoie true si la session existe et qu'on est loggué correctement
 * false sinon
 * @return bool vrai si loggué
 * @see header2.inc.php
 */
function logged ()
{
    return Session::get('auth', AUTH_PUBLIC) >= AUTH_COOKIE;
}

// }}}
// {{{ function identified()

/** renvoie true si la session existe et qu'on est loggué correctement
 * et qu'on a été identifié par un mot de passe depuis le début de la session
 * false sinon
 * @return bool vrai si loggué
 * @see header2.inc.php
 */
function identified ()
{
    return Session::get('auth', AUTH_PUBLIC) >= AUTH_MDP;
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
