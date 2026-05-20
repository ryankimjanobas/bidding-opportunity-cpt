<?php

/**
 * @package  BiddingOpportunityPlugin
 * 
 * All codes related to display datatable in public bidding page
 */

namespace App\Frontend;

defined('ABSPATH') or die('Hey, you should not be here!');

use App\Base\BaseController;

if (!class_exists('AlternativeMethod')) {

  class AlternativeMethod extends BaseController
  {
    public function register()
    {      
      $this->initShortcode();
    }

    public function initShortcode()
    {      
      add_shortcode('bidding_opportunity_alternative_method_datatable', array($this, 'alternativeMethodDatatableShortcode'));
      add_shortcode('bidding_opportunity_alternative_method_awarded_datatable', array($this, 'alternativeMethodTransparencyDatatableShortcode'));
    }
    
    public function alternativeMethodDatatableShortcode()
    {

    ?>     

      <div class='bid-opportunity-cpt-datatable-container'>                
        <div style="display: grid; grid-template-columns: 1fr 1fr;gap: 20px;">
          <div style="text-align:left;">
            <p class='title'>Bidding Opportunities | Alternative Method of Procurement</p>
          </div>
          <div style="text-align:right;">
            <label for="year_publish_filter" class='title'>Year Publish:</label>
            <select id="bo_alternative_year_publish_filter" class="year_publish_filter" style="width:250px;height:35px;border-radius:2px;">        
              <option value="">All</option>              
              <?php              
                $year_end = (int)date('Y');
                $year_start  = $year_end - 5;                     

                for ($year = $year_end; $year >= $year_start; $year--) {         
                  echo '<option value="'. $year .'">'. $year .'</option>';
                }               
              ?>
            </select>
          </div>  
        </div>
        
        <table id='bid-opportunity-cpt-alternative-method-table' class='bid-opportunity-cpt-datatable'>
          <thead>
            <tr>
              <th width='20'>#</th>
              <th>Title</th>
              <th>ABC</th>
              <th>Publish Date</th>
              <th>Closing Date</th>
              <th>Attachment</th>
              <th>Status</th>   
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

    <?php

    }

    public function alternativeMethodTransparencyDatatableShortcode()
    {

    ?>     

      <div class='bid-opportunity-cpt-datatable-container'>
        <div style="text-align:left;">
          <p class='title'>Completed | Alternative Method of Procurement</p>
        </div>        
        <table id='bid-opportunity-cpt-alternative-method-transparency-table' class='bid-opportunity-cpt-datatable'>
          <thead>
            <tr>
              <th width='20'>#</th>
              <th>Title</th>
              <th>ABC</th>
              <th>Publish Date</th>
              <th>Closing Date</th>
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
