<?php
/**
 * Outputs a variation for editing.
 *
 * @package WooCommerce\Admin
 * @var int $variation_id
 * @var WP_POST $variation
 * @var WC_Product_Variation $variation_object
 * @var array $variation_data array of variation data @deprecated 4.4.0.
 License: GPLv3
 License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>
<div class="woocommerce_variation wc-metabox closed">
	<h3>
		<a href="#" class="remove_variation delete" rel="<?php echo esc_attr( $variation_id ); ?>"><?php esc_html_e( 'Remove', 'smm-api' ); ?></a>
		<div class="handlediv" aria-label="<?php esc_attr_e( 'Click to toggle', 'smm-api' ); ?>"></div>
		<div class="tips sort" data-tip="<?php esc_attr_e( 'Drag and drop, or click to set admin variation order', 'smm-api' ); ?>"></div>
		<strong>#<?php echo esc_html( $variation_id ); ?> </strong>
		<?php
		$attribute_values = $variation_object->get_attributes( 'edit' );

		foreach ( $product_object->get_attributes( 'edit' ) as $attribute ) {
			if ( ! $attribute->get_variation() ) {
				continue;
			}
			$selected_value = isset( $attribute_values[ sanitize_title( $attribute->get_name() ) ] ) ? $attribute_values[ sanitize_title( $attribute->get_name() ) ] : '';
			?>
			<select name="attribute_<?php echo esc_attr( sanitize_title( $attribute->get_name() ) . "[{$loop}]" ); ?>">
				<option value="">
					<?php
					/* translators: %s: attribute label */
					printf( esc_html__( 'Any %s&hellip;', 'smm-api' ), esc_html(wc_attribute_label( $attribute->get_name()) ) );
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</option>
				<?php if ( $attribute->is_taxonomy() ) : ?>
					<?php foreach ( $attribute->get_terms() as $option ) : ?>
						<option <?php selected( $selected_value, $option->slug ); ?> value="<?php echo esc_attr( $option->slug ); ?>"><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option->name, $option, $attribute->get_name(), $product_object ) ); ?></option>
					<?php endforeach; ?>
				<?php else : ?>
					<?php foreach ( $attribute->get_options() as $option ) : ?>
						<option <?php selected( $selected_value, $option ); ?> value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute->get_name(), $product_object ) ); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
			<?php
		}
		?>
		<input type="hidden" name="variable_post_id[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation_id ); ?>" />
		<input type="hidden" class="variation_menu_order" name="variation_menu_order[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation_object->get_menu_order( 'edit' ) ); ?>" />

		<?php
		/**
		 * Variations header action.
		 *
		 * @since 3.6.0
		 *
		 * @param WP_Post $variation Post data.
		 */
		 do_action( 'woocommerce_variation_header', $variation );
		?>
	</h3>
	<div class="woocommerce_variable_attributes wc-metabox-content" style="display: none;">
		<div class="data">
			<p class="form-row form-row-first upload_image">
				<a href="#" class="upload_image_button tips <?php 
				$variation_object->get_image_id( 'edit' ) ? 
				printf(/* translators: search here */ esc_attr__( 'remove', 'smm-api' )) : 
				printf('') ?>" data-tip="<?php 
				 $variation_object->get_image_id( 'edit' ) ? 
				printf(/* translators: search here */ esc_html__( 'Remove this image', 'smm-api' )) : 
				printf(/* translators: search here */ esc_html__( 'Upload an image', 'smm-api' )) ?>" 
				rel="<?php printf(esc_attr( $variation_id )) ?>">
					<img src="<?php printf( $variation_object->get_image_id( 'edit' ) ? esc_url( wp_get_attachment_thumb_url( $variation_object->get_image_id( 'edit' ) ) ) : esc_url( wc_placeholder_img_src() )) ?>" /><input type="hidden" name="upload_image_id[<?php printf( esc_attr( $loop )) ?>]" class="upload_image_id" value="<?php printf( esc_attr( $variation_object->get_image_id( 'edit' ) )) ?>" />
				</a>
			</p>
			<?php
			if ( wc_product_sku_enabled() ) {
				woocommerce_wp_text_input(
					array(
						'id'            => "variable_sku{$loop}",
						'name'          => "variable_sku[{$loop}]",
						'value'         => $variation_object->get_sku( 'edit' ),
						'placeholder'   => $variation_object->get_sku(),
						'label'         => '<abbr title="' . esc_attr__( 'Stock Keeping Unit', 'smm-api' ) . '">' . esc_html__( 'SKU', 'smm-api' ) . '</abbr>',
						'desc_tip'      => true,
						'description'   => __( 'SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', 'smm-api' ),
						'wrapper_class' => 'form-row form-row-last',
					)
				);
			}
			?>
			<p class="form-row form-row-full options">
				<label>
					<?php esc_html_e( 'Enabled', 'smm-api' ); ?>:
					<input type="checkbox" class="checkbox" name="variable_enabled[<?php echo esc_attr( $loop ); ?>]" <?php checked( in_array( $variation_object->get_status( 'edit' ), array( 'publish', false ), true ), true ); ?> />
				</label>
				<label class="tips" data-tip="<?php esc_attr_e( 'Enable this option if access is given to a downloadable file upon purchase of a product', 'smm-api' ); ?>">
					<?php esc_html_e( 'Downloadable', 'smm-api' ); ?>:
					<input type="checkbox" class="checkbox variable_is_downloadable" name="variable_is_downloadable[<?php echo esc_attr( $loop ); ?>]" <?php checked( $variation_object->get_downloadable( 'edit' ), true ); ?> />
				</label>
				<label class="tips" data-tip="<?php esc_attr_e( 'Enable this option if a product is not shipped or there is no shipping cost', 'smm-api' ); ?>">
					<?php esc_html_e( 'Virtual', 'smm-api' ); ?>:
					<input type="checkbox" class="checkbox variable_is_virtual" name="variable_is_virtual[<?php echo esc_attr( $loop ); ?>]" <?php checked( $variation_object->get_virtual( 'edit' ), true ); ?> />
				</label>

				<?php if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) : ?>
					<label class="tips" data-tip="<?php esc_attr_e( 'Enable this option to enable stock management at variation level', 'smm-api' ); ?>">
						<?php esc_html_e( 'Manage stock?', 'smm-api' ); ?>
						<input type="checkbox" class="checkbox variable_manage_stock" name="variable_manage_stock[<?php echo esc_attr( $loop ); ?>]" <?php checked( $variation_object->get_manage_stock(), true ); // Use view context so 'parent' is considered. ?> />
					</label>
				<?php endif; ?>
                <label class="tips" data-tip="<?php esc_attr_e( 'Enable this option to enable smm api call', 'smm-api' ); ?>">
						<?php esc_html_e( 'SMM SERVER API', 'smm-api' ); 
						
						$variable_smm_api = get_post_meta( $variation_id, 'variable_smm_api', true ) =='on' ? 1 : null;
						?>
						<input type="checkbox" class="checkbox variable_smm_api" name="variable_smm_api[<?php echo esc_attr( $loop ); ?>]" <?php checked( $variable_smm_api, true ); 
						// Use view context so 'parent' is considered. 
						
						?> />
					</label>
				<?php do_action( 'woocommerce_variation_options', $loop, $variation_data, $variation ); 
				?>
			</p>

			<div class="variable_pricing">
				<?php
				$label = sprintf(
					/* translators: %s: currency symbol */
					__( 'Regular price (%s)', 'smm-api' ),
					get_woocommerce_currency_symbol()
				);

				woocommerce_wp_text_input(
					array(
						'id'            => "variable_regular_price_{$loop}",
						'name'          => "variable_regular_price[{$loop}]",
						'value'         => wc_format_localized_price( $variation_object->get_regular_price( 'edit' ) ),
						'label'         => $label,
						'data_type'     => 'price',
						'wrapper_class' => 'form-row form-row-first',
						'placeholder'   => __( 'Variation price (required)', 'smm-api' ),
					)
				);

				$label = sprintf(
					/* translators: %s: currency symbol */
					__( 'Sale price (%s)', 'smm-api' ),
					get_woocommerce_currency_symbol()
				);

				woocommerce_wp_text_input(
					array(
						'id'            => "variable_sale_price{$loop}",
						'name'          => "variable_sale_price[{$loop}]",
						'value'         => wc_format_localized_price( $variation_object->get_sale_price( 'edit' ) ),
						'data_type'     => 'price',
						'label'         => $label . ' <a href="#" class="sale_schedule">' . esc_html__( 'Schedule', 'smm-api' ) . '</a><a href="#" class="cancel_sale_schedule hidden">' . esc_html__( 'Cancel schedule', 'smm-api' ) . '</a>',
						'wrapper_class' => 'form-row form-row-last',
					)
				);

				$sale_price_dates_from_timestamp = $variation_object->get_date_on_sale_from( 'edit' ) ? $variation_object->get_date_on_sale_from( 'edit' )->getOffsetTimestamp() : false;
				$sale_price_dates_to_timestamp   = $variation_object->get_date_on_sale_to( 'edit' ) ? $variation_object->get_date_on_sale_to( 'edit' )->getOffsetTimestamp() : false;

				$sale_price_dates_from = $sale_price_dates_from_timestamp ? date_i18n( 'Y-m-d', $sale_price_dates_from_timestamp ) : '';
				$sale_price_dates_to   = $sale_price_dates_to_timestamp ? date_i18n( 'Y-m-d', $sale_price_dates_to_timestamp ) : '';

				printf( '<div class="form-field sale_price_dates_fields hidden">
					<p class="form-row form-row-first">
						<label>' . esc_html__( 'Sale start date', 'smm-api' ) . '</label>
						<input type="text" class="sale_price_dates_from" name="variable_sale_price_dates_from[' . esc_attr( $loop ) . ']" value="' . esc_attr( $sale_price_dates_from ) . '" placeholder="' . esc_attr_x( 'From&hellip;', 'placeholder', 'smm-api' ) . ' YYYY-MM-DD" maxlength="10" pattern="' . esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) . '" />
					</p>
					<p class="form-row form-row-last">
						<label>' . esc_html__( 'Sale end date', 'smm-api' ) . '</label>
						<input type="text" class="sale_price_dates_to" name="variable_sale_price_dates_to[' . esc_attr( $loop ) . ']" value="' . esc_attr( $sale_price_dates_to ) . '" placeholder="' . esc_attr_x( 'To&hellip;', 'placeholder', 'smm-api' ) . '  YYYY-MM-DD" maxlength="10" pattern="' . esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) . '" />
					</p>
				</div>');

				/**
				 * Variation options pricing action.
				 *
				 * @since 2.5.0
				 *
				 * @param int     $loop           Position in the loop.
				 * @param array   $variation_data Variation data.
				 * @param WP_Post $variation      Post data.
				 */
				do_action( 'woocommerce_variation_options_pricing', $loop, $variation_data, $variation );
				?>
			</div>

			<?php if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) : ?>
				<div class="show_if_variation_manage_stock" style="display: none;">
					<?php
					woocommerce_wp_text_input(
						array(
							'id'                => "variable_stock{$loop}",
							'name'              => "variable_stock[{$loop}]",
							'value'             => wc_stock_amount( $variation_object->get_stock_quantity( 'edit' ) ),
							'label'             => __( 'Stock quantity', 'smm-api' ),
							'desc_tip'          => true,
							'description'       => __( "Enter a number to set stock quantity at the variation level. Use a variation's 'Manage stock?' check box above to enable/disable stock management at the variation level.", 'smm-api' ),
							'type'              => 'number',
							'custom_attributes' => array(
								'step' => 'any',
							),
							'data_type'         => 'stock',
							'wrapper_class'     => 'form-row form-row-first',
						)
					);

					echo '<input type="hidden" name="variable_original_stock[' . esc_attr( $loop ) . ']" value="' . esc_attr( wc_stock_amount( $variation_object->get_stock_quantity( 'edit' ) ) ) . '" />';

					woocommerce_wp_select(
						array(
							'id'            => "variable_backorders{$loop}",
							'name'          => "variable_backorders[{$loop}]",
							'value'         => $variation_object->get_backorders( 'edit' ),
							'label'         => __( 'Allow backorders?', 'smm-api' ),
							'options'       => wc_get_product_backorder_options(),
							'desc_tip'      => true,
							'description'   => __( 'If managing stock, this controls whether or not backorders are allowed. If enabled, stock quantity can go below 0.', 'smm-api' ),
							'wrapper_class' => 'form-row form-row-last',
						)
					);

					/**
					 * Variation options inventory action.
					 *
					 * @since 2.5.0
					 *
					 * @param int     $loop           Position in the loop.
					 * @param array   $variation_data Variation data.
					 * @param WP_Post $variation      Post data.
					 */
					do_action( 'woocommerce_variation_options_inventory', $loop, $variation_data, $variation );
					?>
				</div></div>
			<?php endif; 
			
			// SMM API SETTINGS STARTS HERE
			
			    global $wpdb;
			    $post_type = 'smapi_server';
			    $parameter_key = '_parameter_%';
			    $meta_key = '_item_%';
			    $api_item_list_options_saved = get_post_meta( $variation_id, 'var_smapi_service_id_option', true );
			    $api_server_list_options_saved = get_post_meta( $variation_id, 'var_smapi_server_name_option', true );
			    $input_text_box_radio_saved   = get_post_meta( $variation_id, 'locate_input_box', true );
	        
	            $input_text_box_radio_product = ($input_text_box_radio_saved == 'product')  || ($input_text_box_radio_saved != 'product')? 'checked' :'';
	            $input_text_box_radio_other = ($input_text_box_radio_saved == 'other') ? 'checked' :'';
			    //get_post_meta( $variation->ID, 'api_server_list_options_saved', true );
			    //Geting Server details for SMM API servers
			    
                
                $my_smm_result  = $wpdb->get_results( $wpdb->query( $wpdb->prepare( "SELECT smapi_p.post_title, smapi_p.ID FROM $wpdb->posts as smapi_p INNER JOIN " . $wpdb->prefix . "postmeta as smapi_pm ON ( smapi_p.ID = smapi_pm.post_id )
                WHERE 1=1
                AND smapi_p.post_type = %s
                AND smapi_pm.meta_key LIKE %s", $post_type, $parameter_key
                ) ), ARRAY_A );
                $api_server_list_options_output='';
                foreach($my_smm_result as $sub_result){
			        if($api_server_list_options_saved == "" )
			        $api_server_list_options_saved = $sub_result['ID'];
			        $api_server_list_options_output .= '<option ';
			        $api_server_list_options_output .=
			        ($sub_result['ID'] == $api_server_list_options_saved ) ?
			        'value="'.$sub_result['ID'].'" selected': 
	                'value="'.$sub_result['ID'].'"' ;
	                $api_server_list_options_output .= '>'
	                .smms_getHost($sub_result['post_title'])
			        . '</option>';
			        }
			        
			        // API ITEM LISTING
			        
                
                
	            $smm_api_items_listing = $wpdb->get_results( $wpdb->query( $wpdb->prepare( "SELECT smapi_pm.* FROM " .$wpdb->prefix ."postmeta as smapi_pm INNER JOIN $wpdb->posts as smapi_p ON ( smapi_pm.post_id = %s )
                WHERE 1=1 
                AND smapi_p.post_type = %s
                AND smapi_pm.meta_key LIKE %s
                GROUP BY smapi_pm.meta_id
                ", $api_server_list_options_saved, $post_type, $meta_key
                )), ARRAY_A);
	            $descrption_opted = '';
	            $api_item_list_options_output='';
	            $api_item_list_options_output_arr = array();
	            
	            
	       foreach($smm_api_items_listing as $sub_item_result){
			$iteration += 1;
			
			    $descrption_opted_data_raw = " Min Order: " .smms_decode_string($sub_item_result['meta_value'],'f_min_order').
			    " Max Order: " .smms_decode_string($sub_item_result['meta_value'],'f_max_order')
			    .' Service ID - '.filter_var($sub_item_result['meta_key'], FILTER_SANITIZE_NUMBER_INT); 
			    if(	$iteration == 1)
			    $descrption_opted_data = $descrption_opted_data_raw;
			    $api_item_list_options_output .= '<option ';
			    $api_item_list_options_output .=
			    ($sub_item_result['meta_key'] == $api_item_list_options_saved ) ?
			    'value="'.$sub_item_result['meta_key'].'"'
			    .' data-desc="'.$descrption_opted_data_raw
			    .'" selected'
		    	: 
	             'value="'.$sub_item_result['meta_key'].'"'
	              .' data-desc="'.$descrption_opted_data_raw.'"'
	             ;
	                if($sub_item_result['meta_key'] == $api_item_list_options_saved ) 
	             $descrption_opted_data = $descrption_opted_data_raw;
	             $api_item_list_options_output .= '>'
	            .smms_decode_string($sub_item_result['meta_value'],'f_api_description')
	        //filter_var($sub_item_result['meta_key'], FILTER_SANITIZE_NUMBER_INT)
			    . '</option>';
		        	}
	            
					?>
					
