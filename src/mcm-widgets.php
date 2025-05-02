<?php
/**
 * My Content Management widgets.
 *
 * @category Views
 * @package  My Content Management
 * @author   Joe Dolson
 * @license  GPLv2 or later
 * @link     https://www.joedolson.com/my-content-management/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include( dirname( __FILE__ ) . '/class-mcm-search-widget.php' );
include( dirname( __FILE__ ) . '/class-mcm-posts-widget.php' );
include( dirname( __FILE__ ) . '/class-mcm-meta-widget.php' );


/**
 * Using the name of a fieldset, get all values of that fieldset from the current post.
 *
 * @param string $fieldset Fieldset key.
 * @param int    $id Post ID.
 *
 * @return array
 */
function mcm_get_fieldset_values( $fieldset, $id = false ) {
	if ( ! $id ) {
		if ( ! is_singular() ) {
			return '';
		}
		global $post;
		$id = $post->ID;
	}
	$options = get_option( 'mcm_options' );
	$fields  = $options['fields'][ $fieldset ];
	foreach ( $fields as $group ) {
		$key = $group[0];
		if ( isset( $group[4] ) && '' !== $group[4] ) {
			$value = get_post_meta( $id, $key );
		} else {
			$value = get_post_meta( $id, $key, true );
		}
		$values[ $key ] = array(
			'label' => $group[1],
			'value' => $value,
			'type'  => $group[3],
		);
	}

	return $values;
}

/**
 * Format a fieldset for output.
 *
 * @param array  $values Fieldset values.
 * @param string $display Display type.
 * @param array  $headers Header row values.
 * @param string $fieldset Fieldset ID.
 * @param string $template Template value.
 *
 * @return string
 */
function mcm_format_fieldset( $values, $display, $headers, $fieldset, $template = false ) {
	$label  = $headers['label'];
	$value  = $headers['values'];
	$list   = '';
	$before = '';
	$after  = '';
	if ( 'custom' !== $display ) {
		if ( 'table' === $display ) {
			$before = '<table class="mcm_display_fieldset">
						<thead>
							<tr>
								<th scope="col">' . $label . '</th>
								<th scope="col">' . $value . '</th>
							</tr>
						</thead>
						<tbody>';
		} else {
			$before = '<ul class="mcm_display_fieldset">';
		}
		if ( is_array( $values ) ) {
			foreach ( $values as $value ) {
				if ( ! empty( $value['value'] ) ) {
					if ( is_array( $value['value'] ) ) {
						$i = 1;
						foreach ( $value['value'] as $val ) {
							$label  = apply_filters( 'mcm_widget_data_label', esc_html( wp_unslash( $value['label'] ) ), $val, $display, $fieldset );
							$output = apply_filters( 'mcm_widget_data_value', mcm_format_value( $val, $value['type'], $value['label'] ), $val, $display, $fieldset );
							if ( 'table' === $display ) {
								$list .= '<tr><td class="mcm_field_label">' . $label . ' <span class="mcm-repeater-label">(' . $i . ')</span></td><td class="mcm_field_value">' . $output . '</td></tr>';
							} else {
								$list .= '<li><strong class="mcm_field_label">' . $label . ' <span class="mcm-repeater-label">(' . $i . ')</span></strong> <div class="mcm_field_value">' . $output . '</div></li>';
							}
							$i++;
						}
					} else {
						$label  = apply_filters( 'mcm_widget_data_label', esc_html( wp_unslash( $value['label'] ) ), $value, $display, $fieldset );
						$output = apply_filters( 'mcm_widget_data_value', mcm_format_value( $value['value'], $value['type'], $value['label'] ), $value, $display, $fieldset );
						if ( 'table' === $display ) {
							$list .= '<tr><td class="mcm_field_label">' . $label . '</td><td class="mcm_field_value">' . $output . '</td></tr>';
						} else {
							$list .= '<li><strong class="mcm_field_label">' . $label . '</strong> <div class="mcm_field_value">' . $output . '</div></li>';
						}
					}
				}
			}
		}
		if ( 'table' === $display ) {
			$after = '</tbody>
				</table>';
		} else {
			$after = '</ul>';
		}
	} elseif ( 'custom' === $display ) {
		if ( $template ) {
			$values = mcm_flatten_array( $values );
			$list   = mcm_draw_template( $values, $template );
		}
		// work out a custom mechanism.
		$list = apply_filters( 'mcm_custom_widget_data', $list, $values, $headers, $fieldset );
		if ( WP_DEBUG && ! $list ) {
			$list .= '<p>' . esc_html__( "You've selected a custom view. Here is the data you're working with on this post:", 'my-content-management' ) . '</p>';
			$list .= '<pre>';
			$list .= print_r( $values, 1 );
			$list .= '</pre>';
			$list .= '<p>' . esc_html__( 'Use the filter <code>mcm_custom_widget_data</code> to create a custom view.', 'my-content-management' ) . '</p>';
		}
	}

	if ( $list ) {
		$list = wp_kses_post( $before . $list . $after );
	}

	return $list;
}

/**
 * Take raw output for MCM Widget and flatten to pass through mcm_draw_template
 *
 * @param array $values multidimensional array.
 *
 * @return array single dimensional array
 */
