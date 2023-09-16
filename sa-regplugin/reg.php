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
        require_once( plugin_dir_path(__FILE__).'/vendor/autoload.php');
    }

 }
 new RegEnroll;
}
?>