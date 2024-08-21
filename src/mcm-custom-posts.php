<?php
/**
 * Create post types and custom field editing.
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

global $mcm_types, $mcm_fields, $mcm_extras, $mcm_enabled, $mcm_templates, $default_mcm_types, $default_mcm_fields, $default_mcm_extras;

/**
 * Create post types.
 */
function mcm_posttypes() {
	global $mcm_types, $mcm_enabled;
	$types   = $mcm_types;
	$enabled = $mcm_enabled;
	if ( is_array( $enabled ) ) {
		foreach ( $enabled as $key ) {
			$value =& $types[ $key ];
			if ( is_array( $value ) && ! empty( $value ) ) {
				$labels = array(
					'name'               => $value[3],
					'singular_name'      => $value[2],
					'add_new'            => __( 'Add New', 'my-content-management' ),
					// Translators: post type name.
					'add_new_item'       => sprintf( __( 'Add New %s', 'my-content-management' ), $value[2] ),
					// Translators: post type name.
					'edit_item'          => sprintf( __( 'Edit %s', 'my-content-management' ), $value[2] ),
					// Translators: post type name.
					'new_item'           => sprintf( __( 'New %s', 'my-content-management' ), $value[2] ),
					// Translators: post type name.
					'view_item'          => sprintf( __( 'View %s', 'my-content-management' ), $value[2] ),
					// Translators: post type plural name.
					'search_items'       => sprintf( __( 'Search %s', 'my-content-management' ), $value[3] ),
					// Translators: post type name.
					'not_found'          => sprintf( __( 'No %s found', 'my-content-management' ), $value[1] ),
					// Translators: post type name.
					'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'my-content-management' ), $value[1] ),
					'parent_item_colon'  => '',
				);
				$labels = apply_filters( 'mcm_post_type_labels', $labels, $value );
				$raw    = $value[4];
				$slug   = ( ! isset( $raw['slug'] ) || '' === $raw['slug'] ) ? $key : $raw['slug'];
				$icon   = ( is_ssl() ) ? str_replace( 'http://', 'https://', $raw['menu_icon'] ) : $raw['menu_icon'];
				$icon   = ( null === $raw['menu_icon'] ) ? plugins_url( 'images', __FILE__ ) . "/$key.png" : $icon;
				$args   = array(
					'labels'              => $labels,
					'public'              => $raw['public'],
					'publicly_queryable'  => $raw['publicly_queryable'],
					'exclude_from_search' => $raw['exclude_from_search'],
					'show_ui'             => $raw['show_ui'],
					'show_in_menu'        => $raw['show_in_menu'],
					'menu_icon'           => ( '' === $icon ) ? plugins_url( 'images', __FILE__ ) . '/mcm_resources.png' : $icon,
					'query_var'           => true,
					'rewrite'             => array(
						'slug'       => $slug,
						'with_front' => false,
					),
					'hierarchical'        => $raw['hierarchical'],
					'has_archive'         => true,
					'show_in_rest'        => ( isset( $raw['show_in_rest'] ) ? $raw['show_in_rest'] : false ),
					'supports'            => $raw['supports'],
					'map_meta_cap'        => true,
					'capability_type'     => 'post', // capability type is post type.
					'taxonomies'          => array( 'post_tag' ),
				);

				register_post_type( $key, $args );
			}
		}
	}
}

/**
 * Set up post type message strings.
 *
 * @param array $messages Array of messages.
 *
 * @return array
 */
function mcm_posttypes_messages( $messages ) {
	global $post, $post_ID, $mcm_types, $mcm_enabled;
	$types   = $mcm_types;
	$enabled = $mcm_enabled;
	if ( is_array( $enabled ) ) {
		foreach ( $enabled as $key ) {
			$value            = $types[ $key ];
			$messages[ $key ] = array(
				0  => '', // Unused. Messages start at index 1.
				// Translators: 1 Post type singular name, 2 link to listing.
				1  => sprintf( __( '%1$s Listing updated. <a href="%2$s">View %1$s listing</a>', 'my-content-management' ), $value[2], esc_url( get_permalink( $post_ID ) ) ),
				2  => __( 'Custom field updated.', 'my-content-management' ),
				3  => __( 'Custom field deleted.', 'my-content-management' ),
				// Translators: post type name.
				4  => sprintf( __( '%s listing updated.', 'my-content-management' ), $value[2] ),
				// translators: Post type name, revision title.
				5  => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$ss', 'my-content-management' ), $value[2], wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				// Translators: Post type name, link, post type lowercase name.
				6  => sprintf( __( '%1$s published. <a href="%2$s">View %3$s listing</a>', 'my-content-management' ), $value[2], esc_url( get_permalink( $post_ID ) ), $value[0] ),
				// Translators: Post type name.
				7  => sprintf( __( '%s listing saved.', 'my-content-management' ), $value[2] ),
				// Translators: Post type name, link to preview, post type lower case name.
				8  => sprintf( __( '%1$s listing submitted. <a target="_blank" href="%2$s">Preview %3$s listing</a>', 'my-content-management' ), $value[2], esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $value[0] ),
				// Translators: Post type name, date scheduled, preview link, post type lowercase name.
				9  => sprintf( __( '%1$s listing scheduled for: <strong>%2$s</strong>. <a target="_blank" href="%3$s">Preview %4$s</a>', 'my-content-management' ), $value[2], date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ), $value[0] ),
				// Translators: Post type name, preview link, post type lowercase.
				10 => sprintf( __( '%1$s draft updated. <a target="_blank" href="%2$s">Preview %3$s listing</a>', 'my-content-management' ), $value[2], esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $value[0] ),
			);
		}
	}

	return $messages;
}

/**
 * Define custom taxonomies.
 */
