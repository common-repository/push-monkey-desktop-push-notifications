<?php

/* WordPress Check */
if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

require_once( plugin_dir_path( __FILE__ ) . 'class_push_monkey_client.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class_push_monkey_ajax.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class_push_monkey_debugger.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class_pushmonkeywoomodel.php');
require_once( plugin_dir_path( __FILE__ ) . './controllers/class_push_monkey_review_notice_controller.php' );
require_once( plugin_dir_path( __FILE__ ) . '../models/class_push_monkey_banner.php' );
require_once( plugin_dir_path( __FILE__ ) . '../models/class_push_monkey_notification_config.php' );
require_once( plugin_dir_path( __FILE__ ) . '../models/class_push_monkey_review_notice.php' );
require_once( plugin_dir_path( __FILE__ ) . 'woocommerce/class-store_api.php' );

/**
 * Main class that connects the WordPress API
 * with the Push Monkey API
 */
class PushMonkey {

	/* Public */

	public $endpointURL;
	public $apiClient;

	/**
	 * Hooks up with the required WordPress actions.
	 */
	public function run() {
		$this->add_actions();
	}
	/**
	 * Checks if an Account Key is stored.
	 * @return boolean
	 */
	public function has_account_key() {

		if( $this->account_key() ) {

			return true;
		}
		return false;
	}

	/**
	 * Returns the stored Account Key.
	 * @return string - the Account Key
	 */
	public function account_key() {

		$account_key = get_option( self::ACCOUNT_KEY_KEY, '' );
		if( ! $this->account_key_is_valid( $account_key ) ) {

			return NULL;
		}
		return $account_key;
	}

	/**
	 * Checks if an Account Key is valid.
	 * @param string $account_key - the Account Key checked.
	 * @return boolean
	 */
	public function account_key_is_valid( $account_key ) {

		if( ! strlen( $account_key ) ) {

			return false;
		}
		return true;
	}

	/**
	 * Checks if a user is signed in.
	 * @return boolean
	 */
	public function signed_in() {

		return get_option( self::USER_SIGNED_IN );
	}

	/**
	 * Signs in a user with an Account Key or a Token-Secret combination.
	 * @param string $account_key
	 * @param string $api_token
	 * @param string $api_secret
	 * @return boolean
	 */
	public function sign_in( $account_key, $api_token, $api_secret ) {

		delete_option( PushMonkeyClient::PLAN_NAME_KEY );
		$response = $this->apiClient->sign_in( $account_key, $api_token, $api_secret );
		$unique_string = $this->push_monkey_api_reset_key( $response->account_key );
		if ( isset( $response->signed_in ) ) {

			if ( $response->signed_in ) {

				// Create service worker file.
				if ( is_ssl() ) {
					$this->push_monkey_service_worker_file_create( $response->account_key );
				}

				update_option( self::USER_SIGNED_IN, true );
				if ( isset( $response->account_key ) ) {
					update_option( self::ACCOUNT_KEY_KEY, $response->account_key );
				}
				if ( isset( $response->email ) ) {
					update_option( self::EMAIL_KEY, $response->email );
				}
				if ( isset( $response->rest_api_key ) && ! empty( $unique_string ) ) {
					update_option( $unique_string, base64_encode( $response->rest_api_key ) );
				}
				$this->review_notice->setSignInDate( new DateTime() );
				update_option( self::FLUSH_REWRITE_RULES_FLAG_KEY, true);
				return true;
			}
		}
		if ( isset( $response->error ) ) {

			$this->sign_in_error = $response->error;
		}
		return false;
	}

	/**
	 * Write the service worker file.
	 *
	 * @param string $account_key Account Key.
	 */
	public function push_monkey_service_worker_file_create( $account_key = '' ) {
		$content_file = plugin_dir_path( __FILE__ ) . '../templates/pages/service_worker.php';
		$content_file = file_get_contents( $content_file );
		$file_name = ABSPATH . 'service-worker-' . $account_key . '.php';
		$file_exists = file_exists( $file_name );
		if ( ! $file_exists ) {
			$create_file = touch( $file_name );
			if ( $create_file ) {
				file_put_contents( $file_name, $content_file, FILE_APPEND | LOCK_EX );
			}
		}
	}

	/**
	 * Delete service worker file in enabled `HTTP` setting.
	 *
	 * @param string $account_key Account Key.
	 */
	public function push_monkey_delete_service_worker_file( $account_key = '' ) {
		$file_name = ABSPATH . 'service-worker-' . $account_key . '.php';
		unlink( $file_name );
	}

	/**
	 * service worker file error
	 */
	public function push_monkey_service_worker_file_error() {

	  if ( ( isset( $_GET['page'] ) ) && ( is_admin() ) && ( $_GET['page'] == "push_monkey_main_config" ) ) {

			echo '<div class="notice notice-error is-dismissible"><p>Error: Could not create service-worker-' . $this->account_key() . '.php file</p></div>';
		}
	}

	/**
	 * Signs out an user.
	 */
	public function sign_out() {

		delete_option( self::USER_SIGNED_IN );
		delete_option( self::ACCOUNT_KEY_KEY );
		delete_option( self::EMAIL_KEY );
		delete_option( self::WEBSITE_PUSH_ID_KEY );
		delete_option( self::SUBDOMAIN_FORCED );
		delete_option( self::FLUSH_REWRITE_RULES_FLAG_KEY );
		delete_option( self::WOO_COMMERCE_ENABLED );
		delete_option( PushMonkeyClient::PLAN_NAME_KEY );
	}

	/**
	 * Puts together the welcome text displayed on the top
	 * right of the page, for signed in users.
	 * @return string
	 */
	public function get_email_text() {

		$email = get_option( self::EMAIL_KEY, '' );
		if ( strlen( $email ) ) {

			return "Hi " . $email . '!';
		}
		return '';
	}

	/**
	 * Check if this is the subscription version of Push Monkey
	 * @return boolean
	 */
	public function is_saas() {

		return file_exists( plugin_dir_path( __FILE__ ) . '../.saas' );
	}

	const ACCOUNT_KEY_KEY = 'push_monkey_account_key';
	const EMAIL_KEY = 'push_monkey_account_email_key';
	const WEBSITE_PUSH_ID_KEY = 'push_monkey_website_push_id_key';
	const WEBSITE_NAME_KEY = 'push_monkey_website_name';
	const EXCLUDED_CATEGORIES_KEY = 'push_monkey_excluded_categories';
	const USER_SIGNED_IN = 'push_monkey_user_signed_in';
	const POST_TYPES_KEY = 'push_monkey_post_types';
	const PAGES_KEY = 'push_monkey_allow_pages';
	const FLUSH_REWRITE_RULES_FLAG_KEY = 'push_monkey_user_flush_key';
	const SUBDOMAIN_FORCED = 'push_monkey_subdomain_settings';
	const WOO_COMMERCE_ENABLED = 'push_monkey_woo_enabled';

	/* Private */

	/**
	 * Constructor that initializes the Push Monkey class.
	 */
	function __construct() {
	    $file_path=plugin_dir_path(__FILE__).'plugin-configs.json';
		if ( is_ssl() ) {
			$this->endpointURL = "https://www.getpushmonkey.com"; //live
		} else {

			$this->endpointURL = "https://www.getpushmonkey.com"; //live
		}
		$this->apiClient = new PushMonkeyClient( $this->endpointURL );
		$this->apiClient->rest_key = $this->push_monkey_get_api_reset_key();
		$this->d = new PushMonkeyDebugger();
		$this->ajax = new PushMonkeyAjax();
		$this->banner = new PushMonkeyBanner();
		$this->notif_config = new PushMonkeyNotificationConfig();
		$this->review_notice = new PushMonkeyReviewNotice();
	}

	/**
	 * Adds all the WordPress action hooks required by Push Monkey.
	 */
	function add_actions() {

		add_action( 'wp_enqueue_scripts', array( $this, 'sdk_js_enqueue_scripts' ) );

		add_action( 'init', array( $this, 'process_forms' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'init', array( $this, 'enqueue_styles' ) );

		add_action( 'init', array( $this, 'catch_review_dismiss' ) );

		add_action( 'init', array( $this, 'set_defaults' ), 20 );
		 
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );

		add_action( 'admin_menu', array( $this, 'register_settings_pages' ));

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		add_action( 'transition_post_status', array( $this, 'post_published' ), 10, 3 );

		add_action( 'admin_enqueue_scripts', array( $this, 'notification_preview_scripts' ) );

		// If not signed in, display an admin_notice prompting the user to sign in.
		if( ! $this->signed_in() ) {

			add_action( 'admin_notices', array( $this, 'big_sign_in_notice' ) );
		} else {
			$file_name = ABSPATH . 'service-worker-' . $this->account_key() . '.php';
			$file_exists = file_exists( $file_name );
			$is_ssl = is_ssl();
			// If check service worker file exists OR not.
			if ( $file_exists == false ) {
					// Create service worker file.
				if ( $is_ssl && ! wp_doing_ajax() ) {
					$this->push_monkey_service_worker_file_create( $this->account_key() );
				}
				add_action( 'admin_notices' , array( $this, 'push_monkey_service_worker_file_error' ) );
			}

			if ( ! $is_ssl ) {
				add_action( 'admin_notices' , array( $this, 'push_monkey_display_ssl_error' ) );
			}
		}

		// If the plan is expired, present an admin_notice informing the user.
		if ( $this->can_show_expiration_notice() ) {

			add_action( 'admin_notices', array( $this, 'big_expired_plan_notice' ) );
		}

		add_action( 'admin_notices', array( $this, 'big_upsell_notice' ) );

		add_action( 'admin_notices', array( $this, 'push_monkey_manifest_js' ) );

		add_action( 'wp_ajax_push_monkey_banner_position', array( $this->ajax, 'banner_position_changed' ) );
		if ( $this->signed_in() ) {
			add_action( 'admin_init', array( $this, 'push_monkey_create_woo_store' ) );
		}
	}
	
	/**
	 * Set some default values.
	 */
	function set_defaults() {

		// By default all posts should send push notifications
		$post_types = get_option( self::POST_TYPES_KEY );
		if ( ! $post_types ) {

			$post_types = $this->get_all_post_types();
			add_option( self::POST_TYPES_KEY, $post_types );
		}
		$subdomain = get_option( self::SUBDOMAIN_FORCED, null );
		
		if ( $subdomain === null && ! is_ssl() ) {

			update_option( self::SUBDOMAIN_FORCED, true );
		}
		$woo_enabled = get_option( self::SUBDOMAIN_FORCED, null );
		if ( $woo_enabled === null ) {

			update_option( self::WOO_COMMERCE_ENABLED, true);
		}
	}

	/**
	 * Callback to add the dashboard widgets.
	 */
	function add_dashboard_widgets() {

		wp_add_dashboard_widget( 'push-monkey-push-dashboard-widget', 'Send Push Notification - Push Monkey', array( $this, 'push_widget' ) );
		wp_add_dashboard_widget( 'push-monkey-stats-dashboard-widget', 'Stats - Push Monkey', array( $this, 'stats_widget') );
	}

	/**
	 * Render the Custom Push Dashboard Widget.
	 */
	function push_widget() {

		$posted = isset( $_GET['posted'] );

		$account_key = false;
		$segments = array();
		if( $this->has_account_key() ) {

			$account_key = $this->account_key();
		}
		$settings_url = admin_url( 'admin.php?page=push_monkey_main_config&push_monkey_signup=1' );
		$segments = array();
		if ( $this->signed_in() ) {

			$segments = $this->apiClient->get_segments( $account_key );
		}
		require_once( plugin_dir_path( __FILE__ ) . '../templates/widgets/push_monkey_push_widget.php' );
	}

	/**
	 * Render the Stats Dashboard Widget.
	 */
	function stats_widget() {

		if( ! $this->has_account_key() ) {

			$settings_url = admin_url( 'admin.php?page=push_monkey_main_config&push_monkey_signup=1' );
		?>
			<div class="error-message">
				<p>
					Sign in before you can use Push Monkey. Don't have an account yet?
					<a href="<?php echo $settings_url; ?>">Click here to sign up</a>.
					<a href="https://www.getpushmonkey.com/help?source=plugin#gpm16" target="_blank">More info about this &#8594;</a>
				</p>
			</div>
		<?php
			echo '<img class="placeholder" src="' . plugins_url( 'img/plugin-stats-placeholder-small.jpg', plugin_dir_path( __FILE__ ) ) . '"/>';
		} else {

			$notice = null;
			if ( $this->review_notice->canDisplayNotice() ) {

				$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
			}
			$account_key = $this->account_key();
			$output = $this->apiClient->get_stats( $account_key );
			require_once( plugin_dir_path( __FILE__ ) . '../templates/widgets/push_monkey_stats_widget.php' );
		}
		echo '<a href="https://www.getpushmonkey.com/help?source=plugin#gpm4">What is this?</a>';
	}

