<?php

/**
 * @package  BiddingOpportunityPlugin
 */

namespace App\Base;

defined('ABSPATH') or die('Hey, you should not be here!');

if (!class_exists('BiddoDeactivate')) {

	class BiddoDeactivate
	{
		public static function deactivate()
		{
			flush_rewrite_rules();
		}
	}
	
}
