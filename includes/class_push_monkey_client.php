<?php

/* WordPress Check */
if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

require_once( plugin_dir_path( __FILE__ ) . 'class_push_monkey_debugger.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class_push_monkey_cache.php' );

/**
 * API Client
 */
class PushMonkeyClient {

	public $endpointURL;
	public $registerURL;
	public $cartURL;

	/* Public */

	const PLAN_NAME_KEY = 'push_monkey_plan_name_output';

	/**
 	* Calls the sign in endpoint with either an Account Key
 	* or with an API Token + API Secret combo.
	*
	* Returns false on WP errors.
	* Returns an object with the returned JSON.
	* @param string $account_key
	* @param string $api_token
	* @param string $api_secret
	* @return mixed; false if not signed in. 
	*/
	public function sign_in( $account_key, $api_token, $api_secret ) {

		$sign_in_url = $this->endpointURL . '/v2/api/sign_in_v2';
		$args = array( 'body' => array( 
			
			'account_key' => $account_key, 
			'api_token' => $api_token, 	
			'api_secret' => $api_secret,
			'website_url' => site_url()
			) );
		$response = wp_remote_post( $sign_in_url, $args );
		if ( is_wp_error( $response ) ) {
			
			return ( object ) array( 'error' => $response->get_error_message() );
		} else {

			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body );
			$this->d->debug(print_r($output, true));
			return $output;				
		}
		return false;
	}
	
	/**
	 * Get the stats for an Account Key.
	 * @param string $account_key 
	 * @return mixed; false if nothing found; array otherwise.
	 */
	public function get_stats( $account_key ) {
		$stats_api_url = $this->endpointURL . '/stats/api';
		$args = array(
			'body' => array(
				'account_key' => $account_key
			),
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);
		$response = wp_remote_post( $stats_api_url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );
		} else {

			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body ); 
			return $output;
		}
		return false;
	}

	/**
	 * Get the Website Push ID for an Account Key.
	 * @param string $account_key 
	 * @return string; array with error info if an error occured.
	 */
	public function get_website_push_ID( $account_key ) {

		$url = $this->endpointURL . '/v2/api/website_push_id';
		$args = array(
			'body' => array(
				'account_key' => $account_key
			),
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);

		$response = wp_remote_post( $url, $args );

		if( is_wp_error( $response ) ) {

			return ( object ) array( 'error' => $response->get_error_message() );
		} 
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body ); 
		return $output;
	}

	/**
	 * Sends a desktop push notification.
	 * @param string $account_key 
	 * @param string $title 
	 * @param string $body 
	 * @param string $url_args 
	 * @param boolean $custom 
	 */
	public function send_push_notification( $account_key, $title, $body, $url_args, $custom, $segments, $locations, $image = NULL, $postdata = array() ) {

		$url = $this->endpointURL . '/push/V2/send';

		$push_monkey = new PushMonkey();
		$args = array( 
			'account_key' => $account_key,
			'title'				=> $title,
			'body'				=> strip_tags( $body ), 
			'url_args'		=> $url_args,
			'send_to_segments_string' => implode(",", $segments),
			'send_to_locations_string' => implode(",", $locations),
			'image' => $image,
			'one_signal_push' => $push_monkey->pm_one_signal_push(),
		);
		$this->d->debug( print_r( $args, true ) );
		if ( $custom ) {
			$args['custom'] = true;
		}

		if ( ! empty( $postdata ) ) {
			$args = array_merge( $args, $postdata );
		}

		$response = $this->post_with_file( $url, $args, $image );
		if( is_wp_error( $response ) ) {

			$this->d->debug('send_push_notification '.$response->get_error_message());
		} else {

			$this->d->debug( print_r( $response, true) );
		}
	}

	/**
	 * Get the plan name.
	 * @param string $account_key 
	 * @return string; array with error info otherwise.
	 */
	public function get_plan_name( $account_key ) {

		$output = $this->cache->get( self::PLAN_NAME_KEY );
		if ( $output ) {
			
			$this->d->debug('served from cache');
			return (object) $output;
		}

		$url = $this->endpointURL . '/v2/api/get_plan_name';
		$args = array(
			'body' => array(
				'account_key' => $account_key
			),
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);

		$response = wp_remote_post( $url, $args );

		if( is_wp_error( $response ) ) {

			return ( object ) array( 'error' => $response->get_error_message() );
		} 
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body ); 
		$serialized_output = json_decode( $body, true );
		if ( isset( $output->error ) ) {
			
			$this->d->debug('get_plan_name: ' . $output->error);
			return $output->error;
		} else {

			$this->d->debug("not from cache");
			$this->cache->store( self::PLAN_NAME_KEY, $serialized_output );
			return $output;
		}
		return '';
	}

	/**
	 * Get all the segments
	 * @param string $account_key
	 * @return associative array of [id=>string]
	 */
	public function get_segments( $account_key ) {

		$segments_api_url = $this->endpointURL . '/push/v1/segments/' . $account_key;
		$args = array(
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);
		$response = wp_remote_post( $segments_api_url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );
		} else {

			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body, true ); 
			if ( isset( $output["segments"] ) ) {

				if ( count( $output["segments"] ) > 0 ) {

					if ( gettype($output["segments"][0]) == "array" ) {

						return $output["segments"];
					}
				}
			}
		}
		return array();		
	}

	/**
	 * Save a segments
	 * @param string $account_key
	 * @param string $name	 
	 * @return response or error
	 */
	public function save_segment( $account_key, $name ) {

		$url = $this->endpointURL . '/push/v1/segments/create/' . $account_key;
		$args = array(
			'body' => array( 
				'name' => $name,
			),
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			
			return ( object ) array( 'error' => $response->get_error_message() );
		} else {

			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body );
			$this->d->debug(print_r($output, true));
			return $output;				
		}
		return false;
	}

	/**
	 * Delete a segments
	 * @param string $account_key
	 * @param string $id of segment	 
	 * @return response or error
	 */
	public function delete_segment( $account_key, $id ) {

		$url = $this->endpointURL . '/push/v1/segments/delete/' . $account_key;
		$args = array(
			'body' => array( 
				'id' => $id,
			),
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);
		$this->d->debug($url);
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			
			return ( object ) array( 'error' => $response->get_error_message() );
		} else {

			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body );
			$this->d->debug(print_r($output, true));
			return $output;				
		}
		return false;		
	}

	/**
	 * Retrieve the status of a welcome message
	 * @param string $account_key
	 * @return associative array of JSON response
	 */
	public function get_welcome_message_status( $account_key ) {

		$url = $this->endpointURL . '/v2/api/welcome_notification_status/' . $account_key;
		$args = array(
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);
		$response = wp_remote_post( $url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true ); 
		if ( empty( $output ) ) {

			return ( object ) array( 'error' => 'empty' );			
		}
		return $output;
	}

	/**
	 * Retrieve the status of a welcome message
	 * @param string $account_key
	 * @return associative array of JSON response
	 */
	public function get_custom_prompt( $account_key ){

		$url = $this->endpointURL . '/v2/api/custom_prompt/' . $account_key;
		$args = array(
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);
		$response = wp_remote_post( $url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true ); 
		if ( empty( $output ) ) {

			return ( object ) array( 'error' => 'empty' );			
		}
		return $output;
	}

	/**
	 * Update the welcome message info
	 * @param string $account_key
	 * @param boolean $enabled
	 * @param string $message
 	 * @param string $title
	 * @return boolean. True if operation finished successfully.
	 */
	public function update_custom_prompt( $account_key, $enabled, $title, $message ) {

		$url = $this->endpointURL . '/v2/api/custom_prompt/' . $account_key . '/update';
		$args = array(
			'body' => array( 
				'custom_prompt_message' => $message,
				'custom_prompt_title' => $title
			),
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);
		if ( $enabled ) {
			$args['body']['enabled'] = true;
		}
		$response = wp_remote_post( $url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true ); 
		if ( isset( $output["response"] ) ) {

			if ( $output['response'] == "ok" ) {

				return true;
			}
		}
		return false;		
	}

	/**
	 * Retrieve locations stored for this account key
	 * @param string $account_key
	 * @return associative array of JSON response	 
	 */
	public function get_locations( $account_key ) { 

		$url = $this->endpointURL . '/v2/api/locations/' . $account_key;
		$args = array(
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);
		$response = wp_remote_post( $url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true ); 
		if ( empty( $output ) ) {

			return ( object ) array( 'error' => 'empty' );			
		}
		return $output;
	}

	/**
	 * Update the welcome message info
	 * @param string $account_key
	 * @param boolean $enabled
	 * @param string $message
	 * @return boolean. True if operation finished successfully.
	 */
	public function update_welcome_message( $account_key, $enabled, $message ) {

		$url = $this->endpointURL . '/v2/api/update_welcome_notification/' . $account_key;
		$args = array(
			'body' => array( 
				'message' => $message
			),
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);		
		if ( $enabled ) {
			$args['body']['enabled'] = true;
		}
		$this->d->debug(print_r($args, true));
		$response = wp_remote_post( $url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true ); 
		$this->d->debug(print_r($output, true));				
		if ( isset( $output["status"] ) ) {

			if ( $output['status'] == "ok" ) {

				return true;
			}
		}
		return false;
	}

	public function update_woo_cards_status( $account_key, $status_feature_key, $status_feature_value ){

      $url=$this->endpointURL.'/woo/v1/api/services?account_key='.$account_key;
		$args = array(
			'body' => array(
				$status_feature_key => $status_feature_value
			),
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);
		$response = wp_remote_post( $url, $args );
       if( is_wp_error( $response ) ) {
			$this->d->debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
	if ( is_wp_error( $response ) ) {
			
			return ( object ) array( 'error' => $response->get_error_message() );
		} else {
			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body );
			$this->d->debug(print_r($output, true));
			return $output;				
		}
		return false;	
	}

