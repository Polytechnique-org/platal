<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     block.min_auth.php
 * Type:     block
 * Name:     min_auth
 * Purpose:  
 * -------------------------------------------------------------
 */
function smarty_block_min_auth($params, $content, &$smarty)
{
    if( empty($content) || empty($params['level'] ))
        return;
    if( ($params['level'] == 'public') ||
        ($params['level'] == 'cookie' && logged()) ||
        ($params['level'] == 'auth' && identified()) )
        return $content;
}
?>