	/**
	 * See if the review notice has been dismissed
	 */
	function catch_review_dismiss() {

		if ( isset( $_GET[PushMonkeyReviewNoticeController::REVIEW_NOTICE_DISMISS_KEY] ) ) {

			$this->review_notice->setDismiss( true );
		}
	}

	/**
	 * Register menu pages
	 */
	function register_settings_pages() {
		$icon_svg = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIHdpZHRoPSI2NHB4IiBoZWlnaHQ9IjY0cHgiIHZpZXdCb3g9IjAgMCA2NCA2NCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj4gICAgICAgIDx0aXRsZT5sb2dvLWdyYXktMTY8L3RpdGxlPiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4gICAgPGRlZnM+PC9kZWZzPiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4gICAgICAgIDxnIGlkPSJsb2dvLWdyYXktMTYiIGZpbGw9IiNFRUVFRUUiPiAgICAgICAgICAgIDxnIGlkPSJsb2dvIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSg2LjAwMDAwMCwgMC4wMDAwMDApIj4gICAgICAgICAgICAgICAgPHBhdGggZD0iTTM2LjY0OTU4NzEsNDAuNTczMDk0IEM0MS4yODQwMTgxLDQwLjAxNTgwNjcgNDQuODcwMjQwMywzNi4yMDIwOTcxIDQ0Ljg3MDI0MDMsMzEuNTgwMzU3NiBDNDQuODcwMjQwMywyNi41NzUxMTExIDQwLjY2NDE2MzMsMjIuNTE3NTU3MSAzNS40NzU3MDQzLDIyLjUxNzU1NzEgQzMyLjUyMDE3MDksMjIuNTE3NTU3MSAyOS44ODM0MDYsMjMuODM0MTczNSAyOC4xNjEyNzI1LDI1Ljg5MjU4NDIgQzI2LjQ0MjQ3ODgsMjMuNzU5MTM4OCAyMy43NTcwMTA5LDIyLjM4NjQ1MDYgMjAuNzM5MTk4OSwyMi4zODY0NTA2IEMxNS41NTA3NCwyMi4zODY0NTA2IDExLjM0NDY2MjksMjYuNDQ0MDA0NiAxMS4zNDQ2NjI5LDMxLjQ0OTI1MTEgQzExLjM0NDY2MjksMzYuMDI0NDE0NyAxNC44NTg5Njg2LDM5LjgwNzc1MjMgMTkuNDI1NTI2OCw0MC40MjQxNDk2IEMxOC45MDA5ODksNDEuNTU0MDM2NCAxOC42MDkyODA1LDQyLjgwNjQ0OTYgMTguNjA5MjgwNSw0NC4xMjQ1ODkxIEMxOC42MDkyODA1LDQ5LjEyOTgzNTcgMjIuODE1MzU3Niw1My4xODczODk3IDI4LjAwMzgxNjYsNTMuMTg3Mzg5NyBDMzMuMTkyMjc1NSw1My4xODczODk3IDM3LjM5ODM1MjYsNDkuMTI5ODM1NyAzNy4zOTgzNTI2LDQ0LjEyNDU4OTEgQzM3LjM5ODM1MjYsNDIuODY0MDk4MiAzNy4xMzE2MDE4LDQxLjY2MzcxMDIgMzYuNjQ5NTg3MSw0MC41NzMwOTQgWiBNMTEuMzYzNDU3NywwLjA4NDE5ODUxNDUgQzE0LjkyODQ2NjcsMC43MjAyNzU0MDMgMTguNjAwODc0LDMuNDQzODE1NyAyMi4zODA2Nzk4LDguMjU0ODE5NDIgQzIyLjY0NTA3Nyw1Ljk1OTI1NjUgMjIuNzYwODc3NiwzLjUyMzA0MzU0IDIyLjcyODA4MTYsMC45NDYxODA1NTYgQzI3LjEyNTM2MTcsMS44NDEwMTkzNiAzMC43MjMyMzUsNC41OTQ2MzYyNiAzMy41MjE3MDE0LDkuMjA3MDMxMjUgQzMzLjk1NjU5NzIsNy41NDk2MjM4NCAzNC4wMzU4MDczLDUuMzgyMDg5MTIgMzMuNzU5MzMxNiwyLjcwNDQyNzA4IEMzOS43OTE5ODI5LDUuNDA3MTE3IDQyLjcyMTAzNTUsNy4zODA1MzI2NiA0Ni44MDgyODQzLDE1Ljg1MzMzNzEgQzQ5LjY3NDc3ODcsMjEuNzk1NTM2NCA0OS44MzczMjk0LDI4LjU1NzI2ODMgNDkuNzAyMDk0OSwzMi4xMTk2MDI0IEM0OS42ODg2NTc0LDMyLjQ3MzU3MzUgNDkuNjE4NDA4MiwzMi45OTgyMzU0IDQ5LjYxODQwODIsMzMuODAyNjUzIEM0OS42MTg0MDgyLDM0LjcyMTI5OTkgNTAuNjY0OTk4NCwzNS4yODQ2MTM3IDUxLjUwODU5OTIsMzQuNzAzODU3NCBDNDkuNDQ0MTYxMyw1MC4yMjU4NzcyIDM4Ljc0NDcwOTYsNjAuMjUwODIwNSAyNy40NDU5MTU1LDU5LjY2Mzg1ODEgQzIyLjQ5ODk4MTIsNTkuNDA2ODY5MyAyMC4wNjU4NDU2LDU4LjQ0MTQ0ODIgMTcuOTM2MzExLDU4LjIzMzgxMDYgQzE1LjMyNjUwMTUsNTcuOTc5MzQ0NSAxMi41MjAwOTkzLDU5LjA3Njk0NDcgMTAuOTkwMTQ1LDYyLjI2ODc3NTEgQzkuMTIyOTM3OTIsNjYuMTY0MTkwOCAyLjY4NzkxNjM3LDg4LjY2MzE5MjYgMTMuMTQ5NDcxLDg3LjQyNjY0ODkgQzE4LjkwMTA0NDcsODYuNzQyNTE3NCAxNi4yMzc5ODcyLDgyLjg5NzczMDggMTcuMzQ1MDU2Nyw4MC42MTQ1ODYxIEMxOC40ODQ2NTU4LDc4LjA4NzIzMDggMjMuNTU5MzAwOSw3OC42Nzk5MTY5IDI0LjY1Mjk3NTgsODAuNjE0NTg2MSBDMjUuMzczMzA0NSw4MS45NzY2NTMxIDI0LjI1NTA2NTIsOTQuMTQ5MTUxOSAxMi44OTIyNDE2LDk0LjQyOTY4NDIgQy01LjEzMzk4NTcxLDk1LjExMjE0MjYgMS4wMjY2MjkzLDcxLjkwODY1ODUgMS4zMjU1MzA1LDcwLjUxNTU2ODQgQzMuNjUzODcxODgsNTkuNjYzODU4MSA4LjM0MzI2NDk2LDU2LjIyMDY5OTEgNy40NzkzNTg0MSw1NC4wOTA3NzY4IEM2LjYxNTQ1MTg2LDUxLjk2MDg1NDUgMi4yMzkxODM2NCw0OC42ODY3MzIzIDAuOTA5NzIyMjIyLDQyLjM3NzYzODEgQzEuMTc1OTM3MjMsNDIuNTYyNTMzOSAxLjc5OTk4MTAxLDQyLjQ3MTgyMjEgMS43OTU4NTA5Nyw0MS44MzgzNDUgQzEuNzk0NzUyMzMsNDEuNjQ0OTM1OSAxLjc3ODM3NjgzLDQxLjQ5NjU3MyAxLjc0NjcyNDQ1LDQxLjM5MzI1NjMgQy0wLjM1NDQ2NzczNSwzMy41OTE1MDAyIC0wLjM5MTExODIxNiwyNy4xODgzMDY1IDEuNjM2NzczLDIyLjE4MzY3NTEgQzEuOTg5OTM1OTgsMjIuODcxMjAyMyAyLjk3MTY3OTY5LDIyLjk5OTc1NTkgMy42NTM4NzE4OCwyMi4zMzk0NTM0IEMzLjc1ODk4MTY4LDIyLjIzNzcxNjMgNC4wMzYzOTc3OCwyMS43OTc4ODY4IDQuNDE5NzY3NTcsMjEuMDY2OTU2IEM2LjMyNTEzNjg2LDE3LjQzNDE4ODYgMTAuOTMyNjkwOCw2LjkzMDA3ODYzIDExLjM2MzQ1NzcsMC4wODQxOTg1MTQ1IFoiIGlkPSJDb21iaW5lZC1TaGFwZSI+PC9wYXRoPiAgICAgICAgICAgICAgICA8ZyBpZD0iZmFjZS1jb3B5IiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyNy45MjY0NDAsIDM4LjA5MDgwOSkgcm90YXRlKDguMDAwMDAwKSB0cmFuc2xhdGUoLTI3LjkyNjQ0MCwgLTM4LjA5MDgwOSkgdHJhbnNsYXRlKDE2LjQyNjQ0MCwgMjYuNTkwODA5KSI+ICAgICAgICAgICAgICAgICAgICA8cGF0aCBkPSJNNi41MDI0MDg2NiwzLjczMDI2MzE1IEM1Ljk0MzE3NzQsMy4yNzQ1MDcyNyA1LjIzMDEyODQ5LDMuMDAxMzY2MyA0LjQ1MzQ5MjQsMy4wMDEzNjYzIEMyLjY1NzQ4NzM3LDMuMDAxMzY2MyAxLjIwMTUzNzYyLDQuNDYyMDg1NzQgMS4yMDE1Mzc2Miw2LjI2Mzk3NDQ5IEMxLjIwMTUzNzYyLDguMDY1ODYzMjMgMi42NTc0ODczNyw5LjUyNjU4MjY3IDQuNDUzNDkyNCw5LjUyNjU4MjY3IEM1Ljk3Njc5OTU2LDkuNTI2NTgyNjcgNy4yNTU0NzY5MSw4LjQ3NTc2NjY2IDcuNjA4NjU2NDEsNy4wNTcyMzMxNyBDNy4zMjQ4OTM4MSw3LjI0MzMyNjY3IDYuOTg1NzcwNjMsNy4zNTE1MTA1NSA2LjYyMTQ2MjI1LDcuMzUxNTEwNTUgQzUuNjIzNjgxNjgsNy4zNTE1MTA1NSA0LjgxNDgyMDcxLDYuNTM5OTk5NzUgNC44MTQ4MjA3MSw1LjUzODk1MDQ0IEM0LjgxNDgyMDcxLDQuNTc4MDMzNjcgNS41NjAxMjY0OSwzLjc5MTc2MjY2IDYuNTAyNDA4NjYsMy43MzAyNjMxNSBaIiBpZD0iZXllLWxlZnQiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDQuNDA1MDk3LCA2LjI2Mzk3NCkgcm90YXRlKC0yMy4wMDAwMDApIHRyYW5zbGF0ZSgtNC40MDUwOTcsIC02LjI2Mzk3NCkgIj48L3BhdGg+ICAgICAgICAgICAgICAgICAgICA8cGF0aCBkPSJNMjAuMDY3OTYzNywxLjgwMDA0MjM5IEMxOS41MDg3MzI1LDEuMzQ0Mjg2NSAxOC43OTU2ODM2LDEuMDcxMTQ1NTMgMTguMDE5MDQ3NSwxLjA3MTE0NTUzIEMxNi4yMjMwNDI0LDEuMDcxMTQ1NTMgMTQuNzY3MDkyNywyLjUzMTg2NDk3IDE0Ljc2NzA5MjcsNC4zMzM3NTM3MiBDMTQuNzY3MDkyNyw2LjEzNTY0MjQ3IDE2LjIyMzA0MjQsNy41OTYzNjE5MSAxOC4wMTkwNDc1LDcuNTk2MzYxOTEgQzE5LjU0MjM1NDYsNy41OTYzNjE5MSAyMC44MjEwMzIsNi41NDU1NDU5IDIxLjE3NDIxMTUsNS4xMjcwMTI0MSBDMjAuODkwNDQ4OSw1LjMxMzEwNTkxIDIwLjU1MTMyNTcsNS40MjEyODk3OCAyMC4xODcwMTczLDUuNDIxMjg5NzggQzE5LjE4OTIzNjgsNS40MjEyODk3OCAxOC4zODAzNzU4LDQuNjA5Nzc4OTggMTguMzgwMzc1OCwzLjYwODcyOTY4IEMxOC4zODAzNzU4LDIuNjQ3ODEyOSAxOS4xMjU2ODE2LDEuODYxNTQxODkgMjAuMDY3OTYzNywxLjgwMDA0MjM5IFoiIGlkPSJleWUtcmlnaHQiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE3Ljk3MDY1MiwgNC4zMzM3NTQpIHJvdGF0ZSgtMjMuMDAwMDAwKSB0cmFuc2xhdGUoLTE3Ljk3MDY1MiwgLTQuMzMzNzU0KSAiPjwvcGF0aD4gICAgICAgICAgICAgICAgICAgIDxlbGxpcHNlIGlkPSJPdmFsLTMiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE0LjM4MzU5NSwgMTEuODYwNjIxKSByb3RhdGUoLTIyLjAwMDAwMCkgdHJhbnNsYXRlKC0xNC4zODM1OTUsIC0xMS44NjA2MjEpICIgY3g9IjE0LjM4MzU5NDkiIGN5PSIxMS44NjA2MjA5IiByeD0iMSIgcnk9IjEuMDg3NTM2MDYiPjwvZWxsaXBzZT4gICAgICAgICAgICAgICAgICAgIDxlbGxpcHNlIGlkPSJPdmFsLTMtQ29weSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTEuMzc0Nzk0LCAxMi4yNjQyMzYpIHNjYWxlKC0xLCAxKSByb3RhdGUoLTIyLjAwMDAwMCkgdHJhbnNsYXRlKC0xMS4zNzQ3OTQsIC0xMi4yNjQyMzYpICIgY3g9IjExLjM3NDc5NDUiIGN5PSIxMi4yNjQyMzYzIiByeD0iMSIgcnk9IjEuMDg3NTM2MDYiPjwvZWxsaXBzZT4gICAgICAgICAgICAgICAgICAgIDxwYXRoIGQ9Ik0xNC4yNjc0MTQ2LDIxLjEwNDg5NyBDMTQuNDM1MTc3OCwyMi43NDg0IDEwLjg4MDc3ODEsMjIuMjM0NjcwMyAxMC4xOTk4MzYsMjIuMDEzMTc4MiBDOC43MjA1MDYyOSwyMS41MzE5OTIgNy45MTY3Njk0MywyMC41NzI4NjY3IDcuNzg4NjI1NDEsMTkuMTM1ODAyMiBDOC4xNzM4MTA0NiwyMC4wNzMyNjUxIDkuMzc4MTQzMTgsMjAuNjUxMjMwOSAxMS40MDE2MjM2LDIwLjg2OTY5OTQgQzEyLjQ2NzExOTksMjAuOTg0NzM3NiAxMy4zMTE0MDUyLDIwLjYwMjIwMjUgMTMuNzkyNDY4NCwyMC42ODM2NzUzIEMxNC4xNDU0ODU1LDIwLjc0MzQ2MjIgMTQuMjQ0NTgyMSwyMC44ODEyMTY3IDE0LjI2NzQxNDYsMjEuMTA0ODk3IFoiIGlkPSJQYXRoLTIiPjwvcGF0aD4gICAgICAgICAgICAgICAgPC9nPiAgICAgICAgICAgIDwvZz4gICAgICAgIDwvZz4gICAgPC9nPjwvc3ZnPg==';
		//NOTE: call a function to load this page. Loading a file instead of a function doesn't execute the page hook suffix.
		$hook_suffix = add_menu_page( 'Statistics', 'Push Monkey', 'manage_options', 'push_monkey_main_config', array( $this, 'submenu_page_content_statistics' ), $icon_svg );
		add_action( 'load-' . $hook_suffix , array( $this, 'settings_screen_loaded' ) );
		add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'enqueue_styles_main_config' ) );

		$hook_suffix = add_submenu_page( 'push_monkey_main_config', 'Send Push Notification', '<span class="push-monkey-send-notification-menu-item">★ Send Notification</span>', 'manage_options', 'push_monkey_send_push', array( $this, 'submenu_page_content_send_push' ));
		add_action( 'load-' . $hook_suffix , array( $this, 'settings_screen_loaded' ) );
		add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'enqueue_styles_main_config' ) );

		$hook_suffix = add_submenu_page( 'push_monkey_main_config', 'Settings', 'Settings', 'manage_options', 'push_monkey_general', array( $this, 'submenu_page_content_general' ));
		add_action( 'load-' . $hook_suffix , array( $this, 'settings_screen_loaded' ) );
		add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'enqueue_styles_main_config' ) );

		$hook_suffix = add_submenu_page( 'push_monkey_main_config', 'Segmentation', 'Segmentation', 'manage_options', 'push_monkey_segmentation', array( $this, 'submenu_page_content_segmentation' ));
		add_action( 'load-' . $hook_suffix , array( $this, 'settings_screen_loaded' ) );
		add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'enqueue_styles_main_config' ) );

		$hook_suffix = add_submenu_page( 'push_monkey_main_config', 'Post Types', 'Post Types', 'manage_options', 'push_monkey_post_types', array( $this, 'submenu_page_content_post_types' ));
		add_action( 'load-' . $hook_suffix , array( $this, 'settings_screen_loaded' ) );
		add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'enqueue_styles_main_config' ) );

		$hook_suffix = add_submenu_page( 'push_monkey_main_config', 'Categories', 'Categories', 'manage_options', 'push_monkey_categories', array( $this, 'submenu_page_content_categories' ));
		add_action( 'load-' . $hook_suffix , array( $this, 'settings_screen_loaded' ) );
		add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'enqueue_styles_main_config' ) );

		$hook_suffix = add_submenu_page( 'push_monkey_main_config', 'Permission Settings', 'Permission Settings', 'manage_options', 'push_monkey_pages', array( $this, 'submenu_page_content_pages' ));
		add_action( 'load-' . $hook_suffix , array( $this, 'settings_screen_loaded' ) );
		add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'enqueue_styles_main_config' ) );		

		$hook_suffix = add_submenu_page( 'push_monkey_main_config', 'Feedback Dialogs', 'Feedback Dialogs', 'manage_options', 'push_monkey_feedback_dialogs', array( $this, 'submenu_page_content_feedback_dialogs' ));
		add_action( 'load-' . $hook_suffix , array( $this, 'settings_screen_loaded' ) );
		add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'enqueue_styles_main_config' ) );

		// $hook_suffix = add_submenu_page( 'push_monkey_main_config', 'Notification Format', 'Notification Format', 'manage_options', 'push_monkey_notification_format', array( $this, 'submenu_page_content_notification_format' ));
		// add_action( 'load-' . $hook_suffix , array( $this, 'settings_screen_loaded' ) );
		// add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'enqueue_styles_main_config' ) );

		$woocommerce_is_active = false;

		$hook_suffix = add_submenu_page( 'push_monkey_main_config', 'WooCommerce', 'WooCommerce','manage_options', 'push_monkey_woocommerce', array( $this, 'submenu_page_content_woocommerce' ));
		add_action( 'load-' . $hook_suffix , array( $this, 'settings_screen_loaded' ) );
		add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'enqueue_styles_main_config' ) );
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			$woocommerce_is_active = true;
			if ( $woocommerce_is_active ) {
				$hook_suffix = add_submenu_page( 'push_monkey_main_config', 'Abandoned Cart', 'Abandoned Cart', 'manage_options', 'push_monkey_woo_abandoned_cart', array( $this, 'submenu_page_content_abandoned_cart' ));
				add_action( 'load-' . $hook_suffix , array( $this, 'settings_screen_loaded' ) );
				add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'enqueue_styles_main_config' ) );

				$hook_suffix = add_submenu_page( 'push_monkey_main_config', 'Back In Stock', 'Back In Stock', 'manage_options', 'push_monkey_woo_back_in_stock', array( $this, 'submenu_page_content_back_in_stock' ));
				add_action( 'load-' . $hook_suffix , array( $this, 'settings_screen_loaded' ) );
				add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'enqueue_styles_main_config' ) );

				$hook_suffix = add_submenu_page( 'push_monkey_main_config', 'Price Drop Notification Settings', 'Price Drop Notification Setting', 'manage_options', 'push_monkey_woo_price_drop_notification', array( $this, 'submenu_page_content_price_drop_notification' ));
				add_action( 'load-' . $hook_suffix , array( $this, 'settings_screen_loaded' ) );
				add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'enqueue_styles_main_config' ) );

				$hook_suffix = add_submenu_page( 'push_monkey_main_config', 'Product Review Reminders', 'Product Review Reminders', 'manage_options', 'push_monkey_woo_product_review_reminders', array( $this, 'submenu_page_content_product_review_reminders' ));
				add_action( 'load-' . $hook_suffix , array( $this, 'settings_screen_loaded' ) );
				add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'enqueue_styles_main_config' ) );

				$hook_suffix = add_submenu_page( 'push_monkey_main_config', 'Welcome Discount Notification', 'Welcome Discount Notification', 'manage_options', 'push_monkey_woo_welcome_notification', array( $this, 'submenu_page_content_welcome_notification' ));
				add_action( 'load-' . $hook_suffix , array( $this, 'settings_screen_loaded' ) );
				add_action( 'admin_print_styles-' . $hook_suffix , array( $this, 'enqueue_styles_main_config' ) );
			}
		}
	}

	/**
	 * Render the Settings Screens
	 */
	function submenu_page_content_main() {

		$this->main_api(plugin_dir_path( __FILE__ ) . '../templates/pages/settings/main.php');
	}

	function submenu_page_content_statistics() {

		$this->statistics_api(plugin_dir_path( __FILE__ ) . '../templates/pages/settings/statistics.php');
	}

	function submenu_page_content_send_push() {

		$this->send_push_api(plugin_dir_path( __FILE__ ) . '../templates/pages/settings/send_push.php');
	}

	function submenu_page_content_general() {

		$this->general_api(plugin_dir_path( __FILE__ ) . '../templates/pages/settings/general.php');
	}

	function submenu_page_content_segmentation() {
		$this->segmentation_api(plugin_dir_path( __FILE__ ) . '../templates/pages/settings/segmentation.php');
	}

	function submenu_page_content_post_types() {

		$this->post_types_api(plugin_dir_path( __FILE__ ) . '../templates/pages/settings/post-types.php');
	}

	function submenu_page_content_pages() {

		$this->content_pages_api(plugin_dir_path( __FILE__ ) . '../templates/pages/settings/pages.php');
	}

	function submenu_page_content_categories() {

		$this->categories_api(plugin_dir_path( __FILE__ ) . '../templates/pages/settings/categories.php');
	}

	function submenu_page_content_feedback_dialogs() {

		$this->feedback_dialogs_api(plugin_dir_path( __FILE__ ) . '../templates/pages/settings/feedback-dialogs.php');
	}

	function submenu_page_content_notification_format() {

		$this->notification_format_api( plugin_dir_path( __FILE__ ) . '../templates/pages/settings/notification-format.php');
	}

	function submenu_page_content_woocommerce() {

		$this->woo_status_cart_api( plugin_dir_path( __FILE__ ) . '../templates/pages/settings/woo-info.php');

	}
	function submenu_page_content_abandoned_cart() {

		$this->abandoned_cart_api( plugin_dir_path(__DIR__). 'templates/pages/push-monkey-woocommerce/AbandonedCartSettings.php');
	}
	function submenu_page_content_back_in_stock() {

		$this->backinstock_cart_api( plugin_dir_path(__DIR__). 'templates/pages/push-monkey-woocommerce/backInStock.php');
	}
	function submenu_page_content_price_drop_notification() {

		$this->price_drop_cart_api( plugin_dir_path(__DIR__). 'templates/pages/push-monkey-woocommerce/Price Drop Notification Settings.php');
	}
	function submenu_page_content_product_review_reminders() {

		$this->product_review_cart_api( plugin_dir_path(__DIR__). 'templates/pages/push-monkey-woocommerce/Product Review Reminders.php');
	}
	function submenu_page_content_welcome_notification() {

		$this->welcome_notification_cart_api( plugin_dir_path(__DIR__). 'templates/pages/push-monkey-woocommerce/Welcome Notification.php');
	}
	/**
	 * The Subdomain API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
	function subdomain_api($template_name) {
		$website_name_key = self::WEBSITE_NAME_KEY;
		$push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );

		$domain_forced = false;
	
		if ( $this->signed_in() ) {
	        // Domain Forced
	       $domain_forced = get_option( self::SUBDOMAIN_FORCED, false );

			$has_account_key = true;
			$account_key = $this->account_key();
			$plan_response = $this->apiClient->get_plan_name( $account_key );
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();
        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}

		require_once( $template_name );
	}
	/**
	 * The Notification Format API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
	function notification_format_api($template_name) {
        $website_name_key = self::WEBSITE_NAME_KEY;
		$push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );

		if ( $this->signed_in() ) {

		    // Notification Format
		    $notification_format_image = plugins_url( 'img/default/notification-image-upload-placeholder.png', plugin_dir_path( __FILE__ ) );
		    $notification_format = $this->notif_config->get_format();
		    $notification_is_custom = $this->notif_config->is_custom_text();
		    $notification_custom_text = $this->notif_config->get_custom_text();

			$has_account_key = true;
			$account_key = $this->account_key();
			$plan_response = $this->apiClient->get_plan_name( $account_key );
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();

		
        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}

		require_once( $template_name );
	}
    /**
	 * The Feedback Dialogs API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
	function feedback_dialogs_api($template_name) {
        $website_name_key = self::WEBSITE_NAME_KEY;
		$push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );

		$welcome_status_enabled = true;
	    $welcome_status_message = "";
	    $custom_prompt_enabled = false;
		$domain_forced = false;
		$custom_prompt_title = "";
		$custom_prompt_message = "";
		if ( $this->signed_in() ) {
			// Banner options
		    $banner_position = $this->banner->get_position();
		    $banner_position_classes = array(
			'top' => 'banner-top',
			'bottom' => 'banner-bottom',
			'disabled' => 'banner-disabled',
			'topLeft' => 'banner-top-left',
			'topRight' => 'banner-top-right',
			'bottomLeft' => 'banner-bottom-left',
			'bottomRight' => 'banner-bottom-right',
			'centerLeft' => 'banner-center-left',
			'centerRight' => 'banner-center-right'
			 );
		    $welcome_status = $this->apiClient->get_welcome_message_status( $this->account_key() );
			if ( $welcome_status != false  && is_array($welcome_status) ) {

				$welcome_status_enabled = $welcome_status["enabled"];
				$welcome_status_message = $welcome_status["message"];
			}
			$custom_prompt = $this->apiClient->get_custom_prompt( $this->account_key() );
			if ( $custom_prompt != false  && is_array($custom_prompt) ) {

				$custom_prompt_enabled = $custom_prompt["custom_prompt_enabled"];
				$custom_prompt_title = $custom_prompt["custom_prompt_title"];
				$custom_prompt_message = $custom_prompt["custom_prompt_message"];
			}
		    $banner_text = $this->banner->get_raw_text();
		    $banner_color = $this->banner->get_color();
		    $banner_subscribe_color = $this->banner->get_subscribe_color();
		    $banner_disabled_home = $this->banner->get_disabled_on_home();
		    //Domain Forced
            $domain_forced = get_option( self::SUBDOMAIN_FORCED, false );

			$has_account_key = true;			
			$account_key = $this->account_key();
			$plan_response = $this->apiClient->get_plan_name( $account_key );
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		$segment_delete_url = admin_url( 'admin.php?page=push_monkey_segmentation&delete_seg=' );
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();

        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}
		require_once( $template_name );
	}
	/**
	 * The categories API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
	function categories_api($template_name) {
		$website_name_key = self::WEBSITE_NAME_KEY;
		$push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );

		$options = $this->get_excluded_categories();
		$cats = $this->get_all_categories();
		$post_types = $this->get_all_post_types();
		if ( $this->signed_in() ) {
			$has_account_key = true;
			$account_key = $this->account_key();
			$plan_response = $this->apiClient->get_plan_name( $account_key);
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();
        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}

		require_once( $template_name );
	}
	/**
	 * The Content pages API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
    function content_pages_api($template_name) {
		$website_name_key = self::WEBSITE_NAME_KEY;
		$push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );

		$pages_post_type = $this->get_pages_all_post_type();
		$pages_taxonomies = $this->get_pages_taxonomies();
		$all_pages = $this->get_all_pages();
		$tmp_pages = $this->get_set_pages();
		$set_allow_pages = array();
		if ( $tmp_pages ) {

			$set_allow_pages = $tmp_pages;
		}
		if ( $this->signed_in() ) {
			$has_account_key = true;
			$account_key = $this->account_key();
			$plan_response = $this->apiClient->get_plan_name( $account_key);
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();
        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}

		require_once( $template_name );
	}
	/**
	 * The Post Types API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
    function post_types_api($template_name) {
        $website_name_key = self::WEBSITE_NAME_KEY;
        $push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );
		
		$set_post_types = $this->get_set_post_types();
		$post_types = $this->get_all_post_types();
		$pages_post_type = $this->get_pages_all_post_type();

		if ( $this->signed_in() ) {
			$has_account_key = true;
			$account_key = $this->account_key();
            $plan_response = $this->apiClient->get_plan_name( $account_key );
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();

        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}
		require_once( $template_name );
	}
	/**
	 * The segmentation API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
	function segmentation_api($template_name) 
	{$website_name_key = self::WEBSITE_NAME_KEY;  
	 $push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );
		$segments = array();
		if ( $this->signed_in() ) {
			$has_account_key = true;
			$account_key = $this->account_key();

			$segments = $this->apiClient->get_segments( $account_key );

			$plan_response = $this->apiClient->get_plan_name( $account_key );
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );

		$segment_delete_url = admin_url( 'admin.php?page=push_monkey_segmentation&delete_seg=' );

		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();
        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}
		require_once( $template_name );
	}
	/**
	 * The general setting API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
	function general_api($template_name) 
	{
		$website_name_key = self::WEBSITE_NAME_KEY;
		$push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}
		$signed_in = $this->signed_in();
		$has_account_key = false;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );
		if ( $this->signed_in() ) {
			$has_account_key = true;
			$account_key = $this->account_key();
			$plan_response = $this->apiClient->get_plan_name( $account_key);
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$one_signal_push = $this->pm_one_signal_push();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();
        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}
		require_once( $template_name );
	}
	/**
	 * The send push API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
	function send_push_api($template_name) 
	{  $website_name_key = self::WEBSITE_NAME_KEY;
		$push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );

		$segments = array();
		if ( $this->signed_in() ) {

			$has_account_key = true;
			$account_key = $this->account_key();

			$segments = $this->apiClient->get_segments( $account_key );
			
			$plan_response = $this->apiClient->get_plan_name( $account_key );
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		
		$segment_delete_url = admin_url( 'admin.php?page=push_monkey_segmentation&delete_seg=' );
		
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();
        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}
		require_once( $template_name );
	}
	 /**
	 * The statistics API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
	function statistics_api($template_name) {
        $website_name_key = self::WEBSITE_NAME_KEY;
		$push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );

		$output = NULL;
		if ( $this->signed_in() ) {

			$has_account_key = true;
			$account_key = $this->account_key();

			$output = $this->apiClient->get_stats( $account_key );

			$plan_response = $this->apiClient->get_plan_name( $account_key);
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();
        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}

		if ( ! is_object( $output ) ) {
			$output = new stdClass();
			if ( ! isset( $output->subscribers ) ) {
						$output->subscribers = 0;
			}
			if ( ! isset( $output->total_subscribers ) ) {
				$output->total_subscribers = 0;
			}
			if ( ! isset( $output->subscribers_yesterday ) ) {
				$output->subscribers_yesterday = 0;
			}
			if ( ! isset( $output->subscribers_today ) ) {
				$output->subscribers_today = 0;
			}
			if ( ! isset( $output->sent_notifications ) ) {
				$output->sent_notifications = 0;
			}
			if ( ! isset( $output->top_countries ) ) {
				$output->top_countries = array();
			}
		}
		require_once( $template_name );
	}
    /**
	 * The WooCommerce status API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
	function woo_status_cart_api($template_name) {
		$website_name_key = self::WEBSITE_NAME_KEY;
		$push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$output = NULL;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );

		$localpath= explode("wp-content", $pluginPath);
		
		if ( $this->signed_in() ) {

			$has_account_key = true;
			$account_key = $this->account_key();
			//woocommerce main page status api
			$woocommerce_is_active = false;
			$woo_status = NULL;
            if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 	        $woocommerce_is_active = true;
			$woo_status = $this->apiClient->get_woo_status_setting($account_key );
              }
			$plan_response = $this->apiClient->get_plan_name( $account_key );
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		$segment_delete_url = admin_url( 'admin.php?page=push_monkey_segmentation&delete_seg=' );
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();
		
        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}
		require_once( $template_name );

	}
	/**
	 * The WooCommerce Abandoned Cart Notificaton API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
	function abandoned_cart_api($template_name) {
		$website_name_key = self::WEBSITE_NAME_KEY;
		$push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$output = NULL;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );
		if ( $this->signed_in() ) {
            
			$has_account_key = true;
			$account_key = $this->account_key();
            //Abandoned Cart Api
            $woo_abandoned_cart = NULL;
            $woo_abandoned_cart = $this->apiClient->get_abandoned_cart_woo_settings($account_key );

			$plan_response = $this->apiClient->get_plan_name( $account_key );
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();
		
        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}
		require_once( $template_name );

	}
	/**
	 * The WooCommerce Back In Stock Notificaton API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
    function backinstock_cart_api($template_name) {
			$website_name_key = self::WEBSITE_NAME_KEY;
		$push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$output = NULL;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );
		if ( $this->signed_in() ) {

			$has_account_key = true;
			$account_key = $this->account_key();
			//Back In Stock Api
			$woo_backinstock = NULL;
			$woo_backinstock = $this->apiClient->get_back_in_stock_woo_settings($account_key );

			$plan_response = $this->apiClient->get_plan_name( $account_key);
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();
		
        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}
		require_once( $template_name );

	}
	/**
	 * The WooCommerce Price Drop Notificaton API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
	function price_drop_cart_api($template_name) {
		$website_name_key = self::WEBSITE_NAME_KEY;
		$push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$output = NULL;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );
		$localpath= explode("wp-content", $pluginPath);
		    if ( $this->signed_in() ) {

			$has_account_key = true;
			$account_key = $this->account_key();
            //Price Drop Notification Api
			$woo_pricedrop= NULL;
		    $woo_pricedrop = $this->apiClient->get_price_drop_woo_settings( $account_key );

			$plan_response = $this->apiClient->get_plan_name( $account_key );
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();
        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}
		require_once( $template_name );
	}
	 /**
	 * The WooCommerce Product Review Reminder API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
	function product_review_cart_api($template_name) {
	$website_name_key = self::WEBSITE_NAME_KEY;
		$push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$output = NULL;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );
		if ( $this->signed_in() ) {

			$has_account_key = true;
			$account_key = $this->account_key();
			//Product Review Api
			$woo_productreview= NULL;
			$woo_productreview = $this->apiClient->get_product_review_woo_settings( $account_key);

			$plan_response = $this->apiClient->get_plan_name( $account_key);
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();
        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}
		require_once( $template_name );
	}
    /**
	 * The WooCommerce Welcome Notification Reminder API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
	function welcome_notification_cart_api($template_name) {
		$website_name_key = self::WEBSITE_NAME_KEY;
		$push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$output = NULL;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );
		if ( $this->signed_in() ) {
            
			$has_account_key = true;
			$account_key = $this->account_key();
            //Welcome Discount Api
			$woo_welcomenotication= NULL;
			$woo_welcomenotication = $this->apiClient->get_wecome_notification_woo_settings( $account_key  );

			$plan_response = $this->apiClient->get_plan_name( $account_key);
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();
        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}
		require_once( $template_name );
	}
	/**
	 * The main API
	 * @param  [string] $template_name [path to template]
	 * @return [void]
	 */
	function main_api($template_name) {

		$website_name_key = self::WEBSITE_NAME_KEY;
		$push_monkey_account_key_key = self::ACCOUNT_KEY_KEY;
		
		$registered = false;

		if ( isset( $_GET['push_monkey_registered'] ) && isset( $_GET['push_monkey_package_pending'] ) ) {

			$this->sign_in_error = "You have signed up and we will verify your account soon.";
		} else if ( isset( $_GET['push_monkey_registered'] ) ) {

			$registered = ( $_GET['push_monkey_registered'] == '1' );
			$account_key = $_GET['push_monkey_account_key'];
			$this->sign_in( $account_key, null, null );
		}

		if ( isset( $this->sign_in_error ) ) {

			$sign_in_error = $this->sign_in_error;
		}

		$sign_up = false;
		if ( isset( $_GET['push_monkey_signup'] ) ) {

			$sign_up = true;
		}

		$signed_in = $this->signed_in();
		$has_account_key = false;
		$output = NULL;
		$plan_name = NULL;
		$plan_can_upgrade = false;
		$plan_expired = false;
		$pluginPath = plugins_url( '/', plugin_dir_path( __FILE__ ) );
		if ( $this->signed_in() ) {
			$has_account_key = true;
			$account_key = $this->account_key();
			$plan_response = $this->apiClient->get_plan_name( $account_key );
			$plan_name = isset( $plan_response->plan_name ) ? $plan_response->plan_name : NULL;
			$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;
			$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		}
		$register_url = $this->apiClient->registerURL;
		$forgot_password_url = $this->apiClient->endpointURL . '/password_reset';
		$return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		$website_name = $this->website_name();
		$website_url = site_url();
		$logout_url = admin_url( 'admin.php?page=push_monkey_main_config&logout=1' );
		$email = $this->get_email_text();
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$is_subscription_version = $this->is_saas();
        $notice = null;
		if ( $this->review_notice->canDisplayNotice() ) {

			$notice = new PushMonkeyReviewNoticeController( $this->is_saas() );
		}

		$has_https = true;
		if ( is_ssl() ) {

			$has_https = false;
		}
		require_once( $template_name );
	}

	/**
	 * Get all the categories.
	 * @return array
	 */		
	function get_public_taxonomies() {

		$taxonomies = get_taxonomies();
		$public_taxonomies = array_diff( array_values ( $taxonomies ), array( 'nav_menu', 'link_category', 'post_format', 'post_tag' ) );
		return $public_taxonomies;
	}

	/**
	 * Get all the categories.
	 * @return array
	 */
	function get_all_categories() {

		$public_taxonomies = $this->get_public_taxonomies();
		$cats = get_terms( $public_taxonomies , array(
		    'hide_empty' => false,
		    'order' => 'ASC'
		) );
		return $cats;
	}

	function get_set_post_types() {

		return get_option( self::POST_TYPES_KEY );
	}

	/**
	 * Gets the set pages.
	 *
	 * @return The set pages.
	 */
	function get_set_pages() {

		return get_option( self::PAGES_KEY );
	}

	/**
	 * Process the form that marks which Post Types send desktop push notifications.
	 */
	function process_allow_pages( $pages ) {

		$allow_pages = array();
		if ( isset( $pages['included_allow_pages'] ) ) {

			foreach ( $pages['included_allow_pages'] as $pages_id ) {

				$allow_pages[] = $pages_id;
			}
		}
		update_option( self::PAGES_KEY, $allow_pages );
		add_action( 'admin_notices', array( $this, 'included_pages_saved_notice' ) );
	}

	/**
	 * Gets all pages.
	 *
	 * @return     array  All pages.
	 */
	function get_all_pages() {

		$new_pages = get_pages();
		if ( ! empty( $new_pages ) ) {

			return ( array ) $new_pages;
		} else {

			$new_pages = array();
		}
		return $new_pages;
	}

	/**
	 * Gets the pages taxonomies.
	 */
	function get_pages_taxonomies() {

		$pages_taxonomies_query = get_taxonomies( array( 'public'	=> true, '_builtin' => false ), 'objects', 'and' );
		$new_taxonomies = array();
		if ( $pages_taxonomies_query ) {

			foreach( $pages_taxonomies_query as $key => $taxonomies_data ) {

				if ( $taxonomies_data->publicly_queryable ) {

					$new_taxonomies[$key] = $taxonomies_data;
				}
			}
			$new_taxonomies['category'] = 'Standard Posts Categories';
		}
		return $new_taxonomies;
	}

	/**
	 * Gets the pages all post type.
	 */
	function get_pages_all_post_type() {

		$pages_post_query = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects', 'and' );
		$pages_post_types = array();
		foreach ( $pages_post_query as $key => $post_type ) {

			if ( isset( $post_type->publicly_queryable ) && $post_type->publicly_queryable == true ) {

				$pages_post_types[$key] = $post_type;
			}
		}
		$pages_post_types['post'] = 'Standard Posts';
		return $pages_post_types;
	}

	function get_all_post_types() {

		$postargs = array(
			'public'   => true,
			'_builtin' => false
			);
		$raw_post_types = get_post_types( $postargs, 'objects', 'and' );
		$post_types = array();
		foreach ( $raw_post_types as $key => $post_type ) {

			$post_types[$key] = $post_type->labels->name;
		}
		$post_types['post'] = "Standard Posts";
		return $post_types;
	}

	/**
	 * Register the meta box for the Notification Preview, when adding a new Post.
	 */
	function add_meta_box() {

		$post_types = $this->get_all_post_types();
		foreach ($post_types as $key => $value) {

			add_meta_box( 'push_monkey_post_opt_out', 'Push Monkey Options',
				array( $this, 'notification_preview_meta_box' ), $key, 'side', 'high' );
		}
	}

	/**
	 * Render the meta box for Notification Preview.
	 */
	function notification_preview_meta_box( $post ) {

		wp_nonce_field( 'push_monkey_meta_box', 'push_monkey_meta_box_nonce' );

		$value = get_post_meta( $post->ID, '_push_monkey_opt_out', true );
		$checked = '';
		if( $value == 'on' ) {

			$checked = ' checked';
		}

		$force = get_post_meta( $post->ID, '_push_monkey_force_send', true );
		$force_checked = '';
		if( $force == 'on' ) {

			$force_checked = ' checked';
		}

		$opt_out_disabled = false;
		if( $post->post_status == 'publish' ) {

			$opt_out_disabled = true;
		}

		$account_key = '';
		if( $this->has_account_key() ) {

			$account_key = $this->account_key();
		}

		$max_len_title = 33;
		$title = strip_tags($post->post_title);
		if ( $this->notif_config->is_custom_text() ) {

			$title = $this->notif_config->get_custom_text();
		}
		if ( strlen( $title ) > $max_len_title ) {

			$title = substr( $title, 0, $max_len_title ) . '...';
		}

		$max_len_body = 70;
		$body = strip_tags(strip_shortcodes($post->post_content));
		if ( $this->notif_config->is_custom_text() ) {

			$body = strip_tags($post->post_title);
		}
		if ( strlen( $body ) > $max_len_body ) {

			$body = substr( $body, 0, $max_len_body ) . '...';
		}
		$segments = array();
		$locations = array();
		if ( $this->signed_in() ) {

			$segments = $this->apiClient->get_segments( $account_key );
			$locations = $this->apiClient->get_locations( $account_key );
			if ( is_object( $locations ) ) {

				$locations = array();
			}
		}
		$register_url = $return_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		require_once( plugin_dir_path( __FILE__ ) . '../templates/widgets/push_monkey_post_meta_box.php' );
	}

	/**
	 * Load scripts for Notification Preview Metabox
	 */
	function notification_preview_scripts( $hook_suffix ) {

		if( 'post.php' == $hook_suffix || 'post-new.php' == $hook_suffix ) {

			$this->d->debug('is on new post');
			wp_enqueue_script( 'custom_js', plugins_url('js/default/push_monkey_optout_metabox.js', dirname( __FILE__ ) ), array( 'jquery' ));
			$local_vars = array(
				'is_custom_text' => $this->notif_config->is_custom_text()
			);
			wp_localize_script( 'custom_js', 'push_monkey_preview_locals', $local_vars );
		}
	}

	/**
	 * Action executed when the Settings Screen has loaded
	 */
	function settings_screen_loaded() {

		remove_action( 'admin_notices', array( $this, 'big_sign_in_notice' ) );
		remove_action( 'admin_notices', array( $this, 'big_expired_plan_notice' ) );
		add_action( 'admin_notices', array( $this, 'big_welcome_notice' ) );
	}

	/**
	 * Action executed when a new post transitions its status.
	 */
	function post_published( $new_status, $old_status, $post ) {

		$this->d->debug(print_r($_POST, true));
		$this->d->debug("$old_status -> $new_status");
		$this->d->debug(1);
		if ( isset( $_POST['push_monkey_opt_out'] ) ) {

			$this->d->debug(2);
			$optout = $_POST['push_monkey_opt_out'];
			update_post_meta( $post->ID, '_push_monkey_opt_out', $optout );

		} else if ( $old_status != 'future' ) {

			$this->d->debug(3);
			delete_post_meta( $post->ID, '_push_monkey_opt_out' );
		}
		if ( isset( $_POST['push_monkey_force_send'] ) ) {

			$this->d->debug(4);
			$force_send = $_POST['push_monkey_force_send'];
			update_post_meta( $post->ID, '_push_monkey_force_send', $force_send );
		} else if ( $old_status != 'future' ) {

			$this->d->debug(5);
			delete_post_meta( $post->ID,  '_push_monkey_force_send');
		}
		$force_send = get_post_meta( $post->ID, '_push_monkey_force_send', true ) === 'on';
		if ( ! $this->has_account_key() ) {

			$this->d->debug(6);
			return;
		}
		if ( $new_status === 'future' ) {

			$this->d->debug(12);
			return;
		}
		if ( $old_status == 'publish' || $new_status != 'publish' ) {

			if ( ! $force_send ) {

				$this->d->debug(7);
				return;
			}
		}
		$included_post_types = $this->get_set_post_types();
		if ( ! array_key_exists( $post->post_type, $included_post_types ) ) {

			$this->d->debug(8);
			return;
		}
		if( ! $this->can_verify_optout( $post->ID ) && 
			! in_array($old_status, array( 'future', 'pending' ) ) ) {

			$this->d->debug(9);
			return;
		}
		$optout = get_post_meta( $post->ID, '_push_monkey_opt_out', true ) === 'on';
		$can_send_push = false;
		if( $optout != 'on' ) {

			if( ! $this->post_has_excluded_category( $post ) ){

				$can_send_push = true;
			}
		}
		if( $force_send == 'on' ) {

					$this->d->debug(10);
			$can_send_push = true;
		}
		$segments = array();
		if ( isset( $_POST['push_monkey_post_segments'] ) ) {

			$segments = $_POST['push_monkey_post_segments'];
		}
		$locations = array();
		if ( isset( $_POST['push_monkey_post_locations'] ) ) {

			$locations = $_POST['push_monkey_post_locations'];
		}
		if( $can_send_push ) {

			$this->d->debug(11);
			$this->d->debug( "can send push" );
			$image = NULL;
			if ( has_post_thumbnail( $post ) ) {

				$this->d->debug( "post has thumb" );
				$featured_image_url = get_the_post_thumbnail_url( $post, 'large' );
				$this->d->debug( $featured_image_url );
        $uploads_info = wp_upload_dir();
				if ( empty( $uploads_info["error"] ) ) {

					$this->d->debug( "no error in upload dir" );
					$this->d->debug( $uploads_info["basedir"] );
					$search_path = $uploads_info["basedir"] . "/*/" . basename( $featured_image_url );
					$found_files = $this->rglob( $search_path );
					$this->d->debug( print_r( $found_files, true ) );
					if ( !empty( $found_files ) ) {

						$image = $found_files[0];
					}
				}
			}
			$title = $post->post_title;
			$body = strip_tags( strip_shortcodes( $post->post_content ) );
			if ( $this->notif_config->is_custom_text() ) {

				$title = $this->notif_config->get_custom_text();
				$body = $post->post_title;
			}
			$post_id = $post->ID;
			$this->send_push_notification( $title, $body, $post_id, false, $segments, $locations, $image );
		}
	}

	/**
   * Recursively search a directory for a file.
   * Return: array of paths to the found files.
	 */
	function rglob( $pattern, $flags = 0 ) {

    $files = glob( $pattern, $flags );
    foreach ( glob( dirname( $pattern ).'/*', GLOB_ONLYDIR|GLOB_NOSORT ) as $dir ) {

        $files = array_merge( $files, $this->rglob( $dir.'/'.basename( $pattern ), $flags ) );
    }
    return $files;
	}

	/**
	 * Checks if the author did not manually disable push notification for
	 * this specific Post, by clicking on the opt-out checkbox.
	 */
	function can_verify_optout( $post_id ) {

		// Check if our nonce is set.
		if ( ! isset( $_POST['push_monkey_meta_box_nonce'] ) ) {

			return false;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['push_monkey_meta_box_nonce'], 'push_monkey_meta_box' ) ) {

			return false;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {

			return false;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {

			return false;
		}

		return true;
	}

	/**
	 * Checks if a Post object is excluded from sending desktop push notifications.
	 * @param object $post
	 * @return boolean
	 */
	function post_has_excluded_category( $post ) {

		$excluded_categories = $this->get_excluded_categories();
		$post_type = get_post_type( $post->ID );
		$taxonomy_objects = get_object_taxonomies( $post_type );
		$public_taxonomy = $this->get_public_taxonomies();
		$result = array_intersect( $public_taxonomy, $taxonomy_objects );
		if ( count( $result ) == 0 ) {

			return false;
		}
		$category_objects = array();
		foreach( $result as $keys => $tax_tag ) {
			
			$post_terms_array =  wp_get_post_terms( $post->ID , $tax_tag, array( "fields" => "all" ) );
			
			if ( ! empty( $post_terms_array ) ) {
				$category_objects[] = $post_terms_array;
			}
		}
		
		$categories = array();
		foreach( $category_objects as $cat ) {

			if ( is_object( $cat ) && property_exists( $cat, 'term_id' ) ) {

				$categories[] = $cat->term_id;				
			}
		}

		$excludable_categories = array_intersect( $excluded_categories, $categories );

		if ( count( $excludable_categories ) ) {

			return true;
		}
		return false;
	}

	/**
	 * Get an array of categories which are marked for not sending desktop push notifications.
	 * @return array of category IDs
	 */
	function get_excluded_categories() {

		$defaults = array();
		$options = get_option( self::EXCLUDED_CATEGORIES_KEY );

		if ( !is_array( $options ) ){

			$options = $defaults;
			update_option( self::EXCLUDED_CATEGORIES_KEY, $options );
		}
		return $options;
	}

	/**
	 * This is the actual point when the Push Monkey API is contacted and the notification is sent.
	 * @param string $title
	 * @param string $body
	 * @param string $url_args
	 * @param boolean $custom
	 */
	function send_push_notification( $title, $body, $url_args, $custom, $segments, $locations, $image = NULL, $postdata = array() ) {

		$this->d->debug("send_push_notification $title");
		$account_key = $this->account_key();
		$clean_title = trim( $title );
		$clean_body = trim( $body );
		$payloadVars = 'title=' . $clean_title . '&body=' . $clean_body . '&url_args=' . $url_args;

		$maxPayloadLength = 150;
		$maxTitleLength = 40;
		$maxBodyLength = 100;
		if( strlen( $payloadVars ) > $maxPayloadLength ){

			$clean_title = substr( $clean_title, 0, $maxTitleLength );
			$clean_body = substr( $clean_body, 0, $maxBodyLength );
		}
		$this->apiClient->send_push_notification( $account_key, $title, $body, $url_args, $custom, $segments, $locations, $image, $postdata );
	}

	/**
	 * Get the name of the website. Can be either from get_bloginfo() or
	 * from a previously saved value.
	 * @return string
	 */
	function website_name() {

		$name = get_option( self::WEBSITE_NAME_KEY, false );
		if( ! $name ) {

			$name = get_bloginfo( 'name' );
		}
		return $name;
	}

	/**
	 * Get the `send to one signal subscribers` setting Default off.
	 *
	 * @return string
	 */
	function pm_one_signal_push() {
		$send_one_singal_subscribers = get_option( 'pm_one_signal_push', false );
		$send_one_singal_subscribers = $send_one_singal_subscribers ? 'on' : 'off';
		return $send_one_singal_subscribers;
	}

	/**
	 * Get the Website Push ID stored.
	 * @return string
	 */
	function website_push_ID() {

		$stored_website_push_id = get_option( self::WEBSITE_PUSH_ID_KEY, false);

		if ( $stored_website_push_id ) {

			return $stored_website_push_id;
		}

		$resp = $this->apiClient->get_website_push_ID( $this->account_key() );
		if ( isset( $resp->website_push_id ) ) {

			update_option( self::WEBSITE_PUSH_ID_KEY, $resp->website_push_id );
			return $resp->website_push_id;
		}
		if ( isset( $resp->error ) ) {

			$this->error = $resp->error;
		}
		return '';
	}

	/**
	 * Push enqueue script.
	 */
	function sdk_js_enqueue_scripts() {

		global $post;
		$allow_page = $this->get_set_pages();
		// If check is admin
		if ( ! is_admin() ) {

			// If check user signed in or not.
			if ( $this->signed_in() ) {

				$url = "https://www.getpushmonkey.com/sdk/config-".$this->account_key().".js";
				$url = $url."?dialog_color=".urlencode( $this->banner->get_color() );
				$url = $url."&button_color=".urlencode( $this->banner->get_subscribe_color() );
				$url = $url."&subdomain_forced=0";

				// If check allow pages
				if ( ! empty( $allow_page ) ) {

					// Get current post detail page.
					$current_object = get_queried_object();
					$current_post_page = array();
					$current_taxonomy = null;
					if ( property_exists( $current_object, 'post_type' ) && 
						property_exists( $current_object, 'taxonomy' ) ) {

						$current_post_page = $current_object->post_type;
						$current_taxonomy = $current_object->taxonomy;
					} else {

						// When we're on collection pages like Archive or WooCommerce default
						// shop page.
						$current_post_page = $post->post_type;
						$current_taxonomy = $post->taxonomy;
					}
					// Found allow pages
					if ( in_array( $post->ID, $allow_page ) ) {

						wp_enqueue_script( 'push_monkey_sdk', $url, array( 'jquery' ) );
					} else if( 
							( in_array( $current_post_page, $allow_page ) ) || 
							( is_taxonomy( $current_taxonomy ) && in_array( $current_taxonomy, $allow_page ) )
						) {

						wp_enqueue_script( 'push_monkey_sdk', $url, array( 'jquery' ) );
					}
				} else {

					// Enqueue script.
					wp_enqueue_script( 'push_monkey_sdk', $url, array( 'jquery' ) );
				}
			}
		}
	}

	/**
	 * Enqueue all the JS files required.
	 */
	function enqueue_scripts() {
			if ( ! wp_script_is( 'jquery-ui-core' ) ) {
				wp_enqueue_script( 'push_monkey_jquery-ui', plugins_url( 'js/plugins/jquery/jquery-ui.min.js', plugin_dir_path( __FILE__ ) ), array('jquery') );
			}
			wp_enqueue_script( 'push_monkey_boostrap', plugins_url( 'js/plugins/bootstrap/bootstrap.min.js', plugin_dir_path( __FILE__ ) ), array('jquery') );

			wp_enqueue_script( 'push_monkey_raphael', plugins_url( 'js/plugins/morris/raphael-min.js', plugin_dir_path( __FILE__ ) ), array('jquery') );
			wp_enqueue_script( 'push_monkey_morris', plugins_url( 'js/plugins/morris/morris.min.js', plugin_dir_path( __FILE__ ) ), array('jquery') );
			wp_enqueue_script( 'push_monkey_icheck', plugins_url( 'js/plugins/icheck/icheck.min.js', plugin_dir_path( __FILE__ ) ), array('jquery') );

			wp_enqueue_script( 'push_monkey_jvectormap', plugins_url( 'js/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js', plugin_dir_path( __FILE__ ) ), array('jquery') );
			wp_enqueue_script( 'push_monkey_jvectormap-world', plugins_url( 'js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js', plugin_dir_path( __FILE__ ) ), array('jquery') );

			wp_enqueue_script( 'push_monkey_boostrap_colorpicker', plugins_url( 'js/plugins/bootstrap-colorpicker/bootstrap-colorpicker.min.js', plugin_dir_path( __FILE__ ) ), array('jquery') );
		
			wp_enqueue_script( 'push_monkey_boostrap_fileinput', plugins_url( 'js/plugins/bootstrap/bootstrap-file-input.js', plugin_dir_path( __FILE__ ) ), array('jquery') );			

			wp_enqueue_script( 'push_monkey_push_widget', plugins_url( 'js/default/push_monkey_push_widget.js', plugin_dir_path( __FILE__ ) ), array('jquery') );	

			wp_enqueue_script( 'push_monkey_woo', plugins_url( 'js/default/push_monkey_woo.js', plugin_dir_path( __FILE__ ) ), array('jquery') );	

			wp_enqueue_script( 'push_monkey_admin', plugins_url( 'js/default/push_monkey_admin.js', plugin_dir_path( __FILE__ ) ), array('jquery') );				

			wp_enqueue_script( 'push_monkey_plugins', plugins_url( 'js/plugins.js', plugin_dir_path( __FILE__ ) ), array('jquery') );
			wp_enqueue_script( 'push_monkey_actions', plugins_url( 'js/actions.js', plugin_dir_path( __FILE__ ) ), array('jquery') );

			$data = $this->set_global_data();
			wp_enqueue_script( 'push_monkey_dashboard', plugins_url( 'js/main.js', plugin_dir_path( __FILE__ ) ), array('jquery') );
			wp_localize_script( 'push_monkey_dashboard', 'global_data', $data );
			wp_enqueue_script( 'push_monkey_dashboard' );
			// Enqueue script for woocommerce settings page.
			$current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
			$allow_page = array(
				'push_monkey_woo_product_review_reminders',
				'push_monkey_woo_abandoned_cart',
				'push_monkey_woo_welcome_notification',
				'push_monkey_woocommerce',
				'push_monkey_woo_back_in_stock',
				'push_monkey_woo_price_drop_notification',
			);
			if ( $this->account_key() ) {
				if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && in_array( $current_page , $allow_page ) ) {
					$woo_api_setting = 'form.api.js';
					if ( isset( $_GET['page'] ) && 'push_monkey_woocommerce' === $_GET['page'] ) {
						$woo_api_setting = 'services.api.js';
					}
					wp_enqueue_script( 'push-woocommerce-settings', plugins_url( 'js/woocommerce/' . $woo_api_setting,  plugin_dir_path( __FILE__ ) ) , array( 'jquery' ), '', true );
					wp_localize_script( 'push-woocommerce-settings', 'PM_Woo', array(
						'account_key' => base64_encode( $this->account_key() ),
						'api' => 'https://getpushmonkey.com/woo/v1/api/%slug%/',
						'image_url' => 'https://getpushmonkey.com',
						'rk' => $this->push_monkey_get_api_reset_key(),
					) );
				}
			}
	}

	/**
	 * Enqueue all the CSS required.
	 */
	function enqueue_styles( $hook_suffix ) {

		if ( is_admin() ) {
			
			wp_enqueue_style( 'push_monkey_styles', plugins_url( '/css/styles.css', plugin_dir_path( __FILE__ ) ) );
			wp_enqueue_style( 'push_monkey_additional', plugins_url( '/css/additional.css', plugin_dir_path( __FILE__ ) ) );
		}
	}

	/**
	 * Enqueue the CSS for the Settings page
	 */
	function enqueue_styles_main_config( ) {
		wp_enqueue_style( 'push_monkey_config_style', plugins_url( 'css/main-config.css', plugin_dir_path( __FILE__ ) ) );
	}

	/**
	 * Multiple manifest js admin notice
	 */
	function push_monkey_manifest_js() {

	  if ( ( isset( $_GET['page'] ) ) && 
	  	( file_exists( get_template_directory() . '/manifest.json' ) ) && 
	  	( is_admin() ) && 
	  	( $_GET['page'] == "push_monkey_main_config" ) ) {

	  	$manifest_json = file_get_contents( get_template_directory() . '/manifest.json' );
			$json_array = json_decode( $manifest_json, true );
	    
	    if ( ( ! array_key_exists( 'gcm_sender_id', $json_array ) ) || 
	    	( ! array_key_exists( 'gcm_user_visible_only', $json_array ) ) ) {

				$res = $this->apiClient->get_sender_id( $this->account_key() );
				$sender_id = $res['gcm_sender_id'];
	    	echo '<div class="notice notice-warning is-dismissible">';
	    	echo '<p>';
	    	echo 'Please add the following code to the manifest.json file of your theme,
	    	before the closing curly-bracket }';
	    	echo '</p>';	    	
	    	echo '<pre>';
	    	echo '"gcm_sender_id": "'.$sender_id.'",';
	    	echo "\r\n";
	    	echo '"gcm_user_visible_only": true';	    	
	    	echo '</pre>';	    	
	    	echo '</div>';
	    }
	  }
	}

	/**
	 * Set global data for JavaScript scripts
	 */
	function set_global_data() {

		if ( $this->signed_in() ) {

			$account_key = $this->account_key();
			$output = $this->apiClient->get_stats( $account_key );
			return array(
				'stats' => $output
			);
		} else {

			return array(
				'stats' => null
			);
		}
	}

	/**
	 * Central point to process forms.
	 */
	function process_forms() {

		if ( isset( $_GET['logout'] ) ) {

			$this->sign_out();
			wp_redirect( admin_url( 'admin.php?page=push_monkey_main_config' ) );
			exit;
		}

		if( isset( $_POST['push_monkey_main_config_submit'] ) ) {

			$this->process_main_config( $_POST );
		} else if( isset( $_POST['push_monkey_category_exclusion'] ) ) {

			$this->process_category_exclusion( $_POST );
		} else if( isset( $_POST['push_monkey_push_submit'] ) ) {

			$this->process_push( $_POST, $_FILES );
		} else if ( isset( $_POST['push_monkey_sign_in'] ) ) {

			$this->process_sign_in( $_POST );
		} else if ( isset( $_POST['push_monkey_post_type_inclusion'] ) ) {

			$this->process_post_type_inclusion( $_POST );
		} else if ( isset( $_POST['push_monkey_pages'] ) ) {

			$this->process_allow_pages( $_POST );
		} else if ( isset( $_POST['push_monkey_banner'] ) ) {

			$this->process_banner_customisation( $_POST );
		} else if ( isset( $_POST['push_monkey_notification_config'] ) ) {

			$this->process_notif_format( $_POST );
		} else if ( isset( $_POST['push_monkey_add_segment'] ) ) {

			$this->process_segment( $_POST );
		} else if ( isset( $_GET['delete_seg'] ) ) {

			$this->process_delete_segment( $_GET );
		} else if ( isset( $_POST['push_monkey_welcome_notification'] ) ) {

			$this->process_welcome_notification( $_POST );
		} else if ( isset( $_POST['push_monkey_custom_prompt'] ) ) {

			$this->process_custom_prompt( $_POST );
		} else if ( isset( $_POST['push_monkey_abandonedcart_settings'] ) ) {

			$this->process_abandonedcart_settings( $_POST, $_FILES);
		}
	       	else if ( isset( $_POST['push_monkey_backinstock_settings'] ) ) {

			$this->process_backinstock_settings( $_POST);
		}
		
		else if ( isset( $_POST['push_monkey_pricedrop_settings'] ) ) {

			$this->process_pricedrop_settings( $_POST);
		}
		else if ( isset( $_POST['push_monkey_productreview_settings'] ) ) {

			$this->process_productreview_settings( $_POST);
		}
		else if ( isset( $_POST['push_monkey_welcomediscount_settings'] ) ) {

			$this->process_welcomediscount_settings( $_POST, $_FILES);
		}
		
			$this->process_woo_card_status_settings( $_POST);
	}

	/**
	 * Process the Sign In form.
	 */
	function process_sign_in( $post ) {

		$api_token = $post['username'];
		$api_secret = $post['password'];
		if ( ! strlen( $api_token ) || ! strlen( $api_secret ) ) {

			$this->sign_in_error = "The two fields can't be empty.";
			return;
		}

		$signed_in = $this->sign_in( null, $api_token, $api_secret );
		if ( $signed_in ) {

			wp_redirect( admin_url( 'admin.php?page=push_monkey_main_config' ) );
			exit;
		}
	}

	/**
	 * Process the form with the website name field, from the Settings page.
	 */
	function process_main_config( $post ) {

		$website_name = $post[self::WEBSITE_NAME_KEY];
		if( $website_name ) {
			update_option( self::WEBSITE_NAME_KEY, $website_name );
		}

		$one_signal_push = isset( $post['one_signal_push'] ) ? true : false;
		
		update_option( 'pm_one_signal_push', $one_signal_push );
	}

	/**
	 * Process the form that marks which Post Categories don't sent desktop push notifications.
	 */
	function process_category_exclusion( $post ) {

		$categories = array();
		if ( isset( $post['excluded_categories'] ) ) {

			$categories = $post['excluded_categories'];
		}
		update_option( self::EXCLUDED_CATEGORIES_KEY, $categories );
		add_action( 'admin_notices', array( $this, 'excluded_categories_saved_notice' ) );
	}

	/**
	 * Process the form that marks which Post Types send desktop push notifications.
	 */
	function process_post_type_inclusion( $post ) {

		$post_types = array();
		if ( isset( $post['included_post_types'] ) ) {

			foreach ( $post['included_post_types'] as $value ) {

				$post_types[$value] = 1;
			}
		}
		update_option( self::POST_TYPES_KEY, $post_types );
		add_action( 'admin_notices', array( $this, 'included_post_types_saved_notice' ) );
	}

	/**
	 * Process the custom push notification, from the widget in the Dashboard.
	 */
	function process_push( $post, $files ) {
		$action_btn = array(
			'action' => ! empty( $post['pm_action_btn'] ) ? $post['pm_action_btn'] : '',
			'action2title' => ! empty( $post['pm_action2_btn'] ) ? $post['pm_action2_btn'] : '',
			'pm_action2_url' => ! empty( $post['pm_action2_url'] ) ? $post['pm_action2_url'] : '',
		);
		$action_btn = array_filter( $action_btn );

		$title = stripcslashes( $post['title'] );
		$body = stripcslashes( $post['message'] );
		$url_args = $post['url'];
		$image = NULL;
		if ( !empty( $files["image"]["name"] ) ) {

			$this->d->debug("we have an uploaded file");
			$this->d->debug($files["image"]["name"]);
			$file_name = $files["image"]["tmp_name"];
			$image = $file_name;
		}
		$segments = array();
		if ( isset( $post['push_monkey_post_segments'] ) ) {

			$segments = $post['push_monkey_post_segments'];
		}
		$this->send_push_notification( $title, $body, $url_args, true, $segments, array(), $image, $action_btn );
		if ( isset( $post['push_monkey_push_submit_page'] ) ) {

			add_action( 'admin_notices', array( $this, 'custom_push_sent_notice' ) );
		} else {
		
			wp_redirect( admin_url( '?posted=1' ) );
			exit();
		}
		
	}

	/**
	 * Process the options to customise the banner.
	 */
	function process_banner_customisation( $post ) {

		if ( isset( $post['push_monkey_banner_color'] ) ) {

			$color = $post['push_monkey_banner_color'];
			$this->banner->set_color( $color );
		}
		if ( isset( $post['push_monkey_subscribe_color'] ) ) {

			$color = $post['push_monkey_subscribe_color'];
			$this->banner->set_subscribe_color( $color );
		}
		add_action( 'admin_notices', array( $this, 'banner_saved_notice' ) );
	}

	/**
	 * Process the notification format
	 */
	function process_notif_format( $post ) {

		$this->d->debug( 'notif format' );

		if ( isset( $post['push_monkey_notification_format'] ) ) {

			$format = $post['push_monkey_notification_format'];

			$this->d->debug( $format );
			$this->notif_config->set_format( $format );
		}
		if ( isset( $post['custom-text'] ) ) {

			$text = $post['custom-text'];
			$this->notif_config->set_custom_text( $text );
		}
		add_action( 'admin_notices', array( $this, 'notif_format_saved_notice' ) );
	}

	/**
	 * Process the segmentation form
	 */
	function process_segment( $post ) {

		if ( !isset( $post['push_monkey_new_segment'] ) ) {

			return;
		}
		$name = $post['push_monkey_new_segment'];
		if ( strlen($name) > 0 ) {

			$account_key = $this->account_key();
			$output = $this->apiClient->save_segment( $account_key, $name );
			if ( $output->response == "ok" ) {

				add_action( 'admin_notices', array( $this, 'segment_saved_notice' ) );
				return;
			}
		}
		add_action( 'admin_notices', array( $this, 'segment_saving_error_notice' ) );
	}

	/**
	 * Process the deleting of a segment.
	 */
	function process_delete_segment( $get ) {

		$id = $get["delete_seg"];
		if ( strlen($id) == 0 ) {

			return;
		}
		$account_key = $this->account_key();
		$output = $this->apiClient->delete_segment( $account_key, $id );
		$this->d->debug(print_r($output, true));
		if ( $output->response == "ok" ) {

			add_action( 'admin_notices', array( $this, 'segment_deleted_notice' ) );
			return;
		}
		add_action( 'admin_notices', array( $this, 'segment_deleting_error_notice' ) );
	}

	/**
	 * Process the welcome notification.
	 */
 	function process_welcome_notification( $post ) {

 		$this->d->debug("process_welcome_notification");
 		$message = $post['push_monkey_welcome_notification_message'];
 		$enabled = false;
 		if ( isset( $post['push_monkey_welcome_notification_enabled'] ) ) {

	 		$enabled = true;
 		}
		$account_key = $this->account_key();
		$updated = $this->apiClient->update_welcome_message( $account_key, $enabled, $message );

		if ( $updated ) {

			add_action( 'admin_notices', array( $this, 'welcome_message_notice' ) );
			return;
		} else {

			add_action( 'admin_notices', array( $this, 'welcome_message_error_notice' ) );
			return;
		}
 	}

	/**
	 * Process the permission dialog.
	 */
 	function process_custom_prompt( $post ) {

 		$title = $post['push_monkey_custom_prompt_title'];
 		$message = $post['push_monkey_custom_prompt_message'];
 		$enabled = false;
 		if ( isset( $post['push_monkey_custom_prompt_enabled'] ) ) {

 			$enabled = true;
 		}
 		$account_key = $this->account_key();
 		$updated = $this->apiClient->update_custom_prompt( $account_key, $enabled, $title, $message);
 		$this->d->debug("Custom prompt updated?");
 		$this->d->debug($updated);
 		if ( $updated ) {

 			add_action( 'admin_notices', array( $this, 'custom_prompt_notice' ) );
 		}
 	}

