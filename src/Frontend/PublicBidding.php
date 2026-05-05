<?php

/**
 * @package  BiddingOpportunityPlugin
 * 
 * All codes related to display datatable in public bidding page
 */

namespace App\Frontend;

defined('ABSPATH') or die('Hey, you should not be here!');

use App\Base\BaseController;

if (!class_exists('PublicBidding')) {

  class PublicBidding extends BaseController
  {
    public function register()
    {      
      $this->initShortcode();
    }

    public function initShortcode()
    {      
      add_shortcode('bidding_opportunity_public_bidding_datatable', array($this, 'publicBiddingDatatableShortcode'));
      add_shortcode('bidding_opportunity_public_bidding_awarded_datatable', array($this, 'publicBiddingTransparencyDatatableShortcode'));
    }
    
    public function publicBiddingDatatableShortcode()
    {

    ?>     

      <div class='bid-opportunity-cpt-datatable-container'>
        <div style="text-align:left;">
          <p class='title'>Bidding Opportunities | Public Bidding</p>
        </div>        
        <table id='bid-opportunity-cpt-public-bidding-table' class='bid-opportunity-cpt-datatable'>
          <thead>
            <tr>
              <th width='20'>#</th>
              <th>Title</th>
              <th>ABC</th>
              <th>Publish Date</th>
              <th>Closing Date</th>
              <th>Pre-bid Date</th>
              <th>Supplemental</th>
              <th>Attachment</th>
              <th>Status</th>        
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

    <?php

    }

    public function publicBiddingTransparencyDatatableShortcode()
    {

    ?>     

      <div class='bid-opportunity-cpt-datatable-container'>
        <div style="text-align:left;">
          <p class='title'>Completed | Public Bidding</p>
        </div>        
        <table id='bid-opportunity-cpt-public-bidding-transparency-table' class='bid-opportunity-cpt-datatable'>
          <thead>
            <tr>
              <th width='20'>#</th>
              <th>Title</th>
              <th>ABC</th>
              <th>Publish Date</th>
              <th>Closing Date</th>
              <th>Pre-bid Date</th>
              <th>Supplemental</th>
              <th>Supplier Name</th>
              <th>Contract Amount</th>
              <th>Attachment</th>                         
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

    <?php

    }

  }

}