function mcm_taxonomies() {
	global $mcm_types, $mcm_enabled;
	$types   = $mcm_types;
	$enabled = $mcm_enabled;
	if ( is_array( $enabled ) ) {
		foreach ( $enabled as $key ) {
			$value =& $types[ $key ];
			if ( is_array( $value ) && ! empty( $value ) ) {
				$cat_key = str_replace( 'mcm_', '', sanitize_key( $key ) );
				// translators: Name of post type.
				$label = sprintf( apply_filters( 'mcm_tax_category_name', __( '%s Categories', 'my-content-management' ), $value[2], $cat_key ), $value[2] );
				$slug  = apply_filters( 'mcm_tax_category_slug', "$cat_key-category", $cat_key );
				register_taxonomy(
					"mcm_category_$cat_key", // internal name = machine-readable taxonomy name.
					array( $key ), // object type = post, page, link, or custom post-type.
					array(
						'hierarchical'      => true,
						// Translators: taxonomy name.
						'label'             => $label,
						'show_in_rest'      => true,
						'show_admin_column' => true,
						'query_var'         => true, // enable taxonomy-specific querying.
						'rewrite'           => array(
							'slug' => $slug,
						), // pretty permalinks for your taxonomy.
					)
				);
				// translators: Name of post type.
				$type_label = sprintf( apply_filters( 'mcm_tax_type_name', __( '%s Types', 'my-content-management' ), $value[2], $cat_key ), $value[2] );
				$slug       = apply_filters( 'mcm_tax_type_slug', "$cat_key-type", $cat_key );

				register_taxonomy(
					"mcm_type_$cat_key", // internal name = machine-readable taxonomy name.
					array( $key ), // object type = post, page, link, or custom post-type.
					array(
						'hierarchical'      => false,
						// Translators: taxonomy name.
						'label'             => $type_label,
						'show_in_rest'      => true,
						'show_admin_column' => true,
						'query_var'         => true, // enable taxonomy-specific querying.
						'rewrite'           => array(
							'slug' => $slug,
						), // pretty permalinks for your taxonomy.
					)
				);
				// translators: Name of post type.
				$tag_label = sprintf( apply_filters( 'mcm_tax_tag_name', __( '%s Tags', 'my-content-management' ), $value[2], $cat_key ), $value[2] );
				$slug      = apply_filters( 'mcm_tax_tag_slug', "$cat_key-tag", $cat_key );

				register_taxonomy(
					"mcm_tag_$cat_key", // internal name = machine-readable taxonomy name.
					array( $key ), // object type = post, page, link, or custom post-type.
					array(
						'hierarchical'      => false,
						// Translators: taxonomy name.
						'label'             => $tag_label,
						'show_in_rest'      => true,
						'show_admin_column' => true,
						'query_var'         => true, // enable taxonomy-specific querying.
						'rewrite'           => array(
							'slug' => $slug,
						), // pretty permalinks for your taxonomy.
					)
				);
			}
		}
	}
}

/**
 * Set up post meta boxes.
 */
function mcm_add_custom_boxes() {
	global $mcm_fields, $mcm_extras;
	$fields = $mcm_fields;
	$extras = $mcm_extras;
	if ( is_array( $fields ) ) {
		foreach ( $fields as $key => $value ) {
			if ( isset( $extras[ $key ] ) && is_array( $extras[ $key ][0] ) ) {
				foreach ( $extras[ $key ][0] as $k ) {
					$fields    = array(
						$key => $value,
					);
					$post_type = $k;
					$location  = $extras[ $key ][1];
					$show      = isset( $extras[ $key ][2] ) ? $extras[ $key ][2] : true;
					$show      = mcm_test_context( $show );
					mcm_add_custom_box( $fields, $post_type, $location, $show );
				}
			} else {
				if ( isset( $extras[ $key ] ) ) {
					if ( ! empty( $extras[ $key ][0] ) ) {
						$fields    = array(
							$key => $value,
						);
						$post_type = $extras[ $key ][0];
						$location  = $extras[ $key ][1];
						$show      = isset( $extras[ $key ][2] ) ? $extras[ $key ][2] : true;
						$show      = mcm_test_context( $show );
						mcm_add_custom_box( $fields, $post_type, $location, $show );
					}
				}
			}
		}
	}
}

/**
 * Add custom meta boxes.
 *
 * @param array  $fields Fields to set up.
 * @param string $post_type Post type to set up for.
 * @param string $location Location in classic editor.
 * @param bool   $show True to show.
 */
function mcm_add_custom_box( $fields, $post_type = 'post', $location = 'side', $show = true ) {
	if ( function_exists( 'add_meta_box' ) ) {
		$location = apply_filters( 'mcm_set_location', $location, $fields, $post_type );
		$priority = apply_filters( 'mcm_set_priority', 'default', $fields, $post_type );
		foreach ( array_keys( $fields ) as $field ) {
			$id              = sanitize_title( $field );
			$field           = stripslashes( $field );
			$fields['group'] = $field;
			$field           = apply_filters( 'mcm_display_name', $field );
			if ( apply_filters( 'mcm_filter_meta_box', $show, $post_type, $id ) ) {
				add_meta_box( $id, $field, 'mcm_build_custom_box', $post_type, $location, $priority, $fields );
			}
		}
	}
}

/**
 * Should this fieldset be shown on this post.
 *
 * @param mixed bool|array|integer $show True or false.
 *
 * @return bool
 */
function mcm_test_context( $show ) {
	if ( true === $show || '' === $show ) {
		return true;
	} else {
		$contexts = explode( ',', $show );
		$contexts = array_map( 'trim', $contexts );
		foreach ( $contexts as $show ) {
			if ( is_numeric( $show ) ) {
				if ( isset( $_GET['post'] ) && $_GET['post'] === $show ) {
					return true;
				}
			}
		}
	}

	return false;
}

/**
 * Create custom field box.
 *
 * @param array $post Post.
 * @param array $fields Fields.
 */
function mcm_build_custom_box( $post, $fields ) {
	static $nonce_flag = false;
	// Run once.
	$id = addslashes( $fields['args']['group'] );
	echo "<div class='mcm_post_fields'>";
	if ( ! $nonce_flag ) {
		mcm_echo_nonce();
		$nonce_flag = true;
	}
	mcm_echo_hidden( $fields['args'][ $id ], $id );
	// Generate box contents.
	echo apply_filters( 'mcm_build_custom_box', '', $post, $fields );
	$i = 0;
	foreach ( $fields['args'][ $id ] as $key => $field ) {
		if ( 'repeatable' !== $key ) {
			echo mcm_field_html( $field );
			$i++;
		}
	}
	echo "<br class='clear' /></div>";
}

