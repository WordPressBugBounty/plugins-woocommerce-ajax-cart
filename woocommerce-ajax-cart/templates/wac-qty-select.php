<?php
/**
 * Product quantity inputs, as a dropdown
 *
 * Ajax Cart for WooCommerce's copy of WooCommerce's global/quantity-input.php, used
 * when "Show quantity as select" is on: the same wrapper, label, id and classes, with
 * a <select> in place of the number input. WooCommerce's before/after field hooks are
 * left out on purpose: they are where the −/+ buttons (this plugin's PRO, many themes)
 * go, and a button stepping a dropdown past its options breaks it.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 11.4.0
 *
 * @var bool   $readonly If the input should be set to readonly mode.
 * @var string $type     The input type attribute.
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables, local to wc_get_template()'s scope, not globals
$min_value = isset( $min_value ) ? $min_value : 0;
$step      = isset( $step ) ? $step : 1;
$input_id  = isset( $input_id ) ? $input_id : uniqid( 'quantity_' );
$classes   = ! empty( $classes ) ? (array) $classes : array( 'input-text', 'qty', 'text' );
$readonly  = ! empty( $readonly );
/* translators: %s: the product's name. */
$label = ! empty( $args['product_name'] ) ? sprintf( esc_html__( '%s quantity', 'woocommerce-ajax-cart' ), wp_strip_all_tags( $args['product_name'] ) ) : esc_html__( 'Quantity', 'woocommerce-ajax-cart' );
?>
<div class="quantity">
	<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $label ); ?></label>
	<?php if ( $readonly ) : ?>
		<input type="<?php echo esc_attr( isset( $type ) ? $type : 'number' ); ?>" readonly="readonly"
			id="<?php echo esc_attr( $input_id ); ?>"
			class="<?php echo esc_attr( join( ' ', $classes ) ); ?>"
			name="<?php echo esc_attr( $input_name ); ?>"
			value="<?php echo esc_attr( $input_value ); ?>"
			min="<?php echo esc_attr( $min_value ); ?>"
			<?php if ( 0 < $max_value ) : ?>
				max="<?php echo esc_attr( $max_value ); ?>"
			<?php endif; ?>
		/>
	<?php else : ?>
		<select name="<?php echo esc_attr( $input_name ); ?>"
				id="<?php echo esc_attr( $input_id ); ?>"
				title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce-ajax-cart' ); ?>"
				class="<?php echo esc_attr( join( ' ', $classes ) ); ?>"
				max="<?php echo esc_attr( wac_option( 'select_items' ) ); ?>">
			<?php if ( ! empty( $dropdown_steps ) ) : ?>
				<?php foreach ( $dropdown_steps as $step ) : ?>
					<option <?php selected( $input_value, $step ); ?> value="<?php echo esc_attr( $step ); ?>">
						<?php echo esc_html( $step ); ?>
					</option>
				<?php endforeach; ?>
			<?php else : ?>
				<?php for ( $i = $min_value; $i <= wac_option( 'select_items' ) && ( empty( $max_value ) || $max_value < 0 || $i <= $max_value ); $i += $step ) : ?>
					<option <?php selected( $input_value, $i ); ?> value="<?php echo esc_attr( $i ); ?>">
						<?php echo esc_html( $i ); ?>
					</option>
				<?php endfor; ?>
			<?php endif; ?>
		</select>
	<?php endif; ?>
</div>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