public function update_abandoned_cart_settings( $account_key,$status, $abandoned_cart_delay, $second_abandoned_cart_delay, $second_abandoned_cart_delay_status,$third_abandoned_cart_delay,$third_notification_sent_after_status,$abandoned_cart_notification_title,$abandoned_cart_message,$image_path, $images ) {
	
	$url=$this->endpointURL.'/woo/v1/api/abandoned-cart/?account_key='.$account_key;
	$args = array( 
		'method'  => 'PATCH',
		    'active' => $status,
			'first_notification_sent_after' => $abandoned_cart_delay,
			'second_notification_sent_after' => $second_abandoned_cart_delay,
		    'second_notification_sent_after_status' => $second_abandoned_cart_delay_status,
	        'third_notification_sent_after' => $third_abandoned_cart_delay,
	        'third_notification_sent_after_status' => $third_notification_sent_after_status,
	        'notification_title'=> $abandoned_cart_notification_title,
			'notification_message' => $abandoned_cart_message,
			'notification_image' => $images
		);	
		$response = $this->post_with_file( $url, $args, $image_path, $images );
	
	if ( is_wp_error( $response ) ) {
			
			return ( object ) array( 'error' => $response->get_error_message() );
		} else {

			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body );
			$this->d->debug(print_r($output, true));
			return $output;				
		}
		return false;		
	}

	public function update_backinstock_settings( $account_key,$status, $notification_title, $notification_message, $pop_up_title, $pop_up_message, $button_text,$success_message,$colorvalue ) {
        $url=$this->endpointURL.'/woo/v1/api/back-in-stock/?account_key='.$account_key;
        $args = array(
        	'body' => array( 	
			    'active' => $status,
				'notification_title' => $notification_title,
				'notification_message' => $notification_message,
				'pop_up_title' => $pop_up_title,
				'pop_up_message' => $pop_up_message,
				'button_text' => $button_text,
				'success_message' => $success_message,
				'color' => $colorvalue
			),
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
        );
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			
			return ( object ) array( 'error' => $response->get_error_message() );
		} else {

			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body );
			$this->d->debug(print_r($output, true));
			return $output;				
		}
		return false;		
	}
	
	public function update_pricedrop_settings( $account_key,$status,$price_drop_notification_title,$price_drop_notification_message,$price_drop_popup_title,$price_drop_popup_message,$price_drop_popup_button,$price_drop_popup_confirmation,$color ) {

    $url=$this->endpointURL.'/woo/v1/api/price-drop/?account_key='.$account_key;
    $args = array(
    	'body' => array( 	
		    'active' => $status,
			'notification_title' => $price_drop_notification_title,
			'notification_message' => $price_drop_notification_message,
			'pop_up_title' => $price_drop_popup_title,
			'pop_up_message' => $price_drop_popup_message,
			'button_text' => $price_drop_popup_button,
			'success_message' => $price_drop_popup_confirmation,
			'color' => $color
		),
		'headers' => array(
			'push-monkey-api-key' => base64_decode( $this->rest_key ),
		),
    );
		$response = wp_remote_post( $url, $args );
	if ( is_wp_error( $response ) ) {
			
			return ( object ) array( 'error' => $response->get_error_message() );
		} else {

			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body );
			$this->d->debug(print_r($output, true));
			return $output;				
		}
		return false;		
	}
	
	public function update_productreview_settings( $account_key,$status,$review_reminder_delay,$review_reminder_notification_title,$review_reminder_message) {
		
  $url=$this->endpointURL.'/woo/v1/api/review-reminder/?account_key='.$account_key;
	  $args = array(
	  		'body' => array( 	
			 	'active' => $status,
				'notification_delay' => $review_reminder_delay,
				'notification_title' => $review_reminder_notification_title,
				'notification_message' => $review_reminder_message,
			),
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
	  	);
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			
			return ( object ) array( 'error' => $response->get_error_message() );
		} else {

			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body );
			$this->d->debug(print_r($output, true));
			return $output;				
		}
		return false;		
	}

