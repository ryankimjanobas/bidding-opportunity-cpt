<?php

/**
 * @package  BiddingOpportunityPlugin
 */

namespace App\Base;

defined('ABSPATH') or die('Hey, you should not be here!');

if (!class_exists('Activate')) {

	class Activate
	{
		public static function activate()
		{
			flush_rewrite_rules();			

			$posts = new \WP_Query(array(
        'post_type' => 'bid_opportunity'        
      ));     

			if ($posts->have_posts()) {
              
        while ($posts->have_posts()) : $posts->the_post();          

        $document = get_post_meta(get_the_ID(), 'bid_opportunity_cpt_key_supplemental_document', true) ?  get_post_meta(get_the_ID(), 'bid_opportunity_cpt_key_supplemental_document', true) : '';
				$link = get_post_meta(get_the_ID(), 'bid_opportunity_cpt_key_attachment', true) ?  get_post_meta(get_the_ID(), 'bid_opportunity_cpt_key_attachment', true) : '';

				 if($document && $link) {

				 		$new_document = array([
							'document_name' => $document,
							'document_link' => $link
						]);

						update_post_meta(get_the_ID(), "bid_opportunity_cpt_key_supplemental_documents", json_encode($new_document));

				 }				

        endwhile;

        wp_reset_query();        

      }

		}
	}
	
}
