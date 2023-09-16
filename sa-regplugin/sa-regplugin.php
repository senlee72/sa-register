<?php
/**
 * Plugin Name: SA Reg Plugin
 * Description: Register user & family members
 * Version: 1.0.0
 * Text Domain: options-plugin
 */

 if (!defined('ABSPATH')) {
    die('You cannot be here ');    
 }

 if (!class_exists('RegEnroll')){
 class RegEnroll {
    public function __construct() {
        define ('MY_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
        require_once( MY_PLUGIN_PATH.'/vendor/autoload.php');
        $pluginlog = plugin_dir_path(__FILE__).'debug.log';
        $message = 'CATCHING ERROR'.PHP_EOL;
        error_log($message, 3, $pluginlog);
    }

    public function initialize() {
        include_once MY_PLUGIN_PATH.'/includes/utilities.php';
        include_once MY_PLUGIN_PATH.'/includes/options-page.php';
    }

 }
 #create new instance
 new RegEnroll;
}
?>