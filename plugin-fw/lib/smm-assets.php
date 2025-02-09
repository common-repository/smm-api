<?php
/**
 * This file belongs to the SMM Plugin Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'SMM_Assets' ) ) {
    /**
     * SMM Assets
     *
     * @class      SMM_Assets
     * @package    SMMS
     * @since      3.0.0
     * @author     sam
     */
    class SMM_Assets {
        /** @var string */
        public $version = '1.0.0';

        /** @var SMM_Assets */
        private static $_instance;

        /** @return SMM_Assets */
        public static function instance() {
            return !is_null( self::$_instance ) ? self::$_instance : self::$_instance = new self();
        }

        /**
         * Constructor
         *
         * @since      1.0
         * @author     sam
         */
        private function __construct() {
            defined( 'SMMS_PLUGIN_FW_VERSION' ) && $this->version = SMMS_PLUGIN_FW_VERSION;
            add_action( 'admin_enqueue_scripts', array( $this, 'register_styles_and_scripts' ) );
        }

        /**
         * Register styles and scripts
         */
        public function register_styles_and_scripts() {
            global $wp_scripts, $woocommerce;

            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

            //scripts
            wp_register_script( 'smms-plugin-fw-fields', SMM_CORE_PLUGIN_URL . '/assets/js/smms-fields' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'wp-color-picker', 'codemirror', 'codemirror-javascript', 'jquery-ui-slider', 'jquery-ui-sortable' ), $this->version, true );
            wp_register_script( 'smm-metabox', SMM_CORE_PLUGIN_URL . '/assets/js/metabox' . $suffix . '.js', array( 'jquery', 'wp-color-picker', 'smms-plugin-fw-fields' ), $this->version, true );
            wp_register_script( 'smm-plugin-panel', SMM_CORE_PLUGIN_URL . '/assets/js/smm-plugin-panel' . $suffix . '.js', array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable', 'smms-plugin-fw-fields' ), $this->version, true );
            wp_register_script( 'codemirror', SMM_CORE_PLUGIN_URL . '/assets/js/codemirror/codemirror.js', array( 'jquery' ), $this->version, true );
            wp_register_script( 'codemirror-javascript', SMM_CORE_PLUGIN_URL . '/assets/js/codemirror/javascript.js', array( 'jquery', 'codemirror' ), $this->version, true );
            wp_register_script( 'colorbox', SMM_CORE_PLUGIN_URL . '/assets/js/jquery.colorbox' . $suffix . '.js', array( 'jquery' ), '1.6.3', true );
            wp_register_script( 'smms_how_to', SMM_CORE_PLUGIN_URL . '/assets/js/how-to' . $suffix . '.js', array( 'jquery' ), $this->version, true );

            //styles
            $jquery_version = isset( $wp_scripts->registered[ 'jquery-ui-core' ]->ver ) ? $wp_scripts->registered[ 'jquery-ui-core' ]->ver : '1.9.2';
            //wp_register_style( 'codemirror', SMM_CORE_PLUGIN_URL . '/assets/css/codemirror/codemirror.css' );
            wp_register_style( 'smm-plugin-style', SMM_CORE_PLUGIN_URL . '/assets/css/smm-plugin-panel.css', array(), $this->version );
            //wp_register_style( 'raleway-font', '//fonts.googleapis.com/css?family=Raleway:400,500,600,700,800,100,200,300,900' );
            wp_register_style( 'smm-jquery-ui-style', SMM_CORE_PLUGIN_URL . '/assets/css/jquery-ui.css', array(), $this->version );
            wp_register_style( 'colorbox', SMM_CORE_PLUGIN_URL . '/assets/css/colorbox.css', array(), $this->version );
            wp_register_style( 'smm-upgrade-to-pro', SMM_CORE_PLUGIN_URL . '/assets/css/smm-upgrade-to-pro.css', array( 'colorbox' ), $this->version );
            wp_register_style( 'smm-plugin-metaboxes', SMM_CORE_PLUGIN_URL . '/assets/css/metaboxes.css', array(), $this->version );
            wp_register_style( 'smms-plugin-fw-fields', SMM_CORE_PLUGIN_URL . '/assets/css/smms-fields.css', false, $this->version );

            $wc_version_suffix = '';
            if ( function_exists( 'WC' ) || !empty( $woocommerce ) ) {
                $woocommerce_version = function_exists( 'WC' ) ? WC()->version : $woocommerce->version;
                $wc_version_suffix   = version_compare( $woocommerce_version, '3.0.0', '>=' ) ? '' : '-wc-2.6';

                wp_register_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css', array(), $woocommerce_version );
            } else {
                wp_register_script( 'select2', SMM_CORE_PLUGIN_URL . '/assets/js/select2.min.js', array( 'jquery' ), '4.0.3', true );
                wp_register_style( 'smms-select2-no-wc', SMM_CORE_PLUGIN_URL . '/assets/css/smms-select2-no-wc.css', false, $this->version );
            }

            wp_register_script( 'smms-enhanced-select', SMM_CORE_PLUGIN_URL . '/assets/js/smms-enhanced-select' . $wc_version_suffix . $suffix . '.js', array( 'jquery', 'select2' ), '1.0.0', true );
            wp_localize_script( 'smms-enhanced-select', 'smms_framework_enhanced_select_params', array(
                'ajax_url'           => admin_url( 'admin-ajax.php' ),
                'search_posts_nonce' => wp_create_nonce( 'search-posts' ),
                'search_terms_nonce' => wp_create_nonce( 'search-terms' ),
                'search_customers_nonce' => wp_create_nonce( 'search-customers' ),
            ) );

            wp_localize_script( 'smms-plugin-fw-fields', 'smms_framework_fw_fields', array(
                'admin_url' => admin_url( 'admin.php' ),
                'ajax_url'  => admin_url( 'admin-ajax.php' ),
            ) );
            wp_enqueue_style( 'smms-plugin-fw-admin', SMM_CORE_PLUGIN_URL . '/assets/css/admin.css', array(), $this->version );
        }
    }
}

SMM_Assets::instance();