public function update_welcomediscount_settings( $account_key,$status, $welcome_notification_message, $welcome_notification_link,$image_path, $images ) {
    $url=$this->endpointURL."/woo/v1/api/welcome-discount/?account_key=".$account_key;
		$args = array( 
		 	'active' => $status,
			'custom_message' => $welcome_notification_message,
			'welcome_link' => $welcome_notification_link,
			'welcome_image' => $images
		);
		$response = $this->post_with_file( $url, $args, $image_path, $images );
		if ( is_wp_error( $response ) ) {
			
			return ( object ) array( 'error' => $response->get_error_message() );
		} else {

			$body = wp_remote_retrieve_body( $response );
			$output = json_decode( $body );
			$this->d->debug(print_r($output, true));
			return $output;				
		}
		return false;		
	}

/**
	 * Retrieve the WooCommerce status setting
	 * @param string $account_key
	 * @return associative array of JSON response.
	 */
	public function get_woo_status_setting( $account_key ) {
		if ( ! $account_key ) {
			return;
		}
		//$url = $this->endpointURL . '/v2/api/locations/' . $account_key;
		$url=$this->endpointURL.'/woo/v1/api/services?account_key='.$account_key;
		$args = array(
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);
		$response = wp_remote_get( $url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true ); 
		return $output;
	
	}
