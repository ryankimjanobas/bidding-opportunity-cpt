<?php

/**
 * @package  BiddingOpportunityPlugin
 */

/*
  Plugin Name: Bidding Opportunity Custom Post Type
  Description: This is a simple plugin for purpose of having a custom post type named bidding opportunity
  Version: 1.0.0
  Author: Ryan Kim Janobas
  License: GPLv2 or later  
*/

defined('ABSPATH') or die('Hey, you should not be here!');

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

/**
 * The code that runs during plugin activation
 */
function activate_bidding_opportunity_plugin() {
	App\Base\Activate::activate();
}
register_activation_hook( __FILE__, 'activate_bidding_opportunity_plugin' );

/**
 * The code that runs during plugin deactivation
 */
function deactivate_bidding_opportunity_plugin() {
	App\Base\Deactivate::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_bidding_opportunity_plugin' );

/**
 * The code that initialize classes neede on the plugin
 */
if ( class_exists( 'App\\Init' ) ) {
  App\Init::registerServices();
}