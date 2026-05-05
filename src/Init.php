<?php

/**
 * @package  BiddingOpportunityPlugin
 */

namespace App;

defined('ABSPATH') or die('Hey, you should not be here!');

if (!class_exists('Init')) {

	final class Init
	{
		/**
		 * Store all the classes inside an array
		 * @return array Full list of classes
		 */
		public static function services()
		{
			return [
				Admin\AdminPanel::class,
				Base\Enqueue::class,
				Frontend\AjaxScript::class,
				Frontend\PublicBidding::class,
				Frontend\AlternativeMethod::class			
			];
		}

		/**
		 * Loop through the classes, initialize them, 
		 * and call the register() method if it exists
		 * @return
		 */
		public static function registerServices()
		{
			foreach (self::services() as $class) {
				$service = self::instantiate($class);
				if (method_exists($service, 'register')) {
					$service->register();
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
