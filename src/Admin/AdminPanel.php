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
      
      add_action( 'registered_taxonomy', array($this, 'insertRequiredStatus'), 10, 3 );

      add_action('init', array($this, 'autoUpdateBidsStatus'));      

      add_action("save_post", array($this, 'cptSaveValues'), 10, 2);                        

      add_action('manage_' . $this->cpt_name . '_posts_columns', array($this, 'cptCustomTableColumns'));

      add_action('manage_' . $this->cpt_name . '_posts_custom_column', array($this, 'cptCustomTableColumnsData'), 10, 2);

      add_filter('manage_edit-' . $this->cpt_name . '_sortable_columns', array($this, 'cptCustomSortableColumns'));      

      add_action("restrict_manage_posts", array($this, 'statusFilterBox'));

      add_action("parse_query", array($this, 'parseStatusFilterBox'));      
      
      add_action('admin_enqueue_scripts', array($this,'customAdminScripts'));
      
    }        

    /* 
    * Add required terms on status cpt
    */
    public function insertRequiredStatus( $taxonomy, $object_type, $arg )
    {                       
      if ( $this->taxonomy_status === $taxonomy ) {

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
    }

    /* 
    * Automatically set bid status to close if closing date elapsed
    */ 
    public function autoUpdateBidsStatus()
    {               
      $tz = new \DateTimeZone('Asia/Manila');
      $date = new \DateTime('now', $tz);      
      $current = $date->format('Y-m-d'); 

      //get all posts that status is not close and the closing date elapsed
      $posts = new \WP_Query(array(
        'post_type' => $this->cpt_name,
        'tax_query'  => array(
            array( 
                'taxonomy' => $this->taxonomy_status,
                'field'    => 'slug',
                'terms'    => array( 'close', 'awarded', 'failed' ),
                'operator' => 'NOT IN',
            ),
        ),
        'meta_query' => array(
          array(
              'key'     => $this->variable_prefix . 'key_closing_date',
              'value'   => $current,
              'compare' => '<=',
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

        //retrieved status slug close
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
    
    /* 
    * Register a custom post type names bid_opportunity 
    */
    public function registerCustomPostType()
    {      
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
        'supports' => array(''),
        'register_meta_box_cb' => array($this, 'cptRegisterMetabox')
      );

      register_post_type($this->cpt_name, $args);
    }    

    /* 
    * Register a taxonomy statuses to custom post type 
    */
    public function cptAddTaxonomyStatusses()
    {      
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
    /* 
    * Register admin styles and scripts
    */
    public function customAdminScripts()
    {
      wp_enqueue_script($this->cpt_name . '_scripts', "$this->plugin_url/assets/bid-opportunity-cpt-admin-scripts.js", array('jquery'), '1.0.0', true);
      wp_enqueue_style($this->cpt_name . '_styles', "$this->plugin_url/assets/bid-opportunity-cpt-admin-styles.css");
    }     
    /* 
    * Register the metabox 
    */
    public function cptRegisterMetabox()
    {      
      add_meta_box("cpt-id", "Bidding Details", array($this, 'cptMetaBoxLayout'), $this->cpt_name);
      //remove publish metabox
      remove_meta_box( 'submitdiv', $this->cpt_name, 'side' );
    }

    /* 
    * metabox layout
    */        
    public function cptMetaBoxLayout($post)
    {                             
      wp_nonce_field( $this->variable_prefix . 'my_cpt_save_action', $this->variable_prefix . 'my_cpt_nonce' );

      //get field values
      $philgeps_reg_no = get_post_meta($post->ID, $this->variable_prefix . "key_philgeps_registration_no", true);
      $mode = get_post_meta($post->ID, $this->variable_prefix . "key_mode", true);
      $title = get_post_meta($post->ID, $this->variable_prefix . "key_title", true);
      $abc = get_post_meta($post->ID, $this->variable_prefix . "key_abc", true);
      $publish_date = get_post_meta($post->ID, $this->variable_prefix . "key_publish_date", true);
      $closing_date = get_post_meta($post->ID, $this->variable_prefix . "key_closing_date", true);
      $prebid_date = get_post_meta($post->ID, $this->variable_prefix . "key_prebid_date", true);
      $supplemental_documents = get_post_meta($post->ID, $this->variable_prefix . "key_supplemental_documents", true);
      $bid_docs = get_post_meta($post->ID, $this->variable_prefix . "key_attachment", true);
      $supplier_name = get_post_meta($post->ID, $this->variable_prefix . "key_supplier_name", true);
      $contract_amount = get_post_meta($post->ID, $this->variable_prefix . "key_contract_amount", true);
      
      ?>
      
      <div>
        <h4><label class='required-field-label'>Philgeps Registration no:</label></h4>        
        <input
          class='bidding-opportunity-admin-input'
          type='text'
          value='<?php echo $philgeps_reg_no; ?>'
          name='<?php echo $this->variable_prefix; ?>philgeps_registration_no'
          placeholder="Philgep's registration number" required
        />
      </div>

      <div>
        <h4><label class='required-field-label'>Mode of Procurement:</label></h4>             
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
        <input
          class="bidding-opportunity-admin-input"
          type="number"
          step=".01"
          value="<?php echo $abc; ?>"
          name="<?php echo $this->variable_prefix;?>abc"
          placeholder="Approved Budget for the Contract"
          required
        />
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr;gap: 20px;">
        <div>
          <h4><label class='required-field-label'>Publish Date:</label></h4>          
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
          <input
            class="bidding-opportunity-admin-input"
            type="date"
            value="<?php echo $prebid_date; ?>"
            name="<?php echo $this->variable_prefix; ?>prebid_date"
          />
        </div>
        <div class="bidding-opportunity-supplemental-container" style="border:1px solid rgba(0,0,0,0.3);margin:10px 0;padding: 10px;border-radius:5px;">          
          <input type="hidden" name="<?php echo $this->variable_prefix; ?>supplemental_documents" value='<?php echo $supplemental_documents; ?>'>          
          <h4><label>Supplemental Document(s):</label></h4>
          <p>
            
            <div id="each_supplemental_documents_container">

            <?php

              $supplemental_documents_json = $supplemental_documents ? json_decode($supplemental_documents, true) : [];

              if(!empty($supplemental_documents_json)) {
              
                foreach ($supplemental_documents_json as $doc) {                

                ?>

                  <span class='badge each-supplemental-document-badge'>
                    <a href='<?php echo esc_url($doc['document_link']); ?>' target='_blank' class='supplemental-document-links'>
                      <?php echo $doc['document_name']; ?>
                    </a>
                    <span class='supplemental-action-delete' data-document_title='<?php echo $doc['document_name']; ?>'>
                      <span class='dashicons dashicons-no' title='Delete'></span>
                    </span>
                  </span>
                
                <?php
                }
              }
            ?>            

            </div>      
          </p>          
          <p><span id="bidding-opportunity-supplemental-warning"></span></P>
          <div style="display: grid; grid-template-columns: 8fr 8fr 1fr;gap: 10px;">            
            <div>                   
              <input
                class="bidding-opportunity-admin-input"
                type="text"
                value=""                
                placeholder="Document Title"
                id="supplemental_documents_document_name"
              />
            </div> 
            <div>                    
              <input
                class="bidding-opportunity-admin-input"
                type="text"
                value=""                
                placeholder="Link of Document"
                id="supplemental_documents_document_link"
              />
            </div>
            <div>              
              <button class="supplemental-action-button" id="bid_opportunity_supplemental_documents_add">Add</button>
            </div>  
          </div>
        </div>                
      </div>
      
      <div>
        <h4><label class='required-field-label'>Attachment:</label></h4>        
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
          <input
            class="bidding-opportunity-admin-input"
            type="number"
            step=".01"
            value="<?php echo $contract_amount; ?>"
            name="<?php echo $this->variable_prefix; ?>contract_amount"
            placeholder="Contract Amount"
          />
        </div> 

      </div>

      <div style="padding:30px 0;">
        <?php echo submit_button('Save'); ?>
      </div>
      <?php        
    }
    /* 
    * Save values of metabox fields to db
    */         
    public function cptSaveValues($post_id, $post)
    {        
      /*
      * NONCE check and verification        
      */
      //check if nonce is set and return if not
      if ( ! isset( $_POST[$this->variable_prefix . 'my_cpt_nonce'] ) ) {
        return;
      }
      //verify nonce
      if ( ! wp_verify_nonce( $_POST[$this->variable_prefix . 'my_cpt_nonce'], $this->variable_prefix . 'my_cpt_save_action' ) ) {
        return;
      }
       
      /*
      * Check if this is an autosave or a revision       
      */      
      if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
      }        
      if ( wp_is_post_revision( $post_id ) ) {
        return;
      }

      //return if action is untrash/restore from trash
      if ( isset($_GET['action']) && $_GET['action'] === 'untrash' ) {
        return;
      }
      //return if post status is auto-draft or trash
      if(in_array($post->post_status, array('auto-draft', 'trash'))) {
        return;
      }              
      
      $philgeps = isset($_POST[$this->variable_prefix . 'philgeps_registration_no']) ? sanitize_text_field($_POST[$this->variable_prefix . 'philgeps_registration_no']) : "";
      $title = isset($_POST[$this->variable_prefix . 'title']) ? sanitize_text_field($_POST[$this->variable_prefix . 'title']) : "";
      $abc = isset($_POST[$this->variable_prefix . 'abc']) ? sanitize_text_field($_POST[$this->variable_prefix . 'abc']) : "";
      $publish_date = isset($_POST[$this->variable_prefix . 'publish_date']) ? sanitize_text_field($_POST[$this->variable_prefix . 'publish_date']) : "";
      $closing_date = isset($_POST[$this->variable_prefix . 'closing_date']) ? sanitize_text_field($_POST[$this->variable_prefix . 'closing_date']) : "";
      $attachment = isset($_POST[$this->variable_prefix . 'attachment']) ? sanitize_url($_POST[$this->variable_prefix . 'attachment']) : "";            
      $mode = isset($_POST[$this->variable_prefix . 'mode']) ? sanitize_text_field($_POST[$this->variable_prefix . 'mode']) : "";
      $prebid_date = isset($_POST[$this->variable_prefix . 'prebid_date']) ? sanitize_text_field($_POST[$this->variable_prefix . 'prebid_date']) : "";
      $supplemental_documents = isset($_POST[$this->variable_prefix . 'supplemental_documents']) ? sanitize_text_field($_POST[$this->variable_prefix . 'supplemental_documents']) : "";      
      $supplier_name = isset($_POST[$this->variable_prefix . 'supplier_name']) ? sanitize_text_field($_POST[$this->variable_prefix . 'supplier_name']) : "";
      $contract_amount = isset($_POST[$this->variable_prefix . 'contract_amount']) ? sanitize_text_field($_POST[$this->variable_prefix . 'contract_amount']) : "";       
      
      update_post_meta($post_id, $this->variable_prefix . "key_philgeps_registration_no", $philgeps);
      update_post_meta($post_id, $this->variable_prefix . "key_title", $title);
      update_post_meta($post_id, $this->variable_prefix . "key_abc", $abc);
      update_post_meta($post_id, $this->variable_prefix . "key_publish_date", $publish_date);
      update_post_meta($post_id, $this->variable_prefix . "key_closing_date", $closing_date);
      update_post_meta($post_id, $this->variable_prefix . "key_attachment", $attachment);      
      update_post_meta($post_id, $this->variable_prefix . "key_mode", $mode);
      update_post_meta($post_id, $this->variable_prefix . "key_prebid_date", $prebid_date);
      update_post_meta($post_id, $this->variable_prefix . "key_supplemental_documents", $supplemental_documents);
      update_post_meta($post_id, $this->variable_prefix . "key_supplier_name", $supplier_name);
      update_post_meta($post_id, $this->variable_prefix . "key_contract_amount", $contract_amount);

      //handle saving of status       
      $status_id = isset($_POST[$this->taxonomy_status]) ? intval(sanitize_text_field($_POST[$this->taxonomy_status])) : "";
      wp_set_object_terms( $post_id, $status_id, $this->taxonomy_status, false );                   

      
      //ensure post title is equal to this meta key title
      if ( ! empty( $title ) && $post->post_title !== $title ) {        

          // Unhook this function to prevent an infinite loop when updating
          remove_action( 'save_post', array($this,'cptSaveValues') );

          wp_update_post( array(
              'ID'         => $post_id,
              'post_title' => $title,
              'post_name'  => sanitize_title( $title ),
              'post_status' => 'publish'
          ) );

          // Re-hook the function
          add_action( 'save_post', array($this, 'cptSaveValues'), 10, 2 );
      }

    }

    /* 
    * Display custom columns on admin panel listing of bidding opportunities 
    */
    public function cptCustomTableColumns($columns)
    {      
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

    /* 
    * Make custom columns sortable on admin panel 
    */
    public function cptCustomSortableColumns($columns)
    {            
      $columns['bo_title'] = "title";
      $columns['bo_abc'] = "abc";
      $columns['bo_closing_date'] = "closing-date";
      $columns['bo_publish_date'] = "publish-date";
      $columns['bo_mode'] = "mode-of-procurement";
      $columns['bo_prebid_date'] = "prebid-date";

      return $columns;
    }

    /* 
    * Add filter by status on admin panel 
    */
    public function statusFilterBox()
    {      
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

    /* 
    * Parse query on status filter in admin panel 
    */
    public function parseStatusFilterBox($query)
    {            
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

    /* 
    * Display data of custom table columns on admin panel
    */
    public function cptCustomTableColumnsData($column, $post_id)
    {     
      switch ($column) {

        case "bo_title":
          echo get_post_meta($post_id, $this->variable_prefix . "key_title", true);
          break;
        case "bo_abc":
          echo get_post_meta($post_id, $this->variable_prefix . "key_abc", true) ? '₱' . number_format(get_post_meta($post_id, $this->variable_prefix . "key_abc", true), 2) : '';
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
            echo get_post_meta($post_id, $this->variable_prefix . "key_supplier_name", true) ? "<br /><small>Supplier:</small> " . get_post_meta($post_id, $this->variable_prefix . "key_supplier_name", true) : '';
            echo get_post_meta($post_id, $this->variable_prefix . "key_contract_amount", true) ? '<br /><small>Contract Amount:</small> ₱' . number_format(get_post_meta($post_id, $this->variable_prefix . "key_contract_amount", true), 2) : '';
          }   
          
          break;
        case "bo_mode":

            $mode = get_post_meta($post_id, $this->variable_prefix . "key_mode", true);
            $thismode = '';
            $supplemental_documents = '';

            //get supplemental documents if mode is public bidding
            if($mode === 'public') {
              
              $documents = get_post_meta($post_id, $this->variable_prefix . "key_supplemental_documents", true);
              $documents_array = $documents ? json_decode($documents, true) : '';

              if(!empty($documents_array)) {                

                $supplemental_documents .= "<div style='margin-top:7px'>";
                $supplemental_documents .= "<span style='font-size:12px'>Supplemental document(s):</span>";
                //loop for each document
                foreach ($documents_array as $document) {
                  $supplemental_documents .= "<br><a href='" . esc_url($document['document_link']) . "' target='_blank'>" . $document['document_name'] . "</a>";
                }

                $supplemental_documents .= "</div>";                

              }              
            }

            if(array_key_exists($mode, $this->modes_of_procurement)) {
              $thismode = $this->modes_of_procurement[$mode];
            }            
            
            echo "<span style='font-weight:500;'>" . $thismode . "</span>";
            echo $supplemental_documents;                      

          break;
        case "bo_attachment":         
          ?>
          <a href="<?php echo esc_url(get_post_meta($post_id, $this->variable_prefix . "key_attachment", true)); ?>" target="_blank" rel="noopener noreferrer">            
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
