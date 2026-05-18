/**
 * @package  BiddingOpportunityPlugin
 * 
 */

jQuery(document).ready(function($) {

  let counter = 0;

  /* 
  * Public bidding datatables
  */

  //initialize the datatable for shortcode intended for bid opportunities public bidding
  $('#bid-opportunity-cpt-public-bidding-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: biddingopportunitydatatablesajax.url + '?action=bid_opportunity_datatable',
      data: {'identifier': 'bid-opportunities-public'}
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

  //initialize the datatable for shortcode intended for transparency public bidding
  $('#bid-opportunity-cpt-public-bidding-transparency-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: biddingopportunitydatatablesajax.url + '?action=bid_opportunity_datatable',
      data: {'identifier': 'transparency-public'}
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

  //initialize the datatable for shortcode intended for bid opportunities of alternative method of procurement
  $('#bid-opportunity-cpt-alternative-method-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: biddingopportunitydatatablesajax.url + '?action=bid_opportunity_datatable',
      data: {'identifier': 'bid-opportunities-alternative'}
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

  //initialize the datatable for shortcode intended for transparency of alternative method of procurement
  $('#bid-opportunity-cpt-alternative-method-transparency-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: biddingopportunitydatatablesajax.url + '?action=bid_opportunity_datatable',
      data: {'identifier': 'transparency-alternative'}
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

});