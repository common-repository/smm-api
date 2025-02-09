<?php
/**
 * This file belongs to the SMM Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

global $post;
$classes = apply_filters('smms_plugin_fw_metabox_class', '', $post );
$classes = smms_plugin_fw_remove_duplicate_classes( $classes );

do_action( 'smm_before_metaboxes_tab' ) ?>
<div class="smms-plugin-fw metaboxes-tab <?php echo esc_attr($classes )?>">
    <?php do_action( 'smm_before_metaboxes_labels' ) ?>
    <ul class="metaboxes-tabs clearfix"<?php if ( count( $tabs ) <= 1 ) : ?> style="display:none;"<?php endif; ?>>
        <?php
        $i = 0;
        foreach ( $tabs as $key=>$tab ) :
            if ( !isset( $tab[ 'fields' ] ) || empty( $tab[ 'fields' ] ) ) {
                continue;
            }
            $anchor_id = 'smms-plugin-fw-metabox-tab-' . urldecode( $key ) . '-anchor';

            // parse deps for the tab visibility
            if ( isset( $tab[ 'deps' ] ) ) {
                $tab[ 'deps' ][ 'target-id' ] = isset( $tab[ 'deps' ][ 'target-id' ] ) ? $tab[ 'deps' ][ 'target-id' ] : $anchor_id;
                if ( isset( $tab[ 'deps' ][ 'id' ] ) && strpos( $tab[ 'deps' ][ 'id' ], '_' ) !== 0 )
                    $tab[ 'deps' ][ 'id' ] = '_' . $tab[ 'deps' ][ 'id' ];
                if ( isset( $tab[ 'deps' ][ 'ids' ] ) && strpos( $tab[ 'deps' ][ 'ids' ], '_' ) !== 0 )
                    $tab[ 'deps' ][ 'ids' ] = '_' . $tab[ 'deps' ][ 'ids' ];

                $tab[ 'deps' ][ 'type' ] = 'hideme';
            }
            ?>
        <li id="<?php echo esc_attr( $anchor_id) ?>" <?php if ( !$i ) : ?>class="tabs"<?php endif ?> <?php echo esc_attr( smms_field_deps_data( $tab )) ?>>
            <a href="#<?php echo esc_url( urldecode( $key )) ?>"><?php echo esc_html( $tab[ 'label' ]) ?></a></li><?php
            $i++;
        endforeach;
        ?>
    </ul>
    <?php do_action( 'smm_after_metaboxes_labels' ) ?>
    <?php if ( isset( $tab[ 'label' ] ) ) : ?>
        <?php do_action( 'smm_before_metabox_option_' . urldecode( $key ) ); ?>
    <?php endif ?>

    <?php
    // Use nonce for verification
    wp_nonce_field( 'metaboxes-fields-nonce', 'smm_metaboxes_nonce' );
    ?>
    <?php foreach ( $tabs as $key=> $tab ) :

        ?>
        <div class="tabs-panel" id="<?php echo esc_attr( urldecode( $key )) ?>">
            <?php
            if ( !isset( $tab[ 'fields' ] ) ) {
                continue;
            }

            $tab[ 'fields' ] = apply_filters( 'smm_metabox_' . $key . '_tab_fields', $tab[ 'fields' ] );

            foreach ( $tab[ 'fields' ] as $id_tab => $field ) :
                $field_name = $field[ 'name' ];
                $field_name  = str_replace( 'smm_metaboxes[', '', $field_name );
                if ( $pos = strpos( $field_name, ']' ) ) {
                    $field_name = substr_replace( $field_name, '', $pos, 1 );
                }
                $value            = smm_get_post_meta( $post->ID, $field_name );
                $field[ 'value' ] = $value != '' ? $value : ( isset( $field[ 'std' ] ) ? $field[ 'std' ] : '' );
	            $field[ 'checkboxgroup' ] = ( $field[ 'type' ] == 'checkbox' && isset( $field[ 'checkboxgroup' ] ) ) ? " " .$field[ 'checkboxgroup' ] : "";
                $container_classes = "the-metabox " . $field[ 'type' ] .$field[ 'checkboxgroup' ] . " clearfix ";
                $container_classes .= empty( $field[ 'label' ] ) ? 'no-label' : '';

                ?>
                <div class="<?php echo esc_attr( $container_classes) ?>">
                    <?php
                    if ( $field_template_path = smms_plugin_fw_get_field_template_path( $field ) ) {
                        $display_row                   = 'hidden' !== $field[ 'type' ];
                        $display_row                   = isset( $field[ 'smms-display-row' ] ) ? !!$field[ 'smms-display-row' ] : $display_row;
                        $field[ 'display-field-only' ] = in_array( $field[ 'type' ], array( 'hidden', 'html', 'sep', 'simple-text', 'title') );

                        if ( $display_row ) {

                            $field_row_path = apply_filters( 'smms_plugin_fw_metabox_field_row_template_path', SMM_CORE_PLUGIN_TEMPLATE_PATH . '/metaboxes/field-row.php', $field );
                            file_exists( $field_row_path ) && include( $field_row_path );
                        } else {

                            smms_plugin_fw_get_field( $field, true );
                        }
                    } else {
                        // backward compatibility
                        $args = apply_filters( 'smm_fw_metaboxes_type_args', array(
                                                                               'basename' => SMM_CORE_PLUGIN_PATH,
                                                                               'path'     => '/metaboxes/types/',
                                                                               'type'     => $field[ 'type' ],
                                                                               'args'     => array( 'args' => $field )
                                                                           )
                        );
                        extract( $args );
                        smm_plugin_get_template( $basename, $path . $type . '.php', $args );
                    }
                    ?>
                </div>
            <?php endforeach ?>
        </div>
    <?php endforeach ?>
</div>