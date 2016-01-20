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

/******************************************************************************
 * Security functions
 *****************************************************************************/

function check_ip($level)
{
    if (empty($_SERVER['REMOTE_ADDR'])) {
        return false;
    }
    if (empty($_SESSION['check_ip'])) {
        $ips = array();
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        }
        $ips[] = $_SERVER['REMOTE_ADDR'];
        foreach ($ips as $key=>$ip) {
            $v = ip_to_uint($ip);
            if (is_null($v)) {
                unset($ips[$key]);
            } else {
                $ips[$key] = '(ip & mask) = (' . $v . '& mask)';
            }
        }
        $res = XDB::query('SELECT  state, description
                             FROM  ip_watch
                            WHERE  ' . implode(' OR ', $ips) . '
                         ORDER BY  state DESC');
        if ($res->numRows()) {
            $state = $res->fetchOneAssoc();
            $_SESSION['check_ip'] = $state['state'];
            $_SESSION['check_ip_desc'] = $state['description'];
        } else {
            $_SESSION['check_ip'] = 'safe';
        }
    }
    $test = array();
    switch ($level) {
      case 'unsafe': $test[] = 'unsafe';
      case 'dangerous': $test[] = 'dangerous';
      case 'ban': $test[] = 'ban'; break;
      default: return false;
    }
    return in_array($_SESSION['check_ip'], $test);
}

function check_email($email, $message)
{
    $res = XDB::fetchOneCell('SELECT  COUNT(*)
                                FROM  email_watch
                               WHERE  state != \'safe\' AND email = {?}',
                             $email);
    if ($res) {
        send_warning_mail($message);
        return true;
    }
    return false;
}

function check_account()
{
    if (S::user()) {
        return S::user()->watch;
    }
    return false;
}

function check_redirect($red = null)
{
    require_once 'emails.inc.php';
    if (is_null($red)) {
        $user = S::user();
        $red = new Redirect($user);
    }
    if ($red->get_uid() == S::v('uid')) {
        $_SESSION['no_redirect'] = !$red->other_active('');
        $_SESSION['mx_failures'] = $red->get_broken_mx();
    }
}

function send_warning_mail($title, $body = '')
{
    global $globals;
    $mailer = new PlMailer();
    $mailer->setFrom("webmaster@" . $globals->mail->domain);
    $mailer->addTo($globals->core->admin_email);
    $mailer->setSubject("[Plat/al Security Alert] $title");
    // Note: we can't do $session = var_export($_SESSION, true) as var_export
    // doesn't handle circular dependency correctly.
    ob_start();
    var_dump($_SESSION);
    $session = ob_get_clean();
    $mailer->setTxtBody($body . "Identifiants de session :\n" . $session . "\n\n"
        ."Identifiants de connexion :\n" . var_export($_SERVER, true));
    $mailer->send();
}

function kill_sessions()
{
    assert(S::admin());
    shell_exec('sudo -u root ' . dirname(dirname(__FILE__)) . '/bin/kill_sessions.sh');
}

/** Compares two strings in *constant* time.
 * Avoids timing attacks.
 */
function secure_string_compare($known, $user_provided)
{
    if (PHP_MAJOR_VERSION >= 5 && PHP_MINOR_VERSION >= 6) {
        // Defined as 'hash_equals' in PHP>=5.6
        // See http://php.net/manual/en/function.hash-equals.php
        return hash_equals($known, $user_provided);
    }

    if (strlen($known) != strlen($user_provided)) {
        return false;
    }
    $result = 0;
    for ($i = 0; $i < strlen($known); $i++) {
        // a ^ b == 0 <=> a == b
        $result |= ord($known[$i]) ^ ord($user_provided[$i]);
    }

    return ($result == 0);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