/**
 * Generate field HTML for a given field type.
 *
 * @param array $args Field settings.
 *
 * @return string
 */
function mcm_field_html( $args ) {
	switch ( $args[3] ) {
		case 'textarea':
			return mcm_text_area( $args );
		case 'select':
			return mcm_select( $args );
		case 'checkbox':
			return mcm_checkbox( $args );
		case 'checkboxes':
			return mcm_checkboxes( $args );
		case 'upload':
			return mcm_upload_field( $args );
		case 'chooser':
			return mcm_chooser_field( $args );
		case 'richtext':
			return mcm_rich_text_area( $args );
		case 'post-relation':
			return mcm_post_relation( $args );
		case 'user-relation':
			return mcm_user_relation( $args );
		default:
			return mcm_text_field( $args, $args[3] );
	}
}

/**
 * Output an uploader field.
 *
 * @param array $args Field arguments.
 *
 * @return string
 */
function mcm_upload_field( $args ) {
	global $post;
	$args[1]     = stripslashes( $args[1] );
	$description = stripslashes( $args[2] );
	// adjust data.
	$single   = true;
	$download = '';
	if ( isset( $args[4] ) && 'true' === $args[4] ) {
		$single = false;
	}
	$args[2] = get_post_meta( $post->ID, $args[0], $single );
	if ( ! empty( $args[2] ) && '0' !== (string) $args[2] ) {
		if ( $single ) {
			$download = '<div><a href="' . $args[2] . '">View ' . $args[1] . '</a></div>';
		} else {
			$download = '<ul>';
			$i        = 0;
			foreach ( $args[2] as $file ) {
				if ( '' !== $file ) {
					$short     = str_replace( site_url(), '', $file );
					$download .= '<li><input type="checkbox" id="del-' . $args[0] . $i . '" name="mcm_delete[' . $args[0] . '][]" value="' . $file . '" /> <label for="del-' . $args[0] . $i . '">' . __( 'Delete', 'my-content-management' ) . '</label> <a href="' . esc_url( $file ) . '">' . esc_html( $short ) . '</a></li>';
					$i++;
				}
			}
			$download .= '</ul>';
		}
	} else {
		$download = '';
	}
	$max_upload   = (int) ( ini_get( 'upload_max_filesize' ) );
	$max_post     = (int) ( ini_get( 'post_max_size' ) );
	$memory_limit = (int) ( ini_get( 'memory_limit' ) );
	$upload_mb    = min( $max_upload, $max_post, $memory_limit );
	// Translators: Upload limit in MB.
	$label_format = '<div class="mcm_text_field mcm_field"><input type="hidden" name="%1$s" id="%1$s" value="%3$s" /><label for="%1$s"><strong>%2$s</strong></label><br />' . '<input type="file" name="%1$s" id="%1$s" /><br />' . sprintf( __( 'Upload limit: %s MB', 'my-content-management' ), $upload_mb );
	if ( '' !== $description ) {
		$label_format .= '<br /><em>' . $description . '</em>';
	}
	if ( '' !== $download ) {
		$label_format .= $download;
	}
	$label_format .= '</div>';

	return vsprintf( $label_format, $args );
}

/**
 * Output a chooser field.
 *
 * @param array $args Field arguments.
 *
 * @return string
 */
function mcm_chooser_field( $args ) {
	global $post;
	$args[1]     = stripslashes( $args[1] );
	$description = stripslashes( $args[2] );
	// adjust data.
	$single   = true;
	$download = '';
	$value    = '';
	if ( isset( $args[4] ) && 'true' === $args[4] ) {
		$single = false;
	}
	$args[2] = get_post_meta( $post->ID, $args[0], $single );
	$attr    = array(
		'height' => 80,
		'width'  => 80,
	);
	if ( ! empty( $args[2] ) && '0' !== (string) $args[2] ) {
		if ( $single ) {
			$value     = '%3$s';
			$url       = wp_get_attachment_url( $args[2] );
			$img       = wp_get_attachment_image( $args[2], array( 80, 80 ), true, $attr );
			$download .= '<div class="mcm-chooser-image"><a href="' . $url . '">' . $img . '</a><span class="mcm-delete"><input type="checkbox" id="del-' . $args[0] . '" name="mcm_delete[' . $args[0] . '][]" value="' . absint( $args[2] ) . '" /> <label for="del-' . $args[0] . '">' . __( 'Delete', 'my-content-management' ) . '</label></span></div>';
			$copy      = __( 'Change Media', 'my-content-management' );
		} else {
			$value = '';
			$i     = 0;
			foreach ( $args[2] as $attachment ) {
				if ( $attachment ) {
					$url       = wp_get_attachment_url( $attachment );
					$img       = wp_get_attachment_image( $attachment, array( 80, 80 ), true, $attr );
					$download .= '<div class="mcm-chooser-image"><a href="' . $url . '">' . $img . '</a><span class="mcm-delete"><input type="checkbox" id="del-' . $args[0] . $i . '" name="mcm_delete[' . $args[0] . '][]" value="' . absint( $attachment ) . '" /> <label for="del-' . $args[0] . $i . '">' . __( 'Delete', 'my-content-management' ) . '</label></span></div>';
				}
				$i++;
			}
			$copy = __( 'Add Media', 'my-content-management' );
		}
	} else {
		$copy = __( 'Choose Media', 'my-content-management' );
	}
	$label_format  = '<div class="mcm_chooser_field mcm_field field-holder"><label for="%1$s"><strong>%2$s</strong></label> ' . '<input type="hidden" name="%1$s" value="' . esc_attr( $value ) . '" class="textfield" id="%1$s" /> <a href="#" class="button textfield-field">' . esc_html( $copy ) . '</a><br />';
	$label_format .= '<br /><div class="selected">' . wp_kses_post( $description ) . '</div>';
	if ( '' !== $download ) {
		$label_format .= $download;
	}
	$label_format .= '</div>';

	return vsprintf( $label_format, $args );
}