/**
	 * Process the  WooCommerce status settings form.
	 */
	function process_woo_card_status_settings( $post) {
	$account_key = $this->account_key();
   	if (isset($_POST['woo_setting_status_feature'])){
	 		$key_value = explode(':', $_POST['woo_setting_status_feature']);
	 		$status_feature_key=$key_value[0];
	 		$status_feature_value=$key_value[1];
	 		$updated = $this->apiClient->update_woo_cards_status( $account_key,$status_feature_key, $status_feature_value);
	 if ( $updated ) {

			add_action( 'admin_notices', array( $this, 'woo_status_notice' ) );
			return;
			 	}
			 		else
		{
			add_action( 'admin_notices', array( $this, 'woo_not_updated_status_notice' ) );
			return;
		}
	}}

/**
	 * Process the Abandoned Cart settings form.
	 */
	function process_abandonedcart_settings( $post, $files ) {	
	if (!isset($_POST['second_abandoned_cart_delay_status'])) 
     { $second_abandoned_cart_delay_status=false;}
    else 
     { $second_abandoned_cart_delay_status=true; }
    if (!isset($_POST['third_notification_sent_after_status'])) 
     { $third_notification_sent_after_status=false;}
    else 
     { $third_notification_sent_after_status=true;}
    if (!isset($_POST['push_monkey_abandoned_cart_woo_enabled'])) 
     { $status=false;}
    else 
     {$status=true;}
        $account_key = $this->account_key();
        $abandoned_cart_delay = $post['abandoned_cart_delay'];
		$second_abandoned_cart_delay = $post['second_abandoned_cart_delay'];
	    $third_abandoned_cart_delay = $post['third_abandoned_cart_delay'];
	    $abandoned_cart_notification_title = $post['abandoned_cart_notification_title'];
		$abandoned_cart_message = $post['abandoned_cart_message'];
		$images = NULL;
		$image_path = NULL;
		if ( !empty( $files["abandoned_cart_image"]["name"] ) ) {

			$image_path = $files["abandoned_cart_image"]["tmp_name"];
			$images = $files["abandoned_cart_image"]["name"];
		}
		$updated = $this->apiClient->update_abandoned_cart_settings( $account_key,$status, $abandoned_cart_delay, $second_abandoned_cart_delay, $second_abandoned_cart_delay_status,$third_abandoned_cart_delay,$third_notification_sent_after_status,$abandoned_cart_notification_title,$abandoned_cart_message,$image_path, $images );
		if ( $updated ) {

			add_action( 'admin_notices', array( $this, 'woo_abandoned_cart_notice' ) );
			return;
		}
		else
		{
			add_action( 'admin_notices', array( $this, 'woo_not_updated_abandoned_cart_notice' ) );
			return;
		}
	}
