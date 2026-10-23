<?php
/**
 * Cart Page
 *
 * Custom template for plugin Ajax Cart for WooCommerce
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;
$min_value = isset($min_value) ? $min_value : 0;
$step = isset($step) ? $step : 1;
?>
<div class="quantity">
    <select name="<?php echo esc_attr( $input_name ); ?>"
            title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce-ajax-cart' ) ?>"
            class="input-text qty text"
            max="<?php echo esc_attr( wac_option('select_items') ); ?>">
        <?php if ( !empty($dropdown_steps) ): ?>
            <?php foreach ( $dropdown_steps as $step ): ?>
                <option <?php selected( $input_value, $step ); ?> value="<?php echo esc_attr( $step ); ?>">
                    <?php echo esc_html( $step ); ?>
                </option>
            <?php endforeach; ?>
        <?php else: ?>
            <?php for ( $i = $min_value; $i <= wac_option('select_items') && ( empty($max_value) || $i <= $max_value ); $i += $step ): ?>
                <option <?php selected( $input_value, $i ); ?> value="<?php echo esc_attr( $i ); ?>">
                    <?php echo esc_html( $i ); ?>
                </option>
            <?php endfor; ?>
        <?php endif; ?>
    </select>
</div>
