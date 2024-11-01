<?php

if ( !defined( 'ABSPATH' ) || !defined( 'SMMS_SMAPI_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements SMAPI_Subscription_Cron Class
 *
 * @class   SMAPI_Subscription_Cron
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
 */
if ( !class_exists( 'SMAPI_Subscription_Cron' ) ) {

    class SMAPI_Subscription_Cron {

	    /**
	     * Single instance of the class
	     *
	     * @var \SMAPI_Subscription_Cron
	     */
	    protected static $instance;

	    /**
	     * Returns single instance of the class
	     *
	     * @return \SMAPI_Subscription_Cron
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
	     * @author sam
	     */
	    public function __construct() {

				add_action( 'smapi_renew_cron', array( $this, 'set_cron' ), 30 );
	    		add_action( 'smapi_renew_orders', array( $this, 'renew_orders' ), 30 );
			if (get_option( 'smapi_cron_job' ) == 'yes'){
				
				do_action('smapi_renew_cron');
				
				}
			else{
				smm_cron_deactivate();
			}
	    	
            
	    	if ( smms_check_privacy_enabled( true ) ) {
				
			    add_action( 'smapi_trash_pending_subscriptions', array( $this, 'smapi_trash_pending_subscriptions' ) );
			    add_action( 'smapi_trash_cancelled_subscriptions', array( $this, 'smapi_trash_cancelled_subscriptions' ) );
		    }
			
	    }


	    public function set_cron() {

		    $ve = get_option( 'gmt_offset' ) > 0 ? '+' : '-';
		    $time_start = strtotime( '00:00 ' . $ve . get_option( 'gmt_offset' ) . ' HOURS' );
            
		    if ( ! wp_next_scheduled( 'smapi_renew_orders' ) ) {
			   wp_schedule_event( $time_start, 'hourly', 'smapi_renew_orders' );
			   do_action('smapi_renew_orders', $this);
			    //smapi_renew_orders calls renew order
                }

		    if ( smms_check_privacy_enabled(true) ) {
			    $trash_pending = get_option( 'smapi_trash_pending_subscriptions' );
			    if ( isset( $trash_pending['number'] ) && ! empty( $trash_pending['number'] ) && !wp_next_scheduled( 'smapi_trash_cancelled_subscriptions' ) ) {
				    wp_schedule_event( $time_start, 'daily', 'smapi_trash_pending_subscriptions' );
			    }

			    $trash_cancelled = get_option( 'smapi_trash_cancelled_subscriptions' );
			    if ( isset( $trash_cancelled['number'] ) && ! empty( $trash_cancelled['number'] ) && !wp_next_scheduled( 'smapi_trash_cancelled_subscriptions' ) ) {
				    wp_schedule_event( $time_start, 'daily', 'smapi_trash_cancelled_subscriptions' );
			    }
		    }
	    }


	    /**
	     * Renew Order
	     *
	     * Create new order for active or in trial period subscription
	     *
	     * @author sam softnwords
	     */
	    public function renew_orders() {

		    global $wpdb;

		    $to         = current_time('timestamp') + 86400;
            $to_time    = current_time('timestamp');
		    

		    $subscriptions = $wpdb->get_row( $wpdb->prepare( "SELECT ID,post_content,post_title,post_excerpt FROM {$wpdb->prefix}posts 
		         WHERE post_type  = %s 
                 AND post_status  = 'publish'
                 AND post_excerpt > 0
                 AND post_content < %d
                 GROUP BY ID ORDER BY ID DESC
                ", 'smapi_subscription', $to_time ));
		  //  file_put_contents(plugin_dir_path( __FILE__ )."check.txt",serialize($subscriptions).PHP_EOL);
	
		   
		    if ( ! empty( $subscriptions ) ) {
			    foreach ( $subscriptions as $subscription ) {
			        
			    	$sbs = smapi_get_subscription( $subscription->ID );
				    $renew_order = $sbs->subscription_meta_data['renew_order'];
				    $can_be_renewed = 0 ;
				    $sub_status = 0;
				    if($subscription->post_title == 'active' ||
				    $subscription->post_title == 'renew' ||
				    $subscription->post_title == 'renewed')
				    $sub_status = 1;
				    if ($subscription->post_content < current_time('timestamp'))
				    $can_be_renewed = 1;
					 
				    if ( $renew_order == 1 && $can_be_renewed == 1 && $sub_status == 1) {
				        
				    // subscription order renews if it has created
                    $renew_subscription = $sbs->renew_subscription($subscription->ID);
                     
                    // New Order is creted in woocomerce order list   
					$order_id = SMAPI_Subscription_Order()->renew_order( $subscription->ID );
					
					// New Api trigger based on order data if the subscription status is active.renew/renewed
					// subsciption update is called if new api order is created at server   
				
				    SMAPI_Subscription_Order()->smm_api_order_trigger($order_id);
					    
				    }
			    }
		    }
	    }

	    /**
	     * Trash pending subscriptions after a specific time.
	     *
	     * @since 1.4.0
	     * @author sam softnwords
	     */
	    public function smapi_trash_pending_subscriptions() {
		    global $wpdb;
		    $trash_pending = get_option( 'smapi_trash_pending_subscriptions' );
		    if ( ! isset( $trash_pending['number'] ) || empty( $trash_pending['number'] ) ) {
			    return;
		    }

		    $time = strtotime( '-' . $trash_pending['number'] . ' ' . $trash_pending['unit'] );

		    $subscriptions = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_p.ID FROM {$wpdb->prefix}posts as smapi_p
                 INNER JOIN  {$wpdb->prefix}postmeta as smapi_pm ON ( smapi_p.ID = smapi_pm.post_id )
                 WHERE ( smapi_pm.meta_key='status' AND  smapi_pm.meta_value = 'pending' )
                 AND smapi_p.post_type = %s
                 AND smapi_p.post_status = 'publish'
                 AND smapi_p.post_date < %s 
                 GROUP BY smapi_p.ID ORDER BY smapi_p.ID DESC
                ", 'smapi_subscription', gmdate( 'Y-m-d H:i:s', $time ) ) );


		    if ( ! empty( $subscriptions ) ) {
			    foreach ( $subscriptions as $subscription ) {
				    $subscription_id = $subscription->ID;
				    wp_trash_post( $subscription_id );
				    do_action( 'smapi_subscription_trashed', $subscription_id );
			    }
		    }

	    }

	    /**
	     * Trash cancelled subscriptions after a specific time.
	     *
	     * @since 1.4.0
	     * @author sam softnwords
	     */
	    public function smapi_trash_cancelled_subscriptions() {
		    global $wpdb;
		    $trash_cancelled = get_option( 'smapi_trash_cancelled_subscriptions' );
		    if ( ! isset( $trash_cancelled['number'] ) || empty( $trash_cancelled['number'] ) ) {
			    return;
		    }

		    $time = strtotime( '-' . $trash_cancelled['number'] . ' ' . $trash_cancelled['unit'] );

		    $subscriptions = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_p.ID FROM {$wpdb->prefix}posts as smapi_p
                 INNER JOIN  {$wpdb->prefix}postmeta as smapi_pm ON ( smapi_p.ID = smapi_pm.post_id )
                 INNER JOIN  {$wpdb->prefix}postmeta as smapi_pm2 ON ( smapi_p.ID = smapi_pm2.post_id )
                 WHERE ( smapi_pm.meta_key='status' AND  smapi_pm.meta_value = 'cancelled' )
                 AND smapi_p.post_type = %s
                 AND smapi_p.post_status = 'publish'
                 AND ( smapi_pm2.meta_key='cancelled_date' AND  smapi_pm2.meta_value  < %d )
                 GROUP BY smapi_p.ID ORDER BY smapi_p.ID DESC
                ", 'smapi_subscription', $time ) );

		    if ( ! empty( $subscriptions ) ) {
			    foreach ( $subscriptions as $subscription ) {
				    $subscription_id = $subscription->ID;
				    wp_trash_post( $subscription_id );
				    do_action( 'smapi_subscription_trashed', $subscription_id );
				    SMMS_WC_Activity()->add_activity( $subscription_id, 'trashed', 'success', 0, __( 'The subscription was been trashed after the specific duration because was in cancelled status.', 'smm-api' ) );
			    }
		    }

	    }
    }
}

/**
 * Unique access to instance of SMAPI_Subscription_Cron class
 *
 * @return \SMAPI_Subscription_Cron
 */
function SMAPI_Subscription_Cron() {
    return SMAPI_Subscription_Cron::get_instance();
}