/**
	 * Process the Back In Stock WooCommerce settings form.
	 */
	function process_backinstock_settings( $post) {

		$this->d->debug("Process Back In Stock settings.");
		$this->d->debug(print_r($post, true));
		if (!isset($_POST['push_monkey_backinstock_woo_enabled'])) 
        { $status=false;}
        else 
        {$status=true;
         }
		$account_key = $this->account_key();
		$notification_title = $post['notification_title'];
		$notification_message = $post['notification_message'];
		$pop_up_title = $post['pop_up_title'];
        $pop_up_message = $post['pop_up_message'];
		$button_text = $post['button_text'];
		$success_message = $post['success_message'];
		$colorvalue = $post['color'];
		$updated = $this->apiClient->update_backinstock_settings( $account_key,$status,$notification_title,$notification_message,$pop_up_title,$pop_up_message,$button_text,$success_message,$colorvalue );
		if ( $updated ) {

			add_action( 'admin_notices', array( $this, 'woo_backinstock_notice' ) );
			return;
		}
	else
		{
			add_action( 'admin_notices', array( $this, 'woo_not_updated_backinstock_notice' ) );
			return;
		}
	}
	/**
	 * Process the Price drop notification WooCommerce settings form.
	 */
	function process_pricedrop_settings( $post) {

		$this->d->debug("Process Price Drop settings.");
		$this->d->debug(print_r($post, true));
		if (!isset($_POST['push_monkey_pricedrop_woo_enabled'])) 
         { $status=false;}
        else 
         { $status=true; }
		$account_key = $this->account_key();
		$price_drop_notification_title = $post['price_drop_notification_title'];
		$price_drop_notification_message = $post['price_drop_notification_message'];
		$price_drop_popup_title = $post['price_drop_popup_title'];
        $price_drop_popup_message = $post['price_drop_popup_message'];
		$price_drop_popup_button = $post['price_drop_popup_button'];
		$price_drop_popup_confirmation = $post['price_drop_popup_confirmation'];
		$color = $post['color'];
		$updated = $this->apiClient->update_pricedrop_settings( $account_key,$status,$price_drop_notification_title,$price_drop_notification_message,$price_drop_popup_title,$price_drop_popup_message,$price_drop_popup_button,$price_drop_popup_confirmation,$color );
		if ( $updated ) {

			add_action( 'admin_notices', array( $this, 'woo_pricedrop_notice' ) );
			return;
		}
			else
		{
			add_action( 'admin_notices', array( $this, 'woo_not_updated_pricedrop_notice' ) );
			return;
		}
	}
	/**
	 * Process the Product review reminder WooCommerce settings form.
	 */
	function process_productreview_settings( $post) {

		$this->d->debug("Product review settings.");
		$this->d->debug(print_r($post, true));
		if (!isset($_POST['push_monkey_productreview_woo_enabled'])) 
         { $status=false;}
        else 
         { $status=true;}
		$account_key = $this->account_key();
		$review_reminder_delay = $post['review_reminder_delay'];
		$review_reminder_notification_title = $post['review_reminder_notification_title'];
		$review_reminder_message = $post['review_reminder_message'];
		$updated = $this->apiClient->update_productreview_settings( $account_key,$status,$review_reminder_delay,$review_reminder_notification_title,$review_reminder_message);
		if ( $updated ) {

			add_action( 'admin_notices', array( $this, 'woo_product_review_notice' ) );
			return;
		}
			else
		{
			add_action( 'admin_notices', array( $this, 'woo_not_updated_product_review_notice' ) );
			return;
		}
	}

