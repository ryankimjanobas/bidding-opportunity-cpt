<?php

/**
 * @package  BiddingOpportunityPlugin
 */

namespace BIDDOP;

defined('ABSPATH') or die('Hey, you should not be here!');

if (!class_exists('BiddopInit')) {

	final class BiddopInit
	{
		/**
		 * Store all the classes inside an array
		 * @return array Full list of classes
		 */
		public static function services()
		{
			return [
				Admin\BiddopAdminPanel::class,
				Frontend\BiddopFrontendCommonMethods::class,
				Frontend\BiddopPublicBidding::class,
				Frontend\BiddopAlternativeMethod::class			
			];
		}

		/**
		 * Loop through the classes, initialize them, 
		 * and call the register() method if it exists
		 * @return
		 */
		public static function biddopRegisterServices()
		{
			foreach (self::services() as $class) {
				$service = self::instantiate($class);
				if (method_exists($service, 'biddopRegister')) {
					$service->biddopRegister();
				}
			}
		}

		/**
		 * Initialize the class
		 * @param  class $class    class from the services array
		 * @return class instance  new instance of the class
		 */
		private static function instantiate($class)
		{
			$service = new $class();

			return $service;
		}
	}
}
