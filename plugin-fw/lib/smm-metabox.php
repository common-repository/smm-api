<?php
/**
 * This file belongs to the SMM Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */


if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'SMM_Metabox' ) ) {
    /**
     * SMM Metabox
     *
     * the metabox can be created using this code
     * <code>
     * $args1 = array(
     *      'label'    => __( 'Metabox Label', 'smm-api' ),
     *      'pages'    => 'page',   //or array( 'post-type1', 'post-type2')
     *      'context'  => 'normal', //('normal', 'advanced', or 'side')
     *      'priority' => 'default',
     *      'tabs'     => array(
     *                 'settings' => array( //tab
     *                          'label'  => __( 'Settings', 'smm-api' ),
     *                          'fields' => array(
     *                          'meta_checkbox' => array(
     *                                 'label'    => __( 'Show title', 'smm-api' ),
     *                                 'desc'     => __( 'Choose whether to show title of the page or not.', 'smm-api' ),
     *                                 'type'     => 'checkbox',
     *                                 'private'  => false,
     *                                 'std'      => '1'),
     *                            ),
     *                      ),
     *  );
     *
     * $metabox1 = SMM_Metabox( 'smm-metabox-id' );
     * $metabox1->init( $args );
     * </code>
     *
     * @class SMM_Metaboxes
     * @package    SMMS
     * @since      1.0.0
     * @author     sam softnwords
     *
     */
    class SMM_Metabox {

        /**
         * @var string the id of metabox
         *
         * @since 1.0
         */

        public $id;

        /**
         * @var array An array where are saved all metabox settings options
         *
         * @since 1.0
         */
        private $options = array();

        /**
         * @var array An array where are saved all tabs of metabox
         *
         * @since 1.0
         */
        private $tabs = array();

        /**
         * @var object The single instance of the class
         * @since 1.0
         */
        protected static $_instance = array();

        /**
         * Main Instance
         *
         * @static
         *
         * @param $id
         *
         * @return object Main instance
         *
         * @since  1.0
         * @author sam
         */
        public static function instance( $id ) {
            if ( !isset( self::$_instance[ $id ] ) ) {
                self::$_instance[ $id ] = new self( $id );
            }

            return self::$_instance[ $id ];
        }

        /**
         * Constructor
         *
         * @param string $id
         *
         * @return \SMM_Metabox
         * @since  1.0
         * @author sam softnwords
         */
        function __construct( $id = '' ) {
            $this->id = $id;

        }


        /**
         * Init
         *
         * set options and tabs, add actions to register metabox, scripts and save data
         *
         * @param array $options
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         */
        public function init( $options = array() ) {

            $this->set_options( $options );
            $this->set_tabs();

            add_action( 'add_meta_boxes', array( $this, 'register_metabox' ), 99 );
            add_action( 'save_post', array( $this, 'save_postdata' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 15 );

            add_filter( 'smm_icons_screen_ids', array( $this, 'add_screen_ids_for_icons' ) );

	        add_action( 'wp_ajax_smms_plugin_fw_save_toggle_element_metabox', array( $this, 'save_toggle_element' ) );
	        add_filter( 'admin_body_class', array( $this, 'add_body_class' ), 10, 1 );
        }

        /**
         * Add Screen ids to include icons
         *
         * @param $screen_ids
         *
         * @return array
         */
        public function add_screen_ids_for_icons( $screen_ids ) {
            return array_unique( array_merge( $screen_ids, (array) $this->options[ 'pages' ] ) );
        }

        /**
         * Enqueue script and styles in admin side
         *
         * Add style and scripts to administrator
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         * @author   sam
         */
        public function enqueue() {
            $enqueue = function_exists( 'get_current_screen' ) && get_current_screen() && in_array( get_current_screen()->id, (array) $this->options[ 'pages' ] );
            $enqueue = apply_filters( 'smms_plugin_fw_metabox_enqueue_styles_and_scripts', $enqueue, $this );

            // load scripts and styles only where the metabox is displayed
            if ( $enqueue ) {
                wp_enqueue_media();

                wp_enqueue_style( 'woocommerce_admin_styles' );

                wp_enqueue_style( 'smms-plugin-fw-fields' );
                wp_enqueue_style( 'wp-color-picker' );
                wp_enqueue_style( 'smm-plugin-metaboxes' );
                wp_enqueue_style( 'smm-jquery-ui-style' );

                wp_enqueue_script( 'smm-metabox' );

                wp_enqueue_script( 'smms-plugin-fw-fields' );
            }
        }

        /**
         * Set Options
         *
         * Set the variable options
         *
         * @param array $options
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         */
        public function set_options( $options = array() ) {
            $this->options = $options;

        }

        /**
         * Set Tabs
         *
         * Set the variable tabs
         *
         * @internal param array $tabs
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         */
        public function set_tabs() {
            if ( !isset( $this->options[ 'tabs' ] ) ) {
                return;
            }
            $this->tabs = $this->options[ 'tabs' ];
            if ( isset( $this->tabs[ 'settings' ][ 'fields' ] ) ) {
                $this->tabs[ 'settings' ][ 'fields' ] = array_filter( $this->tabs[ 'settings' ][ 'fields' ] );
            }
        }


        /**
         * Add Tab
         *
         * Add a tab inside the metabox
         *
         * @internal param array $tabs
         *
         * @param array  $tab the new tab to add to the metabox
         * @param string $where tell where insert the tab if after or before a $refer
         * @param null   $refer an existent tab inside metabox
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         */
        public function add_tab( $tab, $where = 'after', $refer = null ) {
            if ( !is_null( $refer ) ) {
                $ref_pos = array_search( $refer, array_keys( $this->tabs ) );
                if ( $ref_pos !== false ) {
                    if ( $where == 'after' ) {
                        $this->tabs = array_slice( $this->tabs, 0, $ref_pos + 1, true ) +
                                      $tab +
                                      array_slice( $this->tabs, $ref_pos + 1, count( $this->tabs ) - 1, true );
                    } else {
                        $this->tabs = array_slice( $this->tabs, 0, $ref_pos, true ) +
                                      $tab +
                                      array_slice( $this->tabs, $ref_pos, count( $this->tabs ), true );
                    }
                }
            } else {
                $this->tabs = array_merge( $tab, $this->tabs );
            }

        }

        /**
         * Remove Tab
         *
         * Remove a tab from the tabs of metabox
         *
         * @internal param array $tabs
         *
         * @param $id_tab
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         */
        public function remove_tab( $id_tab ) {
            if ( isset( $this->tabs[ $id_tab ] ) ) {
                unset ( $this->tabs[ $id_tab ] );
            }
        }


        /**
         * Add Field
         *
         * Add a field inside a tab of metabox
         *
         * @internal param array $tabs
         *
         * @param string $tab_id the id of the tabs where add the field
         * @param array  $args the  field to add
         * @param string $where tell where insert the field if after or before a $refer
         * @param null   $refer an existent field inside tab
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         */
        public function add_field( $tab_id, $args, $where = 'after', $refer = null ) {
            if ( isset( $this->tabs[ $tab_id ] ) ) {

                $cf = $this->tabs[ $tab_id ][ 'fields' ];
                if ( !is_null( $refer ) ) {
                    $ref_pos = array_search( $refer, array_keys( $cf ) );
                    if ( $ref_pos !== false ) {
                        if ( $where == 'after' ) {
                            $this->tabs[ $tab_id ][ 'fields' ] = array_slice( $cf, 0, $ref_pos + 1, true ) +
                                                                 $args +
                                                                 array_slice( $cf, $ref_pos, count( $cf ) - 1, true );

                        } elseif ( $where == 'before' ) {
                            $this->tabs[ $tab_id ][ 'fields' ] = array_slice( $cf, 0, $ref_pos, true ) +
                                                                 $args +
                                                                 array_slice( $cf, $ref_pos, count( $cf ), true );

                        }
                    }
                } else {
                    if ( $where == 'first' ) {
                        $this->tabs[ $tab_id ][ 'fields' ] = $args + $cf;

                    } else {
                        $this->tabs[ $tab_id ][ 'fields' ] = array_merge( $this->tabs[ $tab_id ][ 'fields' ], $args );
                    }
                }

            }


        }

        /**
         * Remove Field
         *
         * Remove a field from the metabox, search inside the tabs and remove it if exists
         *
         * @param $id_field
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         */
        public function remove_field( $id_field ) {
            foreach ( $this->tabs as $tab_name => $tab ) {
                if ( isset( $tab[ 'fields' ][ $id_field ] ) ) {
                    unset ( $this->tabs[ $tab_name ][ 'fields' ][ $id_field ] );
                }
            }
        }

        /**
         * Reorder tabs
         *
         * Order the tabs and fields and set id and name to each field
         *
         * @internal param $id_field
         *
         * @return void
         * @since  1.0
         * @author sam softnwords
         */
        public function reorder_tabs() {
            foreach ( $this->tabs as $tab_name => $tab ) {
                foreach ( $tab[ 'fields' ] as $id_field => $field ) {
                    $this->tabs[ $tab_name ][ 'fields' ][ $id_field ][ 'private' ] = ( isset( $field[ 'private' ] ) ) ? $field[ 'private' ] : true;
                    if ( empty( $this->tabs[ $tab_name ][ 'fields' ][ $id_field ][ 'id' ] ) )
                        $this->tabs[ $tab_name ][ 'fields' ][ $id_field ][ 'id' ] = $this->get_option_metabox_id( $id_field, $this->tabs[ $tab_name ][ 'fields' ][ $id_field ][ 'private' ] );
                    if ( empty( $this->tabs[ $tab_name ][ 'fields' ][ $id_field ][ 'name' ] ) )
                        $this->tabs[ $tab_name ][ 'fields' ][ $id_field ][ 'name' ] = $this->get_option_metabox_name( $this->tabs[ $tab_name ][ 'fields' ][ $id_field ][ 'id' ] );
                }
            }

        }


        /**
         * Get Option Metabox ID
         *
         * return the id of the field
         *
         * @param string $id_field
         * @param bool   $private if private add an _befor the id
         *
         * @return string
         * @since  1.0
         * @author sam softnwords
         */
        public function get_option_metabox_id( $id_field, $private = true ) {
            if ( $private ) {
                return '_' . $id_field;
            } else {
                return $id_field;
            }
        }

        /**
         * Get Option Metabox Name
         *
         * return the name of the field, this name will be used as attribute name of the input field
         *
         * @param string $id_field
         * @param bool   $private if private add an _befor the id
         *
         * @return string
         * @since  1.0
         * @author sam softnwords
         */
        public function get_option_metabox_name( $id_field, $private = true ) {
            $db_name = apply_filters( 'smm_metaboxes_option_main_name', 'smm_metaboxes' );
            $return  = $db_name . '[';

            if ( !strpos( $id_field, '[' ) ) {
                return $return . $id_field . ']';
            }
            $return .= substr( $id_field, 0, strpos( $id_field, '[' ) );
            $return .= ']';
            $return .= substr( $id_field, strpos( $id_field, '[' ) );

            return $return;
        }

        /**
         * Register the metabox
         *
         * call the wp function add_metabox to add the metabox
         *
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         */
        public function register_metabox( $post_type ) {

            if ( in_array( $post_type, (array) $this->options[ 'pages' ] ) ) {
                add_meta_box( $this->id, $this->options[ 'label' ], array( $this, 'show' ), $post_type, $this->options[ 'context' ], $this->options[ 'priority' ] );
            }
        }

        /**
         * Show metabox
         *
         * show the html of metabox
         *
         *
         * @return void
         * @since    1.0
         * @author   sam softnwords
         */
        public function show() {
            $this->reorder_tabs();

            smm_plugin_get_template( SMM_CORE_PLUGIN_PATH, 'metaboxes/tab.php', array( 'tabs' => $this->tabs ) );
        }

        /**
         * Save Post Data
         *
         * Save the post data in the database when save the post
         *
         * @param $post_id
         *
         * @return int
         * @since  1.0
         * @author sam softnwords
         */
        public function save_postdata( $post_id ) {


            if ( !isset( $_POST[ 'smm_metaboxes_nonce' ] ) || !wp_verify_nonce(sanitize_text_field( wp_unslash (  $_POST[ 'smm_metaboxes_nonce' ])), 'metaboxes-fields-nonce' ) ) {
                return $post_id;
            }


            if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                return $post_id;
            }

            if ( isset( $_POST[ 'post_type' ] ) ) {
                $post_type = sanitize_text_field($_POST[ 'post_type']);
            } else {
                return $post_id;
            }

            if ( 'page' == $post_type ) {
                if ( !current_user_can( 'edit_page', $post_id ) ) {
                    return $post_id;
                }
            } else {
                if ( !current_user_can( 'edit_post', $post_id ) ) {
                    return $post_id;
                }
            }

            /*if (!in_array($post_type, (array)$this->options['pages'])) {
                return $post_id;
            }*/

            if ( isset( $_POST[ 'smm_metaboxes' ] ) ) {
                $smm_metabox_data = sanitize_text_field($_POST[ 'smm_metaboxes' ]);

                if ( is_array( $smm_metabox_data ) ) {
                    foreach ( $smm_metabox_data as $field_name => $field_value ) {
                        if ( !add_post_meta( $post_id, $field_name, $field_value, true ) ) {
                            update_post_meta( $post_id, $field_name, $field_value );
                        }
                    }
                }
            }

            $this->sanitize_fields( $post_id );


        }

	    /**
	     * Sanitize the fields of metabox.
	     *
	     * @return void
	     * @since 3.2.1
	     * @author sam
	     */
	    public function  sanitize_fields( $post_id ) {

		    $this->reorder_tabs();

		    foreach ( $this->tabs as $tab ) {

			    foreach ( $tab['fields'] as $field ) {

				    if ( in_array( $field['type'], array( 'title' ) ) ) {
					    continue;
				    }

				    if ( isset( $_POST['smm_metaboxes'][ $field['id'] ] ) ) {
					    if ( in_array( $field['type'], array( 'onoff', 'checkbox' ) ) ) {
						    update_post_meta( $post_id, $field['id'], '1' );
					    }elseif( in_array( $field['type'], array( 'toggle-element' ) ) ){
					    	if ( isset( $field['elements'] ) && $field['elements'] ) {
							    $elements_value = sanitize_key($_POST['smm_metaboxes'][ $field['id'] ]);
							    if ( $elements_value ) {
							    	if( isset( $elements_value['box_id'])){
							    		unset( $elements_value['box_id']);
								    }

								    foreach ( $field['elements'] as $element ) {
									    foreach ( $elements_value as $key => $element_value ) {
										    if ( isset( $field['onoff_field'] ) ) {
										    	$elements_value[ $key ][ $field['onoff_field']['id'] ] = ! isset( $element_value[ $field['onoff_field']['id'] ] ) ? 0 : $element_value[ $field['onoff_field']['id'] ];
										    }
										    if ( in_array( $element['type'], array( 'onoff', 'checkbox' ) ) ) {
											    $elements_value[ $key ][ $element['id'] ] = ! isset( $element_value[ $element['id'] ] ) ? 0 : 1;
										    }

										    if ( ! empty( $element['smms-sanitize-callback'] ) && is_callable( $element['smms-sanitize-callback'] ) ) {
											    $elements_value[ $key ][ $element['id'] ]  = call_user_func( $element['smms-sanitize-callback'],  $elements_value[ $key ][ $element['id'] ] );
										    }
									    }
								    }
							    }

							    update_post_meta( $post_id, $field['id'], maybe_serialize( $elements_value ) );
						    }
					    } else {
						    $value = sanitize_key($_POST['smm_metaboxes'][ $field['id'] ]);
						    if ( ! empty( $field['smms-sanitize-callback'] ) && is_callable( $field['smms-sanitize-callback'] ) ) {
							    $value = call_user_func( $field['smms-sanitize-callback'], $value );
						    }
						    add_post_meta( $post_id, $field['id'], $value, true ) || update_post_meta( $post_id, $field['id'], $value );
					    }
				    } elseif ( in_array( $field['type'], array( 'onoff', 'checkbox' ) ) ) {
					    update_post_meta( $post_id, $field['id'], '0' );
				    } else {
					    delete_post_meta( $post_id, $field['id'] );
				    }
			    }
		    }
	    }

        /**
         * Remove Fields
         *
         * Remove a fields list from the metabox, search inside the tabs and remove it if exists
         *
         * @param $id_fields
         *
         * @return   void
         * @since    2.0.0
         * @author   sam softnwords
         */
        public function remove_fields( $id_fields ) {
            foreach ( $id_fields as $k => $field ) {
                $this->remove_field( $field );
            }
        }


	    /**
	     * Add custom class to body
	     *
	     * It is necessary to add new style to the metaboxes
	     *
	     * @param $classes
	     *
	     * @return   string
	     * @author   sam
	     */
	    public function add_body_class( $classes ) {
		    global $post;

		    $exclude_post_types = apply_filters( 'smms_plugin_fw_exclude_post_types_to_additional_classes', array( 'product' ) );

		    if ( $post && in_array( $post->post_type, $exclude_post_types ) ) {
			    return $classes;
		    }

		    $new_class = apply_filters( 'smms_plugin_fw_metabox_class', '', $post );

		    if ( empty( $new_class ) ) {
			    return $classes;
		    }

		    $classes = smms_plugin_fw_remove_duplicate_classes( $classes. ' '. $new_class);

		    return $classes;
	    }

	    /**
	     * Save the element toggle via Ajax.
	     *
	     * @return void
	     * @since 3.2.1
	     * @author sam
	     */
	    public function save_toggle_element() {
		    if ( ! isset( $_REQUEST['post_ID'] ) ) {
			    return;
		    }

		    if ( !isset( $_REQUEST[ 'smm_metaboxes_nonce' ] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash ( $_REQUEST[ 'smm_metaboxes_nonce' ])), 'metaboxes-fields-nonce' ) ) {
			    return;
		    }
		    $post_id = sanitize_text_field($_REQUEST['post_ID']);

		    if ( isset( $_REQUEST['smm_metaboxes'] ) ) {
			    $smm_metabox_data = sanitize_text_field($_REQUEST['smm_metaboxes']);

			    if ( is_array( $smm_metabox_data ) ) {
					$this->sanitize_fields( $post_id );
			    }
		    } elseif ( ! isset( $_REQUEST['smm_metaboxes'] ) || ! isset( $_REQUEST['smm_metaboxes'][ $_REQUEST['toggle_id'] ] ) ) {
			    delete_post_meta( $post_id, sanitize_text_field($_REQUEST['toggle_id'] ));
		    }
	    }
    }
}

if ( !function_exists( 'SMM_Metabox' ) ) {

    /**
     * Main instance of plugin
     *
     * @param $id
     *
     * @return \SMM_Metabox
     * @since  1.0
     * @author sam softnwords
     */


    function SMM_Metabox( $id ) {
        return SMM_Metabox::instance( $id );
    }
}