/**
 * Output a simple text or text-like field.
 *
 * @param array  $args Field arguments.
 * @param string $type Input type.
 *
 * @return string
 */
function mcm_text_field( $args, $type = 'text' ) {
	$args[1]     = stripslashes( $args[1] );
	$description = stripslashes( $args[2] );
	$types       = array( 'color', 'date', 'number', 'tel', 'time', 'url' );
	if ( 'mcm_text_field' === $type ) {
		$type = 'text';
	} else {
		$type = ( in_array( $type, $types, true ) ) ? $type : 'text';
	}
	global $post;
	$name        = $args[0];
	$label       = $args[1];
	$description = $args[2];
	// adjust data.
	$single = true;
	if ( isset( $args[4] ) && 'true' === $args[4] ) {
		$single = false;
	}
	$meta  = get_post_meta( $post->ID, $name, $single );
	$value = ( $single ) ? $meta : '';
	if ( 'date' === $type && $single ) {
		$value = ( is_numeric( $value ) ) ? gmdate( 'Y-m-d', $value ) : gmdate( 'Y-m-d', strtotime( $value ) );
	}
	$value   = esc_attr( $value );
	$value   = ( is_array( $value ) ) ? esc_attr( implode( ', ', $value ) ) : esc_attr( $value );
	$output  = "<div class='mcm_text_field mcm_field'>";
	$output .= '<p><label for="' . esc_attr( $name ) . '"><strong>' . esc_html( $label ) . '</strong></label><br /><input class="widefat" type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" id="' . esc_attr( $name ) . '" /></p>';
	if ( is_array( $meta ) ) {
		$i       = 1;
		$output .= '<ul>';
		foreach ( $meta as $field ) {
			if ( '' !== $field ) {
				$field = htmlentities( $field );
				if ( 'date' === $type ) {
					$field = ( is_numeric( $field ) ) ? gmdate( 'Y-m-d', $field ) : gmdate( 'Y-m-d', strtotime( $field ) );
				}
				$field   = esc_attr( $field );
				$output .= '<li><span class="mcm-delete"><input type="checkbox" id="del-' . esc_attr( $name ) . $i . '" name="mcm_delete[' . esc_attr( $name ) . '][]" value="' . esc_attr( $field ) . '" /> <label for="del-' . esc_attr( $name ) . $i . '"><span>' . __( 'Delete', 'my-content-management' ) . '</span> - ' . esc_html( $field ) . '</label></span></li>';
				$i++;
			}
		}
		$output .= '</ul>';
	}
	if ( '' !== $description ) {
		$output .= '<em>' . esc_html( $description ) . '</em>';
	}
	$output .= '</div>';

	return $output;
}

/**
 * Output a select field.
 *
 * @param array $args Field arguments.
 *
 * @return string
 */
function mcm_select( $args ) {
	global $post;
	$args[1] = stripslashes( $args[1] );
	$choices = $args[2];
	$single  = true;
	if ( isset( $args[4] ) && 'true' === $args[4] ) {
		$single = false;
	}
	$args[2]      = get_post_meta( $post->ID, $args[0], $single );
	$label_format = '<p class="mcm_select mcm_field"><label for="%1$s"><strong>%2$s</strong></label><br />' . '<select name="%1$s" id="%1$s">' . mcm_create_options( $choices, $args[2] ) . '</select></p>';

	return vsprintf( $label_format, $args );
}

/**
 * Single checkbox. Value is always 'true' when checked.
 *
 * @param array $args Values associated with this control.
 *
 * @return string
 */
function mcm_checkbox( $args ) {
	global $post;
	$args[1] = stripslashes( $args[1] );
	$choices = $args[2];
	$single  = true;
	$checked = '';
	if ( isset( $args[4] ) && 'true' === $args[4] ) {
		$single = false;
	}
	$args[2] = get_post_meta( $post->ID, $args[0], $single );
	if ( 'true' === $args[2] ) {
		$checked = "checked='checked'";
	}
	$label_format = '<p class="mcm_checkbox mcm_field"><input type="checkbox" name="%1$s" id="%1$s" value="true" ' . $checked . '/> <label for="%1$s"><strong>%2$s</strong></label></p>';

	return vsprintf( $label_format, $args );
}

/**
 * Set of checkboxes.
 *
 * @param array $args Values associated with this control.
 *
 * @return string
 */
function mcm_checkboxes( $args ) {
	global $post;
	$args[1] = stripslashes( $args[1] );
	$choices = $args[2];
	$single  = true;
	if ( isset( $args[4] ) && 'true' === $args[4] ) {
		$single = false;
	}
	$args[2]      = get_post_meta( $post->ID, $args[0], $single );
	$label_format = '<fieldset><legend><strong>%2$s</strong></legend><ul>' . mcm_create_options( $choices, $args[2], $args[0] ) . '</ul></fieldset>';

	return vsprintf( $label_format, $args );
}

/**
 * Output a post relationships field.
 *
 * @param array $args Field arguments.
 *
 * @return string
 */
function mcm_post_relation( $args ) {
	global $post;
	$title     = '';
	$args[1]   = stripslashes( $args[1] );
	$post_type = $args[2];
	$single    = true;
	if ( isset( $args[4] ) && 'true' === $args[4] ) {
		$single = false;
	}
	$args[2] = get_post_meta( $post->ID, $args[0], $single );
	if ( is_numeric( $args[2] ) ) {
		$title = '(' . get_the_title( $args[2] ) . ')';
	}
	$label_format = '<p class="mcm_post-relation mcm_field"><label for="%1$s"><strong>%2$s</strong> <span id="mcm_selected_post_relation" aria-live="assertive">' . $title . '</span></label><br />' . '<input type="text" class="mcm-autocomplete-posts" value="%3$s" name="%1$s" id="%1$s" data-value="' . $post_type . '" aria-describedby="mcm_selected_post_relation" /></p>';

	return vsprintf( $label_format, $args );
}

/**
 * Output user relationship field.
 *
 * @param array $args Field arguments.
 *
 * @return string
 */
