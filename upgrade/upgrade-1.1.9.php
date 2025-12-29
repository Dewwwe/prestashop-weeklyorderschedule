<?php
/**
 * File: /upgrade/upgrade-1.0.3.php
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
   
function upgrade_module_1_1_9($object) {
    // Process Module upgrade to 1.1.9
    return ($object->registerHook('displayCountdown'));
}