function mcm_flatten_array( $values ) {
	$array = array();
	foreach ( $values as $key => $value ) {
		$array[ $key ] = $value['value'];
	}

	return $array;
}

/**
 * Test a chooser field data type.
 *
 * @param int    $id Post ID.
 * @param string $url URL.
 * @param string $type Field type.
 * @param string $label Field label.
 *
 * @return string
 */
function mcm_test_chooser_field( $id = false, $url = false, $type = false, $label = false ) {
	if ( ! $id && ! $url && ! $type && ! $label ) {
		return '';
	}
	if ( $id ) {
		$url   = wp_get_attachment_url( $id );
		$title = get_the_title( $id );
		$type  = get_post_mime_type( $id );
	} else {
		$title = $label;
	}
	switch ( $type ) {
		case 'jpeg':
		case 'png':
		case 'gif':
		case 'image/jpeg':
		case 'image/png':
		case 'image/gif':
			return ( $id ) ? wp_get_attachment_image(
				$id,
				'thumbnail',
				true,
				array(
					'class' => 'mcm-attachment-chooser',
				)
			) : "<img src='$url' alt='' />";
		case 'mp3':
		case 'ogg':
		case 'audio/mpeg':
		case 'audio/wav':
		case 'audio/ogg':
		case 'audio/x-ms-wma':
			return do_shortcode( "[audio src='$url']" );
		case 'mp4':
		case 'avi':
		case 'video/mpeg':
		case 'video/mp4':
		case 'video/quicktime':
		case 'video/avi':
		case 'video/ogg':
		case 'video/webm':
			return do_shortcode( "[video src='$url' width='840' height='600']" );
		case 'text/csv':
		case 'text/plain':
		case 'text/xml':
		case 'application/pdf':
		case 'application/msword':
		case 'application/vnd.ms-powerpoint':
			// case 'application/vnd.ms-excel'.
		case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
		case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
		case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
			return "<a href='$url'>$title</a>";
		default:
			return "<a href='$url'>$title</a>";
	}
}

/**
 * Invert a color. Returns black or white, depending on what has the highest contrast with the selected color.
 *
 * @param string $color Hexadecimal color value.
 *
 * @return string
 */
function mcm_inverse_color( $color ) {
	$color = trim( str_replace( '#', '', $color ) );
	if ( 6 !== strlen( $color ) ) {
		return '#000000';
	}
	$rgb       = '';
	$total     = 0;
	$red       = 0.299 * ( 255 - hexdec( substr( $color, 0, 2 ) ) );
	$green     = 0.587 * ( 255 - hexdec( substr( $color, 2, 2 ) ) );
	$blue      = 0.114 * ( 255 - hexdec( substr( $color, 4, 2 ) ) );
	$luminance = 1 - ( ( $red + $green + $blue ) / 255 );
	if ( $luminance < 0.5 ) {
		return '#ffffff';
	} else {
		return '#000000';
	}
}

/**
 * Format custom values from MCM.
 *
 * @param string $value A value.
 * @param string $type A field type.
 * @param string $label Field label.
 *
 * @return string
 */
function mcm_format_value( $value, $type, $label ) {
	switch ( $type ) {
		case 'color':
			$value  = ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $value ) ) ? $value : '';
			$invert = mcm_inverse_color( $value );
			$return = '<div class="mcm-color-output" style="background: ' . $value . '; color: ' . $invert . '">' . $value . '</div>';
			break;
		case 'select':
		case 'tel':
		case 'time':
		case 'email':
		case 'text':
			$return = wp_unslash( $value );
			break;
		case 'textarea':
		case 'richtext':
			$return = wpautop( wp_unslash( $value ) );
			break;
		case 'upload':
			$components = parse_url( $value );
			$path       = pathinfo( $components['path'] );
			$extension  = $path['extension'];
			$return     = mcm_test_chooser_field( false, $value, $extension, $label );
			// try to figure out upload type; img or link.
			break;
		case 'chooser':
			// use mimetype to produce img link or shortcode.
			$value  = intval( $value );
			$return = mcm_test_chooser_field( $value );
			break;
		case 'date':
			// this value should be a timestamp, but it's not guaranteed.
			if ( is_numeric( $value ) ) {
				$return = date_i18n( get_option( 'date_format' ), $value );
			} else {
				$return = wp_unslash( $value );
			}
			// if is timestamp, convert to date; otherwise, display as is.
			break; // timestamp? convert to date format?
		case 'url':
			$return = "<a href='" . esc_url( $value ) . "'><span class='mcm_url mcm_text'>Link</span> <span class='mcm_url screen-reader-text'>" . esc_html( $value ) . '</span></a>';
			break;
		case 'post-relation':
			$post   = ( is_numeric( $value ) ) ? get_post( $value ) : false;
			$return = ( $post ) ? "<a href='" . get_permalink( $value ) . "'>" . get_the_title( $value ) . '</a>' : '';
			break; // get post name and link.
		case 'user-relation':
			$user   = ( is_numeric( $value ) ) ? get_user_by( 'id', $value ) : false;
			$return = ( is_object( $user ) ) ? $user->display_name : '';
			$return = ( $return ) ? $return : $user->user_login; // if user does not have display name, use login.
			break;
		default:
			$return = $value;
	}

	return $return;
}
