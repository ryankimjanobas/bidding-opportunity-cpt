<?php

/**
 * @package  BiddingOpportunityPlugin
 */

namespace App\Base;

defined('ABSPATH') or die('Hey, you should not be here!');

if (!class_exists('Deactivate')) {

	class Deactivate
	{
		public static function deactivate()
		{
			flush_rewrite_rules();
		}
	}
	
}
