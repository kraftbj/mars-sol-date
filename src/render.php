<?php
/**
 * Render callback for Mars Sol Date block: outputs Sol number instead of Gregorian date.
 *
 * Server-side rendering for the my-custom-block block.
 *
 * @param array $attributes Block attributes.
 * @param string $content Block inner content.
 * @param WP_Block $block Block object.
 *
 * @return string Rendered HTML.
 *
 * @package kraftbj/mars-sol-date
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// These are passed by render_callback but PHPCS doesn't know that.
$attributes = $attributes ?? array();
$content    = $content ?? '';
$block      = $block ?? new WP_Block();

if ( ! function_exists( 'mars_sol_date_get_first_post_time' ) ) {
	echo '';
	return;
}
// Block attributes/props (WP_Block object or array for legacy).
$atts    = array();
$context = array();
if ( is_object( $block ) && property_exists( $block, 'attributes' ) ) {
	$atts    = $block->attributes;
	$context = ( isset( $block->context ) && is_array( $block->context ) ) ? $block->context : array();
} elseif ( is_array( $block ) && isset( $block['attrs'] ) ) {
	$atts    = $block['attrs'];
	$context = isset( $block['context'] ) && is_array( $block['context'] ) ? $block['context'] : array();
}
$date_source   = isset( $atts['dateSource'] ) ? $atts['dateSource'] : 'publish';
$show_prefix   = isset( $atts['showPrefix'] ) ? $atts['showPrefix'] : true;
$link_to       = isset( $atts['linkTo'] ) ? $atts['linkTo'] : 'none';
$custom_field  = isset( $atts['customField'] ) ? $atts['customField'] : '';
$sol_label     = isset( $atts['solLabel'] ) ? $atts['solLabel'] : 'Sol';
$number_format = isset( $atts['numberFormat'] ) ? $atts['numberFormat'] : 'label-number';
$align         = isset( $atts['align'] ) ? $atts['align'] : '';
$post_obj      = null;
if ( isset( $context['postId'] ) ) {
	$post_obj = get_post( $context['postId'] );
} elseif ( isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post ) {
	$post_obj = $GLOBALS['post'];
}
if ( ! $post_obj || ! in_array( $post_obj->post_status, array( 'publish', 'future' ), true ) ) {
	echo '';
	return;
}
switch ( $date_source ) {
	case 'modified':
		$date_val = get_post_modified_time( 'U', false, $post_obj );
		break;
	case 'custom':
		// Try post meta, fallback to object property
		$date_val = false;
		if ( $custom_field ) {
			$meta_val = get_post_meta( $post_obj->ID, $custom_field, true );
			if ( $meta_val ) {
				$date_val = strtotime( $meta_val );
			} elseif ( isset( $post_obj->{$custom_field} ) ) {
				$date_val = strtotime( $post_obj->{$custom_field} );
			}
		}
		break;
	default:
		$date_val = get_post_time( 'U', false, $post_obj );
}
$first_post_time = mars_sol_date_get_first_post_time();
if ( ! $first_post_time || ! $date_val || $date_val < $first_post_time ) {
	echo '';
	return;
}
$sol_length = 24 * 3600 + 39 * 60 + 35;
$sol_number = floor( ( $date_val - $first_post_time ) / $sol_length ) + 1;
if ( $number_format === 'number' ) {
	$label = $sol_number;
} else {
	// Default case: includes 'label-number' and any other values
	$label = ( $show_prefix ? $sol_label . ' ' : '' ) . $sol_number;
}
$label    = trim( $label );
$link_ref = '';
if ( $link_to === 'post' && get_permalink( $post_obj ) ) {
	$link_ref = sprintf( '<a href="%s">%s</a>', esc_url( get_permalink( $post_obj ) ), esc_html( $label ) );
} else {
	$link_ref = esc_html( $label );
}
$wrapper_attrs = get_block_wrapper_attributes();
$extra_style   = '';
if ( $align && in_array( $align, array( 'left', 'center', 'right' ), true ) ) {
	$extra_style = sprintf( ' style="text-align:%s;"', esc_attr( $align ) );
}
printf( '<span%s%s>%s</span>', $wrapper_attrs ? ' ' . esc_attr( $wrapper_attrs ) : '', $extra_style, $link_ref ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped immediately before this function.
