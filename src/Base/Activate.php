<?php

/**
 * @package  BiddingOpportunityPlugin
 */

namespace App\Base;

defined('ABSPATH') or die('Hey, you should not be here!');

if (!class_exists('Activate')) {

	class Activate
	{
		public static function activate()
		{
			flush_rewrite_rules();						
		}
	}
	
}
