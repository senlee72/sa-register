<?php 
add_shortcode( 'regform', 'create_reg_form' );
add_action('rest_api_init', 'create_rest_endpoint' );
add_action('wp_enqueue_scripts', 'registration_scripts');

function create_reg_form() {
    //include MY_PLUGIN_PATH . 'includes/forms/regdetails.php';
    include MY_PLUGIN_PATH . '/forms/regenroll.php';
}

function create_rest_endpoint() {
    register_rest_route( 'v1/regformapi', 'submit', array(
        'methods' => 'POST',
        'callback' => 'handle_form_submit'
));
}

function enqueue_custom_scripts() {
    wp_enqueue_style( 'regform-plugin', MY_PLUGIN_URL.'/assets/css/bootstrap.css');
}

function handle_form_submit($data) {
    $params = $data->get_params();
    error_log('PARAMS '. $params, 3, DEBUG_LOG);

    if (!wp_verify_nonce($params['_wpnonce'], 'wp_rest')) {
        return new WP_REST_Response('Message not handled', 422);
    }
    //wp_mail( 'admin@gmail.com', 'TEST SUBJECT', 'TEST MESSGAGE', "From: {$params['name']}" );
    echo 'HANDLED FOR SUBMISSION';
}

//Function added to load bootstrap
function registration_scripts() {
	//error_log('<<<<<<<<<<<<<CALLING SCRIPT TO REGISTER: START >>>>>>>>>>>');
	wp_register_style('boot431css', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css');
	wp_enqueue_script('boot431script','https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js', array( 'jquery' ),'',true );

	wp_register_style('fontawesome470css', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
	wp_register_style('openiconic111css', 'https://cdnjs.cloudflare.com/ajax/libs/open-iconic/1.1.1/font/css/open-iconic-bootstrap.min.css');

	wp_enqueue_style('boot431css');
	wp_enqueue_style('fontawesome470css');
	wp_enqueue_style('openiconic111css');
	wp_enqueue_script('boot431script');
	//error_log('<<<<<<<<<<<<<CALLING SCRIPT TO REGISTER: END >>>>>>>>>>>');
 }