function mcm_user_relation( $args ) {
	global $post;
	$user_login = '';
	$args[1]    = stripslashes( $args[1] );
	$user_role  = $args[2];
	$single     = true;
	if ( isset( $args[4] ) && 'true' === $args[4] ) {
		$single = false;
	}
	$args[2] = get_post_meta( $post->ID, $args[0], $single );
	if ( is_numeric( $args[2] ) ) {
		$user       = get_userdata( $args[2] );
		$user_login = '(' . $user->user_login . ')';
	}
	$label_format = '<p class="mcm_user-relation mcm_field"><label for="%1$s"><strong>%2$s</strong> <span id="mcm_selected_user_login" aria-live="assertive">' . esc_html( $user_login ) . '</span></label><br />' . '<input type="text" class="mcm-autocomplete-users" value="%3$s" name="%1$s" id="%1$s" data-value="' . esc_attr( $user_role ) . '" aria-describedby="mcm_selected_user_login" /></p>';

	return vsprintf( $label_format, $args );
}

/**
 * Post selection options.
 *
 * @param string        $type Post type to select.
 * @param mixed bool|id $chosen Selected data.
 *
 * @return string
 */
function mcm_choose_posts( $type, $chosen = false ) {
	$args   = apply_filters(
		'mcm_post_relations',
		array(
			'post_type'      => $type,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		),
		$type
	);
	$posts  = get_posts( $args );
	$select = '';
	foreach ( $posts as $post ) {
		$selected = ( $chosen && ( $post->ID === $chosen ) ) ? ' selected="selected"' : '';
		$select  .= "<option value='$post->ID'$selected>" . esc_html( stripslashes( $post->post_title ) ) . "</option>\n";
	}

	return $select;
}

/**
 * User selection input.
 *
 * @param string        $type User role.
 * @param mixed bool|id $chosen Selected user.
 *
 * @return string
 */
function mcm_choose_users( $type, $chosen = false ) {
	$args   = apply_filters(
		'mcm_user_relations',
		array(
			'role'   => $type,
			'fields' => array(
				'ID',
				'user_login',
			),
		),
		$type
	);
	$users  = get_users( $args );
	$select = '';
	foreach ( $users as $user ) {
		$selected = ( $chosen && ( $user->ID === $chosen ) ) ? ' selected="selected"' : '';
		$select  .= "<option value='$user->ID'$selected>" . esc_html( $user->user_login ) . "</option>\n";
	}

	return $select;
}

/**
 * Output a set of options.
 *
 * @param array  $choices Available options.
 * @param string $selected Selected option.
 * @param string $type Type of group.
 *
 * @return string
 */
function mcm_create_options( $choices, $selected, $type = 'select' ) {
	$return = '';
	if ( is_array( $choices ) ) {
		foreach ( $choices as $value ) {
			$v       = sanitize_title( $value );
			$display = stripslashes( $value );
			if ( 'select' === $type ) {
				$chosen  = ( $v === $selected ) ? ' selected="selected"' : '';
				$return .= "<option value='" . esc_attr( $v ) . "'$chosen>" . esc_html( stripslashes( $display ) ) . '</option>';
			} else {
				if ( is_array( $selected ) ) {
					$chosen = ( in_array( $v, $selected, true ) ) ? ' checked="checked"' : '';
				} else {
					$chosen = ( $v === $selected ) ? ' checked="checked"' : '';
				}
				$id      = sanitize_title( $v . '_' . $type );
				$return .= "<li><input type='checkbox' name='" . esc_attr( $type ) . '[]" value="' . esc_attr( $v ) . '" id="' . esc_attr( $id ) . "' $chosen /> <label for='" . esc_attr( $id ) . "'>" . esc_html( stripslashes( $display ) ) . '</label></li>';
			}
		}
	}

	return $return;
}

/**
 * Output a text area.
 *
 * @param array $args Field arguments.
 *
 * @return string
 */
function mcm_text_area( $args ) {
	global $post;
	$name        = $args[0];
	$args[1]     = stripslashes( $args[1] );
	$description = stripslashes( $args[2] );
	$label       = $args[1];
	// adjust data.
	$single = true;
	if ( isset( $args[4] ) && 'true' === $args[4] ) {
		$single = false;
	}
	$meta    = get_post_meta( $post->ID, $name, $single );
	$value   = ( $single ) ? $meta : '';
	$output  = "<div class='mcm_textarea mcm_field'>";
	$output .= '<p><label for="' . esc_attr( $name ) . '"><strong>' . esc_html( stripslashes( $label ) ) . '</strong></label><br /><textarea name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" class="widefat">' . esc_textarea( stripslashes( $value ) ) . '</textarea></p>';
	if ( is_array( $meta ) ) {
		$i       = 1;
		$output .= '<ul>';
		foreach ( $meta as $field ) {
			if ( '' !== $field ) {
				$output .= '<li><span class="mcm-delete"><input type="checkbox" id="del-' . esc_attr( $name ) . $i . '" name="mcm_delete[' . esc_attr( $name ) . '][]" value="' . esc_attr( $field ) . '" /> <label for="del-' . esc_attr( $name ) . $i . '">' . __( 'Delete', 'my-content-management' ) . '</span>' . esc_html( $field ) . '</label></li>';
				$i++;
			}
		}
		$output .= '</ul>';
	}
	if ( '' !== $description ) {
		$output .= '<em>' . esc_html( $description ) . '</em>';
	}
	$output .= '</div>';

	return $output;
}

/**
 * Generate a rich text editor area.
 *
 * @param array $args Field arguments.
 */
