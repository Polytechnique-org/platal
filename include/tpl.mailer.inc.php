<?php
require_once("diogenes.mailer.inc.php");
require_once("Smarty.class.php");

/** Classe de mail avec corps en templates.
 */
class TplMailer extends Smarty {
    var $mail_dir = "mails";
    var $_tpl;

    var $_from;
    var $_to  = Array();
    var $_cc  = Array();
    var $_bcc = Array();
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
        $body = $this->fetch($this->mail_dir."/".$this->_tpl);
        $mailer = new DiogenesMailer($this->_from, implode(',',$this->_to),
                                     $this->_subject, false,
                                     implode(',',$this->_cc), implode(',',$this->_bcc));
        $mailer->setBody($body);
        $mailer->send();
    }
}

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

function set_from($params, &$smarty) { $smarty->_from  = format_addr($params); }
function set_to($params, &$smarty)   { $smarty->_to[]  = format_addr($params); }
function set_cc($params, &$smarty)   { $smarty->_cc[]  = format_addr($params); }
function set_bcc($params, &$smarty)  { $smarty->_bcc[] = format_addr($params); }
function set_subject($params, &$smarty) {
    $smarty->_subject = $params['text'];
}
?>
