<?php
/********************************************************************************
 * include/session.inc.php : sessions for profile
 * -------------------------
 *
 * This file is part of the banana distribution
 * Copyright: See COPYING files that comes with this distribution
 ********************************************************************************/

switch (basename($_SERVER['SCRIPT_NAME'])) {
    case 'thread.php':
        if (!Session::has('bananapostok')) {
            $_SESSION['bananapostok'] = true;
        }
        break;

    case 'index.php':
        if (Get::get('banana') == 'updateall') {
            $globals->xdb->execute('UPDATE auth_user_quick SET banana_last={?} WHERE user_id={?}', gmdate('YmdHis'), Session::getInt('uid'));
            $_SESSION['banana_last'] = time();
        }

    default:
        $_SESSION['bananapostok'] = true;
}

?>
