<?php
/*
 * This file belongs to the SMM Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 *
 *
 * @class smm-pointers
 * @package    SMMS
 * @since      Version 2.0.0
 * @author     Softnwords Themes
 *
 */
if ( ! class_exists( 'SMM_Pointers' ) ) {
    /**
     * SMM Pointers
     *
     * Initializes the new feature pointers.
     *
     * @class       SMM_Pointers
     * @package     SMMS
     * @since       1.0
     * @author      Softnwords Themes
     * @see         WP_Internal_Pointers
     */
    class SMM_Pointers {

        /**
         * @var SMM_Upgrade The main instance
         */
        protected static $_instance;

        /**
         * @var screen id where to show pointer
         */
        public $screen_ids = array();

        public $pointers = array();

        public $special_screen = array();

        protected $_plugins_registered = array();

        protected $_default_pointer = array();

        protected $_default_position = array( 'edge'  => 'left', 'align' => 'center' );

        /**
         * Construct
         *
         * @author sam softnwords
         * @since  1.0
         */
        public function __construct() {

            $this->_default_pointer['plugins'] = array(
                'screen_id'  => 'plugins',
                'options'    => array(
                    'content'  => sprintf( '<h3> %s </h3> <p> %s </p> <p> %s <a href="http://softnwords.com/product-category/plugins/" target="_blank">softnwords.com</a> %s
                                  <a href="https://profiles.wordpress.org/smmsemes/" target="_blank">Wordpress.org</a></p>',
                                __( 'Plugins Activated', 'smm-api' ),
                                __( 'From now on, you can find all plugin options in SMMS Plugins menu.
                                     Plugin customization settings will be available as a new entry in SMMS Plugins menu.', 'smm-api' ),
                                __( 'Discover all our plugins available on:', 'smm-api' ),
                                __( 'and', 'smm-api' )
                    ),
                ),
            );

            $this->_default_pointer['update'] = array(
                'screen_id'  => 'update',
                'options'    => array(
                    'content'  => sprintf( '<h3> %s </h3> <p> %s </p> <p> %s <a href="http://softnwords.com/product-category/plugins/" target="_blank">softnwords.com</a> %s
                                  <a href="https://profiles.wordpress.org/smmsemes/" target="_blank">Wordpress.org</a></p>',
                                __( 'Plugins Upgraded', 'smm-api' ),
                                __( 'From now on, you can find the option panel of SMMS plugins in SMMS Plugins menu.
                                    Every time one of our plugins is added, a new entry will be added to this menu.
                                    For example, after the update, plugin options (such as for SMMS WooCommerce Wishlist, SMMS WooCommerce Ajax Search, etc.)
                                    will be moved from previous location to SMMS Plugins tab.', 'smm-api' ),
                                __( 'Discover all our plugins available on:', 'smm-api' ),
                                __( 'and', 'smm-api' )
                    ),
                ),
            );

            $this->_default_pointer = $this->parse_args( $this->_default_pointer );

            /**
             * Screens that require a particular action
             */
            $this->special_screen = apply_filters( 'smm-pointer-special-screen', array( 'plugins', 'update' ) );

            add_action( 'admin_enqueue_scripts', array( $this, 'pointer_load' ) );
            add_action( 'admin_init', array( $this, 'add_pointers' ), 100 );
        }

        public function parse_args( $args ) {
            $default = array(
                'pointer_id' => 'smms_default_pointer',
                'target'     => '#toplevel_page_smm_plugin_panel',
                'init'       => null
            );

            foreach ( $args as $id => $pointer ) {
                $args[ $id ] = wp_parse_args( $pointer, $default );
                $args[ $id ]['options']['position'] = $this->_default_position;
            }

            return $args;
        }

        public function add_pointers(){
            if( ! empty( $this->screen_ids ) ){
                foreach( $this->screen_ids as $screen_id ){
                    add_filter( "smm_pointers-{$screen_id}", array( $this, 'pointers' ) );
                }
            }
        }

        /**
         * Main plugin Instance
         *
         * @static
         * @return object Main instance
         *
         * @since  1.0
         * @author sam softnwords
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        public function register( $args ) {

            foreach ( $args as $id => $pointer ) {

                extract( $pointer );

                if ( ! isset( $screen_id ) && ! empty( $screen_id ) && ! isset( $init ) && ! empty( $init ) ) {
                    return;
                }

                if ( ! in_array( $screen_id, $this->screen_ids ) ) {
                    $this->screen_ids[] = $screen_id;
                }

                $this->pointers[$screen_id][$pointer_id] = array(
                    'target'  => $target,
                    'options' => array(
                        'content'  => $content,
                        'position' => $position,
                    ),
                    'init'    => isset( $init ) ? $init : false
                );
            }
        }

        public function get_plugins_init( $screen_id ) {

            $registered = array();

            foreach( $this->pointers[ $screen_id ] as $pointer_id => $pointer ){
                $registered[ $pointer['init'] ] = $pointer_id;
            }

            return $registered;
        }

        public function pointer_load( $hook_suffix ) {

            /**
             * Get pointers for this screen
             */
            $screen     = get_current_screen();
            $pointers   = apply_filters( "smm_pointers-{$screen->id}", array() );

            if ( ! $pointers || ! is_array( $pointers ) ) {
                return;
            }

            /**
             * Get dismissed pointers
             */
            $dismissed      = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
            $valid_pointers = array();
            //$point_id       = null;

            /**
             * show pointers only on plugin activate action
             */
            if( in_array( $screen->id, $this->special_screen ) ){

                $show               = false;
                $registered         = $this->get_plugins_init( $screen->id );
                $recently_activate  = get_option( 'smm_recently_activated', array() );

                /**
                 * For "plugins" screen
                 */
                $is_single_activate = ( isset( $_GET['activate'] ) && 'true' == $_GET['activate'] ) ? true : false;
                $is_multi_activate  = ( isset( $_GET['activate-multi'] ) && 'true' == $_GET['activate-multi'] ) ? true : false;

                /**
                 * For "update" screen
                 *
                 * Single plugin update use GET method
                 *
                 * Multi update plugins with bulk action send two post args called "action" and "action2"
                 * action refer to first bulk action button (at the top of plugins table)
                 * action2 refer to last bulk action button (at the bottom of plugins table)
                 *
                 */
                $is_single_upgrade  = ( isset( $_GET['action'] ) && 'upgrade-plugin' == $_GET['action'] ) ? true : false;
                $is_multi_upgrade   = ( isset( $_POST['action'] ) && 'update-selected' == $_POST['action'] ) || ( isset( $_POST['action2'] ) && 'update-selected' == $_POST['action2'] ) ? true: false;

                if( $is_single_activate || $is_single_upgrade ){

                    $point_id = '';

                    /**
                     * Single activation plugin
                     * Single update plugin
                     */
                    foreach( $registered as $init => $p_id  ){
                        if ( in_array( $init, $recently_activate ) ) {
                            $point_id = $p_id;
                            $pointer = $pointers[ $point_id ];

                            /**
                             * Sanity check
                             */
                            if ( ! ( in_array( $point_id, $dismissed ) || empty( $pointer ) || empty( $point_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) ) ) {
                                /**
                                 * Add the pointer to $valid_pointers array
                                 */
                                $pointer['pointer_id']        = $point_id;
                                $valid_pointers['pointers'][] = $pointer;
                                $show                         = true;
                            }
                            break;
                        }
                    }
                } else if( $is_multi_activate || $is_multi_upgrade ){

                    /**
                     * Bulk Action: multi plugins activation
                     * Bulk Action: multi plugins update
                     */
                    $point_id   = array();
                    $screen_id  = $screen->id;

                    if( $is_multi_upgrade && isset( $_POST['checked'] ) && ( count( $_POST['checked'] )  > 0 ) ){
                        $recently_activate  = sanitize_text_field($_POST['checked']);
                        $screen_id          = 'update';
                        $pointers           = apply_filters( "smm_pointers-{$screen_id}", array() );
                    }

                    foreach ( $registered as $init => $p_id ) {
                        if ( in_array( $init, $recently_activate ) ) {
                            $point_id[] = $p_id;
                        }
                    }

                    /**
                     * Bulk Action: Activate Plugins
                     *
                     * count( $point_id ) is the number of SMMS plugins that have registered specific pointers
                     * case 0   -> No pointers -> Exit
                     * case 1   -> Only one pointers to show -> Use the specific plugin pointer
                     * defautl  -> Two or more plugins need to show a pointer -> use a generic pointers
                     *
                     */
                    switch ( count( $point_id ) ) {
                        case 0:
                            $show = false;
                            break;

                        case 1:
                            $point_id = array_pop( $point_id );
                            $pointer = $pointers[$point_id];
                            /**
                             * Sanity check
                             */
                            if ( ! ( in_array( $point_id, $dismissed ) || empty( $pointer ) || empty( $point_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) ) ) {
                                /**
                                 * Add the pointer to $valid_pointers array
                                 */
                                $pointer['pointer_id']        = $point_id;
                                $valid_pointers['pointers'][] = $pointer;
                                $show                         = true;
                            }
                            break;

                        default:
                            $valid_pointers['pointers'][] = $this->_default_pointer[ $screen_id ];
                            $show                         = true;
                            break;
                    }
                }

                update_option( 'smm_recently_activated', array() );

                if( ! $show ){
                    return;
                }

            } else {
                /**
                 * Check pointers and remove dismissed ones.
                 */
                foreach ( $pointers as $pointer_id => $pointer ) {

                    /**
                     * Sanity check
                     */
                    if ( in_array( $pointer_id, $dismissed ) || empty( $pointer ) || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) ) {
                        continue;
                    }

                    $pointer['pointer_id'] = $pointer_id;

                    /**
                     * Add the pointer to $valid_pointers array
                     */
                    $valid_pointers['pointers'][] = $pointer;
                }
            }

            /**
             * No valid pointers? Stop here.
             */
            if ( empty( $valid_pointers ) ) {
                return;
            }

            $script_file = function_exists( 'smm_load_js_file' ) ? smm_load_js_file( 'smm-wp-pointer.js' ) : 'smm-wp-pointer.min.js';

            /**
             * Enqueue wp-pointer script and style
             */
            wp_enqueue_style( 'wp-pointer' );
            wp_enqueue_script( 'wp-pointer' );

            wp_enqueue_script( 'smm-wp-pointer', SMM_CORE_PLUGIN_URL . '/assets/js/' . $script_file, array( 'wp-pointer' ), '1.0', true );
            wp_localize_script( 'smm-wp-pointer', 'custom_pointer', $valid_pointers );
        }

        public function pointers( $pointers ){
            $screen_id          = str_replace( 'smm_pointers-', '', current_filter() );
            $pointers_to_add    = $this->get_pointers( $screen_id  );

            return ! empty( $pointers_to_add ) ? array_merge( $pointers, $pointers_to_add ) : $pointers;
        }

        public function get_pointers( $screen_id ){
            return isset( $this->pointers[ $screen_id ] ) ? $this->pointers[ $screen_id ] : array();
        }
    }
}

if ( ! function_exists( 'SMM_Pointers' ) ) {
    /**
     * Main instance of plugin
     *
     * @return object SMM_Pointers
     * @since  1.0
     * @author sam softnwords
     */
    function SMM_Pointers() {
        return SMM_Pointers::instance();
    }
}

/**
 * Instance a SMM_Pointers object
 */
SMM_Pointers();