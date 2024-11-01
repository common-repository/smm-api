<?php
if ( !defined( 'ABSPATH' ) || !defined( 'SMMS_SMAPI_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements SMAPI_Subscription Cart Class
 *
 * @class   SMAPI_Subscription_Cart
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
 */
if ( !class_exists( 'SMAPI_Api' ) ) {

class SMAPI_Api{
    
    
      public $api_link = ''; // API URL
      public $api_quantity = ''; // Your API key
      public $smm_service_id = ''; // check for API for multiple servers
      public $api_server = ''; // multiple servers post ID is taken

      public $api_server_url = ''; // server parameters for individual order processing for each server
      public $api_server_parameter = array();
      
      


	public function __construct($arg){  //initializing array data

				$this->api_server_parameter = $this->get_all_api_parameter($arg); 
				
				
							}
		// Get Post deatils for API servers		
	Public function get_all_api_parameter($server_name){
	    //global $post;
		$this->api_server = $server_name;
	    $Api_server_handle = array();
	    $post_data = get_post($server_name);
	    $Api_server_handle['server_api_add_url'] = 
	           isset($post_data->post_title) ? $post_data->post_title:'';
	    $Api_server_handle['server_api_key'] = 
	           isset($post_data->post_content) ? $post_data->post_content:'';
        $Api_server_handle['server_api_enable'] = 
               isset($post_data->post_status) ? $post_data->post_status : '';
        $meta_value =   get_post_meta( $this->api_server, '_parameter_handle', true );
        $meta_value_array = json_decode($meta_value, true);
        $Api_server_handle['server_link'] = $meta_value_array['api_link_handle'];
        $Api_server_handle['server_key'] = $meta_value_array['api_key_handle'];
        $Api_server_handle['server_quantity'] = $meta_value_array['api_quantity_handle'];
        $Api_server_handle['server_service'] = $meta_value_array['api_service_handle'];
        $Api_server_handle['server_response'] = $meta_value_array['api_order_response_handle'];
        $Api_server_handle['server_error'] = $meta_value_array['api_error_response_handle'];
        $Api_server_handle['server_retrieve_status_query'] = $meta_value_array['api_retrieve_status_query'];
        $Api_server_handle['server_status_order_handle'] = $meta_value_array['api_status_order_handle'];
        $Api_server_handle['server_enable_api'] = $meta_value_array['enable_api'];
        $Api_server_handle['server_retrieve_status_check'] = 'https://seoclerks.in/api/check/';
        
                        
	    
	    return $Api_server_handle;
	    
	}
    public function order($link, $service_id, $quantity) {                  
          
          // Add order
          $this->api_server_url = 
          $this->api_server_parameter['server_api_add_url'];
            return json_decode($this->connect(array(
            $this->api_server_parameter['server_key'] 
            => $this->api_server_parameter['server_api_key'],
            
            'action' => 'add',
            $this->api_server_parameter['server_link'] => $link,
            $this->api_server_parameter['server_service'] => $service_id,
	        $this->api_server_parameter['server_quantity'] => $quantity
            )));
          }
    
    public function status($order_id) { // Get status, remains
            $this->api_server_url = 
            $this->api_server_parameter['server_retrieve_status_query'];
		
            return json_decode($this->connect(array(
            $this->api_server_parameter['server_key'] => 
            $this->api_server_parameter['server_api_key'],
            'action' => 'status',
          $this->api_server_parameter['server_status_order_handle'] => $order_id
          )));
          }
    public function smm_url_check_status($order_id) { // Get status
            $this->api_server_url = 
            $this->api_server_parameter['server_retrieve_status_check'];
		
            return json_decode($this->connect(array(
            'action' => 'status_check',
            'check' => $order_id
          )));
          }
	public function multi_status($order_ids) { // get order status
	
          $this->api_server_url = 
          $this->api_server_parameter['server_retrieve_status_query'];
        return json_decode($this->connect(array(
            $this->api_server_parameter['server_key'] 
            => $this->api_server_parameter['server_api_key'],
            'action' => 'status',
            $this->api_server_parameter['server_status_order_handle'] => implode(",", (array)$order_ids)
        )));
    }
	public function services() { // get services
			$this->api_server_url = 
			$this->api_server_parameter['server_api_add_url'];
				
        return json_decode($this->connect(array(
            $this->api_server_parameter['server_key'] 
            => $this->api_server_parameter['server_api_key'],
            'action' => 'services'
        )));
    }
      
      private function connect($post) {
        
          $httpcode = wp_remote_post( $this->api_server_url, array(
                        'method'      => 'POST',
                        'timeout'     => 45,
                        'redirection' => 5,
                        'httpversion' => '1.0',
                        'blocking'    => true,
                        'headers'     => array(),
                        'body'        => $post,
                        'cookies'     => array()
                                        )
            );
             
          $get_details_of_request = "server arg = ".esc_url($this->api_server_url)."?"." return data = ".esc_html(serialize($httpcode))."body data = ";
        // file_put_contents(plugin_dir_path( __FILE__ )."check.txt", $get_details_of_request );
                if ( is_wp_error( $httpcode ) ) {
                $error_message =  wp_kses_post($httpcode->get_error_message());
                $myarr = array('Error from API HTTP CALL' => $error_message, 'result' => $get_details_of_request );

                $result = wp_json_encode($myarr, JSON_FORCE_OBJECT);
                }
          $result = wp_remote_retrieve_body( $httpcode );
          
          return $result;
          }
 }
}//End of class