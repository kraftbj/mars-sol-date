<?php
/**
 * Render callback for Mars Sol Date block: outputs Sol number instead of Gregorian date.
 */
if ( ! function_exists( 'mars_sol_date_get_first_post_time' ) ) {
	echo '';
	return;
}
// Block attributes/props (WP_Block object or array for legacy).
$atts = array();
$context = array();
if ( is_object( $block ) && property_exists( $block, 'attributes' ) ) {
	$atts = $block->attributes;
	$context = ( isset( $block->context ) && is_array( $block->context ) ) ? $block->context : array();
} elseif ( is_array( $block ) && isset( $block['attrs'] ) ) {
	$atts = $block['attrs'];
	$context = isset( $block['context'] ) && is_array( $block['context'] ) ? $block['context'] : array();
}
$dateSource   = isset( $atts['dateSource'] ) ? $atts['dateSource'] : 'publish';
$showPrefix   = isset( $atts['showPrefix'] ) ? $atts['showPrefix'] : true;
$linkTo       = isset( $atts['linkTo'] ) ? $atts['linkTo'] : 'none';
$customField  = isset( $atts['customField'] ) ? $atts['customField'] : '';
$solLabel     = isset( $atts['solLabel'] ) ? $atts['solLabel'] : 'Sol';
$numberFormat = isset( $atts['numberFormat'] ) ? $atts['numberFormat'] : 'label-number';
$align        = isset( $atts['align'] ) ? $atts['align'] : '';
$post = null;
if ( isset( $context['postId'] ) ) {
	$post = get_post( $context['postId'] );
} elseif ( isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post ) {
	$post = $GLOBALS['post'];
}
if ( ! $post || ! in_array( $post->post_status, array( 'publish', 'future' ), true ) ) {
	echo '';
	return;
}
switch ( $dateSource ) {
	case 'modified':
		$date_val = get_post_modified_time( 'U', false, $post );
		break;
	case 'custom':
		// Try post meta, fallback to object property
		$date_val = false;
		if ( $customField ) {
			$meta_val = get_post_meta( $post->ID, $customField, true );
			if ( $meta_val ) {
				$date_val = strtotime( $meta_val );
			} elseif ( isset( $post->{$customField} ) ) {
				$date_val = strtotime( $post->{$customField} );
			}
		}
		break;
	default:
		$date_val = get_post_time( 'U', false, $post );
}
$first_post_time = mars_sol_date_get_first_post_time();
if ( ! $first_post_time || ! $date_val || $date_val < $first_post_time ) {
	echo '';
	return;
}
$sol_length = 24 * 3600 + 39 * 60 + 35;
$sol_number = floor( ( $date_val - $first_post_time ) / $sol_length ) + 1;
if ( $numberFormat === 'number' ) {
	$label = $sol_number;
} else {
	// Default case: includes 'label-number' and any other values
	$label = ( $showPrefix ? $solLabel . ' ' : '' ) . $sol_number;
}
$label = trim( $label );
$link = '';
if ( $linkTo === 'post' && get_permalink( $post ) ) {
	$link = sprintf( '<a href="%s">%s</a>', esc_url( get_permalink( $post ) ), esc_html( $label ) );
} else {
	$link = esc_html( $label );
}
$wrapper_attrs = get_block_wrapper_attributes();
$extra_style = '';
if ( $align && in_array( $align, array( 'left', 'center', 'right' ), true ) ) {
	$extra_style = sprintf( ' style="text-align:%s;"', esc_attr( $align ) );
}
printf( '<span%s%s>%s</span>', $wrapper_attrs ? ' ' . esc_attr( $wrapper_attrs ) : '', $extra_style, $link );