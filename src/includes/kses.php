<?php
/**
 * Custom KSES to allow some otherwise excluded attributes.
 *
 * @category Utilities
 * @package  My Content Management
 * @author   Joe Dolson
 * @license  GPLv2 or later
 * @link     https://www.joedolson.com/my-content-management/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Array of allowed elements for using KSES on forms.
 *
 * @return array
 */
function mcm_kses_elements() {
	$elements = array(
		'svg'              => array(
			'class'           => array(),
			'style'           => array(),
			'focusable'       => array(),
			'role'            => array(),
			'aria-labelledby' => array(),
			'xmlns'           => array(),
			'viewbox'         => array(),
		),
		'g'                => array(
			'fill' => array(),
		),
		'title'            => array(
			'id'    => array(),
			'title' => array(),
		),
		'path'             => array(
			'd'    => array(),
			'fill' => array(),
		),
		'h2'               => array(
			'class' => array(),
			'id'    => array(),
		),
		'h3'               => array(
			'class' => array(),
			'id'    => array(),
		),
		'h4'               => array(
			'class' => array(),
			'id'    => array(),
		),
		'h5'               => array(
			'class' => array(),
			'id'    => array(),
		),
		'h6'               => array(
			'class' => array(),
			'id'    => array(),
		),
		'label'            => array(
			'for'   => array(),
			'class' => array(),
		),
		'option'           => array(
			'value'    => array(),
			'selected' => array(),
		),
		'select'           => array(
			'id'               => array(),
			'aria-describedby' => array(),
			'aria-labelledby'  => array(),
			'name'             => array(),
			'disabled'         => array(),
			'min'              => array(),
			'max'              => array(),
			'required'         => array(),
			'readonly'         => array(),
			'autocomplete'     => array(),
		),
		'input'            => array(
			'id'               => array(),
			'class'            => array(),
			'aria-describedby' => array(),
			'aria-labelledby'  => array(),
			'value'            => array(),
			'type'             => array(),
			'name'             => array(),
			'size'             => array(),
			'checked'          => array(),
			'disabled'         => array(),
			'min'              => array(),
			'max'              => array(),
			'required'         => array(),
			'readonly'         => array(),
			'autocomplete'     => array(),
			'data-href'        => array(),
		),
		'textarea'         => array(
			'id'               => array(),
			'class'            => array(),
			'cols'             => array(),
			'rows'             => array(),
			'aria-describedby' => array(),
			'aria-labelledby'  => array(),
			'disabled'         => array(),
			'required'         => array(),
			'readonly'         => array(),
			'name'             => array(),
		),
		'form'             => array(
			'id'     => array(),
			'name'   => array(),
			'action' => array(),
			'method' => array(),
			'class'  => array(),
		),
		'button'           => array(
			'name'             => array(),
			'disabled'         => array(),
			'type'             => array(),
			'class'            => array(),
			'aria-expanded'    => array(),
			'aria-describedby' => array(),
			'role'             => array(),
			'aria-selected'    => array(),
			'aria-controls'    => array(),
			'data-href'        => array(),
		),
		'ul'               => array(
			'class' => array(),
		),
		'fieldset'         => array(
			'class' => array(),
			'id'    => array(),
		),
		'legend'           => array(
			'class' => array(),
			'id'    => array(),
		),
		'li'               => array(
			'class' => array(),
		),
		'span'             => array(
			'id'          => array(),
			'class'       => array(),
			'aria-live'   => array(),
			'aria-hidden' => array(),
			'style'       => array(),
			'lang'        => array(),
		),
		'i'                => array(
			'id'          => array(),
			'class'       => array(),
			'aria-live'   => array(),
			'aria-hidden' => array(),
		),
		'strong'           => array(
			'id'    => array(),
			'class' => array(),
		),
		'b'                => array(
			'id'    => array(),
			'class' => array(),
		),
		'hr'               => array(
			'class' => array(),
		),
		'p'                => array(
			'class' => array(),
		),
		'div'              => array(
			'class'           => array(),
			'aria-live'       => array(),
			'id'              => array(),
			'role'            => array(),
			'data-default'    => array(),
			'aria-labelledby' => array(),
			'style'           => array(),
			'lang'            => array(),
		),
		'img'              => array(
			'class'    => true,
			'src'      => true,
			'alt'      => true,
			'width'    => true,
			'height'   => true,
			'id'       => true,
			'longdesc' => true,
			'tabindex' => true,
		),
		'br'               => array(),
		'table'            => array(
			'class' => array(),
			'id'    => array(),
		),
		'caption'          => array(),
		'thead'            => array(),
		'tfoot'            => array(),
		'tbody'            => array(),
		'tr'               => array(
			'class' => array(),
			'id'    => array(),
		),
		'th'               => array(
			'scope' => array(),
			'class' => array(),
			'id'    => array(),
		),
		'td'               => array(
			'class'     => array(),
			'id'        => array(),
			'aria-live' => array(),
		),
		'a'                => array(
			'aria-labelledby'  => array(),
			'aria-describedby' => array(),
			'href'             => array(),
			'class'            => array(),
			'aria-current'     => array(),
			'target'           => array(),
		),
		'section'          => array(
			'id'    => array(),
			'class' => array(),
		),
		'aside'            => array(
			'id'    => array(),
			'class' => array(),
		),
		'code'             => array(
			'class' => array(),
		),
		'pre'              => array(
			'class' => array(),
		),
		'script'           => array(
			'type' => 'application/ld+json',
		),
		'duet-date-picker' => array(
			'identifier'        => array(),
			'first-day-of-week' => array(),
			'name'              => array(),
			'value'             => array(),
			'required'          => array(),
		),
		'time'             => array(
			'data-label' => array(),
		),
	);

	return $elements;
}

/**
 * Add iFrame to allowed wp_kses_post tags
 *
 * @param array  $tags Allowed tags, attributes, and/or entities.
 * @param string $context Context to judge allowed tags by. Allowed values are 'post'.
 *
 * @return array
 */
function mcm_kses_post_tags( $tags, $context ) {
	if ( 'post' === $context ) {
		$tags['iframe'] = array(
			'src'             => true,
			'height'          => true,
			'width'           => true,
			'frameborder'     => true,
			'allowfullscreen' => true,
			'title'           => true,
		);
	}

	return $tags;
}
add_filter( 'wp_kses_allowed_html', 'mcm_kses_post_tags', 10, 2 );
