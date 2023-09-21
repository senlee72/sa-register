<?php 

// Act on plugin activation
register_activation_hook( __FILE__, "activate_myplugin" );

// Act on plugin de-activation
register_deactivation_hook( __FILE__, "deactivate_myplugin" );

// Activate Plugin
function activate_myplugin() {

	// Execute tasks on Plugin activation

	// Insert DB Tables
	//init_db_myplugin();
}

// De-activate Plugin
function deactivate_myplugin() {

	// Execute tasks on Plugin de-activation
}

// Initialize DB Tables
function init_db_myplugin() {

	// WP Globals
	global $table_prefix, $wpdb;

	// Customer Table
	$customerTable = $table_prefix . 'customer';

	// Create Customer Table if not exist
	if( $wpdb->get_var( "show tables like '$customerTable'" ) != $customerTable ) {

		// Query - Create Table
		$sql = "CREATE TABLE `$customerTable` (";
		$sql .= " `id` int(11) NOT NULL auto_increment, ";
		$sql .= " `email` varchar(500) NOT NULL, ";
		$sql .= " `fname` varchar(500) NOT NULL, ";
		$sql .= " `sname` varchar(500), ";
		$sql .= " `line1` varchar(500) NOT NULL, ";
		$sql .= " `line2` varchar(500), ";
		$sql .= " `line3` varchar(500), ";
		$sql .= " `city` varchar(150) NOT NULL, ";
		$sql .= " `state` varchar(150), ";
		$sql .= " `area` varchar(15), ";
		$sql .= " `country` varchar(5) NOT NULL, ";
		$sql .= " PRIMARY KEY `customer_id` (`id`) ";
		$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

		// Include Upgrade Script
		//require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	
		// Create Table
		dbDelta( $sql );
	}

}

?>