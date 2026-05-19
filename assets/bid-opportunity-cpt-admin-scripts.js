/**
 * @package  BiddingOpportunityPlugin
 * 
 */

jQuery(document).ready(function($) {

  /* 
  * conditional render if mode of procurement is public bidding or Alternative mode of procurement
  */
  $(document).on( "change", '#conditional_render_trigger', function(e) {

    e.preventDefault();

    const conditional_render_container = $("#conditional_render_container");
    const trigger_value = $("#conditional_render_trigger").val();

    if(trigger_value === 'public') {
      conditional_render_container.removeClass("hidden");
    } else {
      conditional_render_container.addClass("hidden");
    }    
    
  });

  /* 
  * conditional render if status is change to awarded
  */  
  $(document).on( "change", '#awarded_conditional_render_container_trigger', function(e) {

    e.preventDefault();

    const awarded_conditional_trigger_html = $("#awarded_conditional_render_container_trigger option:selected").html();
    const awarded_conditional_render_container = $("#awarded_conditional_render_container");
    
    if(awarded_conditional_trigger_html.toLowerCase() === 'awarded') {
      awarded_conditional_render_container.removeClass("hidden");
    } else {
      awarded_conditional_render_container.addClass("hidden");
    }
    
  });

  /* 
  * supplemental documents script
  */    
  $(document).on( "click", "#bid_opportunity_supplemental_documents_add", function(e) {

    e.preventDefault();

    const fields_array = ['supplemental_documents_document_name', 'supplemental_documents_document_link'];
    let error = 0;

    $("#bidding-opportunity-supplemental-warning").html('');

    fields_array.forEach(element => {
      const this_element = $("#" + element);

      //remove required class of an element
      this_element.removeClass("bid-opportunity-required-field-border");

      //check if element is null or undefined and add required class if it is
      if(!this_element.val() || this_element.val().trim().length === 0) {
        this_element.addClass("bid-opportunity-required-field-border");
        //increment error
        error = ++error;
        //display error
        $("#bidding-opportunity-supplemental-warning").html('Please fill all required fields');
      }                 

    });

    if(!error) {
      //validate document link if url or not
      const supplemental_link = $("#supplemental_documents_document_link");

      if(!isValidURL(supplemental_link.val())) {    

        supplemental_link.addClass("bid-opportunity-required-field-border");
        $("#bidding-opportunity-supplemental-warning").html('Please input a valid url.');

      } else {

        //add the supplemental item if no error
        const supplemental_document_name = $("#supplemental_documents_document_name");
        const supplemental_document_link = $("#supplemental_documents_document_link");
        const each_supplemental_container = $("#each_supplemental_documents_container");

        supplemental_link.removeClass("bid-opportunity-required-field-border");
        $("#bidding-opportunity-supplemental-warning").html('');

        each_supplemental_container.append("<span class='badge each-supplemental-document-badge'><a href='" + supplemental_document_link.val()
        + "' target='_blank' class='supplemental-document-links'> &nbsp;" + supplemental_document_name.val()
        + "</a> <span class='supplemental-action-delete'><span class='dashicons dashicons-no' title='Delete'></span></span></span>");     
        //reset input values
        supplemental_document_name.val('');
        supplemental_document_link.val('');

      }
    }
    
  });
  //handle remove of supplemental document
  $(document).on( "click", '.supplemental-action-delete', function(e) {

    e.preventDefault();

    if (confirm("Are you sure you want to delete this?")) {
        $(this).closest('.each-supplemental-document-badge').remove();
    }
    
  });
  
});

function isValidURL(string) {
  try {
    new URL(string);
    return true;
  } catch (_) {
    return false;  
  }
}