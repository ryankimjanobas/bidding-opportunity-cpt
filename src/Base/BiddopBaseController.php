<?php

/**
 * @package  BiddingOpportunityPlugin
 */

namespace BIDDOP\Base;

defined('ABSPATH') or die('Hey, you should not be here!');

if (!class_exists('BiddopBaseController')) {

	class BiddopBaseController
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
        ),
				array(
          'term'      => 'Failed',
          'description' => 'Bidding Failure',
          'slug'        => 'failed'
        ),
      );

		}

		/**
		 * Returns the status term that should be displayed for a bid.
		 *
		 * A bid can end up with more than one status term (for example when the
		 * automatic "Close" status is appended to a bid that already has "Active").
		 * This picks the most relevant one instead of simply using the first term.
		 *
		 * @param  int $post_id Post ID.
		 * @return \WP_Term|null
		 */
		public function biddopGetPrimaryStatus($post_id)
		{
			$terms = get_the_terms($post_id, $this->taxonomy_status);

			if (empty($terms) || is_wp_error($terms)) {
				return null;
			}

			$priority = array('awarded', 'failed', 'close', 'active');

			foreach ($priority as $slug) {
				foreach ($terms as $term) {
					if ($term->slug === $slug) {
						return $term;
					}
				}
			}

			return $terms[0];
		}

		/**
		 * Formats a stored date (Y-m-d) for display, returning an empty string
		 * when no (valid) date is stored.
		 *
		 * @param  string $value Date value.
		 * @return string
		 */
		protected function biddopFormatDate($value)
		{
			if (empty($value)) {
				return '';
			}

			$timestamp = strtotime($value);

			return $timestamp ? date('F j, Y', $timestamp) : '';
		}

		/**
		 * Checks whether the shortcodes that need the datatable assets are present.
		 * Non singular pages (archives, widgets, etc.) always load the assets.
		 *
		 * @return bool
		 */
		public function biddopPageHasShortcode()
		{
			if (!is_singular()) {
				return true;
			}

			$post = get_post();

			if (!$post) {
				return true;
			}

			$shortcodes = array(
				'bidding_opportunity_public_bidding_datatable',
				'bidding_opportunity_public_bidding_awarded_datatable',
				'bidding_opportunity_alternative_method_datatable',
				'bidding_opportunity_alternative_method_awarded_datatable'
			);

			foreach ($shortcodes as $shortcode) {
				if (has_shortcode($post->post_content, $shortcode)) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Registers and enqueues the frontend datatable assets.
		 * Safe to call multiple times (e.g. from shortcodes rendered outside
		 * the main post content).
		 *
		 * @return void
		 */
		public function biddopEnqueueFrontendAssets()
		{
			$datatables_handle = $this->cpt_name . 'datatables-scripts';
			$scripts_handle    = $this->cpt_name . '_scripts';
			$styles_handle     = $this->cpt_name . '_styles';

			if (!wp_script_is($datatables_handle, 'registered')) {
				wp_register_script($datatables_handle, $this->plugin_url . 'assets/DataTables/datatables.min.js', array('jquery'), '2.3.8', true);
				wp_localize_script($datatables_handle, 'biddingopportunitydatatablesajax', array('url' => admin_url('admin-ajax.php')));
			}

			if (!wp_style_is($datatables_handle, 'registered')) {
				wp_register_style($datatables_handle, $this->plugin_url . 'assets/DataTables/datatables.min.css');
			}

			if (!wp_script_is($scripts_handle, 'registered')) {
				wp_register_script($scripts_handle, $this->plugin_url . 'assets/bid-opportunity-cpt-scripts.js', array('jquery', $datatables_handle), '1.0.1', true);
			}

			if (!wp_style_is($styles_handle, 'registered')) {
				wp_register_style($styles_handle, $this->plugin_url . 'assets/bid-opportunity-cpt-styles.css', array(), '1.0.1');
			}

			wp_enqueue_script($datatables_handle);
			wp_enqueue_style($datatables_handle);
			wp_enqueue_script($scripts_handle);
			wp_enqueue_style($styles_handle);
		}

		public static function biddopActivate()
		{
			/*
			 * Defer the rewrite flush until the next init, after the custom post
			 * type and taxonomy have been registered.
			 */
			update_option('biddop_flush_rewrite_rules', 1);
		}

		public static function biddopDeactivate()
		{
			delete_option('biddop_flush_rewrite_rules');
			flush_rewrite_rules();
		}

	}
}
