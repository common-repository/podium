<?php

// if uninstall.php is not called by WordPress, die
if ( ! defined('WP_UNINSTALL_PLUGIN') ) {
    die;
}

$podium_token = 'podium-script-code';
$podium_installation = 'podium-installation';
$podium_feature_flag = 'podium-feature-flag';

delete_option($podium_token);
delete_option($podium_installation);
delete_option($podium_feature_flag);