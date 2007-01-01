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

require_once('smarty/libs/Smarty.class.php');

// {{{ class PlMail

/** Classe de mail avec corps en templates.
 */
class PlMail extends Smarty
{
    private $tpl;
    private $mailer = null;

    function __construct(&$mailer, $tpl)
    {
        global $globals;
        $this->tpl           = $tpl;
        $this->mailer       =& $mailer;
        $this->caching       = false;
        $this->compile_check = true;

        $this->template_dir  = $globals->spoolroot . "/templates/";
        $this->compile_dir   = $globals->spoolroot . "/spool/templates_c/";
        $this->config_dir    = $globals->spoolroot . "/configs/";

        $this->register_outputfilter(Array($this, 'mail_format'));
        $this->register_function('from',    Array($this, 'setFrom'));
        $this->register_function('to',      Array($this, 'addTo'));
        $this->register_function('cc',      Array($this, 'addCc'));
        $this->register_function('bcc',     Array($this, 'addBcc'));
        $this->register_function('subject', Array($this, 'setSubject'));
        $this->register_function('add_header', Array($this, 'addHeader'));
    }

    public function run($html)
    {
        $this->assign('html_version', $html);
        return $this->fetch($this->tpl);
    }

    /** used to remove the empty lines due to {from ...}, {to ...} ... functions */
    static public function mail_format($output, &$smarty)
    {
        return wordwrap("\n".trim($output)."\n",75);
    }

    static protected function format_addr(&$params)
    {
        if (isset($params['full'])) {
            return $params['full'];
        } elseif (empty($params['text'])) {
            return $params['addr'];
        } else {
            return $params['text'].' <'.$params['addr'].'>';
        }
    }

    /** template function : from.
     * {from full=...} for an already formatted address
     * {from addr=... [text=...]} else
     */
    public function setFrom($params, &$smarty)
    {
        $smarty->mailer->setFrom(PlMail::format_addr($params));
    }

    /** template function : to.
     * {to full=...} for an already formatted address
     * {to addr=... [text=...]} else
     */
    public function addTo($params, &$smarty)
    {
        $smarty->mailer->addTo(PlMail::format_addr($params));
    }

    /** template function : cc.
     * {cc full=...} for an already formatted address
     * {cc addr=... [text=...]} else
     */
    public function addCc($params, &$smarty)
    {
        $smarty->mailer->addCc(PlMail::format_addr($params));
    }

    /** template function : bcc.
     * {bcc full=...} for an already formatted address
     * {bcc addr=... [text=...]} else
     */
    public function addBcc($params, &$smarty)
    {
        $smarty->mailer->addBcc(PlMail::format_addr($params));
    }

    /** template function : subject.
     * {subject text=...} 
     */
    public function setSubject($params, &$smarty)
    {
        $smarty->mailer->setSubject($params['text']);
    }

    /** template function : add_header.
     * {add_header name=... value=...}
     */
    public function addHeader($params, &$smarty)
    {
        $smarty->mailer->addHeader($params['name'], $params['value']);
    }
}

require_once('Mail.php');
require_once('Mail/mime.php');

/** Class for sending inline or multipart-emails.
 * Based on Diogenes' HermesMailer
 */
class PlMailer extends Mail_Mime {

    private $mail;
    private $page    = null;
    private $charset;

    function __construct($tpl = null, $charset = "ISO-8859-15")
    {
        $this->charset = $charset;
        $this->Mail_Mime("\n");
        $this->mail = @Mail::factory('sendmail', Array('sendmail_args' => '-oi'));
        if (!is_null($tpl)) {
            $this->page = new PlMail($this, $tpl);
        }
    }

    /**
     * converts all : Foo Bar Baz <quux@foobar.org> into "Foo Bar Baz" <quux@foobar.org> which is RFC compliant
     */
    private function correct_emails($email)
    {
        return preg_replace('!(^|, *)([^<"]+?) *(<[^>]*>)!', '\1"\2" \3', $email);
    }

    public function addTo($email)
    {
        $email = $this->correct_emails($email);
        if (isset($this->_headers['To'])) {
            $this->_headers['To'] .= ", $email";
        } else {
            $this->_headers['To'] = $email;
        }
    }

    public function addCc($email)
    {
        return parent::addCc($this->correct_emails($email));
    }

    public function addBcc($email)
    {
        return parent::addBcc($this->correct_emails($email));
    }

    public function setFrom($email)
    {
        return parent::setFrom($this->correct_emails($email));
    }

    public function addHeader($hdr,$val)
    {
        switch($hdr) {
            case 'From':
                $this->setFrom($val);
                break;

            case 'To':
                unset($this->_headers[$hdr]);
                $this->addTo($val);
                break;

            case 'Cc':
                unset($this->_headers[$hdr]);
                $this->addCc($val);
                break;

            case 'Bcc':
                unset($this->_headers[$hdr]);
                $this->addBcc($val);
                break;

            default:
                $this->headers(Array($hdr=>$val));
        }
    }

    public function assign($var, $value)
    {
        if (!is_null($this->page)) {
            $this->page->assign($var, $value);
        }
    }
    
    public function assign_by_ref($var, &$value)
    {
        if (!is_null($this->page)) {
            $this->page->assign_by_ref($var, $value);
        }
    }

    public function register_modifier($var, $callback)
    {
        if (!is_null($this->page)) {
            $this->page->register_modifier($var, $callback);
        }
    }
    
    public function register_function($var, $callback)
    {
        if (!is_null($this->page)) {
            $this->page->register_function($var, $callback);
        }
    }
    
    private function processPage($with_html = true)
    {
        if (!is_null($this->page)) {
            $this->setTxtBody($this->page->run(false));
            if ($with_html) {
                $html = trim($this->page->run(true));
                if (!empty($html)) {
                    $this->setHtmlBody($html);
                }
            }
        }
    }

    public function send($with_html = true)
    {
        $this->processPage($with_html);
        if (S::v('forlife')) {
            $this->addHeader('X-Org-Mail', S::v('forlife') . '@polytechnique.org');
        }
        $addrs = Array();
        foreach(Array('To', 'Cc', 'Bcc') as $hdr) {
            if(isset($this->_headers[$hdr])) {
                require_once 'Mail/RFC822.php';
                $addrs = array_merge($addrs, @Mail_RFC822::parseAddressList($this->_headers[$hdr]));
            }
        }
        if(empty($addrs)) {
            return false;
        }
    
        $dests = Array();
        foreach($addrs as $a) {
            $dests[] = "{$a->mailbox}@{$a->host}";
        }
    
        // very important to do it in THIS order very precisely.
        $body = $this->get(array('text_charset' => $this->charset,
                                 'html_charset' => $this->charset,
                                 'head_charset' => $this->charset));
        $hdrs = $this->headers();
        if (empty($hdrs['From'])) {
            trigger_error('Empty "From", mail not sent', E_USER_WARNING);
            return false;
        }
        return $this->mail->send($dests, $hdrs, $body);
    }
}

?>
