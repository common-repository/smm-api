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
$class = !empty( $class ) ? $class : 'smms-plugin-fw-preview-field';

?>
<img src="<?php echo esc_url( $value) ?>" class="<?php echo esc_attr( $class) ?>"
    <?php echo esc_attr( $custom_attributes) ?>
    <?php if ( isset( $data ) ) echo esc_attr( smms_plugin_fw_html_data_to_string( $data )) ?>>