function mcm_rich_text_area( $args ) {
	global $post;
	// adjust data.
	$single      = true;
	$args[1]     = stripslashes( $args[1] );
	$description = stripslashes( $args[2] );
	if ( isset( $args[4] ) && 'true' === $args[4] ) {
		$single = false;
	}
	$args[2]     = get_post_meta( $post->ID, $args[0], $single );
	$meta        = $args[2];
	$id          = str_replace( array( '_', '-' ), '', $args[0] );
	$editor_args = apply_filters(
		'mcm_filter_editor_args',
		array(
			'textarea_name' => $args[0],
			'editor_css'    => '<style>.wp_themeSkin iframe { background: #fff; color: #222; }</style>',
			'editor_class'  => 'mcm_rich_text_editor',
		),
		$args
	);
	echo "<div class='mcm_rich_text_area'><label for='" . esc_attr( $id ) . "'><strong>" . esc_html( $args[1] ) . '</strong></label><br /><em>' . esc_html( stripslashes( $description ) ) . '</em>';
	wp_editor( $meta, $id, $editor_args );
	echo '</div>';
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int    $post_id Post ID.
 * @param object $post Post object.
 *
 * @return void
 */
function mcm_save_postdata( $post_id, $post ) {
	$parent_id = wp_is_post_revision( $post_id );

	if ( empty( $_POST ) || isset( $_POST['_inline_edit'] ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	global $mcm_fields;
	$fields = $mcm_fields;
	// verify this came from our screen and with proper authorization.
	// because save_post can be triggered at other times.
	if ( isset( $_POST['mcm_nonce_name'] ) ) {
		if ( ! wp_verify_nonce( $_POST['mcm_nonce_name'], plugin_basename( __FILE__ ) ) ) {
			return $post->ID;
		}
		// Is the user allowed to edit the post or page?
		if ( 'page' === $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_pages', $post->ID ) ) {
				return $post->ID;
			}
		} else {
			if ( ! current_user_can( 'edit_posts', $post->ID ) ) {
				return $post->ID;
			}
		}
		$these_fields = array();
		if ( isset( $_POST['mcm_fields'] ) ) {
			$these_fields = map_deep( $_POST['mcm_fields'], 'sanitize_text_field' );
			do_action( 'mcm_processing_fields_action', $_POST, $fields, $post->ID );
		} else {
			return;
		}
		foreach ( $fields as $set => $field ) {
			foreach ( $field as $key => $value ) {
				$custom_field_name       = $value[0];
				$custom_field_label      = $value[1];
				$custom_field_notes      = $value[2];
				$custom_field_type       = $value[3];
				$custom_field_repeatable = ( isset( $value[4] ) ) ? $value[4] : 'false';
				$repeatable              = ( isset( $custom_field_repeatable ) && 'true' === $custom_field_repeatable ) ? true : false;

				if ( in_array( $custom_field_name, $these_fields, true ) && in_array( $set, $_POST['mcm_fieldsets'], true ) ) {
					if ( isset( $_POST[ $custom_field_name ] ) && ! $repeatable ) {
						$this_value = mcm_sanitize( $custom_field_name, $custom_field_type );
						if ( $parent_id ) {
							$parent    = get_post( $parent_id );
							$test_meta = get_post_meta( $parent->ID, $custom_field_name, true );
							if ( false !== $test_meta ) {
								add_metadata( 'post', $post_id, $custom_field_name, $this_value );
							}
						} else {
							update_post_meta( $post->ID, $custom_field_name, $this_value );
						}
					}
					if ( isset( $_POST[ $custom_field_name ] ) && $repeatable ) {
						if ( $parent_id ) {
							add_metadata( 'post', $post_id, $custom_field_name, $this_value );
						} else {
							if ( '' !== $_POST[ $custom_field_name ] ) {
								$this_value = apply_filters( 'mcm_filter_saved_data', sanitize_textarea_field( $_POST[ $custom_field_name ] ), $custom_field_name, $custom_field_type );
								add_post_meta( $post->ID, $custom_field_name, $this_value );
							}
						}
					}
					if ( ! empty( $_FILES[ $custom_field_name ]['name'] ) ) {
						$file   = $_FILES[ $custom_field_name ];
						$upload = wp_handle_upload(
							$file,
							array(
								'test_form' => false,
							)
						);
						if ( ! isset( $upload['error'] ) && isset( $upload['file'] ) ) {
							$filetype   = wp_check_filetype( basename( $upload['file'] ), null );
							$title      = $file['name'];
							$ext        = strrchr( $title, '.' );
							$title      = ( false !== $ext ) ? substr( $title, 0, -strlen( $ext ) ) : $title;
							$attachment = array(
								'post_mime_type' => $filetype['type'],
								'post_title'     => sanitize_text_field( $title ),
								'post_content'   => '',
								'post_status'    => 'inherit',
								'post_parent'    => $post->ID,
							);
							$attach_id  = wp_insert_attachment( $attachment, $upload['file'] );
							$url        = wp_get_attachment_url( $attach_id );
							if ( ! $repeatable ) {
								if ( $parent_id ) {
									$parent    = get_post( $parent_id );
									$test_meta = get_post_meta( $parent->ID, $custom_field_name, true );
									if ( false !== $test_meta ) {
										add_metadata( 'post', $post_id, $custom_field_name, $url );
									}
								} else {
									update_post_meta( $post->ID, $custom_field_name, $url );
								}
							} else {
								if ( $parent_id ) {
									$parent    = get_post( $parent_id );
									$test_meta = get_post_meta( $parent->ID, $custom_field_name, true );
									if ( false !== $test_meta ) {
										add_metadata( 'post', $post_id, $custom_field_name, $url );
									}
								} else {
									add_post_meta( $post->ID, $custom_field_name, $url );
								}
							}
						}
					}
					if ( empty( $_FILES[ $custom_field_name ]['name'] ) && ! isset( $_POST[ $custom_field_name ] ) ) {
						if ( mcm_is_repeatable( $value ) && mcm_has_value( $post->ID, $custom_field_name ) ) {
							// do something here.
						} else {
							update_post_meta( $post->ID, $custom_field_name, '' );
						}
					}
				}
			}
		}
		if ( isset( $_POST['mcm_delete'] ) ) {
			$deletions = map_deep( $_POST['mcm_delete'], 'sanitize_textarea_field' );
			foreach ( $deletions as $data => $deletion ) {
				foreach ( $deletion as $delete ) {
					if ( '' !== $delete ) {
						delete_post_meta( $post->ID, $data, $delete );
					}
				}
			}
		}
	}
}
add_action( 'save_post', 'mcm_save_postdata', 1, 2 );

/**
 * Based on field type, sanitize saved data.
 *
 * @param string $name Custom field name in POST data.
 * @param string $type Custom field type.
 * @param array  $post (Optional) Array containing value to be sanitized as $post[ $name ].
 *
 * @return string
 */
function mcm_sanitize( $name, $type, $post = array() ) {
	if ( empty( $post ) ) {
		$value = $_POST[ $name ]; // values are sanitized below based on data types.
	} else {
		$value = $post[ $name ]; // values are sanitized below based on data types.
	}
	switch ( $type ) {
		case 'text':
		case 'select':
		case 'checkboxes':
		case 'checkbox':
		case 'upload':
		case 'chooser':
		case 'color':
		case 'date':
		case 'tel':
		case 'time':
			$value = sanitize_text_field( $value );
			break;
		case 'textarea': // Many uses of this seem to be for HTML, rather than text.
			$value = wp_kses_post( $value );
			break;
		case 'url':
			$value = sanitize_url( $value );
			break;
		case 'email':
			$value = sanitize_email( $value );
			break;
		case 'post-relation':
			$value = absint( $value );
			break;
		case 'user-relation':
			$value = absint( $value );
			break;
		case 'richtext':
			$value = wp_kses_post( $value );
			break;
		default:
			$value = sanitize_text_field( $value );
	}
	$return = apply_filters( 'mcm_filter_saved_data', $value, $custom_field_name, $custom_field_type, $post );

	return $return;
}

/**
 * Restore fields from post revision.
 *
 * @param int $post_id Post ID.
 * @param int $revision_id Revision ID.
 */
function mcm_restore_revision( $post_id, $revision_id ) {
	$post        = get_post( $post_id );
	$revision    = get_post( $revision_id );
	$meta_fields = get_metadata( 'post', $revision->ID );

	if ( is_array( $meta_fields ) ) {
		foreach ( $meta_fields as $this_field => $this_value ) {
			if ( ! empty( $this_value ) ) {
				delete_post_meta( $post_id, $this_field );
				foreach ( $this_value as $value ) {
					add_post_meta( $post_id, $this_field, $value );
				}
			} else {
				delete_post_meta( $post_id, $this_field );
			}
		}
	}
}
add_action( 'wp_restore_post_revision', 'mcm_restore_revision', 10, 2 );

/**
 * Add MCM Fields into revision set.
 *
 * @param array $fields Fields stored in revisions.
 *
 * @return array
 */
function mcm_revision_fields( $fields ) {
	global $mcm_fields;
	foreach ( $mcm_fields as $set => $field ) {
		foreach ( $field as $key => $value ) {
			// Cannot save array values into revisions.
			if ( 'checkboxes' === $value[3] ) {
				continue;
			}
			$custom_field_name            = $value[0];
			$custom_field_label           = $value[1];
			$fields[ $custom_field_name ] = $custom_field_label;
		}
	}

	return $fields;
}
add_filter( '_wp_post_revision_fields', 'mcm_revision_fields' );

/**
 * Test whether this field is a repeatable field.
 *
 * @param array $value Field arguments.
 *
 * @return boolean
 */
function mcm_is_repeatable( $value ) {
	if ( is_array( $value ) ) {
		if ( isset( $value[4] ) && 'true' === $value[4] ) {
			return true;
		}
	} else {
		$options    = get_option( 'mcm_options' );
		$mcm_fields = isset( $options['simplified'] ) ? $options['simplified'] : array();
		if ( is_array( $mcm_fields ) ) {
			foreach ( $mcm_fields as $set ) {
				if ( isset( $set['repetition'] ) && 'true' === $set['repetition'] ) {
					return true;
				}
			}
		}
	}

	return false;
}

/**
 * Get revision data for a My Content Management custom field.
 *
 * @param string $value Value of meta field. Unused.
 * @param string $field Custom field name.
 *
 * @return string
 */
function mcm_revision_field( $value, $field ) {
	global $revision;
	if ( is_array( $field ) ) {
		return;
	}

	return get_metadata( 'post', $revision->ID, $field, true );
}
add_filter( '_wp_post_revision_field_my_meta', 'mcm_revision_field', 10, 2 );

/**
 * Check whether a text field is rich text.
 *
 * @param array $value Field values.
 *
 * @return bool
 */
function mcm_is_richtext( $value ) {
	if ( is_string( $value ) ) { // if this isn't a custom field, ignore it.
		if ( 0 !== strpos( $value, '_' ) ) {
			return false;
		}
	}
	if ( is_array( $value ) ) {
		if ( isset( $value[3] ) && 'richtext' === $value[3] ) {
			return true;
		}
	} else {
		$options    = get_option( 'mcm_options' );
		$mcm_fields = isset( $options['simplified'] ) ? $options['simplified'] : array();
		if ( is_array( $mcm_fields ) ) {
			foreach ( $mcm_fields as $set ) {
				if ( isset( $set['type'] ) && 'richtext' === $set['type'] && isset( $set['key'] ) && $set['key'] === $value ) {
					return true;
				}
			}
		}
	}

	return false;
}

/**
 * Check for a saved value on a field.
 *
 * @param int    $post_ID Post ID.
 * @param string $key Meta key.
 *
 * @return bool
 */
function mcm_has_value( $post_ID, $key ) {
	$meta = get_post_meta( $post_ID, $key, true );
	if ( $meta ) {
		return true;
	}

	return false;
}

/**
 * Get a nonce.
 */
function mcm_echo_nonce() {
	// Use nonce for verification.
	echo sprintf(
		'<input type="hidden" name="%1$s" id="%1$s" value="%2$s" />',
		'mcm_nonce_name',
		wp_create_nonce( plugin_basename( __FILE__ ) )
	);
}

/**
 * Echo hidden fields.
 *
 * @param array $fields Fields.
 * @param int   $id Current post ID? Fieldset ID?.
 */
function mcm_echo_hidden( $fields, $id ) {
	// finish when I add hidden fields.
	echo '<input type="hidden" name="mcm_fieldsets[]" value="' . $id . '" />';
	if ( is_array( $fields ) ) {
		foreach ( $fields as $field ) {
			$new_fields[] = $field[0];
		}
		$value = apply_filters( 'mcm_hidden_fields', $new_fields, $fields );
		foreach ( $new_fields as $hidden ) {
			echo '<input type="hidden" name="mcm_fields[]" value="' . esc_attr( $hidden ) . '" />';
		}
	}
}

// Default settings for post types.
$d_mcm_args = array(
	'public'              => true,
	'publicly_queryable'  => true,
	'exclude_from_search' => false,
	'show_ui'             => true,
	'show_in_menu'        => true,
	'show_ui'             => true,
	'menu_icon'           => null,
	'hierarchical'        => true,
	'show_in_rest'        => false,
	'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
	'slug'                => '',
);

// Default post types.
$default_mcm_types = array(
	'mcm_faq'          => array(
		__( 'faq', 'my-content-management' ),
		__( 'faqs', 'my-content-management' ),
		__( 'FAQ', 'my-content-management' ),
		__( 'FAQs', 'my-content-management' ),
		$d_mcm_args,
	),
	'mcm_people'       => array(
		__( 'person', 'my-content-management' ),
		__( 'people', 'my-content-management' ),
		__( 'Person', 'my-content-management' ),
		__( 'People', 'my-content-management' ),
		$d_mcm_args,
	),
	'mcm_testimonials' => array(
		__( 'testimonial', 'my-content-management' ),
		__( 'testimonials', 'my-content-management' ),
		__( 'Testimonial', 'my-content-management' ),
		__( 'Testimonials', 'my-content-management' ),
		$d_mcm_args,
	),
	'mcm_locations'    => array(
		__( 'location', 'my-content-management' ),
		__( 'locations', 'my-content-management' ),
		__( 'Location', 'my-content-management' ),
		__( 'Locations', 'my-content-management' ),
		$d_mcm_args,
	),
	'mcm_quotes'       => array(
		__( 'quote', 'my-content-management' ),
		__( 'quotes', 'my-content-management' ),
		__( 'Quote', 'my-content-management' ),
		__( 'Quotes', 'my-content-management' ),
		$d_mcm_args,
	),
	'mcm_glossary'     => array(
		__( 'glossary term', 'my-content-management' ),
		__( 'glossary terms', 'my-content-management' ),
		__( 'Glossary Term', 'my-content-management' ),
		__( 'Glossary Terms', 'my-content-management' ),
		$d_mcm_args,
	),
	'mcm_portfolio'    => array(
		__( 'portfolio item', 'my-content-management' ),
		__( 'portfolio items', 'my-content-management' ),
		__( 'Portfolio Item', 'my-content-management' ),
		__( 'Portfolio Items', 'my-content-management' ),
		$d_mcm_args,
	),
	'mcm_resources'    => array(
		__( 'resource', 'my-content-management' ),
		__( 'resources', 'my-content-management' ),
		__( 'Resource', 'my-content-management' ),
		__( 'Resources', 'my-content-management' ),
		$d_mcm_args,
	),
);

// @fields multidimensional array: array( 'Box set'=> array( array( '_name','label','type') ) ).
// @post_type string post_type.
// @location string side/normal/advanced.
// add custom fields to the custom post types.
$default_mcm_fields = array(
	__( 'Personal Information', 'my-content-management' ) => array(
		array( '_title', __( 'Title', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_subtitle', __( 'Subtitle', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_business', __( 'Business', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_phone', __( 'Phone Number', 'my-content-management' ), '', 'tel', 'true' ),
		array( '_email', __( 'E-mail', 'my-content-management' ), '', 'email' ),
	),
	__( 'Location Info', 'my-content-management' )        => array(
		array( '_street', __( 'Street Address', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_city', __( 'City', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_neighborhood', __( 'Neighborhood', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_state', __( 'State', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_country', __( 'Country', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_postalcode', __( 'Postal Code', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_phone', __( 'Phone', 'my-content-management' ), '', 'tel' ),
		array( '_fax', __( 'Fax', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_business', __( 'Business Name', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_email', __( 'Contact Email', 'my-content-management' ), '', 'email' ),
	),
	__( 'Quotation Info', 'my-content-management' )       => array(
		array( '_url', __( 'URL', 'my-content-management' ), '', 'url' ),
		array( '_title', __( 'Title', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_location', __( 'Location', 'my-content-management' ), '', 'mcm_text_field' ),
	),
	__( 'Testimonial Info', 'my-content-management' )     => array(
		array( '_url', __( 'URL', 'my-content-management' ), '', 'url' ),
		array( '_title', __( 'Title', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_location', __( 'Location', 'my-content-management' ), '', 'mcm_text_field' ),
	),
	__( 'Portfolio Info', 'my-content-management' )       => array(
		array( '_medium', __( 'Medium', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_width', __( 'Width', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_height', __( 'Height', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_depth', __( 'Depth', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_price', __( 'Price', 'my-content-management' ), '', 'mcm_text_field' ),
		array( '_year', __( 'Year', 'my-content-management' ), '', 'mcm_text_field' ),
	),
	__( 'Resource Info', 'my-content-management' )        => array(
		array( '_authors', __( 'Additional Authors', 'my-content-management' ), '', 'mcm_text_field', 'true' ),
		array( '_licensing', __( 'License Terms', 'my-content-management' ), '', 'mcm_text_area' ),
		array( '_show', __( 'Show on', 'my-content-management' ), 'This is a label for advanced use in themes', 'mcm_text_field' ),
	),
);

// Default extra field sets.
$default_mcm_extras = array(
	__( 'Personal Information', 'my-content-management' ) => array( 'mcm_people', 'side' ),
	__( 'Location Info', 'my-content-management' )        => array( 'mcm_locations', 'side' ),
	__( 'Testimonial Info', 'my-content-management' )     => array( 'mcm_testimonials', 'side' ),
	__( 'Quotation Info', 'my-content-management' )       => array( 'mcm_quotes', 'side' ),
	__( 'Portfolio Info', 'my-content-management' )       => array( 'mcm_portfolio', 'side' ),
	__( 'Resource Info', 'my-content-management' )        => array( 'mcm_resources', 'side' ),
);
