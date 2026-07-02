<?php

/**
 * @package  BiddingOpportunityPlugin
 */

namespace BIDDOP\Base;

defined('ABSPATH') or die('Hey, you should not be here!');

if (!class_exists('BiddoActivate')) {

	class BiddoActivate
	{
		public static function activate()
		{
			flush_rewrite_rules();						
		}
	}
	
}
