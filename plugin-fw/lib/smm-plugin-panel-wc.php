<?php
/**
 * This file belongs to the SMM Plugin Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'SMM_Plugin_Panel_WooCommerce' ) ) {
    /**
     * SMM Plugin Panel for WooCommerce
     * Setting Page to Manage Plugins
     *
     * @class      SMM_Plugin_Panel
     * @package    SMMS
     * @since      1.0
     * @author     sam
     * @author     sam
     */
    class SMM_Plugin_Panel_WooCommerce extends SMM_Plugin_Panel {

        /**
         * @var string version of class
         */
        public $version = '1.0.0';

        /**
         * @var array a setting list of parameters
         */
        public $settings = array();

        /**
         * @var array a setting list of parameters
         */
        public static $wc_type = array( 'checkbox', 'textarea', 'multiselect', 'multi_select_countries', 'image_width' );

        /**
         * @var array a setting list of parameters
         */
        public static $body_class = ' smms-plugin-fw-panel ';


        /**
         * @var array
         */
        protected $_tabs_path_files;

        /**
         * @var bool
         */
        protected static $_actions_initialized = false;

        /**
         * Constructor
         *
         * @since    1.0
         * @author   sam softnwords
         * @author   sam
         */
        public function __construct( $args = array() ) {

            $args = apply_filters( 'smm_plugin_fw_wc_panel_option_args', $args );
            if ( !empty( $args ) ) {
                if ( isset( $args[ 'parent_page' ] ) && 'smm_plugin_panel' === $args[ 'parent_page' ] )
                    $args[ 'parent_page' ] = 'smms_plugin_panel';

                $this->settings         = $args;
                $this->_tabs_path_files = $this->get_tabs_path_files();

                if ( isset( $this->settings[ 'create_menu_page' ] ) && $this->settings[ 'create_menu_page' ] ) {
                    $this->add_menu_page();
                }

                if ( !empty( $this->settings[ 'links' ] ) ) {
                    $this->links = $this->settings[ 'links' ];
                }

                add_action( 'admin_init', array( $this, 'set_default_options' ) );
                add_action( 'admin_menu', array( $this, 'add_setting_page' ) );
                add_action( 'admin_menu', array( $this, 'add_premium_version_upgrade_to_menu' ), 100 );
                add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 100 );
                add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
                add_action( 'admin_init', array( $this, 'woocommerce_update_options' ) );
                add_filter( 'woocommerce_screen_ids', array( $this, 'add_allowed_screen_id' ) );

                add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'maybe_unserialize_panel_data' ), 10, 3 );

                //smms-plugin-ui
                add_action( 'smms_plugin_fw_get_field_after', array( $this, 'add_smms_ui' ) );
                add_action( 'smms_plugin_fw_before_woocommerce_panel', array( $this, 'add_plugin_banner' ), 10, 1 );
                add_action( 'admin_action_smms_plugin_fw_save_toggle_element', array( $this, 'save_toggle_element_options' ) );


                // init actions once to prevent multiple actions
                static::_init_actions();
            }
        }

        protected static function _init_actions() {
            if ( !static::$_actions_initialized ) {
                /* Add VideoBox and InfoBox */
                //add_action( 'woocommerce_admin_field_boxinfo', array( __CLASS__, 'add_infobox' ), 10, 1 );

                /* Add SMMS Fields */
                add_action( 'woocommerce_admin_field_smms-field', array( __CLASS__, 'add_smms_field' ), 10, 1 );

                /* WooCommerce 2.4 Support */
                add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );

                add_filter( 'woocommerce_admin_settings_sanitize_option', array( __CLASS__, 'sanitize_option' ), 10, 3 );

                // sort plugins by name in SMMS Plugins menu
                add_action( 'admin_menu', array( __CLASS__, 'sort_plugins' ), 90 );

                add_filter( 'add_menu_classes', array( __CLASS__, 'add_menu_class_in_smms_plugin' ) );


                static::$_actions_initialized = true;
            }
        }


        /**
         * Show a tabbed panel to setting page
         * a callback function called by add_setting_page => add_submenu_page
         *
         * @return   void
         * @since    1.0
         * @author   sam
         * @author   sam
         */
        public function smm_panel() {
            $additional_info = array(
                'current_tab'    => $this->get_current_tab(),
                'available_tabs' => $this->settings[ 'admin-tabs' ],
                'default_tab'    => $this->get_available_tabs( true ), //get default tabs
                'page'           => $this->settings[ 'page' ],
                'wrap_class'     => isset( $this->settings[ 'class' ] ) ? $this->settings[ 'class' ] : '',
            );


            $additional_info                      = apply_filters( 'smms_admin_tab_params', $additional_info );
            $additional_info[ 'additional_info' ] = $additional_info;

            extract( $additional_info );
            require_once( SMM_CORE_PLUGIN_TEMPLATE_PATH . '/panel/woocommerce/woocommerce-panel.php' );
        }

        /**
         * Show a input fields to upload images
         *
         * @return   string
         * @since    1.0
         * @author   sam
         */

        public function smm_upload_update( $option_value ) {
            return $option_value;
        }

        /**
         * Show a input fields to upload images
         *
         * @param array $args
         * @since    1.0
         * @author   sam
         */

        public function smm_upload( $args = array() ) {
            if ( !empty( $args ) ) {
                $args[ 'value' ] = ( get_option( $args[ 'id' ] ) ) ? get_option( $args[ 'id' ] ) : $args[ 'default' ];
                extract( $args );

                include( SMM_CORE_PLUGIN_TEMPLATE_PATH . '/panel/woocommerce/woocommerce-upload.php' );
            }
        }

        /**
         * Add the plugin woocommerce page settings in the screen ids of woocommerce
         *
         * @param $screen_ids
         * @return mixed
         * @since    1.0.0
         * @author   softnwords
         */
        public function add_allowed_screen_id( $screen_ids ) {
            global $admin_page_hooks;

            if ( !isset( $admin_page_hooks[ $this->settings[ 'parent_page' ] ] ) ) {
                return $screen_ids;
            }

            $screen_ids[] = $admin_page_hooks[ $this->settings[ 'parent_page' ] ] . '_page_' . $this->settings[ 'page' ];

            return $screen_ids;
        }

        /**
         * Returns current active tab slug
         *
         * @return string
         * @since    2.0.0
         * @author   sam
         * @author   sam
         */
        public function get_current_tab() {
            global $pagenow;
            $tabs = $this->get_available_tabs();

            if ( $pagenow == 'admin.php' && isset( $_REQUEST[ 'tab' ] ) && in_array( $_REQUEST[ 'tab' ], $tabs ) ) {
                return sanitize_text_field($_REQUEST[ 'tab' ]);
            } else {
                return $tabs[ 0 ];
            }
        }

        /**
         * Return available tabs
         * read all options and show sections and fields
         *
         * @param bool false for all tabs slug, true for current tab
         * @return mixed Array tabs | String current tab
         * @since    1.0
         * @author   sam
         * @author   sam
         */
        public function get_available_tabs( $default = false ) {
            $tabs = array_keys( $this->settings[ 'admin-tabs' ] );

            return $default ? $tabs[ 0 ] : $tabs;
        }


        /**
         * Add sections and fields to setting panel
         * read all options and show sections and fields
         *
         * @return void
         * @since    1.0
         * @author   sam
         * @author   sam
         */
        public function add_fields() {


            $smm_options = $this->get_main_array_options();
            $current_tab = $this->get_current_tab();

            if ( !$current_tab ) {
                return;
            }

            woocommerce_admin_fields( $smm_options[ $current_tab ] );
        }
        

        /**
         * Print the panel content
         * check if the tab is a wc options tab or custom tab and print the content
         *
         * @return void
         * @since    1.0
         * @author   sam
         * @author   sam
         * @author   sam
         */
        public function print_panel_content() {
            $smm_options       = $this->get_main_array_options();
            $current_tab       = $this->get_current_tab();
            $custom_tab_action = $this->is_custom_tab( $smm_options, $current_tab );

            if ( $custom_tab_action ) {
                $this->print_custom_tab( $custom_tab_action );

                return;
            } else {
                
                require_once( SMM_CORE_PLUGIN_TEMPLATE_PATH . '/panel/woocommerce/woocommerce-form.php' );
            }
        }

        /**
         * Update options
         *
         * @return void
         * @since    1.0
         * @author   sam
         * @author   sam
         * @see      woocommerce_update_options function
         * @internal fire two action (before and after update): smm_panel_wc_before_update and smm_panel_wc_after_update
         */
        public function woocommerce_update_options() {

            if ( isset( $_POST[ 'smm_panel_wc_options_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST[ 'smm_panel_wc_options_nonce' ])), 'smm_panel_wc_options_' . $this->settings[ 'page' ] ) ) {

                do_action( 'smm_panel_wc_before_update' );

                $smm_options = $this->get_main_array_options();
                $current_tab = $this->get_current_tab();

                if ( version_compare( WC()->version, '2.4.0', '>=' ) ) {
                    if ( !empty( $smm_options[ $current_tab ] ) ) {
                        foreach ( $smm_options[ $current_tab ] as $option ) {
                            if ( isset( $option[ 'id' ] ) && isset( $_POST[ $option[ 'id' ] ] ) && isset( $option[ 'type' ] ) && !in_array( $option[ 'type' ], self::$wc_type ) ) {
                                $_POST[ $option[ 'id' ] ] = maybe_serialize( wc_clean($_POST[ $option[ 'id' ] ]));
                            }
                        }
                    }
                }
				if(wc_clean($_POST[ $option[ 'id' ] ])){
				$posts = wc_clean($_POST);//phpcs:ignore
                foreach ( $posts as $name => $value ) {

                    //  Check if current POST var name ends with a specific needle and make some stuff here
                    $attachment_id_needle = "-smms-attachment-id";
                    $is_hidden_input      = ( ( $temp = strlen( $name ) - strlen( $attachment_id_needle ) ) >= 0 && strpos( $name, $attachment_id_needle, $temp ) !== false );
                    if ( $is_hidden_input ) {
                        //  Is an input element of type "hidden" coupled with an input element for selecting an element from the media gallery
                        $smm_options[ $current_tab ][ $name ] = array(
                            "type" => "text",
                            "id"   => $name
                        );
                    }
                }
				}
                woocommerce_update_options( $smm_options[ $current_tab ] );

                do_action( 'smm_panel_wc_after_update' );

            } elseif ( isset( $_REQUEST[ 'smm-action' ] ) && $_REQUEST[ 'smm-action' ] == 'wc-options-reset'
                       && isset( $_POST[ 'smms_wc_reset_options_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST[ 'smms_wc_reset_options_nonce' ])), 'smms_wc_reset_options_' . $this->settings[ 'page' ] )
            ) {

                do_action( 'smm_panel_wc_before_reset' );

                $smm_options = $this->get_main_array_options();
                $current_tab = $this->get_current_tab();

                foreach ( $smm_options[ $current_tab ] as $id => $option ) {
                    if ( isset( $option[ 'default' ] ) ) {
                        update_option( $option[ 'id' ], $option[ 'default' ] );
                    }
                }

                do_action( 'smm_panel_wc_after_reset' );
            }
        }

        /**
         * Add Admin WC Style and Scripts
         *
         * @return void
         * @since    1.0
         * @author   sam
         * @author   sam
         * @author   sam
         */
        public function admin_enqueue_scripts() {
            global $woocommerce, $pagenow;

            if ( 'customize.php' != $pagenow ) {
                wp_enqueue_style( 'wp-jquery-ui-dialog' );
            }

            // enqueue styles only in the current panel page
            if ( 'admin.php' === $pagenow && strpos( get_current_screen()->id, $this->settings[ 'page' ] ) !== false ) {
                $woocommerce_version       = function_exists( 'WC' ) ? WC()->version : $woocommerce->version;
                $woocommerce_settings_deps = array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris' );

                if ( version_compare( '2.5', $woocommerce_version, '<=' ) ) {
                    // WooCommerce > 2.6
                    $woocommerce_settings_deps[] = 'select2';
                } else {
                    // WooCommerce < 2.6
                    $woocommerce_settings_deps[] = 'jquery-ui-dialog';
                    $woocommerce_settings_deps[] = 'chosen';
                }

                wp_enqueue_media();

                wp_enqueue_style( 'smms-plugin-fw-fields' );
                wp_enqueue_style( 'woocommerce_admin_styles' );
                wp_enqueue_style( 'raleway-font' );

                wp_enqueue_script( 'woocommerce_settings', $woocommerce->plugin_url() . '/assets/js/admin/settings.min.js', $woocommerce_settings_deps, $woocommerce_version, true );
                wp_localize_script( 'woocommerce_settings', 'woocommerce_settings_params', array(
                    'i18n_nav_warning' => __( 'The changes you have made will be lost if you leave this page.', 'smm-api' )
                ) );

                wp_enqueue_script( 'smms-plugin-fw-fields' );
            }

            if ( 'admin.php' === $pagenow && strpos( get_current_screen()->id, 'smms-plugins_page' ) !== false ) {
                wp_enqueue_media();
                wp_enqueue_style( 'smm-plugin-style' );
                wp_enqueue_script( 'smm-plugin-panel' );
            }

            if ( 'admin.php' === $pagenow && strpos( get_current_screen()->id, 'smms_upgrade_premium_version' ) !== false ) {
                wp_enqueue_style( 'smm-upgrade-to-pro' );
                wp_enqueue_script( 'colorbox' );
            }
        }

        /**
         * Default options
         * Sets up the default options used on the settings page
         *
         * @access public
         * @return void
         * @since  1.0.0
         */
        public function set_default_options() {
            // check if the default options for this panel are already set
            $page                = $this->settings[ 'page' ];
            $default_options_set = get_option( 'smm_plugin_fw_panel_wc_default_options_set', array() );
            if ( isset( $default_options_set[ $page ] ) && $default_options_set[ $page ] )
                return;

            $default_options = $this->get_main_array_options();

            foreach ( $default_options as $section ) {
                foreach ( $section as $value ) {
                    if ( ( isset( $value[ 'std' ] ) || isset( $value[ 'default' ] ) ) && isset( $value[ 'id' ] ) ) {
                        $default_value = ( isset( $value[ 'default' ] ) ) ? $value[ 'default' ] : $value[ 'std' ];

                        if ( $value[ 'type' ] == 'image_width' ) {
                            add_option( $value[ 'id' ] . '_width', $default_value );
                            add_option( $value[ 'id' ] . '_height', $default_value );
                        } else {
                            add_option( $value[ 'id' ], $default_value );
                        }
                    }

                }
            }

            // set the flag for the default options of this panel
            $default_options_set[ $page ] = true;
            update_option( 'smm_plugin_fw_panel_wc_default_options_set', $default_options_set );
        }

        /**
         * Delete the "default options added" option
         *
         * @author   sam
         */
        public static function delete_default_options_set_option() {
            delete_option( 'smm_plugin_fw_panel_wc_default_options_set' );
        }

        /**
         * Add the woocommerce body class in plugin panel page
         *
         * @author sam softnwords
         * @since  2.0
         * @param $classes The body classes
         * @return array Filtered body classes
         */
        public static function admin_body_class( $admin_body_classes ) {
            global $pagenow;

            if ( ( 'admin.php' == $pagenow && strpos( get_current_screen()->id, 'smms-plugins_page' ) !== false ) )
                $admin_body_classes = substr_count( $admin_body_classes, self::$body_class ) == 0 ? $admin_body_classes . self::$body_class : $admin_body_classes;

            return 'admin.php' == $pagenow && substr_count( $admin_body_classes, 'woocommerce' ) == 0 ? $admin_body_classes .= ' woocommerce ' : $admin_body_classes;
        }

        /**
         * Maybe unserialize panel data
         *
         * @param $value     mixed  Option value
         * @param $option    mixed  Option settings array
         * @param $raw_value string Raw option value
         * @return mixed Filtered return value
         * @author sam
         * @since  2.0
         */
        public function maybe_unserialize_panel_data( $value, $option, $raw_value ) {


            if ( !version_compare( WC()->version, '2.4.0', '>=' ) || !isset( $option[ 'type' ] ) || in_array( $option[ 'type' ], self::$wc_type ) ) {
                return $value;
            }

            $smm_options = $this->get_main_array_options();
            $current_tab = $this->get_current_tab();

            if ( !empty( $smm_options[ $current_tab ] ) ) {
                foreach ( $smm_options[ $current_tab ] as $option_array ) {
                    if ( isset( $option_array[ 'id' ] ) && isset( $option[ 'id' ] ) && $option_array[ 'id' ] == $option[ 'id' ] ) {
                        return maybe_unserialize( $value );
                    }
                }
            }

            return $value;
        }

        /**
         * Sanitize Option
         *
         * @param $value     mixed  Option value
         * @param $option    mixed  Option settings array
         * @param $raw_value string Raw option value
         * @return mixed Filtered return value
         * @author sam
         * @since  3.0.0
         */
        public static function sanitize_option( $value, $option, $raw_value ) {

            if ( isset( $option[ 'type' ] ) && 'smms-field' === $option[ 'type' ] ) {
                // set empty array if is multiple
                if ( !empty( $option[ 'multiple' ] ) && is_null( $value ) ) {
                    $value = array();
                }

                // sanitize the option for the checkbox field: 'yes' or 'no'
                if ( isset( $option[ 'smms-type' ] ) && in_array( $option[ 'smms-type' ], array( 'checkbox', 'onoff' ) ) ) {
                    $value = smms_plugin_fw_is_true( $raw_value ) ? 'yes' : 'no';
                }

                if ( isset( $option[ 'smms-type' ] ) && in_array( $option[ 'smms-type' ], array( 'textarea', 'textarea-editor', 'textarea-codemirror' ) ) ) {
                    $value = $raw_value;
                }

                // sanitize the option date-format when the user choose the custom option
                if ( isset( $option[ 'smms-type' ] ) && in_array( $option[ 'smms-type' ], array( 'date-format' ) ) && '\c\u\s\t\o\m' == $raw_value ) {
                    $custom = isset( $_REQUEST[ $option[ 'id' ] . '_text' ] ) ? sanitize_text_field($_REQUEST[ $option[ 'id' ] . '_text' ] ): $option[ 'default' ];
                    $value  = $custom;
                }

                if ( isset( $option[ 'smms-type' ] ) && in_array( $option[ 'smms-type' ], array( 'toggle-element' ) ) ) {

                    //  error_log( print_r( $raw_value, true ) );

                    if ( $value && isset( $option[ 'elements' ] ) && !empty( $option[ 'elements' ] ) ) {

                        foreach ( $value as $index => $single_toggle ) {

                            if ( $value && isset( $option[ 'onoff_field' ] ) && !empty( $option[ 'onoff_field' ] ) ) {
                                $onoff                = $option[ 'onoff_field' ];
                                $onoff[ 'type' ]      = 'smms-field';
                                $onoff[ 'smms-type' ] = 'onoff';
                                $onoff_id             = $onoff[ 'id' ];

                                $value[ $index ][ $onoff_id ] = isset( $single_toggle[ $onoff_id ] ) ? self::sanitize_option( $single_toggle[ $onoff_id ], $onoff, $single_toggle[ $onoff_id ] ) : 'no';
                            }

                            foreach ( $option[ 'elements' ] as $element ) {
                                $value[ $index ][ $element[ 'id' ] ] = self::sanitize_option( $value[ $index ][ $element[ 'id' ] ], $element, $value[ $index ][ $element[ 'id' ] ] );
                            }
                        }
                    }


                    // error_log('sanitizes value');
                    // error_log( print_r( $value, true ) );
                }


                if ( !empty( $option[ 'smms-sanitize-callback' ] ) && is_callable( $option[ 'smms-sanitize-callback' ] ) ) {
                    $value = call_user_func( $option[ 'smms-sanitize-callback' ], $value );
                }
            }

            return $value;
        }

        /**
         * Add SMMS Fields
         *
         * @param array $field
         * @return   void
         * @since    3.0.0
         * @author   sam
         */
        public static function add_smms_field( $field = array() ) {
            if ( !empty( $field ) && isset( $field[ 'smms-type' ] ) ) {
                $field[ 'type' ] = $field[ 'smms-type' ];
                unset( $field[ 'smms-type' ] );

                $field[ 'id' ]      = isset( $field[ 'id' ] ) ? $field[ 'id' ] : '';
                $field[ 'name' ]    = $field[ 'id' ];
                $field[ 'default' ] = isset( $field[ 'default' ] ) ? $field[ 'default' ] : '';

                $value = apply_filters( 'smms_plugin_fw_wc_panel_pre_field_value', null, $field );
                if ( is_null( $value ) ) {
                    if ( 'toggle-element' === $field[ 'type' ] ) {
                        $value = get_option( $field[ 'id' ], $field[ 'default' ] );
                    } else {
                        $value = WC_Admin_Settings::get_option( $field[ 'id' ], $field[ 'default' ] );
                    }
                }
                $field[ 'value' ] = $value;

                require( SMM_CORE_PLUGIN_TEMPLATE_PATH . '/panel/woocommerce/woocommerce-option-row.php' );
            }
        }

        /**
         *  Save the content of the toggle element present inside the panel.
         *  Called by the action 'admin_action_smms_plugin_fw_save_toggle_element'
         *  via Ajax
         *
         * @author sam
         */
        public function save_toggle_element_options() {
            $posted      = wc_clean($_POST);
            $tabs        = $this->get_available_tabs();
            $smm_options = $this->get_main_array_options();
            $current_tab = isset( $_REQUEST[ 'tab' ] ) && in_array( $_REQUEST[ 'tab' ], $tabs ) ? sanitize_text_field($_REQUEST[ 'tab' ]) : $tabs[ 0 ];
            $option_id   = isset( $_REQUEST[ 'toggle_id' ] ) ? sanitize_text_field($_REQUEST[ 'toggle_id' ]) : '';
            $updated     = false;

            if ( !empty( $smm_options[ $current_tab ] ) && !empty( $option_id ) ) {

                $tab_options = $smm_options[ $current_tab ];
                foreach ( $tab_options as $key => $item ) {
                    if ( !isset( $item[ 'id' ] ) ) {
                        unset( $tab_options[ $key ] );
                    }
                }

                $option_array = array_combine( wp_list_pluck( $tab_options, 'id' ), $tab_options );
                if ( isset( $option_array[ $option_id ] ) ) {
                    $value = isset( $posted[ $option_id ] ) ? $posted[ $option_id ] : '';

                    //drag and drop
                    $order_elements = isset( $posted[ 'smms_toggle_elements_order_keys' ] ) ? explode( ',', $posted[ 'smms_toggle_elements_order_keys' ] ) : false;
                    if ( $order_elements ) {
                        $i         = 0;
                        $new_value = array();
                        foreach ( $order_elements as $key ) {
                            $index               = apply_filters( 'smms_toggle_elements_index', $i++, $key );
                            $new_value[ $index ] = $value[ $key ];
                        }

                        $value = $new_value;
                    }
                    $value   = self::sanitize_option( $value, $option_array[ $option_id ], $value );
                    $updated = update_option( $option_id, $value );
                }
            }

            return $updated;
        }
    }
}
