<?php

/**
 * @package  BiddingOpportunityPlugin
 */

namespace App\Base;

defined('ABSPATH') or die('Hey, you should not be here!');

if (!class_exists('BaseController')) {

	class BaseController
	{
		public $plugin_path;

		public $plugin_url;

		public $plugin;

		public $cpt_name;
		
		public $taxonomy_status;

		public $variable_prefix;

		public $modes_of_procurement;
		
		public $required_status;

		public function __construct()
		{
			$this->plugin_path = plugin_dir_path(dirname(__FILE__, 2));
			$this->plugin_url = plugin_dir_url(dirname(__FILE__, 2));
			$this->plugin = plugin_basename(dirname(__FILE__, 3)) . '/bidding-opportunity-cpt.php';
			$this->cpt_name = 'bid_opportunity';			
			$this->taxonomy_status = 'bid_opportunity_status';
			$this->variable_prefix = 'bid_opportunity_cpt_';

			$this->modes_of_procurement = array(
				'public' => 'Public Bidding',
				'alternative' => 'Alternative Method of Procurement'
			);

			$this->required_status = array(
        array(
          'term'      => 'Close',
          'description' => 'Bidding close',
          'slug'        => 'close'
        ),
        array(
          'term'      => 'Awarded',
          'description' => 'Bidding awarded',
          'slug'        => 'awarded'
        )
      );


		}
	}
}
