<?php 
add_shortcode( 'regform', 'create_reg_form' );
add_action('rest_api_init', 'create_rest_endpoint' );

function create_reg_form() {
    include MY_PLUGIN_PATH . 'includes/forms/regdetails.php';
}

function create_rest_endpoint() {
    register_rest_route( 'v1/regformapi', 'submit', array(
        'methods' => 'POST',
        'callback' => 'handle_form_submit'
));
}

function handle_form_submit() {
    echo 'HANDLED FOR SUBMISSION';   
}