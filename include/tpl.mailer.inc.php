<?php
require_once("diogenes.mailer.inc.php");
require_once("Smarty.class.php");

/** Classe de mail avec corps en templates.
 */
class TplMailer extends Smarty {
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
    
    function TplMailer($tpl) {
        global $globals;
        $this->_tpl = $tpl;
        $this->caching=false;
        $this->compile_check=true;

        $this->template_dir = $globals->spoolroot . "/templates/";
        $this->compile_dir  = $globals->spoolroot . "/templates_c/";
        $this->config_dir   = $globals->spoolroot . "/configs/";

        $this->register_outputfilter('mail_format');
        $this->register_function('from', 'set_from');
        $this->register_function('to', 'set_to');
        $this->register_function('cc', 'set_cc');
        $this->register_function('bcc', 'set_bcc');
        $this->register_function('subject', 'set_subject');
    }

    function send() {
        // do not try to optimize, in the templates, some function can modify our object, then we
        // have to fetch in the first time, and only then send the mail.
        $body = $this->fetch($this->mail_dir."/".$this->_tpl);
        $mailer = new DiogenesMailer($this->_from, implode(',',$this->_to),
                                     $this->_subject, false,
                                     implode(',',$this->_cc), implode(',',$this->_bcc));
        $mailer->setBody($body);
        $mailer->send();
    }
}

/** used to remove the empty lines due to {from ...}, {to ...} ... functions */
function mail_format($output, &$smarty) {
    return wordwrap("\n".trim($output)."\n",75);
}

function format_addr(&$params) {
    if(isset($params['full']))
        return $params['full'];
    if(empty($params['text']))
        return $params['addr'];
    else
        return $params['text'].' <'.$params['addr'].'>';
}

/** template function : from.
 * {from full=...} for an already formatted address
 * {from addr=... [text=...]} else
 */
function set_from($params, &$smarty) { $smarty->_from  = format_addr($params); }
/** template function : to.
 * {to full=...} for an already formatted address
 * {to addr=... [text=...]} else
 */
function set_to($params, &$smarty)   { $smarty->_to[]  = format_addr($params); }
/** template function : cc.
 * {cc full=...} for an already formatted address
 * {cc addr=... [text=...]} else
 */
function set_cc($params, &$smarty)   { $smarty->_cc[]  = format_addr($params); }
/** template function : bcc.
 * {bcc full=...} for an already formatted address
 * {bcc addr=... [text=...]} else
 */
function set_bcc($params, &$smarty)  { $smarty->_bcc[] = format_addr($params); }
/** template function : subject.
 * {subject text=...} 
 */
function set_subject($params, &$smarty) {
    $smarty->_subject = $params['text'];
}
?>
