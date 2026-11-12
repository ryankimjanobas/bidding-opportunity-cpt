<?php

/**
 * @package  BiddingOpportunityPlugin
 * 
 * All codes handling the request of datatables on frontend
 */

namespace BIDDOP\Frontend;

defined('ABSPATH') or die('Hey, you should not be here!');

use BIDDOP\Base\BiddopBaseController;

if (!class_exists('BiddopFrontendCommonMethods')) {

  class BiddopFrontendCommonMethods extends BiddopBaseController
  {
    public function biddopRegister()
    {
      /* 
      *Enqueue scripts for frontend
      */
      add_action('wp_enqueue_scripts', array($this, 'biddopEnqueueFrontendScripts'));
      /* 
      * Register ajax endpoint
      */
      add_action('wp_ajax_bid_opportunity_datatable', array($this, 'biddopFrontendAjaxEndpoint'));
      add_action('wp_ajax_nopriv_bid_opportunity_datatable', array($this, 'biddopFrontendAjaxEndpoint'));      

    }    

    public function biddopEnqueueFrontendScripts()
    {
      /*
       * Only load the datatable assets on pages that actually render one of the
       * shortcodes. Shortcode callbacks enqueue the assets as a fallback for
       * content that is not stored in post_content (page builders, widgets...).
       */
      if ($this->biddopPageHasShortcode()) {
        $this->biddopEnqueueFrontendAssets();
      }
    }

    public function biddopFrontendAjaxEndpoint()
    {

    /*
    * Identifiers:
    *    bid-opportunities-public, bid-opportunities-alternative, transparency-public and transparency-alternative
    */
      $request = $_GET;

      $default_length = 10;
      $posts_per_page = isset($request['length']) ? intval($request['length']) : $default_length;

      // Guard against invalid or unbounded page sizes.
      if ($posts_per_page < 1 || $posts_per_page > 100) {
        $posts_per_page = $default_length;
      }

      $offset              = isset($request['start']) ? max(0, intval($request['start'])) : 0;
      $draw                = isset($request['draw']) ? intval($request['draw']) : 0;
      $search              = isset($request['search']['value']) ? sanitize_text_field(wp_unslash($request['search']['value'])) : '';
      $page_identifier     = isset($request['identifier']) ? sanitize_key($request['identifier']) : '';
      $year_publish_filter = isset($request['year_publish_filter']) ? sanitize_text_field(wp_unslash($request['year_publish_filter'])) : '';

      if (!preg_match('/^\d{4}$/', $year_publish_filter)) {
        $year_publish_filter = '';
      }

      /*
       * Column index => meta key/type used for sorting.
       * Columns 1 to 5 have the same meaning on all four datatables.
       */
      $sortable_columns = array(
        1 => array('key' => $this->variable_prefix . 'key_title', 'type' => 'CHAR'),
        2 => array('key' => $this->variable_prefix . 'key_philgeps_registration_no', 'type' => 'CHAR'),
        3 => array('key' => $this->variable_prefix . 'key_abc', 'type' => 'NUMERIC'),
        4 => array('key' => $this->variable_prefix . 'key_publish_date', 'type' => 'DATE'),
        5 => array('key' => $this->variable_prefix . 'key_closing_date', 'type' => 'DATE'),
      );

      $default_sort_column = 4;
      $sort_column = isset($request['order'][0]['column']) ? intval($request['order'][0]['column']) : $default_sort_column;

      if (!isset($sortable_columns[$sort_column])) {
        $sort_column = $default_sort_column;
      }

      $sort_dir = (isset($request['order'][0]['dir']) && 'ASC' === strtoupper($request['order'][0]['dir'])) ? 'ASC' : 'DESC';

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
        case 'bid-opportunities-alternative': //only get non-awarded alternative bidding
          //only get alternative bidding
          $meta_query_value = 'alternative';
          $tax_query = array( 
            'taxonomy' => $this->taxonomy_status,
            'field'    => 'slug',
            'terms'    => array( 'awarded' ),
            'operator' => 'NOT IN',
          );          
          break;        
        case 'transparency-alternative': //only get awarded alternative bidding
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

      // Restricts the datatable to its mode of procurement.
      $mode_query = array(
        array(
          'key'     => $this->variable_prefix . 'key_mode',
          'value'   => $meta_query_value,
          'compare' => '='
        )
      );

      $meta_query = array_merge($mode_query, array(
        'search' => array(
          'key'     => $this->variable_prefix . 'key_title',
          'value'   => $search,
          'compare' => 'LIKE'
        ),
        'order_by' => array(
          'key'     => $sortable_columns[$sort_column]['key'],
          'compare' => 'EXISTS',
          'type'    => $sortable_columns[$sort_column]['type']
        )
      ));

      if (!empty($year_publish_filter)) {
        $meta_query['year_publish'] = array(
          'key'     => $this->variable_prefix . 'key_publish_date',
          'value'   => array( $year_publish_filter . '-01-01', $year_publish_filter . '-12-31' ),
          'compare' => 'BETWEEN',
          'type'    => 'DATE',
        );
      }

      $data = [];

      $posts = new \WP_Query(array(
        'post_type' => $this->cpt_name,
        'tax_query'  => $tax_query ? array($tax_query) : array(),
        'meta_query' => $meta_query,
        'posts_per_page' => $posts_per_page,
        'offset' => $offset,        
        'orderby' => 'order_by',
        'order' => $sort_dir
      ));

      // Unfiltered count for this datatable (mode/taxonomy scope only).
      $total_query = new \WP_Query(array(
        'post_type' => $this->cpt_name,
        'tax_query'  => $tax_query ? array($tax_query) : array(),
        'meta_query' => $mode_query,
        'posts_per_page' => 1,
        'fields' => 'ids'
      ));

      $totalData = intval($total_query->found_posts);
      $filteredData = intval($posts->found_posts);

      if ($posts->have_posts()) {

        $counter = 1;
       
        while ($posts->have_posts()) : $posts->the_post();

          $post_id = get_the_ID();
          $status = $this->biddopGetPrimaryStatus($post_id);
          
          $title = get_post_meta($post_id, $this->variable_prefix . 'key_title', true);
          $philgeps_ref_no = get_post_meta($post_id, $this->variable_prefix . 'key_philgeps_registration_no', true);
          $abc_raw = get_post_meta($post_id, $this->variable_prefix . 'key_abc', true);
          $abc = ('' !== $abc_raw) ? '₱' . number_format((float) $abc_raw, 2) : '';
          $publish_date = $this->biddopFormatDate(get_post_meta($post_id, $this->variable_prefix . 'key_publish_date', true));
          $closing_date = $this->biddopFormatDate(get_post_meta($post_id, $this->variable_prefix . 'key_closing_date', true));
          $prebid_date = $this->biddopFormatDate(get_post_meta($post_id, $this->variable_prefix . 'key_prebid_date', true));
          $prebid_link = get_post_meta($post_id, $this->variable_prefix . 'key_prebid_link', true);
          $supplier_name = get_post_meta($post_id, $this->variable_prefix . 'key_supplier_name', true);
          $contract_amount_raw = get_post_meta($post_id, $this->variable_prefix . 'key_contract_amount', true);
          $contract_amount = ('' !== $contract_amount_raw) ? '₱' . number_format((float) $contract_amount_raw, 2) : '';
          $awarded_date = $this->biddopFormatDate(get_post_meta($post_id, $this->variable_prefix . 'key_awarded_date', true));

          //show the awarded date below the supplier name in the awarded/transparency tables
          $supplier_name_display = esc_html($supplier_name);
          if (!empty($awarded_date)) {
            $supplier_name_display .= ('' !== $supplier_name ? '<br />' : '') . "<small><em>Awarded: " . esc_html($awarded_date) . "</em></small>";
          }

          $attachment_url = get_post_meta($post_id, $this->variable_prefix . 'key_attachment', true);
          $attachment = '';
          if (!empty($attachment_url)) {
            $attachment = "<a href='" . esc_url($attachment_url) . "' target='_blank' rel='noopener noreferrer' title='Click to see Attachment' class='bid-opportunity-datatable-attachment-icon'>"
              . "<img src='" . esc_url($this->plugin_url . 'assets/images/external-link.svg') . "' alt='External Link' width='25' />"
              . "</a>";
          }

          $supplemental_docs = get_post_meta($post_id, $this->variable_prefix . 'key_supplemental_documents', true);
          $supplemental_docs = $supplemental_docs ? json_decode($supplemental_docs, true) : array();
          $supplemental = '';
          if (is_array($supplemental_docs)) {
            foreach ($supplemental_docs as $doc) {
              $document_link = isset($doc['document_link']) ? $doc['document_link'] : '';
              $document_name = isset($doc['document_name']) ? $doc['document_name'] : '';
              $supplemental .= "<li class='biddop-frontend-supplemental-list'><a href='" . esc_url($document_link) . "' target='_blank'>" . esc_html($document_name) . "</a></li>";
            }
          }

          $formatted_prebid_link = '';
          if (!empty($prebid_link)) {
            $formatted_prebid_link .= "<br><a href='" . esc_url($prebid_link) . "' target='_blank' title='" . esc_attr($prebid_link) . "'><span class='biddop-frontend-prebid-link-exerp'>" . esc_html($prebid_link) . "</span></a>";
          }

          $status_html = '';
          if ($status) {
            $status_html = "<span class='" . esc_attr('bid-opportunity-status-' . $status->slug) . "'>" . esc_html($status->name) . "</span>";
          }

          array_push($data, [            
            'counter'         => $counter++ + $offset,
            'title'           => esc_html($title),
            'philgeps_ref_no' => esc_html($philgeps_ref_no),
            'abc'             => esc_html($abc),
            'publish_date'    => esc_html($publish_date),
            'closing_date'    => esc_html($closing_date),
            'prebid_date'     => $prebid_date . $formatted_prebid_link,
            'supplemental'    => $supplemental,
            'supplier_name'   => $supplier_name_display,
            'contract_amount' => esc_html($contract_amount),
            'attachment'      => $attachment,
            'status'          => $status_html
          ]);                             

        endwhile;

        wp_reset_postdata();        

      }

      wp_send_json([
        "draw" => $draw,
        "recordsTotal" => $totalData,
        "recordsFiltered" => $filteredData,
        "data" => $data
      ]);

    }        
  }

}
