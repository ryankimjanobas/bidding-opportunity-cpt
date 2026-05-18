<?php

/**
 * @package  BiddingOpportunityPlugin
 * 
 * All codes related to display datatable in public bidding page
 */

namespace App\Frontend;

defined('ABSPATH') or die('Hey, you should not be here!');

use App\Base\BaseController;

if (!class_exists('AjaxScript')) {

  class AjaxScript extends BaseController
  {
    public function register()
    {
      add_action('wp_ajax_bid_opportunity_datatable', array($this, 'bidOpportunityDatatable'));
      add_action('wp_ajax_nopriv_bid_opportunity_datatable', array($this, 'bidOpportunityDatatable'));      
    }    

    public function bidOpportunityDatatable()
    {

    /*
    * Identifiers:
    *    bid-opportunities-public, bid-opportunities-alternative, transparency-public and transparency-alternative
    */
      $request = $_GET;
      $posts_per_page = $request['length'];
      $offset = $request['start'];
      $orderBy = $request['order'][0]['column'] ? $request['order'][0]['column'] : 0;
      $orderDir = $request['order'][0]['dir'];
      $search = $request['search']['value'];
      $page_identifier = $request['identifier'];
      
      $columns = array(
        1 => 'order_by_title',
        2 => 'order_by_abc',
        3 => 'order_by_publish_date',
        4 => 'order_by_closing_date',
        0 => 'modified' //default order by last modified
      );

      $meta_query_value = 'public';
      $tax_query = array();

      switch ($page_identifier) {                
        case 'bid-opportunities-public': //only get non-awarded public bidding
          $tax_query = array( 
            'taxonomy' => $this->taxonomy_status,
            'field'    => 'slug',
            'terms'    => array( 'awarded' ),
            'operator' => 'NOT IN',
          );          
          break;        
        case 'transparency-public': //only get awarded public bidding
          $tax_query = array( 
            'taxonomy' => $this->taxonomy_status,
            'field'    => 'slug',
            'terms'    => 'awarded'
          );  
          break;
        case 'bid-opportunities-alternative': //only get non-awarded public bidding
          //only get alternative bidding
          $meta_query_value = 'alternative';
          $tax_query = array( 
            'taxonomy' => $this->taxonomy_status,
            'field'    => 'slug',
            'terms'    => array( 'awarded' ),
            'operator' => 'NOT IN',
          );          
          break;        
        case 'transparency-alternative': //only get awarded public bidding
          //only get alternative bidding
          $meta_query_value = 'alternative';
          $tax_query = array( 
            'taxonomy' => $this->taxonomy_status,
            'field'    => 'slug',
            'terms'    => 'awarded'
          );  
          break;
        default:          
          break;
      }

      $data = [];

      $posts = new \WP_Query(array(
        'post_type' => 'bid_opportunity',
        'tax_query'  => count($tax_query) ? array($tax_query) : '',
        'meta_query' => array(
          array(
            'key'     => $this->variable_prefix . 'key_mode',
            'value'   => $meta_query_value,
            'compare' => '='
          ),
          array(
            'key'     => $this->variable_prefix . 'key_title',
            'value'   => $search,
            'compare' => 'LIKE'
          ),
          'order_by_title' => array(
            'key'     => $this->variable_prefix . 'key_title',
            'compare' => 'EXISTS'
          ),
          'order_by_abc' => array(
            'key'     => $this->variable_prefix . 'key_abc',
            'compare' => 'EXISTS'
          ),
          'order_by_publish_date' => array(
            'key'     => $this->variable_prefix . 'key_publish_date',
            'compare' => 'EXISTS',
            'type'    => 'DATE'
          ),
          'order_by_closing_date' => array(
            'key'     => $this->variable_prefix . 'key_closing_date',
            'compare' => 'EXISTS',
            'type'    => 'DATE'
          )
        ),
        'posts_per_page' => $posts_per_page,
        'offset' => $offset,        
        'orderby' => $columns[$orderBy],        
        'order' => $orderDir
      ));     
      
      $totalData = intval($posts->found_posts);

      if ($posts->have_posts()) {

        $counter = 1;
       
        while ($posts->have_posts()) : $posts->the_post();

          $status = get_the_terms( get_the_ID(), $this->taxonomy_status );
          
          $title = get_post_meta(get_the_ID(), $this->variable_prefix . 'key_title', true) ? get_post_meta(get_the_ID(), $this->variable_prefix . 'key_title', true) : '';
          $abc = get_post_meta(get_the_ID(), $this->variable_prefix . 'key_abc', true) ? '₱' . number_format(get_post_meta(get_the_ID(), $this->variable_prefix . 'key_abc', true), 2) : '';
          $publish_date = get_post_meta(get_the_ID(), $this->variable_prefix . 'key_publish_date', true) ? date('F j, Y', strtotime(get_post_meta(get_the_ID(), $this->variable_prefix . 'key_publish_date', true))) : '';
          $closing_date = get_post_meta(get_the_ID(), $this->variable_prefix . 'key_closing_date', true) ? date('F j, Y', strtotime(get_post_meta(get_the_ID(), $this->variable_prefix . 'key_closing_date', true))) : '';
          $prebid_date = get_post_meta(get_the_ID(), $this->variable_prefix . 'key_prebid_date', true) ? date('F j, Y', strtotime(get_post_meta(get_the_ID(), $this->variable_prefix . 'key_prebid_date', true))) : '';
          $supplier_name = get_post_meta(get_the_ID(), $this->variable_prefix . 'key_supplier_name', true) ? get_post_meta(get_the_ID(), $this->variable_prefix . 'key_supplier_name', true) : '';
          $contract_amount = get_post_meta(get_the_ID(), $this->variable_prefix . 'key_contract_amount', true) ? '₱' . number_format(get_post_meta(get_the_ID(), $this->variable_prefix . 'key_contract_amount', true), 2) : '';
          $supplemental = "<a title='Click to see Attachment' href='" . esc_url(get_post_meta(get_the_ID(), $this->variable_prefix . 'key_supplemental_document', true)) . "' target='_blank'>" . get_post_meta(get_the_ID(), $this->variable_prefix . 'key_supplemental_document', true) . "</a>";
          $attachment = "<a href='" . esc_url(get_post_meta(get_the_ID(), $this->variable_prefix . 'key_attachment', true)) . "' target='_blank' title='Click to see Attachment' class='bid-opportunity-datatable-attachment-icon'>
                <img src='" . $this->plugin_url . "/assets/images/external-link.svg" . "' alt='External Link' width='25' />
                </a>";

          array_push($data, [            
            'counter'         => $counter++ + $offset,
            'title'           => $title,
            'abc'             => $abc,
            'publish_date'    => $publish_date,
            'closing_date'    => $closing_date,
            'prebid_date'     => $prebid_date,
            'supplemental'    => $supplemental,
            'supplier_name'   => $supplier_name,
            'contract_amount' => $contract_amount,
            'attachment'      => $attachment,
            'status'          => ( ! empty($status)) ? $status[0]->name : ''
          ]);                             

        endwhile;

        wp_reset_query();        

      }

      echo json_encode([
        "draw" => intval($request['draw']),
        "recordsTotal" => $totalData,
        "recordsFiltered" => $totalData,
        "data" => $data
      ]);

      wp_die();
    }        
  }

}
