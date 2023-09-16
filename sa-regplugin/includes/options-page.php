<?php 

use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('after_setup_theme', 'load_carbon_fields');
add_action('carbon_fields_register_fields', 'create_options_page');

function load_carbon_fields() {
    \Carbon_Fields\Carbon_Fields::boot();
    error_log('LOADED CARBON FIELDS'.PHP_EOL, 3, DEBUG_LOG );
}

function create_options_page() {
    error_log('CREATING OPTIONS PAGE'.PHP_EOL, 3, DEBUG_LOG );
    Container::make( 'theme_options', ' Theme Options') 
    ->add_fields( array(
        Field::make( 'text', 'regplugin_email', 'Email')->set_attribute('placeholder', 'ex: your@email.com')
        ->set_help_text('Email used to send to receipients'),

        Field::make( 'textarea', 'regplugin_confmsg', 'Confirmation Message')->set_attribute('placeholder', 'Enter confirmation message')
        ->set_help_text('Email message sent during confirmation')
    ) );

}