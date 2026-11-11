<?php

/**
 * @package  BiddingOpportunityPlugin
 */


/*
  Plugin Name: Bidding Opportunity Custom Post Type
  Description: This is a simple plugin for purpose of having a custom post type named bidding opportunity
  Plugin URI: https://github.com/ryankimjanobas/bidding-opportunity-cpt
  Version: 1.0.1
  Author: Ryan Kim Janobas
  License: GPLv2 or later
  Requires at least: 5.9
  Requires PHP: 7.4
*/

defined('ABSPATH') or die('Hey, you should not be here!');

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

/**
 * The code that runs during plugin activation
 */
function activate_bidding_opportunity_plugin() {	
  BIDDOP\Base\BiddopBaseController::biddopActivate();
}
register_activation_hook( __FILE__, 'activate_bidding_opportunity_plugin' );

/**
 * The code that runs during plugin deactivation
 */
function deactivate_bidding_opportunity_plugin() {
	BIDDOP\Base\BiddopBaseController::biddopDeactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_bidding_opportunity_plugin' );

/**
 * The code that initialize classes needed for the plugin to work
 */
if ( class_exists( 'BIDDOP\\BiddopInit' ) ) {
  BIDDOP\BiddopInit::biddopRegisterServices();
}