/**
	 * Process the Welcome Discount settings form.
	 */
	function process_welcomediscount_settings( $post, $files ) {
	
     if (!isset($_POST['push_monkey_welcomediscount_woo_enabled'])) 
      { $status=false;}
     else 
      { $status=true; }
		$account_key = $this->account_key();
		$welcome_notification_message = $post['welcome_notification_message'];
		$welcome_notification_link = $post['welcome_notification_link'];
		$images = NULL;
		$image_path = NULL;
		if ( !empty( $files["welcome_notification_image"]["name"] ) ) {

			$image_path = $files["welcome_notification_image"]["tmp_name"];
			$images = $files["welcome_notification_image"]["name"];
		}
		$updated = $this->apiClient->update_welcomediscount_settings( $account_key,$status, $welcome_notification_message, $welcome_notification_link,$image_path, $images );
		if ( $updated ) {

			add_action( 'admin_notices', array( $this, 'woo_welcomediscount_notice' ) );
			return;
		}
			else
		{
			add_action( 'admin_notices', array( $this, 'woo_not_updated_welcomediscount_notice' ) );
			return;
		}
	}
	/**
	 * Renders the admin notice that prompts the user to sign in.
	 */
	function big_sign_in_notice() {

		$image_url = plugins_url( 'img/plugin-big-message-image.png', plugin_dir_path( __FILE__ ) );
		$settings_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		require_once( plugin_dir_path( __FILE__ ) . '../templates/messages/push_monkey_big_message.php' );
	}

	/**
	 * Renders an admin notice to say that excluded categories are saved.
	 */
	function excluded_categories_saved_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>Excluded categories successfuly updated! *victory dance*</p></div>';
	}

	/**
	 * Renders an admin notice to say that post types are saved.
	 */
	function included_post_types_saved_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>Included Post Types successfuly updated! *high five*</p></div>';
	}

	/**
	 * Renders an admin notice to say that post types are saved.
	 */
	function included_pages_saved_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>Included pages successfuly updated! *high five*</p></div>';
	}

	/**
	 * Renders an admin notice to say that custom push was sent.
	 */
	function custom_push_sent_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>You custom push notification was sent! *high five*</p></div>';
	}

	/**
	 * Renders an admin notice to say that the banner customisation has been saved.
	 */
	function banner_saved_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>Banner saved! *high five*</p></div>';
	}

	/**
	 * Renders an admin notice to say that the notification format has been saved.
	 */
	function notif_format_saved_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>Notification format saved! *yay*</p></div>';
	}

	/**
	 * Admin notice to confirm that a segment has been saved.
	 */
	function segment_saved_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>Segment saved! *woohoo*</p></div>';
	}

	/**
	 * Admin notice to confirm that welcome message has been saved.
	 */
	function welcome_message_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>Welcome message and permission prompt saved! *woohoo*</p></div>';
	}

	/**
	 * Admin notice to show error when saving welcome message.
	 */
	function welcome_message_error_notice() {

		echo '<div class="error alert alert-global alert-danger"><p>Error saving the welcome message and permission prompt! Please try again later. *sob*</p></div>';
	}	

	/**
	 * Admin notice to confirm that custom permission dialog has been updated.
	 */
	function custom_prompt_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>Permission dialog updated! *oh yeah!*</p></div>';
	}

	/**
	 * Admin notice to show an error when saving segments.
	 */
	function segment_saving_error_notice() {

		echo '<div class="error alert alert-global alert-danger"><p>Error saving segment. Please try again.</p></div>';
	}

	/**
	 * Admin notice to confirm that a segment has been deleted.
	 */
	function segment_deleted_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>Segment deleted! *boom*</p></div>';
	}

	/**
	 * Admin notice to show an error when saving segments.
	 */
	function segment_deleting_error_notice() {

		echo '<div class="error alert alert-global alert-danger"><p>Error deleting segment. Please try again.</p></div>';
	}

	/**
	 * Admin notice to confirm that the woo status settings have been saved.
	 */
	function woo_status_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>Status settings saved! *woohoo*</p></div>';
	}	
	/**
	 * Admin notice to confirm that the woo status settings have been saved.
	 */
	function woo_not_updated_status_notice() {

		echo '<div class="updated alert alert-global alert-danger"><p>Status settings Not saved!</p></div>';
	}	

	/**
	 * Admin notice to confirm that the Abandoned cart  settings have been saved.
	 */
	function woo_abandoned_cart_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>Abandoned cart settings saved! *woohoo*</p></div>';
	}	
