/**
 * @package  BiddingOpportunityPlugin
 * 
 */

jQuery(document).ready(function($) {

  let counter = 0;

  /* 
  * Public bidding datatables
  */
  let bo_cpt_public_bidding_table;
  //initialize the datatable for shortcode intended for bid opportunities public bidding
  bo_cpt_public_bidding_table = $('#bid-opportunity-cpt-public-bidding-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: biddingopportunitydatatablesajax.url + '?action=bid_opportunity_datatable',
      data: (data) => {
        data.identifier = 'bid-opportunities-public',
        data.year_publish_filter = $("#bo_public_year_publish_filter").val()
      }
    },    
    language: {
      lengthMenu: "_MENU_",
      search: "",
      searchPlaceholder: "Search...",
    },
    lengthMenu: [[25, 50, 100],[25, 50, 100]],
    columns: [        
        { data: 'counter' },
        { data: 'title' },
        { data: 'abc' },
        { data: 'publish_date' },
        { data: 'closing_date' },
        { data: 'prebid_date' },
        { data: 'supplemental' },
        { data: 'attachment' },
        { data: 'status' }
    ],
    columnDefs: [
      {
        orderable: false,
        "targets": [0, 5, 6, 7, 8]
      },
      {
        targets: [7],
        className: 'text-center'
      }
    ],
    order: [[0, 'DESC']],
  }); 
  let bo_cpt_public_bidding_completed_table;
  //initialize the datatable for shortcode intended for transparency public bidding
  bo_cpt_public_bidding_completed_table = $('#bid-opportunity-cpt-public-bidding-transparency-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: biddingopportunitydatatablesajax.url + '?action=bid_opportunity_datatable',      
      data: (data) => {
        data.identifier = 'transparency-public',
        data.year_publish_filter = $("#bo_completed_public_year_awarded_filter").val()
      }
    },    
    language: {
      lengthMenu: "_MENU_",
      search: "",
      searchPlaceholder: "Search...",
    },
    lengthMenu: [ [25, 50, 100],[25, 50, 100] ],
    columns: [        
        { data: 'counter' },
        { data: 'title' },
        { data: 'abc' },
        { data: 'publish_date' },
        { data: 'closing_date' },
        { data: 'prebid_date' },
        { data: 'supplemental' },
        { data: 'supplier_name' },
        { data: 'contract_amount' },
        { data: 'attachment' }        
    ],
    columnDefs: [
      {
        orderable: false,
        "targets": [0, 5, 6]
      },
      {
        targets: [9],
        className: 'text-center'
      }
    ],
    order: [[0, 'DESC']],
  });

  /* 
  * Alternative method of procurement datatables
  */
  let bo_cpt_alternative_method_table;
  //initialize the datatable for shortcode intended for bid opportunities of alternative method of procurement
  bo_cpt_alternative_method_table = $('#bid-opportunity-cpt-alternative-method-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: biddingopportunitydatatablesajax.url + '?action=bid_opportunity_datatable',
      data: (data) => {
        data.identifier = 'bid-opportunities-alternative',
        data.year_publish_filter = $("#bo_alternative_year_publish_filter").val()
      }
    },    
    language: {
      lengthMenu: "_MENU_",
      search: "",
      searchPlaceholder: "Search...",
    },
    lengthMenu: [[25, 50, 100],[25, 50, 100]],
    columns: [        
        { data: 'counter' },
        { data: 'title' },
        { data: 'abc' },
        { data: 'publish_date' },
        { data: 'closing_date' },       
        { data: 'attachment' },
        { data: 'status' }
    ],    
    columnDefs: [
      {
        orderable: false,
        "targets": [0, 5, 6]
      },
      {
        targets: [5],
        className: 'text-center'
      }
    ],
    order: [[0, 'DESC']],
  });
  let bo_cpt_alternative_method_completed_table;
  //initialize the datatable for shortcode intended for transparency of alternative method of procurement
  bo_cpt_alternative_method_completed_table = $('#bid-opportunity-cpt-alternative-method-transparency-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: biddingopportunitydatatablesajax.url + '?action=bid_opportunity_datatable',      
      data: (data) => {
        data.identifier = 'transparency-alternative',
        data.year_publish_filter = $("#bo_completed_alternative_year_awarded_filter").val()
      }
    },    
    language: {
      lengthMenu: "_MENU_",
      search: "",
      searchPlaceholder: "Search...",
    },
    lengthMenu: [ [25, 50, 100],[25, 50, 100] ],
    columns: [        
        { data: 'counter' },
        { data: 'title' },
        { data: 'abc' },
        { data: 'publish_date' },
        { data: 'closing_date' },
        { data: 'supplier_name' },
        { data: 'contract_amount' },
        { data: 'attachment' }        
    ],
    columnDefs: [
      {
        orderable: false,
        "targets": [0, 5]
      },
      {
        targets: [7],
        className: 'text-center'
      }
    ],
    order: [[0, 'DESC']],
  });
  /* 
  * handle the reload of datatable onchange of datatable filter on frontend
  */  
  $(document).on('change', '.year_publish_filter', function() {        

    const this_element_id = $(this).prop("id");

    switch (this_element_id) {
      case "bo_public_year_publish_filter":
        bo_cpt_public_bidding_table.ajax.reload(null, false);
        break;
      case "bo_alternative_year_publish_filter":
        bo_cpt_alternative_method_table.ajax.reload(null, false);
        break;
      case "bo_completed_public_year_awarded_filter":
        bo_cpt_public_bidding_completed_table.ajax.reload(null, false);
        break;
      case "bo_completed_alternative_year_awarded_filter":
        bo_cpt_alternative_method_completed_table.ajax.reload(null, false);
        break;
      default:
        break;
    }

  });

});