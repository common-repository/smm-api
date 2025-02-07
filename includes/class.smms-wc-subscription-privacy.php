<?php

if ( ! defined( 'ABSPATH' ) || ! defined( 'SMMS_SMAPI_VERSION' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Implements admin features of SMMS WooCommerce Subscription
 *
 * @class   SMAPI_Subscription_Privacy
 * @package SMMS WooCommerce Subscription
 * @since   1.4.0
 * @author  SMMS
 */
if ( ! class_exists( 'SMAPI_Subscription_Privacy' ) ) {

	class SMAPI_Subscription_Privacy {

		/**
		 * Single instance of the class
		 *
		 * @var \SMAPI_Subscription_Privacy
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \SMAPI_Subscription_Privacy
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0.0
		 */
		public function __construct() {
			add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporters' ), 5 );
			add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_erasers' ), 4 );
		}

		/**
		 * Register the exporter for SMMS Subscription.
		 *
		 * @param array $exporters
		 *
		 * @return array
		 * @author sam softnwords
		 */
		public function register_exporters( $exporters = array() ) {
			$exporters['smapi-customer-subscriptions'] = array(
				'exporter_friendly_name' => __( 'Customer Subscriptions', 'smm-api' ),
				'callback'               => array( 'SMAPI_Subscription_Privacy', 'subscription_data_exporter' ),
			);
			return $exporters;
		}

		/**
		 * Register the eraser for SMMS Subscription.
		 *
		 * @param array $erasers
		 *
		 * @return array
		 * @author sam softnwords
		 */
		public function register_erasers( $erasers = array() ) {
			$erasers['smapi-customer-subscriptions'] = array(
				'eraser_friendly_name' => __( 'Customer Subscriptions', 'smm-api' ),
				'callback'               => array( 'SMAPI_Subscription_Privacy', 'subscription_data_eraser' ),
			);

			return $erasers;
		}

		/**
		 *
		 * @param $email_address
		 * @param $page
		 *
		 * @return array
		 * @author sam softnwords
		 */
		public static function subscription_data_exporter( $email_address, $page ) {
			$done           = false;
			$data_to_export = array();

			$subscription_query = self::get_query_args( $email_address, $page );

			$subscriptions = ! empty( $subscription_query ) ? get_posts( $subscription_query ) : false;

			if ( $subscriptions ) {
				foreach ( $subscriptions as $subscription ) {
					$data_to_export[] = array(
						'group_id'    => 'smapi_subscriptions',
						'group_label' => __( 'Subscriptions', 'smm-api' ),
						'item_id'     => 'subscription-' . $subscription->ID,
						'data'        => self::get_subscription_personal_data( $subscription ),
					);
				}
				$done = 10 > count( $subscriptions );
			}else {
				$done = true;
			}

			return array(
				'data' => $data_to_export,
				'done' => $done,
			);
		}

		/**
		 * @param $email_address
		 * @param $page
		 *
		 * @return array
		 * @author sam softnwords
		 */
		public static function subscription_data_eraser( $email_address, $page ) {

			$erasure_enabled = wc_string_to_bool( get_option( 'smapi_erasure_request', 'no' ) );

			$response        = array(
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => array(),
				'done'           => true,
			);

			$subscription_query = self::get_query_args( $email_address, $page );

			$subscriptions = ! empty( $subscription_query ) ? get_posts( $subscription_query ) : false;

			if ( $subscriptions ) {
				foreach ( $subscriptions as $subscription ) {
					if ( apply_filters( 'smapi_privacy_erase_personal_data', $erasure_enabled, $subscription ) ) {
						self::remove_personal_data( $subscription );

						/* Translators: %s Order number. */
						$response['messages'][]    = sprintf( __( 'Removed personal data from subscription %s.', 'smm-api' ), esc_attr($subscription->ID) );
						$response['items_removed'] = true;
					} else {
						/* Translators: %s Order number. */
						$response['messages'][]     = sprintf( __( 'Personal data within subscription %s has been retained.', 'smm-api' ), esc_attr($subscription->ID) );
						$response['items_retained'] = true;
					}
				}
				$response['done'] = 10 > count( $subscriptions );
			} else {
				$response['done'] = true;
			}

			return $response;
		}

		/**
		 * @param $email_address
		 * @param $page
		 *
		 * @return array|string
		 * @author sam softnwords
		 */
		protected static function get_query_args( $email_address, $page ) {
			$subscription_query = '';
			$user               = get_user_by( 'email', $email_address ); // Check if user has an ID in the DB to load stored personal data.
			$page               = (int) $page;


			if ( $user instanceof WP_User ) {
				$subscription_query = array(
					'post_type'      => 'smapi_subscription',
					'posts_per_page' => 10,
					'paged'          => $page,
					'meta_key'       => '_user_id',
					'meta_value'     => (int) $user->ID,
				);
			} else {
				$order_list  = array();
				$order_query = array(
					'limit'    => - 1,
					'customer' => array( $email_address ),
				);

				$orders = wc_get_orders( $order_query );

				if ( 0 < count( $orders ) ) {
					foreach ( $orders as $order ) {
						$order_list[] = $order->get_id();
					}

					$subscription_query = array(
						'post_type'      => 'smapi_subscription',
						'posts_per_page' => 10,
						'paged'          => $page,
						'meta_query'     => array(
							array(
								'key'     => '_order_id',
								'value'   => $order_list,
								'compare' => 'IN'
							)
						)
					);
				}
			}

			return $subscription_query;
		}

		/**
		 * @param $subscription_post
		 *
		 * @return array
		 * @author sam softnwords
		 */
		protected static function get_subscription_personal_data( $subscription_post ) {
			$personal_data = array();
			$subscription  = smapi_get_subscription( $subscription_post->ID );

			$props_to_export = apply_filters( 'smapi_privacy_export_personal_data_props', array(
				'id'                         => __( 'Subscription Number', 'smm-api' ),
				'status'                     => __( 'Subscription Status', 'smm-api' ),
				'date_created'               => __( 'Subscription Creation Date', 'smm-api' ),
				'subscription_total'         => __( 'Subscription Total', 'smm-api' ),
				'item'                       => __( 'Items Purchased', 'smm-api' ),
				'_customer_ip_address'        => __( 'IP Address', 'smm-api' ),
				'_customer_user_agent'        => __( 'Browser User Agent', 'smm-api' ),
				'formatted_billing_address'  => __( 'Billing Address', 'smm-api' ),
				'formatted_shipping_address' => __( 'Shipping Address', 'smm-api' ),
				'billing_phone'              => __( 'Phone Number', 'smm-api' ),
				'billing_email'              => __( 'Email Address', 'smm-api' ),
			), $subscription, $subscription_post );


			foreach ( $props_to_export as $prop => $name ) {
				$value  = '';
				$fields = '';
				switch ( $prop ) {
					case 'item':
						$value = $subscription->_product_name . ' x ' . $subscription->_quantity;
						break;
					case 'date_created':
						$value = mysql2date( get_option( 'date_format' ), $subscription_post->post_date );
						break;
					case 'status':
						$status = smapi_get_status();
						$value = isset( $status[ $subscription->status ] ) ? $status[ $subscription->status ] : '';
						break;
					case 'subscription_total':
						$value = wc_price( $subscription->_subscription_total, $subscription->_order_currency );
						break;
					case 'formatted_billing_address':
						$fields = $subscription->get_address_fields( 'billing', true );
					case 'formatted_shipping_address':
						$fields = empty( $fields ) ? $subscription->get_address_fields( 'shipping', true ) : $fields;

						$address = WC()->countries->get_formatted_address( $fields );
						$value   = preg_replace( '#<br\s*/?>#i', ', ', $address );
						break;
					default:
						if ( is_callable( array( $subscription, 'get_' . $prop ) ) ) {
							$value = $subscription->{"get_$prop"}();
						} else {
							$value = $subscription->get( $prop );
						}
						break;
				}

				$value = apply_filters( 'smapi_privacy_export_personal_data_prop', $value, $prop, $subscription );

				if ( $value ) {
					$personal_data[] = array(
						'name'  => $name,
						'value' => $value,
					);
				}
			}

			return $personal_data;
		}


		/**
		 * @param $subscription_post
		 *
		 * @author sam softnwords
		 */
		protected static function remove_personal_data( $subscription_post ){
			$anonymized_data = array();

			/**
			 * Expose props and data types we'll be anonymizing.
			 *
			 * @since 3.4.0
			 * @param array    $props Keys are the prop names, values are the data type we'll be passing to wp_privacy_anonymize_data().
			 * @param WC_Order $order A customer object.
			 */

			$subscription = smapi_get_subscription( $subscription_post->ID );

			if ( apply_filters( 'smapi_cancel_subscription_before_remove_personal_data', true ) && $subscription->can_be_cancelled() ) {
				$subscription->cancel_subscription();
			}

			$props_to_remove = apply_filters( 'smapi_privacy_remove_personal_data_props', array(
				'_customer_ip_address'    => 'ip',
				'_customer_user_agent'    => 'text',
				'_billing_first_name'    => 'text',
				'_billing_last_name'     => 'text',
				'_billing_company'       => 'text',
				'_billing_address_1'     => 'text',
				'_billing_address_2'     => 'text',
				'_billing_city'          => 'text',
				'_billing_postcode'      => 'text',
				'_billing_state'         => 'address_state',
				'_billing_country'       => 'address_country',
				'_billing_phone'         => 'phone',
				'_billing_email'         => 'email',
				'_shipping_first_name'   => 'text',
				'_shipping_last_name'    => 'text',
				'_shipping_company'      => 'text',
				'_shipping_address_1'    => 'text',
				'_shipping_address_2'    => 'text',
				'_shipping_city'         => 'text',
				'_shipping_postcode'     => 'text',
				'_shipping_state'        => 'address_state',
				'_shipping_country'      => 'address_country',
				'_user_id'                => 'numeric_id',
			), $subscription, $subscription_post );



			if ( ! empty( $props_to_remove ) && is_array( $props_to_remove ) ) {
				foreach ( $props_to_remove as $prop => $data_type ) {

					$value = $subscription->get( $prop );

					// If the value is empty, it does not need to be anonymized.
					if ( empty( $value ) || empty( $data_type ) ) {
						continue;
					}

					if ( function_exists( 'wp_privacy_anonymize_data' ) ) {
						$anon_value = wp_privacy_anonymize_data( $data_type, $value );
					} else {
						$anon_value = '';
					}

					$anonymized_data[ $prop ] = apply_filters( 'smapi_privacy_remove_personal_data_prop_value', $anon_value, $prop, $value, $data_type, $subscription );
				}
			}

			$subscription->update_subscription_meta( $anonymized_data );

		}
	}
}

/**
 * Unique access to instance of SMAPI_Subscription_Privacy class
 *
 * @return \SMAPI_Subscription_Privacy
 */
function SMAPI_Subscription_Privacy() {
	return SMAPI_Subscription_Privacy::get_instance();
}
