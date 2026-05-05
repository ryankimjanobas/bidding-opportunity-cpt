<!-- ABOUT THE PROJECT -->
## About The Project

The project is a simple wordpress plugin that creates a custom post type (name: Bid Opportunities) on admin panel together with a custom taxonomy (Statusses).
The plugin has two types of bidding opportunity namely Public Bidding and Alternative Method of Procurement, it also has statusses with default values of (Close, Active and Awarded),
it has the ability to add statusses as many as you like and can set bidding status according to your needs. When the bidding closing date is equal to current day the status of the bidding is automatically set to close.

  - Close status means the project closing date elapsed
  - Active status means tha bidding is active
  - Awarded status means the bidding is completed and awarded to a certain supplier

The project creates 4 shortcode to display the added bid opportunity on the frontend of the site by using jQuery dataTables.
  _Below is the list of shortcodes that the plugin creates upon activation._

  1. [bidding_opportunity_public_bidding_datatable] - This shortcode shows all bid opportunities of type public bidding and status is not awarded.
  2. [bidding_opportunity_public_bidding_awarded_datatable] - This shortcode shows all bid opportunities of type public bidding with a status of awarded.
  3. [bidding_opportunity_alternative_method_datatable] - This shortcode shows all bid opportunities of type alternative method of procurement and status is not awarded.
  4. [bidding_opportunity_alternative_method_awarded_datatable] - This shortcode shows all bid opportunities of type alternative method of procurement with a status of awarded.


## Usage

  1. Download this repo (https://github.com/ryankimjanobas/bidding-opportunity-cpt) as zip file.
  2. Add the plugin to your wordpress site manually and activate.
  3. Create a page for Public bidding.
  4. Edit the page and add the shortcode [bidding_opportunity_public_bidding_datatable].
  5. Save the page and you should see a datatable showing all bid opportunities of type public bidding and status is not awarded.
  6. Do steps 3 to 5 for the other 3 shortcodes


## License