/**
	 * Retrieve the WooCommerce setting
	 * @param string $account_key
	 * @return associative array of JSON response.
	 */
	public function get_abandoned_cart_woo_settings( $account_key ) {
		//Checking account key exists or not.
		if ( ! $account_key ) {
			return; 
		}
		$args = array(
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);
		$url=$this->endpointURL.'/woo/v1/api/abandoned-cart/?account_key='.$account_key;
		$response = wp_remote_get( $url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true ); 
		return $output;
	
	}
	/**
	 * Retrieve the WooCommerce setting
	 * @param string $account_key
	 * @return associative array of JSON response.
	 */
	public function get_back_in_stock_woo_settings( $account_key ) {
		//Checking account key exists or not.
		if ( ! $account_key ) {
			return; 
		}
		$args = array(
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);
	    $url=$this->endpointURL.'/woo/v1/api/back-in-stock/?account_key='.$account_key;
		$response = wp_remote_get( $url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true ); 
		return $output;
	
	}
public function get_price_drop_woo_settings( $account_key ) {
		//Checking account key exists or not.
		if ( ! $account_key ) {
			return; 
		}
		$args = array(
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);
	    $url=$this->endpointURL.'/woo/v1/api/price-drop/?account_key='.$account_key;
		$response = wp_remote_get( $url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true );    
		return $output;
	
	}
	public function get_product_review_woo_settings( $account_key ) {
		//Checking account key exists or not.
		if ( ! $account_key ) {
			return; 
		}
		$args = array(
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);
		$url=$this->endpointURL.'/woo/v1/api/review-reminder/?account_key='.$account_key;
		$response = wp_remote_get( $url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true );   
		return $output;
	
	}
	public function get_wecome_notification_woo_settings( $account_key ) {
		//Checking account key exists or not.
		if ( ! $account_key ) {
			return; 
		}
		$args = array(
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);
		$url=$this->endpointURL."/woo/v1/api/welcome-discount/?account_key=".$account_key;
		$response = wp_remote_get( $url, $args );
		if( is_wp_error( $response ) ) {

		$this->d->debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true ); 
		return $output;
	
	}
	//woo Hooks Settings
	/**
 	 * Creates cart with the API token and cart ID
 	 * Returns false on WP errors.
	 * Returns true.
	 * @param string $api_token
	 * @param string $cart_id
	 * @return mixed; false if nothing found; array otherwise. 
	 */
	public function create_cart( $cart_id, $api_token ) {
	
		$cart_url = $this->cartURL;
		$args = array( 
			'headers'   => array(
				'Content-Type' => 'application/json; charset=utf-8',
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		  	'body'      => json_encode( array( 'token' => $api_token, 'cart_id' => $cart_id ) ) 
		);
		$response = wp_remote_post( $cart_url, $args );
	
		if ( is_wp_error( $response ) ) {
			
			return ( object ) array( 'error' => $response->get_error_message() );
		} 
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true );
		if ( isset( $output["response"] ) ) {

			if ( $output['response'] == "ok" ) {
				$this->d->debug( 'Cart created successfully.' );
				return true;
			}
		}
		$this->d->debug( 'Error creating cart.' );		
		return false;
	}

	/**
 	 * Updates cart with the API token and cart ID
	 * Returns false on WP errors.
	 * Returns true.
	 * @param string $api_token
	 * @param string $cart_id
	 * @return mixed; false if nothing found; array otherwise. 
	 */
	public function update_cart( $cart_id, $api_token ) {

		$this->d->debug( "update cart" );
		$cart_url = $this->cartURL;
		$args = array( 
			'method'  => 'PUT',
			'headers' => array(
				'Content-Type' => 'application/json; charset=utf-8',
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
			'body'    => json_encode(array( 'token' => $api_token, 'cart_id' => $cart_id ) ) 
		);
		$response = wp_remote_request( $cart_url, $args );
	
		if ( is_wp_error( $response ) ) {
			
			return ( object ) array( 'error' => $response->get_error_message() );
		} 
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body,true );
		$this->d->debug( print_r( $output, true ) );
		if ( isset( $output["response"] ) ) {

			if ( $output['response'] == "ok" ) {

				return true;
			}
		}
		return false;
	}
