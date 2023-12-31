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
        define ('MY_PLUGIN_URL', plugin_dir_url( __FILE__ ));
        define ('DEBUG_LOG', plugin_dir_path(__FILE__).'debug.log' );

        require_once( MY_PLUGIN_PATH.'vendor/autoload.php');
        
        error_log('DEBUG LOG: '.MY_PLUGIN_PATH.PHP_EOL, 3, DEBUG_LOG );
    }

    public function initialize() {
        //include_once MY_PLUGIN_PATH.'/includes/utilities.php';
        //include_once MY_PLUGIN_PATH.'/includes/options-page.php';
        include_once MY_PLUGIN_PATH.'/forms/forminit.php';
        //include_once MY_PLUGIN_PATH.'/includes/pluginreg.php';
    }

 }
 #create new instance
 $regEnroll = new RegEnroll;
 $regEnroll->initialize();

}