/**
	 * Admin notice to confirm that the Abandoned cart  settings have been saved.
	 */
	function woo_not_updated_abandoned_cart_notice() {

		echo '<div class="updated alert alert-global alert-danger"><p>Abandoned cart Not saved!</p></div>';
	}	

	/**
	 * Admin notice to confirm that the Back In Stock settings have been saved.
	 */
	function woo_backinstock_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>Back In Stock settings saved! *woohoo*</p></div>';
	}	
/**
	 * Admin notice to confirm that the Back In Stock settings have been saved.
	 */
	function woo_not_updated_backinstock_notice() {

		echo '<div class="updated alert alert-global alert-danger"><p>Back In Stock cart Not saved!</p></div>';
	}	
	/**
	 * Admin notice to confirm that the Price Drop Notification settings have been saved.
	 */
	function woo_pricedrop_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>Price Drop Notification settings saved! *woohoo*</p></div>';
	}	
/**
	 * Admin notice to confirm that the Price Drop Notification settings have been saved.
	 */
	function woo_not_updated_pricedrop_notice() {

		echo '<div class="updated alert alert-global alert-danger"><p>Price Drop Notification settings Not saved!</p></div>';
	}	
	/**
	 * Admin notice to confirm that the Product Review Reminder settings have been saved.
	 */
	function woo_product_review_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>Product Review Reminder settings saved! *woohoo*</p></div>';
	}	
