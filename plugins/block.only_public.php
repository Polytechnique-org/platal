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
function smarty_block_only_public($params, $content, &$smarty)
{
    if( empty($content) || logged() )
        return;
    return $content;
}
?>
