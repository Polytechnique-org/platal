<?php
require("auto.prepend.inc.php");
require("validations.inc.php");
new_admin_page('admin/valider.tpl',true);

if(isset($_REQUEST["uid"]) and isset($_REQUEST["type"])
        and isset($_REQUEST["stamp"])) {
    $req = Validate::get_request($_REQUEST["uid"],$_REQUEST['type'],$_REQUEST["stamp"]);
    if($req)
        $page->assign('mail', $req->handle_formu());
}

$it = new ValidateIterator ();

$valids = Array();
while($valids[] = $it->next());
array_pop($valids);

$page->assign_by_ref('valids', $valids);

$page->run();
?>
