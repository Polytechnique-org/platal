<?php
require_once('diogenes.table-editor.inc.php');

class XOrgAdminTableEditor extends DiogenesTableEditor {
    function XOrgTableEditor($table,$idfield) {
        $this->DiogenesTableEditor($table,$idfield);
    }

    function assign($var_name, $contenu) {
        global $page;
        $page->assign($var_name, $contenu);
    }
    
    function run() {
        global $page;
        parent::run($page);
        $page->run();
    }
}

?>
