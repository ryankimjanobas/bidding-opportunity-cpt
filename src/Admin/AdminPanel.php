<?php
/**
 * @package  BiddingOpportunityPlugin
 * 
 * All codes related to admin panel here
 */

namespace App\Admin;

defined('ABSPATH') or die('Hey, you should not be here!');

use App\Base\BaseController;

if (!class_exists('AdminPanel'))
{

  class AdminPanel extends BaseController
  {        
    public function register()
    {
      /* 
      * Register all services neeeded for the plugin to work 
      */

      add_action('init', array($this, 'registerCustomPostType'));
      
      add_action('init', array($this, 'cptAddTaxonomyStatusses'));    
      
      add_action('init', array($this, 'insertRequiredStatus'));

      add_action('init', array($this, 'autoUpdateBidsStatus'));

      add_action("add_meta_boxes", array($this, 'cptRegisterMetabox'));
      
      add_action( 'admin_menu', array($this, 'hidePublishMetabox') );

      add_action("save_post", array($this, 'cptSaveValues'), 10, 2);      

      add_action('save_post', array($this, 'syncCustomPostTitle'), 10, 3 );
            
      add_filter( 'wp_insert_post_data', array($this, 'autoPublishPost'), 10, 2 );

      add_action('manage_' . $this->cpt_name . '_posts_columns', array($this, 'cptCustomTableColumns'));

      add_action('manage_' . $this->cpt_name . '_posts_custom_column', array($this, 'cptCustomTableColumnsData'), 10, 2);

      add_filter('manage_edit-' . $this->cpt_name . '_sortable_columns', array($this, 'cptCustomSortableColumns'));      

      add_action("restrict_manage_posts", array($this, 'statusFilterBox'));

      add_action("parse_query", array($this, 'parseStatusFilterBox'));

      add_action('admin_head', array($this,'customAdminStylesScripts'));      
      
    }
    
    public function hidePublishMetabox()
    {
      /* 
      * Hides the publish meta box on admin panel 
      */
        
      remove_meta_box( 'submitdiv', $this->cpt_name, 'side' );
    }

    public function insertRequiredStatus()
    {
      /* 
      * Add required terms on status cpt
      */                 
      foreach ($this->required_status as $status) {

        if( ! term_exists( $status['term'], $this->taxonomy_status )) {
          wp_insert_term(
            $status['term'],
            $this->taxonomy_status,
            array(
              'description' => $status['description'],
              'slug'        => $status['slug']
            )
          );    
        }                
      }
    }
    
    public function autoUpdateBidsStatus()
    {
      /* 
      * Automatically set post status to close if closing date and current date is the same 
      */           

      $tz = new \DateTimeZone('Asia/Manila');
      $date = new \DateTime('now', $tz);      
      $current = $date->format('Y-m-d'); 

      //get all posts that status is not close and the closing date is today
      $posts = new \WP_Query(array(
        'post_type' => $this->cpt_name,
        'tax_query'  => array(
            array( 
                'taxonomy' => $this->taxonomy_status,
                'field'    => 'slug',
                'terms'    => array( 'close', 'awarded' ),
                'operator' => 'NOT IN',
            ),
        ),
        'meta_query' => array(
          array(
              'key'     => $this->variable_prefix . 'key_closing_date',
              'value'   => $current,
              'compare' => '=',
              'type'    => 'DATE',
          ),
        ),
      ));                        
      
      if ($posts->have_posts()) {        

        //get statusses
        $status_taxonimies = get_terms( array(  
          'taxonomy' => $this->taxonomy_status,
          'hide_empty' => false,
        ));          

        //retrieved status name close
        $close = array_values(array_filter($status_taxonimies, function($obj) {
          return $obj->slug === 'close';              
        }));                

        $close_id = $close[0]->term_id;                             

        while ($posts->have_posts()) : $posts->the_post();      
        
          wp_set_object_terms( get_the_ID(), $close_id, $this->taxonomy_status, false );     

        endwhile;
        
        wp_reset_query();        

      }
      
    }

    public function autoPublishPost( $data, $postarr )
    {                       
      if ( $data['post_type'] === $this->cpt_name && $data['post_status'] != 'trash' && $data['post_status'] != 'auto-draft' ) {

        //todo validate post data

        $data['post_status'] = 'publish';
        
      }        
      return $data;

    }    

    public function registerCustomPostType()
    {
      /* 
      * Register a custom post type names bid_opportunity 
      */

      $labels = array(
        'name' => __('Bid Opportunities'),
        'singular_name' => __('Bid Opportunity'),
        'menu_name' => __('Bid Opportunities'),
        'name_admin_bar' => __('Bid Opportunity'),
        'add_new' => __('Add New'),
        'add_new_item' => __('Add New'),
        'new_item' => __('New'),
        'edit_item' => __('Edit'),
        'view_item' => __('View'),
        'all_items' => __('All Bids'),
        'search_items' => __('Search'),
        'parent_item_colon' => __('Parent Biddings:'),
        'not_found' => __('No bidding found.'),
        'not_found_in_trash' => __('No bidding found in Trash.')
      );

      $args = array(
        'labels' => $labels,
        'description' => __('Description.'),
        'public' => true,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => $this->cpt_name),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 6,
        'menu_icon' => 'dashicons-image-filter',
        'supports' => array('')
      );

      register_post_type($this->cpt_name, $args);
    }    

    public function cptAddTaxonomyStatusses()
    {
      /* 
      * Register a taxonomy statuses to custom post type 
      */

      register_taxonomy(
        $this->taxonomy_status,
        $this->cpt_name,
        array(
          'labels' => array(
              'name' => __( 'Statusses' ),
              'singular_name' => __( 'Status' ),
              'menu_name' => __( 'Statusses' ),
              'all_items' => __( 'All Statusses' ),
              'edit_item' => __( 'Edit Status' ),
              'add_new_item' => __( 'Add Status' ),
              'update_item' => __( 'Update Status' ),
          ),
          'default_term' => array('name' => 'Active', 'slug' => 'active'),           
          'hierarchical' => false,
          'show_ui' => true,
          'meta_box_cb' => false,          
        )
      );
    }
    
    public function customAdminStylesScripts() {
      /* 
      * Register inline style and scripts
      */
      global $typenow;
      //only add to head if type is equal to this cpt
      if($typenow === $this->cpt_name) {
        ?>
          <style>            
              .bidding-opportunity-admin-input {              
                width: 100%;
              }
              .required-field-label::after {
                content: " *";
                color: red;
                font-weight: bold;
              }
              .hidden {
                display: none;
              }
              .bid-opportunity-status-active {
                color: #5498f1
              }
              .bid-opportunity-status-close {
                color: #df6e12
              }
              .bid-opportunity-status-awarded {
                color: #14b336
              }
              .bid-opportunity-status-default {
                color: #50575e
              }
          </style>

          <script>
            document.addEventListener('DOMContentLoaded', function() {
              //conditional render if mode of procurement is public bidding or Alternative mode of procurement
              const trigger = document.getElementById("conditional_render_trigger");
              const conditional_render_container = document.getElementById("conditional_render_container");
              
              trigger.addEventListener("change", function(){
                
                const trigger_value = trigger.value;
                
                if(trigger_value === 'public') {
                  conditional_render_container.classList.remove("hidden");
                } else {
                  conditional_render_container.classList.add("hidden");
                }
                
              });
              //conditional render if status is change to awarded
              const awarded_conditional_trigger = document.getElementById("awarded_conditional_render_container_trigger");
              const awarded_conditional_render_container = document.getElementById("awarded_conditional_render_container");
              
              awarded_conditional_trigger.addEventListener("change", function (e){
                
                const selected_inner_html = awarded_conditional_trigger.selectedOptions[0].innerHTML;               
                
                if(selected_inner_html.toLowerCase() === 'awarded') {
                  awarded_conditional_render_container.classList.remove("hidden");
                } else {
                  awarded_conditional_render_container.classList.add("hidden");
                }
                
              });
              
            });
          </script>

      <?php
      }    
    }    

    public function cptRegisterMetabox()
    {
      /* 
      * Register the metabox 
      */

      add_meta_box("cpt-id", "Bidding Details", array($this, 'cptMetaBoxLayout'), $this->cpt_name);
    }

    public function cptMetaBoxLayout($post)
    {
      /* 
      * metabox layout
      */                         
      ?>
      
      <div>
        <h4><label class='required-field-label'>Philgeps Registration no:</label></h4>
        <?php $title = get_post_meta($post->ID, $this->variable_prefix . "key_philgeps_registration_no", true) ?>
        <input
          class='bidding-opportunity-admin-input'
          type='text'
          value='<?php echo $title; ?>'
          name='<?php echo $this->variable_prefix; ?>philgeps_registration_no'
          placeholder="Philgep's registration number" required
        />
      </div>

      <div>
        <h4><label class='required-field-label'>Mode of Procurement:</label></h4>
        <?php $mode = get_post_meta($post->ID, $this->variable_prefix . "key_mode", true); ?>        
        <select
          id="conditional_render_trigger"
          class="bidding-opportunity-admin-input"
          name='<?php echo $this->variable_prefix; ?>mode'
          required
        >
          <option value=''>Select Mode</option>
          <?php
            foreach($this->modes_of_procurement as $value => $label) {
              $selected = $mode === $value ? "selected" : '';              
              echo '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
            }
          ?>          
        </select>        
      </div>

      <div>
        <h4><label class='required-field-label'>Title:</label></h4>        
        <?php $title = get_post_meta($post->ID, $this->variable_prefix . "key_title", true) ?>        
        <input
          class="bidding-opportunity-admin-input"
          type="text"
          value="<?php echo $title; ?>"
          name="<?php echo $this->variable_prefix?>title"
          placeholder="Bidding title"
          required
        />
      </div>
      <div>
        <h4><label class='required-field-label'>Approved Budget for the Contract:</label></h4>
        <?php $abc = get_post_meta($post->ID, $this->variable_prefix . "key_abc", true) ?>
        <input
          class="bidding-opportunity-admin-input"
          type="number"
          value="<?php echo $abc; ?>"
          name="<?php echo $this->variable_prefix;?>abc"
          placeholder="Approved Budget for the Contract"
          required
        />
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr;gap: 20px;">
        <div>
          <h4><label class='required-field-label'>Publish Date:</label></h4>
          <?php $publish_date = get_post_meta($post->ID, $this->variable_prefix . "key_publish_date", true) ?>
          <input
            class="bidding-opportunity-admin-input"
            type="date"
            value="<?php echo $publish_date; ?>"
            name="<?php echo $this->variable_prefix; ?>publish_date"
            required
          />
        </div>
        <div>
          <h4><label class='required-field-label'>Closing Date:</label></h4>
          <?php $closing_date = get_post_meta($post->ID, $this->variable_prefix . "key_closing_date", true) ?>
          <input
            class="bidding-opportunity-admin-input"
            type="date"
            value="<?php echo $closing_date; ?>"
            name="<?php echo $this->variable_prefix; ?>closing_date"
            required
          />
        </div>        
      </div>

      <div id='conditional_render_container' class='<?php echo $mode !== 'public' ? "hidden" : ""; ?>'>
        <div>
          <h4><label>Prebid Date:</label></h4>
          <?php $prebid_date = get_post_meta($post->ID, $this->variable_prefix . "key_prebid_date", true) ?>
          <input
            class="bidding-opportunity-admin-input"
            type="date"
            value="<?php echo $prebid_date; ?>"
            name="<?php echo $this->variable_prefix; ?>prebid_date"
          />
        </div>
        <div>
          <h4><label>Supplemental Document:</label></h4>
          <?php $supplemental = get_post_meta($post->ID, $this->variable_prefix . "key_supplemental_document", true) ?>
          <input
            class="bidding-opportunity-admin-input"
            type="text"
            value="<?php echo $supplemental; ?>"
            name="<?php echo $this->variable_prefix; ?>supplemental_document"
            placeholder="Supplemental Document"
          />
        </div> 
      </div>
      
      <div>
        <h4><label class='required-field-label'>Attachment:</label></h4>
        <?php $bid_docs = get_post_meta($post->ID, $this->variable_prefix . "key_attachment", true) ?>
        <input
          class="bidding-opportunity-admin-input"
          type="text"
          value="<?php echo $bid_docs; ?>"
          name="<?php echo $this->variable_prefix; ?>attachment"
          placeholder="Attachment link"
          required
        />
      </div>  

      <div>
        <h4><label class='required-field-label'>Status:</label></h4> 
        <?php     

          $status_taxonimies = get_terms( array(
              'taxonomy' => $this->taxonomy_status,
              'hide_empty' => false,
          ));          
          
          $active_tax = array_values(array_filter($status_taxonimies, function($obj) {
            return $obj->name === 'Active';              
          }));         
          //default selected to active
          $selected_id = $active_tax[0]->term_id;

          $selected_status = get_the_terms($post->ID, $this->taxonomy_status);
          $selected_status_slug = '';                    
          
          if(!empty($selected_status)) {
            //set id of selected status if not empty
            $selected_id = intval($selected_status[0]->term_id);
            $selected_status_slug = $selected_status[0]->slug;
          }       

          wp_dropdown_categories(array(
            'id' => 'awarded_conditional_render_container_trigger',
            'show_option_all' => 'Select Status',
            'name' => $this->taxonomy_status,
            'selected' => $selected_id,
            'taxonomy' => $this->taxonomy_status,
            'class' => 'bidding-opportunity-admin-input',            
            'hide_empty' => 0          
          ));
        
        ?>
      </div>

      <div id='awarded_conditional_render_container' class='<?php echo $selected_status_slug !== 'awarded' ? "hidden" : ""; ?>'>
        <div>
          <h4><label>Supplier Name:</label></h4>
          <?php $supplier_name = get_post_meta($post->ID, $this->variable_prefix . "key_supplier_name", true) ?>
          <input
            class="bidding-opportunity-admin-input"
            type="text"
            value="<?php echo $supplier_name; ?>"
            name="<?php echo $this->variable_prefix; ?>supplier_name"
            placeholder="Supplier Name"
          />
        </div>
        <div>
          <h4><label>Contract Amount:</label></h4>
          <?php $contract_amount = get_post_meta($post->ID, $this->variable_prefix . "key_contract_amount", true) ?>
          <input
            class="bidding-opportunity-admin-input"
            type="number"
            value="<?php echo $contract_amount; ?>"
            name="<?php echo $this->variable_prefix; ?>contract_amount"
            placeholder="Contract Amount"
          />
        </div> 

      </div>

      <div style="padding:30px 0;">
        <?= submit_button('Save'); ?>
      </div>
      <?php        
    }

    public function cptSaveValues($post_id, $post)
    {
      /* 
      * Save value of the fields to db
      */            
      $philgeps = isset($_POST[$this->variable_prefix . 'philgeps_registration_no']) ? sanitize_text_field($_POST[$this->variable_prefix . 'philgeps_registration_no']) : "";
      $title = isset($_POST[$this->variable_prefix . 'title']) ? sanitize_text_field($_POST[$this->variable_prefix . 'title']) : "";
      $bo_abc = isset($_POST[$this->variable_prefix . 'abc']) ? sanitize_text_field($_POST[$this->variable_prefix . 'abc']) : "";
      $bo_publish_date = isset($_POST[$this->variable_prefix . 'publish_date']) ? sanitize_text_field($_POST[$this->variable_prefix . 'publish_date']) : "";
      $bo_closing_date = isset($_POST[$this->variable_prefix . 'closing_date']) ? sanitize_text_field($_POST[$this->variable_prefix . 'closing_date']) : "";
      $attachment = isset($_POST[$this->variable_prefix . 'attachment']) ? sanitize_text_field($_POST[$this->variable_prefix . 'attachment']) : "";            
      $mode = isset($_POST[$this->variable_prefix . 'mode']) ? sanitize_text_field($_POST[$this->variable_prefix . 'mode']) : "";
      $prebid_date = isset($_POST[$this->variable_prefix . 'prebid_date']) ? sanitize_text_field($_POST[$this->variable_prefix . 'prebid_date']) : "";
      $supplemental_document = isset($_POST[$this->variable_prefix . 'supplemental_document']) ? sanitize_text_field($_POST[$this->variable_prefix . 'supplemental_document']) : "";
      $status_id = isset($_POST[$this->taxonomy_status]) ? intval(sanitize_text_field($_POST[$this->taxonomy_status])) : "";
      $supplier_name = isset($_POST[$this->variable_prefix . 'supplier_name']) ? sanitize_text_field($_POST[$this->variable_prefix . 'supplier_name']) : "";
      $contract_amount = isset($_POST[$this->variable_prefix . 'contract_amount']) ? sanitize_text_field($_POST[$this->variable_prefix . 'contract_amount']) : "";  
      
      update_post_meta($post_id, $this->variable_prefix . "key_philgeps_registration_no", $philgeps);
      update_post_meta($post_id, $this->variable_prefix . "key_title", $title);
      update_post_meta($post_id, $this->variable_prefix . "key_abc", $bo_abc);
      update_post_meta($post_id, $this->variable_prefix . "key_publish_date", $bo_publish_date);
      update_post_meta($post_id, $this->variable_prefix . "key_closing_date", $bo_closing_date);
      update_post_meta($post_id, $this->variable_prefix . "key_attachment", $attachment);      
      update_post_meta($post_id, $this->variable_prefix . "key_mode", $mode);
      update_post_meta($post_id, $this->variable_prefix . "key_prebid_date", $prebid_date);
      update_post_meta($post_id, $this->variable_prefix . "key_supplemental_document", $supplemental_document);
      update_post_meta($post_id, $this->variable_prefix . "key_supplier_name", $supplier_name);
      update_post_meta($post_id, $this->variable_prefix . "key_contract_amount", $contract_amount);

      //handle saving of status       
      wp_set_object_terms( $post_id, $status_id, $this->taxonomy_status, false );            
     
    }

    public function syncCustomPostTitle( $post_id, $post, $update ) {
      
        // Only run for our specific custom post type
        if ( $this->cpt_name !== $post->post_type ) {
            return;
        }

        // Check if this is an autosave or a revision to avoid unnecessary runs
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( wp_is_post_revision( $post_id ) ) return;

        // Get the custom field value        
        $custom_title = get_post_meta( $post_id, $this->variable_prefix . 'key_title', true );
        
        if ( ! empty( $custom_title ) && $post->post_title !== $custom_title ) {
          
            // Unhook this function to prevent an infinite loop when updating
            remove_action( 'save_post', array($this,'syncCustomPostTitle') );

            wp_update_post( array(
                'ID'         => $post_id,
                'post_title' => $custom_title,
                'post_name'  => sanitize_title( $custom_title ) // Also updates the URL slug
            ) );

            // Re-hook the function
            add_action( 'save_post', array($this, 'syncCustomPostTitle'), 10, 3 );
        }
    }

    public function cptCustomTableColumns($columns)
    {
      /* 
      * Display custom columns on admin panel listing of bidding opportunities 
      */

      $columns = array(
        "cb" => "<input type='checkbox'/>",
        "bo_title" => "Title",
        "bo_abc" => "ABC",
        "bo_publish_date" => "Publish Date",
        "bo_closing_date" => "Closing Date",
        "bo_prebid_date" => 'Pre-bid Date',           
        "bo_mode" => "Mode of Procurement",
        "bo_status" => 'Status',
        "bo_attachment" => "Attachment"
      );

      return $columns;
    }

    public function cptCustomSortableColumns($columns)
    {
      /* 
      * Make custom columns sortable on admin panel 
      */
      
      $columns['bo_title'] = "title";
      $columns['bo_abc'] = "abc";
      $columns['bo_closing_date'] = "closing-date";
      $columns['bo_publish_date'] = "publish-date";
      $columns['bo_mode'] = "mode-of-procurement";
      $columns['bo_prebid_date'] = "prebid-date";

      return $columns;
    }
            
    public function statusFilterBox()
    {
      /* 
      * Add filter by status on admin panel 
      */

      global $typenow;

      if ($this->cpt_name !== $typenow) {
        return;
      }

      $selected_status = isset($_GET[$this->taxonomy_status]) ? intval($_GET[$this->taxonomy_status]) : false;
      
      wp_dropdown_categories(array(
        "show_option_all" => "All Statuses",
        "name" => $this->taxonomy_status,
        "selected" => $selected_status,
        "taxonomy" => $this->taxonomy_status,
        "hide_empty" => 0,
        "show_count" => true
      ));

    }

    public function parseStatusFilterBox($query)
    {
      /* 
      * Parse query on status filter in admin panel 
      */
      
      global $typenow;
      global $pagenow;

      $query_variables = &$query->query_vars;            

      if ($typenow == $this->cpt_name && $pagenow == "edit.php" && isset($query_variables[$this->taxonomy_status]) && is_numeric($query_variables[$this->taxonomy_status])) {
        
        $term_details = get_term_by("id", $query_variables[$this->taxonomy_status], $this->taxonomy_status);
        if ($term_details && $term_details->slug) {
          $query_variables[$this->taxonomy_status] = $term_details->slug;
        }

      }
    }    

    public function cptCustomTableColumnsData($column, $post_id)
    {
      /* 
      * Display data of custom table columns on admin panel
      */

      switch ($column) {

        case "bo_title":
          echo get_post_meta($post_id, $this->variable_prefix . "key_title", true);
          break;
        case "bo_abc":
          echo '₱' . number_format(intval(get_post_meta($post_id, $this->variable_prefix . "key_abc", true)), 2);
          break;
        case "bo_publish_date":
          echo date('F j, Y', strtotime(get_post_meta($post_id, $this->variable_prefix . "key_publish_date", true)));
          break;
        case "bo_closing_date":
          echo date('F j, Y', strtotime(get_post_meta($post_id, $this->variable_prefix . "key_closing_date", true)));
          break;
        case "bo_prebid_date":
          $prebid_date = get_post_meta($post_id, $this->variable_prefix . "key_prebid_date", true) ? date('F j, Y', strtotime(get_post_meta($post_id, $this->variable_prefix . "key_prebid_date", true))) : '';
          echo $prebid_date;
          break;        
        case "bo_status":
          $term = get_the_terms($post_id, $this->taxonomy_status);
          $status_name = '';
          $status_class = '';

          if(!empty($term)) {
            $status_name = $term[0]->name;
            $status_class = 'bid-opportunity-status-' . $term[0]->slug;
          }          

          echo "<span class='" . $status_class . "'>" . $status_name . "</span>";

          if(!empty($term) && $term[0]->slug === 'awarded') {
            echo "<br /><small>Supplier:</small> " . get_post_meta($post_id, $this->variable_prefix . "key_supplier_name", true);
            echo '<br /><small>Contract Amount:</small> ₱' . number_format(intval(get_post_meta($post_id, $this->variable_prefix . "key_contract_amount", true)), 2);
          }   
          
          break;
        case "bo_mode":

            $mode = get_post_meta($post_id, $this->variable_prefix . "key_mode", true);
            $thismode = '';

            if(array_key_exists($mode, $this->modes_of_procurement)) {
              $thismode = $this->modes_of_procurement[$mode];
            }            
            
            echo $thismode;          

          break;
        case "bo_attachment":
          ?>
          <a href="<?php echo get_post_meta($post_id, $this->variable_prefix . "key_attachment", true); ?>" target="_blank" rel="noopener noreferrer">            
            <span class="dashicons dashicons-external"></span>
          </a>
        <?php
          break;
        default: 
          break;
      }
    }       
  }
  
}