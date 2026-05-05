<?php

/**
 * @package  BiddingOpportunityPlugin
 * 
 * This class handles all scripts to enqueue
 */

namespace App\Base;

defined('ABSPATH') or die('Hey, you should not be here!');

use App\Base\BaseController;

if (!class_exists('Enqueue')) {
  
  class Enqueue extends BaseController
  {
    public function register()
    {
      add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
    }

    public function enqueueScripts()
    {
      //datables scripts
      wp_enqueue_script('datatables', 'https://cdn.datatables.net/2.3.8/js/dataTables.min.js', array('jquery'));
      wp_localize_script('datatables', 'biddingopportunitydatatablesajax', ['url' => admin_url('admin-ajax.php')]);
      wp_enqueue_style('datatables', 'https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.min.css');

      wp_enqueue_script($this->cpt_name . '_scripts', "$this->plugin_url/assets/bid-opportunity-cpt-scripts.js", array('jquery'), '1.0.0', true);
      wp_enqueue_style($this->cpt_name . '_styles', "$this->plugin_url/assets/bid-opportunity-cpt-styles.css");
    }
  }
}
