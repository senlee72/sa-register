<?php 
add_shortcode( 'regform', 'create_reg_form' );

function create_reg_form() {
    include MY_PLUGIN_PATH . 'includes/firns.regdetails.php';
}

function create_rest_endpoint() {
    
}