/**
	 * Admin notice to confirm that the Product Review Reminder settings have been saved.
	 */
	function woo_not_updated_product_review_notice() {

		echo '<div class="updated alert alert-global alert-danger"><p>Product Review Reminder settings Not saved!</p></div>';
	}	
	/**
	 * Admin notice to confirm that the Welcome Discount settings have been saved.
	 */
	function woo_welcomediscount_notice() {

		echo '<div class="updated alert alert-global alert-info"><p>Welcome Discount settings saved! *woohoo*</p></div>';
	}	
/**
	 * Admin notice to confirm that the Welcome Discount settings have been saved.
	 */
	function woo_not_updated_welcomediscount_notice() {

		echo '<div class="updated alert alert-global alert-danger"><p>Welcome Discount settings Not saved!</p></div>';
	}	
	/**
	 * Renders a notice to say that the chosen plan is expired.
	 */
	function big_expired_plan_notice() {

		if ( ! $this->signed_in() ) {

			return;
		}

		$account_key = $this->account_key();
		$plan_response = $this->apiClient->get_plan_name( $account_key );
		$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		if ( ! $plan_expired ) {

			return;
		}
		$stats = $this->apiClient->get_stats( $account_key );
		if ( ! isset( $stats->subscribers ) ) {

			return;
		}

		$subscribers = $stats->subscribers;
		$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=plugin';
		$image_url = plugins_url( 'img/plugin-big-expiration-notice.png', plugin_dir_path( __FILE__ ) );
		$settings_url = admin_url( 'admin.php?page=push_monkey_main_config' );
		require_once( plugin_dir_path( __FILE__ ) . '../templates/messages/push_monkey_big_expiration_notice.php' );
	}

	/**
	 * Checks the Push Monkey API to see if the current price plan expired.
	 * @return boolean
	 */
	function can_show_expiration_notice() {

		if ( ! $this->signed_in() ) {

			return false;
		}
		$plan_response = $this->apiClient->get_plan_name( $this->account_key() );
		$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		return $plan_expired;
	}

	/**
	 * Renders an admin notice asking the user for an upgrade.
	 */
	function big_upsell_notice() {

		global $hook_suffix;
		if ( $hook_suffix != 'plugins.php' ) {

			return;
		}

		if ( ! $this->signed_in() ) {

			return;
		}

		$plan_response = $this->apiClient->get_plan_name( $this->account_key() );
		$plan_expired = isset( $plan_response->expired ) ? $plan_response->expired : false;
		$plan_can_upgrade = isset( $plan_response->can_upgrade ) ? $plan_response->can_upgrade : false;

		$push_monkey_us_notice_cookie = isset( $_COOKIE['push_monkey_us_notice'] ) ? $_COOKIE['push_monkey_us_notice'] : false;

		if ( $push_monkey_us_notice_cookie ) {

			return;
		}

		if ( ! $plan_expired && $plan_can_upgrade ) {

			$upgrade_url = $this->apiClient->endpointURL . '/v2/dashboard/upgrade?source=us-notice';
			$price_plans = $this->apiClient->endpointURL . '/#plans';
			$image_url = plugins_url( 'img/plugin-big-message-image.png', plugin_dir_path( __FILE__ ) );
			$close_url = plugins_url( 'img/banner-close-dark.png', plugin_dir_path( __FILE__ ) );
			require_once( plugin_dir_path( __FILE__ ) . '../templates/messages/push_monkey_upsell_notice.php' );
		}
	}

	/**
	 * Renders an admin notice for a first time user. Displays a few useful links to get started.
	 */
	function big_welcome_notice() {


		$push_monkey_welcome_notice_cookie = isset( $_COOKIE['push_monkey_welcome_notice'] ) ? $_COOKIE['push_monkey_welcome_notice'] : false;

		if ( ! $this->signed_in() ) {

			return;
		}

		if ( $push_monkey_welcome_notice_cookie ) {

			return;
		}

		$image_url = plugins_url( 'img/logo-party.png', plugin_dir_path( __FILE__ ) );
		$close_url = plugins_url( 'img/banner-close-dark.png', plugin_dir_path( __FILE__ ) );
		require_once( plugin_dir_path( __FILE__ ) . '../templates/messages/push_monkey_welcome_notice.php' );
	}

	/**
	 * Create WooCommerce Store.
	 */
	public function push_monkey_create_woo_store() {
		$store_exists = get_option( 'pm_store_token', false );
		if ( class_exists( 'woocommerce' ) && ( ! $store_exists && empty( $store_exists ) ) ) {
			// Generate token.
			$generate_token = bin2hex( random_bytes( 20 ) );
			// Send create store request.
			$store_url = add_query_arg( 'account_key', $this->account_key(), $this->endpointURL . '/woo/v1/api/store/' );
			$create_store = wp_remote_post( $store_url,
				array(
					'body' => array(
						'token' => $generate_token,
					),
					'headers' => array(
						'push-monkey-api-key' => base64_decode( $this->push_monkey_get_api_reset_key() ),
					),
				)
			);
			// If check API error.
			if ( ! is_wp_error( $create_store ) ) {
				$response = wp_remote_retrieve_body( $create_store );
				$response = json_decode( $response );
				if ( $response ) {
					update_option( 'pm_store_token', $generate_token );
				}
			}
		}
	}

	/**
	 * Create unique string.
	 *
	 * @param string $account_key Account key Default false.
	 * @return string Default false.
	 */
	public function push_monkey_api_reset_key( $account_key = false ) {
		if ( $account_key ) {
			return strtolower( trim( preg_replace( '/[^A-Za-z0-9-]+/', '', base64_encode( $account_key ) ) ) );
		}
		return false;
	}

	/**
	 * get login unique key.
	 *
	 * @return string Default false.
	 */
	public function push_monkey_get_api_reset_key( ) {
		$account_key = $this->account_key();
		if ( $account_key ) {
			$option_key = strtolower( trim( preg_replace( '/[^A-Za-z0-9-]+/', '', base64_encode( $account_key ) ) ) );
			return get_option( $option_key, '' );
		}
		return false;
	}

	/**
	 * Display SSL error.
	 */
	public function push_monkey_display_ssl_error() {
		// If check is dashboard page OR not.
		if ( isset( $_GET['page'] ) && ( 'push_monkey_main_config' === $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo wp_sprintf( '<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', esc_html( 'Your website seems to be correctly unsecured under HTTP!' ) );
		}
	}
}
