<?php

/**
 * @package  BiddingOpportunityPlugin
 */

namespace BIDDOP\Base;

defined('ABSPATH') or die('Hey, you should not be here!');

if (!class_exists('BiddopDeactivate')) {

	class BiddopDeactivate
	{
		public static function deactivate()
		{
			flush_rewrite_rules();
		}
	}
	
}
