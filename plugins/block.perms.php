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
function smarty_block_perms($params, $content, &$smarty)
{
    if( empty($content) || empty($params['level'] ))
        return;
    if( ($params['level'] == 'public') ||
        ($params['level'] == 'admin' && has_perms()) )
        return $content;
}
?>
