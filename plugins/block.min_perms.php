<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     block.min_perms.php
 * Type:     block
 * Name:     min_perms
 * Purpose:  
 * -------------------------------------------------------------
 */
function smarty_block_min_perms($params, $content, &$smarty)
{
    if( empty($content) || empty($params['level'] ))
        return;
    if( ($params['level'] == 'public') ||
        ($params['level'] == 'marketing' && has_perms($marketing_admin)) ||
        ($params['level'] == 'admin' && has_perms()) )
        return $content;
}
?>