/**
 	 * publish_product with the API token and Product ID
	 * Returns false on WP errors.
	 * Returns true.
	 * @param string $api_token
	 * @param string $productId
	 * @return mixed; false if nothing found; array otherwise. 
	 */
	public function product( $product_id, $api_token,$product_name,$product_price,$product_stock_status ) {
        $productURL = $this->productURL;
		$args = array( 
			'headers'   => array(
				'Content-Type' => 'application/json; charset=utf-8',
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		  	'body'    => json_encode(array( 'token' => $api_token, 'product_id' => $product_id, 'product_name' => $product_name,'product_price' => $product_price,'product_stock_status' => $product_stock_status) ) 
		);
$debug='/var/www/html/wp-content/plugins/debug.log';
          error_log($product_id, 3, $debug);
		$response = wp_remote_post( $cart_url, $args );
		if ( is_wp_error( $response ) ) {
			
			return ( object ) array( 'error' => $response->get_error_message() );
		} 
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true );
		if ( isset( $output["response"] ) ) {

			if ( $output['response'] == "ok" ) {

				$this->d->debug( 'Cart created successfully.' );
				return true;
			}
		}
		$this->d->debug( 'Error creating cart.' );		
		return false;
	}
// /**
//  	 * Updates Product with the API token and Product ID
// 	 * Returns false on WP errors.
// 	 * Returns true.
// 	 * @param string $api_token
// 	 * @param string $product_id
// 	 * @return mixed; false if nothing found; array otherwise. 
// 	 */
// 	public function update_product( $product_id, $api_token,$product_name,$product_price,$product_stock_status ) {

