<?php
/*
    Plugin Name: WooCommerce MYOB Integration
    Plugin URI: http://creativem.co.nz
    Description: Custom MYOB integration for fendershop.co.nz
    Author: Creative Marketing
    Version: 1.0
    Author URI: http://creativem.co.nz
    */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class CMSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    public $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'MYOB API Settings', 
            'manage_options', 
            'cm-myob-api-settings', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'cm_myob_options' );
        ?>
        <div class="wrap">
            <h2>MYOB API Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'my_option_group' );   
                do_settings_sections( 'cm-myob-api-settings' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'my_option_group', // Option group
            'cm_myob_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Settings for MYOB API Integration', // Title
            array( $this, 'print_section_info' ), // Callback
            'cm-myob-api-settings' // Page
        );  

        add_settings_field(
            'api_name', // ID
            'Access Name', // Title 
            array( $this, 'api_name_callback' ), // Callback
            'cm-myob-api-settings', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'api_code', 
            'Access Code', 
            array( $this, 'api_code_callback' ), 
            'cm-myob-api-settings', 
            'setting_section_id'
        ); 
        
        add_settings_field(
            'dev_key', 
            'Developer Key', 
            array( $this, 'dev_key_callback' ), 
            'cm-myob-api-settings', 
            'setting_section_id'
        ); 
        
        add_settings_field(
            'exo_token', 
            'EXO Token', 
            array( $this, 'exo_token_callback' ), 
            'cm-myob-api-settings', 
            'setting_section_id'
        );
        
        add_settings_field(
            'warehouse_id', 
            'Warehouse LocationID', 
            array( $this, 'warehouse_id_callback' ), 
            'cm-myob-api-settings', 
            'setting_section_id'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['api_name'] ) )
            $new_input['api_name'] = sanitize_text_field( $input['api_name'] );

        if( isset( $input['api_code'] ) )
            $new_input['api_code'] = sanitize_text_field( $input['api_code'] );
            
        if( isset( $input['dev_key'] ) )
            $new_input['dev_key'] = sanitize_text_field( $input['dev_key'] );
        
        if( isset( $input['exo_token'] ) )
            $new_input['exo_token'] = sanitize_text_field( $input['exo_token'] );
            
        if( isset( $input['warehouse_id'] ) )
            $new_input['warehouse_id'] = sanitize_text_field( $input['warehouse_id'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function api_name_callback()
    {
        printf(
            '<input type="text" id="api_name" name="cm_myob_options[api_name]" value="%s" />',
            isset( $this->options['api_name'] ) ? esc_attr( $this->options['api_name']) : ''
        );
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function api_code_callback()
    {
        printf(
            '<input type="text" id="api_code" name="cm_myob_options[api_code]" value="%s" />',
            isset( $this->options['api_code'] ) ? esc_attr( $this->options['api_code']) : ''
        );
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function dev_key_callback()
    {
        printf(
            '<input type="text" id="dev_key" name="cm_myob_options[dev_key]" value="%s" />',
            isset( $this->options['dev_key'] ) ? esc_attr( $this->options['dev_key']) : ''
        );
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function exo_token_callback()
    {
        printf(
            '<input type="text" id="exo_token" name="cm_myob_options[exo_token]" value="%s" />',
            isset( $this->options['exo_token'] ) ? esc_attr( $this->options['exo_token']) : ''
        );
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function warehouse_id_callback()
    {
        printf(
            '<input type="text" id="warehouse_id" name="cm_myob_options[warehouse_id]" value="%s" />',
            isset( $this->options['warehouse_id'] ) ? esc_attr( $this->options['warehouse_id']) : ''
        );
    }

}

if( is_admin() )
    $my_settings_page = new CMSettingsPage();

$options = get_option( 'cm_myob_options' );

// Base64 encode the name and password for the API
$auth = base64_encode( $options['api_name'] .':'. $options['api_code'] );

global $context;

$context = array(
    'headers' => array(
        "Authorization" => 'Basic ' .$auth,
        "x-myobapi-key" => $options['dev_key'],
        "x-myobapi-exotoken" => $options['exo_token']
    )
);

/**
 * Check the stock levels of items in a wishlist.
 * Update stock levels and allow page to continue loading
 */
function cm_check_wishlist_stock() {
    if ( isset( $_GET['wlid'] ) ) {
        $wishlist = new WC_Wishlists_Wishlist( $_GET['wlid'] );
        $wishlist_items = WC_Wishlists_Wishlist_Item_Collection::get_items( $wishlist->id );
        
        foreach( $wishlist_items AS $hash => $item ) {
            
             //  Get the SKU(s) for the product or its variations.
            $fetch = cm_get_skus( $item['data'] );
            
            //Here we will call the MYOB API to get the stock based on the SKU
            $fetched = cm_get_stock( $fetch );
            
            //  Update stock levels with response
            cm_update_stock( $fetched );
            
        }
    }
}

add_action( 'woocommerce_wishlists_before_wrapper', 'cm_check_wishlist_stock' );

/**
 * Check the stock of an item before the load of an individual product page.
 * Update stock levels and allow page to continue loading
 */
function cm_check_stock() {
    
    global $prods, $post, $woocommerce, $product;
    
    //  Get the SKU(s) for the product or its variations.
    $fetch = cm_get_skus( $product );
    
    //Here we will call the MYOB API to get the stock based on the SKU
    $fetched = cm_get_stock( $fetch );
    
    //  Update stock levels with response
    cm_update_stock( $fetched );
}

add_action( 'woocommerce_before_single_product', 'cm_check_stock', 10 );

/**
 * Get the SKUs of a product (and its variations) from its ID
 */
function cm_get_skus( $product ) {
    
    //  Check to see if product is variable
    if ( $product->product_type == 'variable' ) {
        
        
        //  Get variations of product
        $args = array(
            'post_type'			=> 'product_variation',
            'post_status' 		=> 'publish',
            'posts_per_page' 	=> -1,
            'orderby'			=> 'title',
            'order'				=> 'ASC',
            'post_parent' => $product->id
        );
        
        $variations = get_posts( $args );
        
        //  Loop through to get SKU for each variation
        $i = 0;
        foreach( $variations AS $var ) {
            
            $sku = get_post_meta( $var->ID, '_sku', true );
            
            //Assign to fetch array with ID and SKU
            $fetch[$i] = array(
                'sku' => $sku,
                'ID' => $var->ID
            );
            
            $i++;
        }
        
    } else {
        
        // At Wishlist and Cart level the item is a 'variation' and will have a separate ID to its Variation ID. We need the Variation ID
        if ( $product->product_type == 'variation' ) {
            //  Since product is a variation there is only one call to get the SKU
            $sku = get_post_meta( $product->variation_id, '_sku', true );
            
            //  Create array with single item to streamline functionality
            $fetch[0] = array(
                'sku' => $sku,
                'prod_id' => $product->id,
                'ID' => $product->variation_id
            );
        } else {
            //  Since product is simple there is only one call to get the SKU
            $sku = get_post_meta( $product->id, '_sku', true );
            
            //  Create array with single item to streamline functionality
            $fetch[0] = array(
                'sku' => $sku,
                'ID' => $product->id
            );
        }
        
    }
    
    return $fetch;
}

/**
 * Calls to MYOB ExoNET API to get stock details of item in particular.
 * We are using the 'Free' part of the XML response for the stock available to the website
 */
function cm_get_stock( $products ) {
    // Pull down the headers that we generated at the start of the file
    global $context;
    
    $options = get_option( 'cm_myob_options' );
    
    // We need to access master array so keep count of where we are
    $ind = 0;
    foreach( $products AS $product ) {
        // Searching for item by SKU
        $url = 'https://exo.api.myob.com/stockitem/' . $product['sku'];
        
        // Call API to get response
        $file = wp_remote_get( $url, $context );
        
        if ( $file['response']['code'] == '200' ) {
            // Turn XML response into array to feed data
            $xml = json_decode(json_encode(simplexml_load_string($file['body'])),true);
            
            // Check if StockLevels exists in array. If not then the product is out of stock completely
            if ( isset( $xml['StockLevels'] ) ) {
                
				// We need to check if the result is a multi dimensional array to see if it is in more than one location
				if ( isset( $xml['StockLevels'][0] ) ) {
					
					$wflag = false; // We need to set a flag to check that the location has been found
					foreach( $xml['StockLevels'] as $stockloc ) {
						// We are only looking for the LocationId of the warehouse.
						if ( $stockloc['LocationId'] == $options['warehouse_id'] ) {
							
							$wflag = true;
							
							// Pull the 'Physical' part of the response
							$free = $stockloc['Physical'];
							// Pull the 'Committed' part of the response
							$commit = $stockloc['Committed'];
							
							// Check to make sure the 'Committed' amount is not greater than the 'Physical' amount
							if ( !( $commit > $free ) ) {
								
								// Subtract the 'Committed' stock from the 'Physical' stock to give better indication of stock.
								$free = $free - $commit;
								
							} else {
								// If the 'Comitted' amount is greater then we set to 0 to avoid negative numbers.
								$free = 0;
							}
						}
					}
					
					// Check if flag has been activated and if not expressly set the stock to 0
					if ( !$wflag ) {
						$free = 0;
					}
				} else { // Single dimension array so we can immediately double check location is the warehouse
				
					if ( $xml['StockLevels']['LocationId'] == $options['warehouse_id'] ) {
						
						// Pull the 'Physical' part of the response
						$free = $xml['StockLevels']['Physical'];
						// Pull the 'Committed' part of the response
						$commit = $xml['StockLevels']['Committed'];
						
						// Check to make sure the 'Committed' amount is not greater than the 'Physical' amount
						if ( !( $commit > $free ) ) {
							
							// Subtract the 'Committed' stock from the 'Physical' stock to give better indication of stock.
							$free = $free - $commit;
							
						} else {
							// If the 'Comitted' amount is greater then we set to 0 to avoid negative numbers.
							$free = 0;
						}
						
					} else {
						
						//An absolute check to make sure we're not pulling the wrong information
						$free = 0;
					}
				}
				
            } else {
                // Item is out of stock.
                $free = 0;
            }
            
            // Assign to original array, increase our count
            $products[$ind]['stock'] = $free;
        } else {
             wc_add_notice( sprintf( __( 'The stock for some items may not be correct. Please refresh your page and if this issue persists please contact a site administrator. ', 'woocommerce' ) ), 'error' );
        }
        $ind++;
    }
    
    return $products;
}

/**
 * Update stock in WooCommerce database, including a switch for instock/outofstock depending on stock amount
 * Clear cache/transients and refresh system with new stock amounts
 */
function cm_update_stock( $products ) {
    
    global $wpdb;
    
    // Pulled directly from WC product code. Manually clear transients ready to reset stock amounts.
    delete_transient( 'wc_low_stock_count' );
    delete_transient( 'wc_outofstock_count' );
    
    foreach( $products AS $product ) {
        
        // Update level of stock in database for each item
        $wpdb->update( "$wpdb->postmeta", array( 'meta_value' => $product['stock'] ), array( 'post_id' => $product['ID'], 'meta_key' => '_stock' ) );
        
        // Update in/out of stock identifier
        if ( $product['stock'] > 0 ) {
            $wpdb->update( "$wpdb->postmeta", array( 'meta_value' => 'instock' ), array( 'post_id' => $product['ID'], 'meta_key' => '_stock_status' ) );
        } else if ( $product['stock'] == 0 ) {
            $wpdb->update( "$wpdb->postmeta", array( 'meta_value' => 'outofstock' ), array( 'post_id' => $product['ID'], 'meta_key' => '_stock_status' ) );
        }
        
         // Clear caches
        wp_cache_delete( $product['ID'], 'post_meta' );
        
        // Refresh product's stock. Will update before rest of item is loaded
        do_action( 'woocommerce_product_set_stock', $product['ID'] );
    }
    
   
}