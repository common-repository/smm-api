<?php
/**
 * This file belongs to the SMM Plugin Framework.
 * Author: Yith
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

extract( $field );

$show_button = false;
if ( isset( $add_new_button ) && isset( $post_type ) ) {
	$show_button         = true;
	$admin_url           = admin_url( 'post-new.php' );
	$params['post_type'] = $post_type;
	$add_new_url         = apply_filters( 'smms_plugin_fw_add_new_post_url', esc_url( add_query_arg( $params, $admin_url ) , $params, isset( $args ) ? $args : false  ));
}

if ( isset( $list_table_class ) && ! class_exists( $list_table_class ) && isset( $list_table_class_dir ) ) {
	include_once( $list_table_class_dir );
}

if ( class_exists( $list_table_class ) ):
	$list_table = isset( $args ) ? new $list_table_class( $args ) : new $list_table_class() ;
?>

<div id="<?php echo esc_attr( $id )?>" class="smms-plugin-fw-list-table <?php echo esc_attr( $class) ?>">
        <div class="smms-plugin-fw-list-table-container smms-plugin-fw smm-admin-panel-container">
            <div class="list-table-title">
            <h2>
		        <?php echo esc_html( isset( $title ) ? $title : '' )?>
            </h2>
	        <?php if( $show_button ): ?>
                <a href="<?php echo esc_url( $add_new_url) ?>" class="smms-add-button">
			        <?php echo esc_html( $add_new_button) ?>
                </a>
	        <?php endif ?>
            </div>

	        <?php if( isset( $desc) && !empty( $desc ) ) :?>
                <p class="smms-section-description"><?php echo esc_html( $desc) ?></p>
			<?php
            endif;
			$list_table->prepare_items();
			$list_table->views();
			?>
            <form method="post">
	            <?php if( isset( $search_form ) ) {
	                $list_table->search_box( $search_form['text'], $search_form['input_id'] );
                } ?>
				<?php
				$list_table->display();
				?>
            </form>
        </div>
    </div>

	<?php endif; ?>