// 		$this->d->debug( "update product" );
// 		$productURL = $this->productURL;
// 		$args = array( 
// 			'method'  => 'PUT',
// 			'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ),
// 			'body'    => json_encode(array( 'token' => $api_token, 'product_id' => $product_id, 'product_name' => $product_name,'product_price' => $product_price,'product_stock_status' => $product_stock_status) ) 
// 		);
// 		$response = wp_remote_request( $productURL, $args );
		
// 		if ( is_wp_error( $response ) ) {
			
// 			return ( object ) array( 'error' => $response->get_error_message() );
// 		} 
// 		$body = wp_remote_retrieve_body( $response );
// 		$output = json_decode( $body,true );
// 		$this->d->debug( print_r( $output, true ) );
// 		if ( isset( $output["response"] ) ) {

// 			if ( $output['response'] == "ok" ) {

// 				return true;
// 			}
// 		}
// 		return false;
// 	}
	
	/**
	 * Retrieve the Google Sender ID setting from our back-end
	 * @param string $account_key
	 * @return associative array of JSON response.
	 */
	public function get_sender_id( $account_key ) {

		$url = $this->endpointURL . '/push/v1/gcm_sender_id';
		$args = array(
			'body' => array( 
				'account_key' => $account_key
			),
			'headers' => array(
				'push-monkey-api-key' => base64_decode( $this->rest_key ),
			),
		);		
		$response = wp_remote_post( $url, $args );
		if( is_wp_error( $response ) ) {

			$this->d->debug( $response->get_error_message() );
			return ( object ) array( 'error' => $response->get_error_message() );			
		}
		$body = wp_remote_retrieve_body( $response );
		$output = json_decode( $body, true ); 
		if ( empty( $output ) ) {

			return array( 'error' => 'empty' );			
		}
		return $output;
	}

	/* Private */

	function __construct( $endpoint_url ) {

		$this->endpointURL = $endpoint_url;
		$this->registerURL = $endpoint_url.'/v2/register';
		$this->cartURL = $endpoint_url.'/magento/v1/cart';
		//$this->cartURL = 'https://demo1886901.mockable.io/api/cartURL';
        $this->productURL ='https://demo1886901.mockable.io/api/productURL';
		
		$this->d = new PushMonkeyDebugger();
		$this->cache = new PushMonkeyCache();
	}

	function post_with_file( $url, $data, $file_path, $filename = NULL ) {

		$boundary = wp_generate_password( 24 );
		$headers  = array(
			'push-monkey-api-key' => base64_decode( $this->rest_key ),
			'Content-Type' => 'multipart/form-data; boundary=' . $boundary
		);
		$payload = '';
		// First, add the standard POST fields:
		foreach ( $data as $name => $value ) {

			$payload .= '--' . $boundary;
			$payload .= "\r\n";
			$payload .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n";
			$payload .= $value;
			$payload .= "\r\n";
		}
		// Upload the file
		if ( $file_path ) {

			$payload .= '--' . $boundary;
			$payload .= "\r\n";
			if ( $filename ) {

				$payload .= 'Content-Disposition: form-data; name="' . 'image' . '"; filename="' . basename( $filename ) . '"' . "\r\n";
			} else {

				$payload .= 'Content-Disposition: form-data; name="' . 'image' . '"; filename="' . basename( $file_path ) . '"' . "\r\n";				
			}
			//        $payload .= 'Content-Type: image/jpeg' . "\r\n";
			$payload .= "\r\n";
			$payload .= file_get_contents( $file_path );
			$payload .= "\r\n";
		}
		$payload .= '--' . $boundary . '--';
		$response = wp_remote_post( $url,
			array(
				'headers'    => $headers,
				'body'       => $payload,
			)
		);
		return $response;
	}
}