<script>
function check_status(obj) {
  var uid = obj.options[obj.selectedIndex].getAttribute('data-desc');
  var sid = obj.id;
  var numb = sid.match(/\d/g);
  var cid = "var_smapi_service_span_option_"+ numb;
  //alert(uid +cid );
  document.getElementById(cid).innerHTML = uid;
}

</script>

					<div class="options_group ">
					    
					    <h3 class="smapi_server_name"><?php esc_html_e('SMM API Settings','smm-api') ?></h3>
            <div class="options_group show_if_api smm-flex-container">
                 <div class="smm-flex-item-1">
                
			            <div> 
			            <label for="var_smapi_service_id_option_<?php echo esc_attr( $loop ); ?>""><?php esc_html_e( 'SMM API SERVICE', 'smm-api' ); ?></label>
			            </div>
                        <select id="var_smapi_service_id_option_<?php echo esc_attr( $loop ); ?>" name="var_smapi_service_id_option[<?php echo esc_attr( $loop ); ?>]" class="select" onchange="check_status(this);" style=" width: 400px;float:left;" >
                        <?php 
                            printf( '%s',esc_attr($api_item_list_options_output));
                        ?>
                        </select>  
                        <div>
                        <span id="var_smapi_service_span_option_<?php echo esc_attr( $loop ); ?>" style="float:left;display:block;"><?php 
                      printf( '%s',esc_html($descrption_opted_data) ) ?></span>
                        </div>
                </div> 
                
                <div class="smm-flex-item-2">
                        <div>
                            <label for="_var_smapi_server_name_option_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'SERVER NAME', 'smm-api' ); ?></label>
                        </div>
                    
                    <select id="var_smapi_server_name_option_<?php echo esc_attr( $loop ); ?>" name="var_smapi_server_name_option[<?php echo esc_attr( $loop ); ?>]" class="var_smapi_server_name_option_<?php echo esc_attr( $loop ); ?>"  style="width: 300px;float:left;">
                            <?php 
                            printf( '%s',esc_attr($api_server_list_options_output));
                            ?>
                    </select>
                    <div>
                    <span class="description" style="float:left;display:block;">Please Select Server</span>
                    </div>
                </div>
                
                <div class="smm-flex-item-3">
                <input  type="radio" id="product_input_box" name = "locate_input_box[<?php printf( esc_attr( $loop )) ?>]" value="product" <?php printf( esc_attr($input_text_box_radio_product))?> >Use Custom Field at Product Page
                <input  type="radio" id="checkout_input_box" name = "locate_input_box[<?php printf( esc_attr( $loop )) ?>]" value="other" <?php printf( esc_attr($input_text_box_radio_other))?> > Use field From Other Plugin
               </div>
                    </div></div>
                    <?php
			    woocommerce_wp_text_input( array(
                'id' => 'var_smm_customer_input_field_label[' . $loop . ']',
                'class' => 'short',
                'label' => __( 'Customer Input Text Label', 'smm-api' ),
                'value' => get_post_meta( $variation->ID, 'var_smm_customer_input_field_label', true )
                   ) );
                ?>
			

			<div>
						<div class="show_if_variation_downloadable" style="display: none;">
				<div class="form-row form-row-full downloadable_files">
					<label><?php esc_html_e( 'Downloadable files', 'smm-api' ); ?></label>
					<table class="widefat">
						<thead>
							<div>
								<th><?php esc_html_e( 'Name', 'smm-api' ); ?> <?php wc_help_tip( esc_html__( 'This is the name of the download shown to the customer.', 'smm-api' ) ); ?></th>
								<th colspan="2"><?php esc_html_e( 'File URL', 'smm-api' ); ?> <?php  wc_help_tip( esc_html__( 'This is the URL or absolute path to the file which customers will get access to. URLs entered here should already be encoded.', 'smm-api' ) ); ?></th>
								<th>&nbsp;</th>
							</div>
						</thead>
						<tbody>
							<?php
							$downloads = $variation_object->get_downloads( 'edit' );

							if ( $downloads ) {
								foreach ( $downloads as $key => $file ) {
									include  WP_PLUGIN_DIR . '/woocommerce/includes/admin/meta-boxes/views/html-product-variation-download.php';
								}
							}
							?>
						</tbody>
						<tfoot>
							<div>
								<th colspan="4">
									<a href="#" class="button insert" data-row="
									<?php
									$key  = '';
									$file = array(
										'file' => '',
										'name' => '',
									);
									ob_start();
								require WP_PLUGIN_DIR . '/woocommerce/includes/admin/meta-boxes/views/html-product-variation-download.php';
									echo esc_attr( ob_get_clean() );
									?>
									"><?php esc_html_e( 'Add file', 'smm-api' ); ?></a>
								</th>
							</div>
						</tfoot>
					</table>
				</div>
			</div>
			<div class="show_if_variation_downloadable" style="display: none;">
				<?php
				woocommerce_wp_text_input(
					array(
						'id'                => "variable_download_limit{$loop}",
						'name'              => "variable_download_limit[{$loop}]",
						'value'             => $variation_object->get_download_limit( 'edit' ) < 0 ? '' : $variation_object->get_download_limit( 'edit' ),
						'label'             => __( 'Download limit', 'smm-api' ),
						'placeholder'       => __( 'Unlimited', 'smm-api' ),
						'description'       => __( 'Leave blank for unlimited re-downloads.', 'smm-api' ),
						'type'              => 'number',
						'desc_tip'          => true,
						'custom_attributes' => array(
							'step' => '1',
							'min'  => '0',
						),
						'wrapper_class'     => 'form-row form-row-first',
					)
				);

				woocommerce_wp_text_input(
					array(
						'id'                => "variable_download_expiry{$loop}",
						'name'              => "variable_download_expiry[{$loop}]",
						'value'             => $variation_object->get_download_expiry( 'edit' ) < 0 ? '' : $variation_object->get_download_expiry( 'edit' ),
						'label'             => __( 'Download expiry', 'smm-api' ),
						'placeholder'       => __( 'Never', 'smm-api' ),
						'description'       => __( 'Enter the number of days before a download link expires, or leave blank.', 'smm-api' ),
						'type'              => 'number',
						'desc_tip'          => true,
						'custom_attributes' => array(
							'step' => '1',
							'min'  => '0',
						),
						'wrapper_class'     => 'form-row form-row-last',
					)
				);

				/**
				 * Variation options download action.
				 *
				 * @since 2.5.0
				 *
				 * @param int     $loop           Position in the loop.
				 * @param array   $variation_data Variation data.
				 * @param WP_Post $variation      Post data.
				 */
				do_action( 'woocommerce_variation_options_download', $loop, $variation_data, $variation );
					?>
			</div>
				<?php
				
				woocommerce_wp_select(
					array(
						'id'            => "variable_stock_status{$loop}",
						'name'          => "variable_stock_status[{$loop}]",
						'value'         => $variation_object->get_stock_status( 'edit' ),
						'label'         => __( 'Stock status', 'smm-api' ),
						'options'       => wc_get_product_stock_status_options(),
						'desc_tip'      => true,
						'description'   => __( 'Controls whether or not the product is listed as "in stock" or "out of stock" on the frontend.', 'smm-api' ),
						'wrapper_class' => 'form-row form-row-full variable_stock_status',
					)
				);

				if ( wc_product_weight_enabled() ) {
					$label = sprintf(
						/* translators: %s: weight unit */
						__( 'Weight (%s)', 'smm-api' ),
						esc_html( get_option( 'woocommerce_weight_unit' ) )
					);

					woocommerce_wp_text_input(
						array(
							'id'            => "variable_weight{$loop}",
							'name'          => "variable_weight[{$loop}]",
							'value'         => wc_format_localized_decimal( $variation_object->get_weight( 'edit' ) ),
							'placeholder'   => wc_format_localized_decimal( $product_object->get_weight() ),
							'label'         => $label,
							'desc_tip'      => true,
							'description'   => __( 'Weight in decimal form', 'smm-api' ),
							'type'          => 'text',
							'data_type'     => 'decimal',
							'wrapper_class' => 'form-row form-row-first hide_if_variation_virtual',
						)
					);
				}

				if ( wc_product_dimensions_enabled() ) {
					$parent_length = wc_format_localized_decimal( $product_object->get_length() );
					$parent_width  = wc_format_localized_decimal( $product_object->get_width() );
					$parent_height = wc_format_localized_decimal( $product_object->get_height() );

					?>
					<p class="form-field form-row dimensions_field hide_if_variation_virtual form-row-last">
						<label for="product_length">
							<?php
							printf(
								/* translators: %s: dimension unit */
								esc_html__( 'Dimensions (L&times;W&times;H) (%s)', 'smm-api' ),
								esc_html( get_option( 'woocommerce_dimension_unit' ) )
							);
							?>
						</label>
						<?php  wc_help_tip( esc_html__( 'Length x width x height in decimal form', 'smm-api' ) ); ?>
						<span class="wrap">
							<input id="product_length" placeholder="<?php echo $parent_length ? esc_attr( $parent_length ) : esc_attr__( 'Length', 'smm-api' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="variable_length[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( $variation_object->get_length( 'edit' ) ) ); ?>" />
							<input placeholder="<?php echo $parent_width ? esc_attr( $parent_width ) : esc_attr__( 'Width', 'smm-api' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="variable_width[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( $variation_object->get_width( 'edit' ) ) ); ?>" />
							<input placeholder="<?php echo $parent_height ? esc_attr( $parent_height ) : esc_attr__( 'Height', 'smm-api' ); ?>" class="input-text wc_input_decimal last" size="6" type="text" name="variable_height[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( $variation_object->get_height( 'edit' ) ) ); ?>" />
						</span>
					</p>
					<?php
				}

				/**
				 * Variation options dimensions action.
				 *
				 * @since 2.5.0
				 *
				 * @param int     $loop           Position in the loop.
				 * @param array   $variation_data Variation data.
				 * @param WP_Post $variation      Post data.
				 */
				do_action( 'woocommerce_variation_options_dimensions', $loop, $variation_data, $variation );
				?>
			</div>

			<div>
				<p class="form-row hide_if_variation_virtual form-row-full">
					<label><?php esc_html_e( 'Shipping class', 'smm-api' ); ?></label>
					<?php
					wp_dropdown_categories(
						array(
							'taxonomy'         => 'product_shipping_class',
							'hide_empty'       => 0,
							'show_option_none' => __( 'Same as parent', 'smm-api' ),
							'name'             => 'variable_shipping_class[' . $loop . ']',
							'id'               => '',
							'selected'         => $variation_object->get_shipping_class_id( 'edit' ),
						)
					);
					?>
				</p>

				<?php
				if ( wc_tax_enabled() ) {
					woocommerce_wp_select(
						array(
							'id'            => "variable_tax_class{$loop}",
							'name'          => "variable_tax_class[{$loop}]",
							'value'         => $variation_object->get_tax_class( 'edit' ),
							'label'         => __( 'Tax class', 'smm-api' ),
							'options'       => array( 'parent' => __( 'Same as parent', 'smm-api' ) ) + wc_get_product_tax_class_options(),
							'desc_tip'      => 'true',
							'description'   => __( 'Choose a tax class for this product. Tax classes are used to apply different tax rates specific to certain types of product.', 'smm-api' ),
							'wrapper_class' => 'form-row form-row-full',
						)
					);

					/**
					 * Variation options tax action.
					 *
					 * @since 2.5.0
					 *
					 * @param int     $loop           Position in the loop.
					 * @param array   $variation_data Variation data.
					 * @param WP_Post $variation      Post data.
					 */
					do_action( 'woocommerce_variation_options_tax', $loop, $variation_data, $variation );
				}
				?>
			</div>
			<div>
				<?php
				woocommerce_wp_textarea_input(
					array(
						'id'            => "variable_description{$loop}",
						'name'          => "variable_description[{$loop}]",
						'value'         => $variation_object->get_description( 'edit' ),
						'label'         => __( 'Description', 'smm-api' ),
						'desc_tip'      => true,
						'description'   => __( 'Enter an optional description for this variation.', 'smm-api' ),
						'wrapper_class' => 'form-row form-row-full',
					)
				);
				?>
			</div>

			<?php do_action( 'woocommerce_product_after_variable_attributes', $loop, $variation_data, $variation ); 
			?>
		</div>
	</div>
</div>