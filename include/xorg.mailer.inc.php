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
 ***************************************************************************/

require_once('diogenes/diogenes.hermes.inc.php');
require_once('Smarty.class.php');

// {{{ class XOrgMailer

/** Classe de mail avec corps en templates.
 */
class XOrgMailer extends Smarty
{
    // {{{ properties
    
    /** Directory used to store mails_templates.
     * Smarty::template_dir subdir used to sotre the mails templates.
     * The body of the message is taken from a tsmarty template
     */
    var $mail_dir = "mails";
    /** stores the mail template name */
    var $_tpl;

    /** stores the mail From: header */
    var $_from;
    /** stores the recipients of the mail */
    var $_to  = Array();
    /** stores the Cc recipients of the mail */
    var $_cc  = Array();
    /** stores the Bcc recipients of the mail */
    var $_bcc = Array();
    /** stores the subject of the mail */
    var $_subject;

    // }}}
    // {{{ constructor
    
    function XorgMailer($tpl)
    {
        global $globals;
        $this->_tpl = $tpl;
        $this->caching=false;
        $this->compile_check=true;

        $this->template_dir = $globals->root . "/templates/";
        $this->compile_dir  = $globals->root . "/templates_c/";
        $this->config_dir   = $globals->root . "/configs/";

        $this->register_outputfilter('mail_format');
        $this->register_function('from', 'set_from');
        $this->register_function('to', 'set_to');
        $this->register_function('cc', 'set_cc');
        $this->register_function('bcc', 'set_bcc');
        $this->register_function('subject', 'set_subject');
    }

    // }}}
    // {{{ function send()

    function send()
    {
        // do not try to optimize, in the templates, some function can modify our object, then we
        // have to fetch in the first time, and only then send the mail.
        $body = $this->fetch($this->mail_dir."/".$this->_tpl);
        $mailer = new HermesMailer();
	$mailer->setFrom($this->_from);
	$mailer->addTo(implode(',',$this->_to));
	$mailer->setSubject($this->_subject);
	if (!empty($this->_cc)) {
            $mailer->addCc(implode(',',$this->_cc));
        }
	if (!empty($this->_bcc)) {
            $mailer->addBcc(implode(',',$this->_bcc));
        }
        $mailer->setTxtBody($body);
        $mailer->send();
    }

    // }}}
}

// }}}
// {{{ function mail_format()

/** used to remove the empty lines due to {from ...}, {to ...} ... functions */
function mail_format($output, &$smarty)
{
    return wordwrap("\n".trim($output)."\n",75);
}

// }}}
// {{{ function format_addr()

function format_addr(&$params)
{
    if (isset($params['full'])) {
        return $params['full'];
    } elseif (empty($params['text'])) {
        return $params['addr'];
    } else {
        return $params['text'].' <'.$params['addr'].'>';
    }
}

// }}}
// {{{ function set_from()

/** template function : from.
 * {from full=...} for an already formatted address
 * {from addr=... [text=...]} else
 */
function set_from($params, &$smarty)
{ $smarty->_from = format_addr($params); }

// }}}
// {{{ function set_to()

/** template function : to.
 * {to full=...} for an already formatted address
 * {to addr=... [text=...]} else
 */
function set_to($params, &$smarty)
{ $smarty->_to[] = format_addr($params); }

// }}}
// {{{ function set_cc()

/** template function : cc.
 * {cc full=...} for an already formatted address
 * {cc addr=... [text=...]} else
 */
function set_cc($params, &$smarty)
{ $smarty->_cc[] = format_addr($params); }

// }}}
// {{{ function set_bcc()

/** template function : bcc.
 * {bcc full=...} for an already formatted address
 * {bcc addr=... [text=...]} else
 */
function set_bcc($params, &$smarty)
{ $smarty->_bcc[] = format_addr($params); }

// }}}
// {{{ function set_subject()

/** template function : subject.
 * {subject text=...} 
 */
function set_subject($params, &$smarty)
{
    $smarty->_subject = $params['text'];
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
