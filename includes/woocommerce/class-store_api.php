<?php
/**
 * Push monkey store API.
 *
 * @package WordPress
 * @subpackage Push_Monkey
 */

if ( ! class_exists( 'PM_Store_API' ) ) {

	/**
	 * Declare `PM_Store_API` class.
	 */
	class PM_Store_API {

		/**
		 * Push monkey account key.
		 *
		 * @var $account_key
		 */
		private $account_key = null;

		/**
		 * API URL.
		 *
		 * @var $api_url
		 */
		private $api_url = 'https://getpushmonkey.com/woo/v1/hook/%slug%/';

		/**
		 * Rest key.
		 *
		 * @var $api_rest_key
		 */
		private $api_rest_key = '';

		/**
		 * Calling class construct.
		 */
		public function __construct() {
			$pushmonkey = new PushMonkey();
			$this->account_key = $pushmonkey->account_key();
			$this->api_rest_key = $pushmonkey->push_monkey_get_api_reset_key();
			// If check is valid account key OR not.
			if ( $this->account_key ) {
				// If check WooCommerce plugin activate OR not.
				if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
					add_action( 'woocommerce_thankyou', array( $this, 'push_monkey_order_after' ) );
					add_action( 'woocommerce_add_to_cart', array( $this, 'push_monkey_cart_api_update' ) );
					add_action( 'woocommerce_update_cart_action_cart_updated', array( $this, 'push_monkey_cart_api_update' ) );
					add_action( 'woocommerce_cart_item_removed', array( $this, 'push_monkey_cart_api_update' ) );
					add_action( 'woocommerce_cart_item_restored', array( $this, 'push_monkey_cart_api_update' ) );
					add_action( 'woocommerce_checkout_create_order', array( $this, 'push_monkey_checkout_create_order' ), 10, 2 );
					add_action( 'woocommerce_cart_is_empty', array( $this, 'push_monkey_destroy_cart_session' ) );
					// Add / Update product.
					add_action( 'woocommerce_update_product', array( $this, 'push_monkey_product_request' ) );
					add_filter( 'woocommerce_settings_tabs_array', array( $this, 'push_monkey_settings_tab' ), 99 );
					add_action( 'woocommerce_settings_tabs_push_monkey', array( $this, 'push_monkey_get_settings_products' ) );
					add_action( 'wp_ajax_push_monkey_products_sync_process', array( $this, 'push_monkey_send_exists_products' ) );
					if ( get_option( '_pm_total_synced', 0 ) <= 0 ) {
						add_action( 'admin_notices', array( $this, 'push_monkey_product_sync_notice' ) );
					}
				}
			}
		}

		/**
		 * Update cart data.
		 */
		public function push_monkey_cart_api_update() {
			add_action( 'woocommerce_cart_updated', array( $this, 'push_monkey_cart_data' ) );
		}

		/**
		 * WooCommerce create/update cart data.
		 */
		public function push_monkey_cart_data() {
			$cart_session = WC()->session->get( 'pm_cart_token' );
			$cart_contents = WC()->cart->get_cart_contents();
			if ( empty( $cart_session ) ) {
				$cart_session = WC()->session->set( 'pm_cart_token', bin2hex( random_bytes( 20 ) ) );
				$cart_session = WC()->session->get( 'pm_cart_token' );
				wc_setcookie( '_pm_cart_token', $cart_session, strtotime( '+1 month' ) );
			}
			if ( ! empty( $cart_session ) && ! empty( $cart_contents ) ) {
				$cart_contents = reset( $cart_contents );
				$total = WC()->cart->get_subtotal();
				// Send update cart request.
				$send_cart_request = $this->push_monkey_send_api_request( $this->get_api_url( 'cart' ),
					array(
						'cart_id' => $cart_session,
						'abandoned' => "true",
						'first_product_handle' => (string) $cart_contents['data']->get_id(),
						'total_value' => $total,
					)
				);
			}
		}

		/**
		 * WooCommerce order after get order data.
		 *
		 * @param int $order_id Order ID.
		 */
		public function push_monkey_order_after( $order_id ) {
			// If check order ID exist OR not.
			if ( ! $order_id ) {
				return;
			}
			// Get cart data.
			$order = new WC_Order( $order_id );
			$items = $order->get_items();
			$financial_status = $order->get_status();
			$cart_id = $order->get_meta( 'pm_cart_token' );
			// Send update cart request.
			$send_order_request = $this->push_monkey_send_api_request( $this->get_api_url( 'order' ),
				array(
					'order_id' => $order_id,
					'cart_id' => $cart_id,
					'financial_status' => $financial_status,
				)
			);
			// Clear token
			setcookie( '_pm_cart_token', null, -1, COOKIEPATH );
		}

		/**
		 * Get API url.
		 *
		 * @param  string $slug API endpoint url.
		 * @return string API url.
		 */
		private function get_api_url( $slug = '' ) {
			$url = str_replace( '%slug%', $slug, $this->api_url );
			$url = add_query_arg( 'account_key', $this->account_key, $url );
			return $url;
		}

		/**
		 * Push monkey call API endpoints.
		 *
		 * @param string $url API Url.
		 * @param array $data Data
		 * @return array
		 */
		private function push_monkey_send_api_request( $url = '', $data = array() ) {
			$pm_api = wp_remote_post( $url,
				array(
					'body' => $data,
					'headers' => array(
						'push-monkey-api-key' => base64_decode( $this->api_rest_key ),
					),
				)
			);
			$response = wp_remote_retrieve_body( $pm_api );
			// If check api errors.
			if ( ! is_wp_error( $pm_api ) ) {
				$response = wp_remote_retrieve_body( $pm_api );
				$response = json_decode( $response );
				return $response;
			}
			return [];
		}

		/**
		 * Destroy cart session data.
		 */
		public function push_monkey_destroy_cart_session() {
			// Clear cart token.
			WC()->session->set( 'pm_cart_token', null );
			setcookie( '_pm_cart_token', null, -1, COOKIEPATH );
		}

		/**
		 * Checkout create order.
		 * @param object $order Order Data
		 * @param object|array $data Data
		 */
		public function push_monkey_checkout_create_order( $order, $data ) {
			$pm_cart_token = WC()->session->get( 'pm_cart_token' );
			$order->update_meta_data( 'pm_cart_token', $pm_cart_token );
		}

		/**
		 * Update WooCommerce Product.
		 * @param int $product_id Product ID.
		 */
		public function push_monkey_product_request( $product_id ) {
			$data = $this->push_monkey_get_product_data( $product_id );
			// Send update cart request.
			$send_product_request = $this->push_monkey_send_api_request( $this->get_api_url( 'product' ), $data );
		}

		/**
		 * Get product data by product ID.
		 *
		 * @param int $product_id Product ID.
		 * @return Product data.
		 */
		public function push_monkey_get_product_data( $product_id = 0 ) {
			// Get product data.
			$product = wc_get_product( $product_id );
			$pm_store_token = get_option( 'pm_store_token', false );
			$default_data = array(
				'product_id' => 0,
				'product_url' => '',
				'price' => 0,
				'image_url' => '',
				'quantity' => 0,
				'title' => '',
				'handle' => '',
				'variants' => array(
					'variant_id' => 0,
					'quantity' => 0,
				),
			);
			$data = array(
				'product_id' => $pm_store_token ? $pm_store_token . '_' . $product_id : $product_id,
				'price' => $product->get_price(),
				'image_url' => get_the_post_thumbnail_url( $product_id ),
				'quantity' => $product->get_stock_quantity(),
				'title' => get_the_title( $product_id ),
				'product_url' => get_the_permalink( $product_id ),
				'handle' => $product->get_sku(),
			);
			$data = wp_parse_args( array_filter( $data ), $default_data );
			// Get available variations.
			if ( $product->is_type( 'variable' ) ) {
				$variation_attributes = $product->get_available_variations();
				$product_variation = array();
				if ( ! empty( $variation_attributes ) ) {
					foreach ( $variation_attributes as $variation ) {
						$product_variation[] = array(
							'variant_id' => $variation['variation_id'],
							'quantity' => $variation['max_qty'],
						);
					}
				}
				if ( ! empty( $product_variation ) ) {
					$data['variants'] = $product_variation;
				}
			}
			return $data;
		}

		/**
		 * Push monkey send exits product.
		 */
		public function push_monkey_send_exists_products() {
			$paged = isset( $_POST['page'] ) ? $_POST['page'] : 1;
			$total_synced = 0;
			// Update product process.
			$store_exists = get_option( 'pm_store_token', false );
			$total_synced = 0;
			if ( $paged > 1 ) {
				$total_synced = get_option( '_pm_total_synced', 0 );
			}
			if ( $store_exists ) {
				$all_product_ids = get_posts(
					array(
						'fields'          => 'ids',
						'posts_per_page'  => 5,
						'post_type' => 'product',
						'paged' => $paged,
						//'offset' => $total_synced,
					)
				);
				// If check array empry OR not.
				if ( ! empty( $all_product_ids ) ) {
					$products = array();
					foreach ( $all_product_ids as $product_id ) {
						$products[] = $this->push_monkey_get_product_data( $product_id );
					}
					// Send update cart request.
					$send_product_request = $this->push_monkey_send_api_request( $this->get_api_url( 'product/bulk' ), array( 'products' => json_encode( $products ) ) );
					// Update synced products.
					$total_synced += count( $all_product_ids );
					update_option( '_pm_total_synced', $total_synced );
					// Send ajax continue response.
					wp_send_json(
						array(
							'result' => 1,
							'status' => 'continue',
							'next_page' => $paged + 1,
							'total_synced' => $total_synced,
							'api_update' => $send_product_request,
						)
					);
				} else {
					// Send ajax end response.
					wp_send_json(
						array(
							'result' => 1,
							'status' => 'end',
						)
					);
				}
			} else {
				wp_send_json(
					array(
						'result' => 0,
					),
					403
				);
			}
			die();
		}

		/**
		 * Push monkey setting sections.
		 *
		 * @param array $sections Setting sections.
		 * @return Setting sections
		 */
		public function push_monkey_settings_tab( $settings_tabs ) {
			$settings_tabs['push_monkey'] = 'Push Monkey';
			return $settings_tabs;
		}

		/**
		 * WooCommerce setting.
		 */
		public function push_monkey_get_settings_products() {
			echo '<span class="spinner sync-product" style="position: absolute;"></span>';
			$total_synced = get_option( '_pm_total_synced', 0 );
			$button_atrr = array();
			$total_empty = empty( $total_synced ) ? false : true;
			if ( $total_empty ) {
				$button_atrr = array(
					'disabled' => true,
				);
			}
			submit_button( 'Sync Products', 'button', 'push-monkey-sync-products', false, $button_atrr );
			$total_synced = $total_synced ? $total_synced : 0;
			$message = $total_synced . ' Product Synced';
			if ( false === $total_empty ) {
				echo '<p class="total-synced">' . $message .'</p>';
			}
		}

		/**
		 * Show product sync notice.
		 */
		public function push_monkey_product_sync_notice() { ?>
			<div class="notice notice-warning is-dismissible">
				<p>Please sync your products to Push Monkey to effectively work the push notification. <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'wc-settings', 'tab' => 'push_monkey' ), admin_url( 'admin.php' ) ) ); ?>" class="button button-primary">Sync Products</a><p>
			</div>
			<?php
		}
	}
	$pm_store_api = new PM_Store_API();
}
