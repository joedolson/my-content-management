<?php
/**
 * My Content Management, Custom Post Type manager for WordPress
 *
 * @package     MyContentManagement
 * @author      Joe Dolson
 * @copyright   2011-2025 Joe Dolson
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: My Content Management
 * Plugin URI:  http://www.joedolson.com/my-content-management/
 * Description: Creates a set of common custom post types for extended content management: FAQ, Testimonials, people lists, term lists, etc.
 * Author:      Joseph C Dolson
 * Author URI:  http://www.joedolson.com
 * Text Domain: my-content-management
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/license/gpl-2.0.txt
 * Domain Path: lang
 * Update URI:  https://www.joedolson.com/my-content-management/
 * Version:     1.7.11
 */

/*
	Copyright 2011-2025  Joe Dolson (email : joe@joedolson.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mcm_version = '1.7.11';
/**
 * Enable internationalisation
 */
function mcm_load_textdomain() {
	load_plugin_textdomain( 'my-content-management', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}
add_action( 'init', 'mcm_load_textdomain' );

include( dirname( __FILE__ ) . '/includes/kses.php' );
include( dirname( __FILE__ ) . '/mcm-custom-posts.php' );
include( dirname( __FILE__ ) . '/mcm-view-custom-posts.php' );
include( dirname( __FILE__ ) . '/mcm-widgets.php' );

// Shortcodes.
add_shortcode( 'my_content', 'mcm_show_posts' );
add_shortcode( 'custom_search', 'mcm_search_custom' );
add_shortcode( 'my_archive', 'mcm_show_archive' );
add_shortcode( 'email', 'mcm_munger' );
add_shortcode( 'my_terms', 'mcm_terms' );

// Filters.
add_filter( 'pre_get_posts', 'mcm_searchfilter' );
add_filter( 'post_updated_messages', 'mcm_posttypes_messages' );

// Actions.
add_action( 'init', 'mcm_taxonomies', 0 );
add_action( 'init', 'mcm_posttypes' );
add_action( 'admin_menu', 'mcm_add_custom_boxes' );

add_action( 'widgets_init', 'mcm_register_widgets' );
/**
 * Register My Content Management Widgets
 */
function mcm_register_widgets() {
	register_widget( 'Mcm_Search_Widget' );
	register_widget( 'Mcm_Posts_Widget' );
	register_widget( 'Mcm_Meta_Widget' );
}

if ( ! get_option( 'mcm_version' ) ) {
	mcm_install_plugin();
}
if ( version_compare( get_option( 'mcm_version' ), $mcm_version, '<' ) ) {
	mcm_upgrade_plugin();
}

$mcm_options   = get_option( 'mcm_options' );
$mcm_enabled   = $mcm_options['enabled'];
$mcm_templates = $mcm_options['templates'];
$mcm_types     = $mcm_options['types'];
$mcm_fields    = $mcm_options['fields'];
$mcm_extras    = $mcm_options['extras'];

/**
 * Enqueue admin scripts & styles used in post meta fields.
 */
function mcm_enqueue_admin_scripts() {
	$version = get_option( 'mcm_version' );
	$version = ( SCRIPT_DEBUG ) ? $version . '-' . wp_rand( 10000, 99999 ) : $version;
	$screen  = get_current_screen();
	if ( 'post' === $screen->base ) {
		if ( function_exists( 'wp_enqueue_media' ) && ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
		wp_enqueue_style( 'mcm-posts', plugins_url( 'css/mcm-post.css', __FILE__ ), array(), $version );
		wp_enqueue_script( 'mcm-admin-script', plugins_url( 'js/uploader.js', __FILE__ ), array( 'jquery' ), $version );
		wp_localize_script(
			'mcm-admin-script',
			'mcm_images',
			array(
				'thumbsize' => get_option( 'thumbnail_size_h' ),
			)
		);
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'mcm.autocomplete', plugins_url( 'js/autocomplete.js', __FILE__ ), array( 'jquery', 'jquery-ui-autocomplete' ), $version );
		wp_localize_script(
			'mcm.autocomplete',
			'mcm_ac',
			array(
				'post_action' => 'mcm_post_lookup',
				'user_action' => 'mcm_user_lookup',
				'i18n'        => array(
					'selected' => esc_html__( 'Selected', 'my-content-management' ),
				),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'mcm_enqueue_admin_scripts' );

/**
 * AJAX post lookup.
 */
function mcm_post_lookup() {
	if ( isset( $_REQUEST['term'] ) ) {
		$args = array(
			's' => sanitize_text_field( $_REQUEST['term'] ),
		);
		if ( isset( $_REQUEST['post_type'] ) && '' !== $_REQUEST['post_type'] ) {
			$args['post_type'] = sanitize_text_field( $_REQUEST['post_type'] );
		}
		$posts       = get_posts( apply_filters( 'mcm_filter_posts_autocomplete', $args ) );
		$suggestions = array();
		foreach ( $posts as $post ) {
			setup_postdata( $post );
			$suggestion          = array();
			$suggestion['value'] = esc_html( $post->post_title );
			$suggestion['id']    = $post->ID;
			$suggestions[]       = $suggestion;
		}

		echo esc_html( $_GET['callback'] ) . '(' . json_encode( $suggestions ) . ')';
		exit;
	}
}
add_action( 'wp_ajax_mcm_post_lookup', 'mcm_post_lookup' );
add_action( 'wp_ajax_mcm_user_lookup', 'mcm_user_lookup' );

/**
 * AJAX user lookup
 */
function mcm_user_lookup() {
	if ( isset( $_REQUEST['term'] ) ) {
		$args = array();
		if ( isset( $_REQUEST['role'] ) && '' !== $_REQUEST['role'] ) {
			$args['role'] = sanitize_text_field( $_REQUEST['role'] );
		}
		$args        = apply_filters( 'mcm_filter_user_autocomplete', $args );
		$users       = get_users( $args );
		$suggestions = array();
		foreach ( $users as $user ) {
			$suggestion          = array();
			$suggestion['value'] = esc_html( $user->user_login );
			$suggestion['id']    = $user->ID;
			$suggestions[]       = $suggestion;
		};

		echo esc_html( $_GET['callback'] ) . '(' . json_encode( $suggestions ) . ')';
		exit;
	}
}

/**
 * Display posts shortcode.
 *
 * @param array $atts Shortcode attributes.
 *
 * @return string
 */
function mcm_show_posts( $atts ) {
	$args = shortcode_atts(
		array(
			'type'           => 'page',
			'display'        => 'excerpt',
			'taxonomy'       => 'all',
			'term'           => '',
			'operator'       => 'IN',
			'count'          => -1,
			'order'          => 'menu_order',
			'direction'      => 'DESC',
			'meta_key'       => '',
			'template'       => '',
			'year'           => '',
			'month'          => '',
			'week'           => '',
			'day'            => '',
			'cache'          => false,
			'offset'         => false,
			'id'             => false,
			'custom_wrapper' => 'div',
			'custom'         => false,
		),
		$atts,
		'my_content'
	);
	if ( isset( $_GET['mcm'] ) && isset( $_GET['mcm_value'] ) ) {
		$key          = sanitize_key( $_GET['mcm'] );
		$args[ $key ] = sanitize_text_field( $_GET['mcm_value'] );
	}

	return mcm_get_show_posts( $args );
}

/**
 * Add enclosure type to forms when uploading required.
 */
function mcm_post_edit_form_tag() {
	echo ' enctype="multipart/form-data"';
}
add_action( 'post_edit_form_tag', 'mcm_post_edit_form_tag' );

/**
 * Output archive content.
 *
 * @param array $atts Shortcode attributes.
 *
 * @return string
 */
function mcm_show_archive( $atts ) {
	// TODO: remove extract.
	extract( // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		shortcode_atts(
			array(
				'type'           => false,
				'display'        => 'list',
				'taxonomy'       => false,
				'count'          => -1,
				'order'          => 'menu_order',
				'direction'      => 'DESC',
				'meta_key'       => '',
				'exclude'        => '',
				'include'        => '',
				'template'       => '',
				'offset'         => '',
				'cache'          => false,
				'show_links'     => false,
				'custom_wrapper' => 'div',
				'custom'         => false,
				'year'           => '',
				'month'          => '',
				'week'           => '',
				'day'            => '',
			),
			$atts,
			'my_archive'
		)
	);
	if ( ! $type || ! $taxonomy ) {
		return;
	}
	$args    = apply_filters( 'mcm_archive_taxonomies', array(), $atts );
	$terms   = get_terms( $taxonomy, $args );
	$output  = '';
	$linker  = "<ul class='archive-links'>";
	$exclude = explode( ',', $exclude );
	$include = explode( ',', $include );
	if ( is_array( $terms ) ) {
		foreach ( $terms as $term ) {
			$taxo      = $term->name;
			$tax       = $term->slug;
			$tax_class = sanitize_title( $tax );
			if ( ( ! empty( $exclude ) && '' !== $exclude[0] && ! in_array( $tax, $exclude, true ) ) || ( ! empty( $include ) && '' !== $include[0] && in_array( $tax, $include, true ) ) || '' === $exclude[0] && '' === $include[0] ) {
				$linker .= "<li><a href='#$tax_class'>$taxo</a></li>";
				$output .= "\n<div class='archive-group'>";
				$output .= "<h2 class='$tax_class' id='$tax_class'>$taxo</h2>";
				$args    = array(
					'type'           => $type,
					'display'        => $display,
					'taxonomy'       => $taxonomy,
					'term'           => $tax,
					'count'          => $count,
					'order'          => $order,
					'direction'      => $direction,
					'meta_key'       => $meta_key,
					'template'       => $template,
					'cache'          => $cache,
					'offset'         => $offset,
					'id'             => false,
					'custom_wrapper' => $custom_wrapper,
					'custom'         => $custom,
					'operator'       => 'IN',
					'year'           => $year,
					'month'          => $month,
					'week'           => $week,
					'day'            => $day,
					'post_filter'    => false,
				);
				$output .= mcm_get_show_posts( $args );
				$output .= "</div>\n";
			}
		}
		$linker .= '</ul>';
	}
	if ( ! $show_links ) {
		$linker = '';
	} else {
		$linker = $linker;
	}

	return $linker . $output;
}

/**
 * Convert date information to timestamp for storage.
 *
 * @param string $data Saved data.
 * @param array  $field Field data.
 * @param string $type Field type.
 *
 * @return string
 */
function mcm_transform_date_data( $data, $field, $type ) {
	if ( 'date' !== $type ) {
		return $data;
	} else {
		$data = strtotime( $data );
	}

	return $data;
}
add_filter( 'mcm_filter_saved_data', 'mcm_transform_date_data', 10, 3 );

/**
 * Convert saved timestamp into a date.
 *
 * @param array  $data Field data.
 * @param string $key Post meta field.
 *
 * @return string
 */
function mcm_reverse_date_data( $data, $key ) {
	if ( isset( $data['type'] ) && 'date' === $data['type'] && is_numeric( $data[0] ) ) {
		$value   = date_i18n( get_option( 'date_format', 'Y-m-d' ), $data[0] );
		$data[0] = $value;
	}

	return $data;
}
add_filter( 'mcm_filter_output_data', 'mcm_reverse_date_data', 10, 2 );

/**
 * Check what template is currently in use.
 *
 * @param string $template Template name.
 *
 * @return string
 */
function mcm_set_current_template( $template ) {
	$GLOBALS['current_theme_template'] = basename( $template );

	return $template;
}
add_action( 'template_include', 'mcm_set_current_template', 1000 );

/**
 * Get the current template.
 *
 * @return string
 */
function mcm_get_current_template() {
	if ( ! isset( $GLOBALS['current_theme_template'] ) ) {
		return false;
	}

	return $GLOBALS['current_theme_template'];
}

/**
 * Replace post content with MCM template.
 *
 * @param string         $content Post content.
 * @param mixed int|bool $id Post ID.
 *
 * @return string
 */
function mcm_replace_content( $content, $id = false ) {
	$template = mcm_get_current_template();
	if ( ! $template ) {
		return;
	}

	if ( ! is_main_query() && ! $id ) {
		return $content;
	}
	$post_type = get_post_type();
	if ( ! $post_type ) {
		return $content;
	}
	$mcm_options = get_option( 'mcm_options' );
	// If this is a custom template for this post type, don't render replacement.
	if ( false !== strpos( $template, $post_type ) ) {
		return $content;
	}
	$enabled = $mcm_options['enabled'];
	if ( $enabled && is_singular( $enabled ) ) {
		$id     = get_the_ID();
		$custom = mcm_get_single_post( $post_type, $id );

		return $custom;
	} else {

		return $content;
	}
}
add_filter( 'the_content', 'mcm_replace_content', 10, 2 );

/**
 * Custom search shortcode.
 *
 * @param array $atts Shortcode attributes.
 *
 * @return string
 */
function mcm_search_custom( $atts ) {
	// TODO: remove extract.
	extract( // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		shortcode_atts(
			array(
				'type' => 'page',
			),
			$atts,
			'custom_search'
		)
	);

	return mcm_search_form( $type );
}

/**
 * Email address munging shortcode.
 *
 * @param array $atts Shortcode attributes.
 *
 * @return string
 */
function mcm_munger( $atts ) {
	// TODO: remove extract.
	extract( // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		shortcode_atts(
			array(
				'address' => '',
			),
			$atts,
			'email'
		)
	);

	return mcm_munge( $address );
}

/**
 * Shortcode to list terms.
 *
 * @param array  $atts Shortcode attributes.
 * @param string $content Shortcode contained content.
 *
 * @return string
 */
function mcm_terms( $atts, $content ) {
	// TODO: remove extract.
	extract( // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		shortcode_atts(
			array(
				'taxonomy'   => '',
				'hide_empty' => 'false',
				'show_count' => 'false',
			),
			$atts,
			'my_terms'
		)
	);

	return mcm_list_terms( $taxonomy, $hide_empty, $show_count );
}

/**
 * List out terms in a given taxonomy.
 *
 * @param string $taxonomy Taxonomy key.
 * @param string $hide_empty (string boolean value) True to hide empty terms.
 * @param bool   $show_count True to display number of terms.
 *
 * @return string.
 */
function mcm_list_terms( $taxonomy, $hide_empty, $show_count ) {
	$hide_empty = ( 'true' === $hide_empty ) ? true : false;
	$args       = array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => $hide_empty,
	);
	$terms      = get_terms( $taxonomy, $args );
	$count      = count( $terms );
	$i          = 0;
	$term_list  = '';
	if ( $count > 0 ) {
		$term_list = "<ul class='mcm-term-archive $taxonomy'>";
		foreach ( $terms as $term ) {
			$i++;
			$count      = ( 'true' === $show_count ) ? " <span class='term-count mcm-$term->slug'>($term->count)</span>" : '';
			$term_list .= '<li><a href="' . get_term_link( $term ) . '">' . esc_html( $term->name ) . "</a>$count</li>";
		}
		$term_list .= '</ul>';
	}

	return $term_list;
}

/**
 * Force theme support for thumbnails.
 *
 * Not having post thumbnails enabled can cause fatal errors when thumbnail is requested by info query.
 */
function mcm_grant_support() {
	add_theme_support( 'post-thumbnails' );
}
add_action( 'after_setup_theme', 'mcm_grant_support' );

/**
 * Installation function.
 */
function mcm_install_plugin() {
	$types     = mcm_globals( 'mcm_types' );
	$templates = array();
	if ( is_array( $types ) ) {
		foreach ( $types as $key => $value ) {
			$templates[ $key ]['full']                       = '{content}';
			$templates[ $key ]['excerpt']                    = '<h3>{title}</h3>
<div class="article">{excerpt}
	<p class="read-more">{link_title}</p>
</div>';
			$templates[ $key ]['list']                       = '{link_title}';
			$templates[ $key ]['wrapper']['item']['full']    = '';
			$templates[ $key ]['wrapper']['item']['excerpt'] = 'div';
			$templates[ $key ]['wrapper']['item']['list']    = 'li';
			$templates[ $key ]['wrapper']['list']['full']    = '';
			$templates[ $key ]['wrapper']['list']['excerpt'] = 'div';
			$templates[ $key ]['wrapper']['list']['list']    = 'ul';
		}
	}
	$options = array(
		'enabled'   => array(),
		'templates' => $templates,
		'types'     => mcm_globals( 'mcm_types' ),
		'fields'    => mcm_globals( 'mcm_fields' ),
		'extras'    => mcm_globals( 'mcm_extras' ),
	);
	if ( '' === get_option( 'mcm_options', '' ) ) { // this should protect against deleting changes.
		add_option( 'mcm_options', $options );
	}
}

/**
 * Run upgrades.
 */
function mcm_upgrade_plugin() {
	global $mcm_version;
	$from = get_option( 'mcm_version' );
	if ( $mcm_version === $from ) {
		return;
	}

	update_option( 'mcm_version', $mcm_version );
}

/**
 * Add admin scripts for content management.
 */
function mcm_add_scripts() {
	wp_register_script( 'addfields', plugins_url( 'js/jquery.addfields.js', __FILE__ ), array( 'jquery' ) );
	wp_register_script( 'mcm.tabs', plugins_url( 'js/tabs.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_script( 'addfields' );
	wp_localize_script(
		'addfields',
		'mcmi18n',
		array(
			'mcmWarning' => esc_html__( 'Fieldset titles do not support quote characters.', 'my-content-management' ),
			'mcmOK'      => esc_html__( 'Your Fieldset title is OK!', 'my-content-management' ),
		)
	);
	wp_enqueue_script( 'mcm.tabs' );
	global $mcm_enabled;
	$keys = $mcm_enabled;
	if ( is_array( $keys ) && ! empty( $keys ) ) {
		$mcm_selected = ( is_string( $keys[0] ) ) ? $keys[0] . '-container' : 'undefined-container';
	} else {
		$mcm_selected = 'disabled';
	}
	wp_localize_script(
		'mcm.tabs',
		'firstItem',
		array(
			'selected' => $mcm_selected,
		),
	);
}

/**
 * Settings page
 */
function mcm_settings_page() {
	global $mcm_enabled;
	$enabled = $mcm_enabled;
	$enabled = ( isset( $_POST['mcm_enabler'] ) && isset( $_POST['mcm_posttypes'] ) ) ? map_deep( $_POST['mcm_posttypes'], 'sanitize_textarea_field' ) : $enabled;
	?>
	<div class='wrap mcm-settings'>
		<h1><?php esc_html_e( 'My Content Management', 'my-content-management' ); ?></h1>
		<div class="postbox-container" style="width: 70%">
			<div class="metabox-holder">
				<div class="mcm-settings ui-sortable meta-box-sortables">
					<?php
					if ( isset( $_GET['mcm_delete'] ) ) {
						$message = mcm_delete_type( sanitize_key( $_GET['mcm_delete'] ) );
						echo wp_kses_post( $message );
					}
					if ( isset( $_GET['mcm_edit'] ) || isset( $_GET['mcm_add'] ) ) {
						?>
					<div class="postbox">
						<h2 class='hndle'><?php esc_html_e( 'Edit Custom Post Type', 'my-content-management' ); ?></h2>
						<div class="inside">
						<?php mcm_updater(); ?>
						</div>
					</div>
						<?php
					}
					?>
					<div class="postbox">
						<h2 class='hndle'><?php esc_html_e( 'Enable Custom Post Types', 'my-content-management' ); ?></h2>
						<div class="inside">
						<form method='post' action='<?php echo esc_url( admin_url( 'options-general.php?page=mcm_settings' ) ); ?>'>
							<div><input type='hidden' name='_wpnonce' value='<?php echo esc_attr( wp_create_nonce( 'my-content-management-nonce' ) ); ?>' /></div>
							<div>
							<?php mcm_enabler(); ?>
							<p>
								<input type='submit' value='<?php esc_attr_e( 'Update Enabled Post Types', 'my-content-management' ); ?>' name='mcm_enabler' class='button-primary' /> <a class="button-secondary" href="<?php echo esc_url( admin_url( 'options-general.php?page=mcm_settings&mcm_add=new' ) ); ?>"><?php esc_html_e( 'Add new post type', 'my-content-management' ); ?></a>
							</p>
							</div>
						</form>
						</div>
					</div>
					<?php
					if ( ! empty( $enabled ) ) {
						mcm_template_setter();
					}
					?>
					<div class="mcm-template-guide postbox" id="get-support">
						<h2 class='hndle'><?php esc_html_e( 'Template Tags', 'my-content-management' ); ?></h2>
						<div class="inside">
							<dl>
								<dt><code>{id}</code></dt>
								<dd><?php esc_html_e( 'Post ID', 'my-content-management' ); ?></dd>

								<dt><code>{slug}</code></dt>
								<dd><?php esc_html_e( 'Post Slug', 'my-content-management' ); ?></dd>

								<dt><code>{excerpt}</code></dt>
								<dd><?php esc_html_e( 'Post excerpt (with auto paragraphs)', 'my-content-management' ); ?></dd>

								<dt><code>{excerpt_raw}</code></dt>
								<dd><?php esc_html_e( 'Post excerpt (unmodified)', 'my-content-management' ); ?></dd>

								<dt><code>{content}</code></dt>
								<dd><?php esc_html_e( 'Post content (with auto paragraphs and shortcodes processed)', 'my-content-management' ); ?></dd>

								<dt><code>{content_raw}</code></dt>
								<dd><?php esc_html_e( 'Post content (unmodified)', 'my-content-management' ); ?></dd>

								<dt><code>{full}</code></dt>
								<dd><?php esc_html_e( 'Featured image at original size.', 'my-content-management' ); ?></dd>

								<?php
								$sizes = get_intermediate_image_sizes();
								foreach ( $sizes as $size ) {
									// Translators: Image size name.
									echo '<dt><code>{' . esc_html( $size ) . '}</code></dt><dd>' . sprintf( esc_html__( 'Featured image at %s size', 'my-content-management' ), esc_html( $size ) ) . '</dd>';
								}
								?>

								<dt><code>{permalink}</code></dt>
								<dd><?php esc_html_e( 'Permalink URL for post', 'my-content-management' ); ?></dd>

								<dt><code>{link_title}</code></dt>
								<dd><?php esc_html_e( 'Post title linked to permalink URL', 'my-content-management' ); ?></dd>

								<dt><code>{title}</code></dt>
								<dd><?php esc_html_e( 'Post title', 'my-content-management' ); ?></dd>

								<dt><code>{shortlink}</code></dt>
								<dd><?php esc_html_e( 'Post shortlink', 'my-content-management' ); ?></dd>

								<dt><code>{modified}</code></dt>
								<dd><?php esc_html_e( 'Post last modified date', 'my-content-management' ); ?></dd>

								<dt><code>{date}</code></dt>
								<dd><?php esc_html_e( 'Post publication date', 'my-content-management' ); ?></dd>

								<dt><code>{author}</code></dt>
								<dd><?php esc_html_e( 'Post author display name', 'my-content-management' ); ?></dd>

								<dt><code>{terms}</code></dt>
								<dd><?php esc_html_e( 'List of taxonomy terms associated with post.', 'my-content-management' ); ?></dd>

								<dt><code>{edit_link}</code></dt>
								<dd><?php esc_html_e( 'When logged in, display link to edit the current post.', 'my-content-management' ); ?></dd>
							</dl>
							<p>
							<?php
								esc_html_e( 'Any custom field can also be referenced via shortcode, using the same pattern with the name of the custom field: <code>{custom_field_name}</code>', 'my-content-management' );
							?>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php mcm_support_column(); ?>
	</div>
	<?php
}

/**
 * Enable a post type.
 */
function mcm_enabler() {
	if ( isset( $_POST['mcm_enabler'] ) ) {
		$nonce = $_REQUEST['_wpnonce'];
		if ( ! wp_verify_nonce( $nonce, 'my-content-management-nonce' ) ) {
			wp_die( 'My Content Management: Security check failed' );
		}
		$enable            = isset( $_POST['mcm_posttypes'] ) ? map_deep( $_POST['mcm_posttypes'], 'sanitize_textarea_field' ) : array();
		$option            = get_option( 'mcm_options' );
		$option['enabled'] = $enable;
		update_option( 'mcm_options', $option );
		flush_rewrite_rules();
		echo "<div class='updated fade'><p>" . esc_html__( 'Enabled post types updated', 'my-content-management' ) . '</p></div>';
	}
	$option  = get_option( 'mcm_options' );
	$enabled = $option['enabled'];
	$types   = $option['types'];
	$checked = '';
	echo '<fieldset aria-labelledby="available-post-types"><h3 id="available-post-types">' . esc_html__( 'Available Post Types', 'my-content-management' ) . "</h3><ul class='mcm_posttypes'>"
	if ( is_array( $types ) ) {
		foreach ( $types as $key => $value ) {
			if ( $key && ! is_int( $key ) ) {
				if ( is_array( $enabled ) ) {
					if ( in_array( $key, $enabled, true ) ) {
						$checked = ' checked="checked"';
					} else {
						$checked = '';
					}
				}
				echo "<li><input type='checkbox' value='" . esc_attr( $key ) . "' name='mcm_posttypes[]' id='mcm_" . esc_attr( $key ) . "'$checked /><label for='mcm_" . esc_attr( $key ) . "'>" . esc_html( $value[3] ) . ' (<code>' . esc_html( $key ) . "</code>)</label> <a href='" . esc_url( admin_url( "options-general.php?page=mcm_settings&mcm_edit=$key" ) ) . "'>" . esc_html__( 'Edit', 'my-content-management' ) . " '" . esc_html( $value[3] ) . "'</a> &bull; <a href='" . esc_url( admin_url( "options-general.php?page=mcm_settings&mcm_delete=$key" ) ) . "'>" . esc_html__( 'Delete', 'my-content-management' ) . "  '" . esc_html( $value[3] ) . "'</a></li>\n";
			}
		}
	}
	echo '</ul></fieldset>';
}

/**
 * Save update to post type settings.
 */
function mcm_save_updates() {
	if ( isset( $_POST['mcm_updater'] ) ) {
		$nonce = $_REQUEST['_wpnonce'];
		if ( ! wp_verify_nonce( $nonce, 'my-content-management-nonce' ) ) {
			wp_die( 'My Content Management: Security check failed' );
		}
		if ( ! isset( $_POST['mcm_new'] ) ) {
			$type     = sanitize_text_field( $_POST['mcm_type'] );
			$option   = get_option( 'mcm_options' );
			$ns       = map_deep( $_POST[ $type ], 'sanitize_text_field' );
			$supports = ( empty( $ns['supports'] ) ) ? array() : $ns['supports'];
			$new      = array(
				$ns['pt1'],
				$ns['pt2'],
				$ns['pt3'],
				$ns['pt4'],
				array(
					'public'              => ( isset( $ns['public'] ) && 1 === (int) $ns['public'] ) ? true : false,
					'publicly_queryable'  => ( isset( $ns['publicly_queryable'] ) && 1 === (int) $ns['publicly_queryable'] ) ? true : false,
					'exclude_from_search' => ( isset( $ns['exclude_from_search'] ) && 1 === (int) $ns['exclude_from_search'] ) ? true : false,
					'show_in_menu'        => ( isset( $ns['show_in_menu'] ) && 1 === (int) $ns['show_in_menu'] ) ? true : false,
					'show_ui'             => ( isset( $ns['show_ui'] ) && 1 === (int) $ns['show_ui'] ) ? true : false,
					'hierarchical'        => ( isset( $ns['hierarchical'] ) && 1 === (int) $ns['hierarchical'] ) ? true : false,
					'show_in_rest'        => ( isset( $ns['show_in_rest'] ) && 1 === (int) $ns['show_in_rest'] ) ? true : false,
					'menu_icon'           => ( ! isset( $ns['menu_icon'] ) || '' === $ns['menu_icon'] ) ? null : $ns['menu_icon'],
					'supports'            => $supports,
					'slug'                => ( isset( $ns['slug'] ) ) ? sanitize_key( $ns['slug'] ) : '',
				),
			);

			$option['types'][ $type ] = $new;
			update_option( 'mcm_options', $option );
			set_transient( 'mcm_update_notice', __( 'Post type settings modified.', 'my-content-management' ), 10 );
		} else {
			$option = get_option( 'mcm_options' );
			$ns     = map_deep( $_POST['new'], 'sanitize_textarea_field' );
			$type   = substr( 'mcm_' . sanitize_title( $ns['pt1'] ), 0, 20 );
			$new    = array(
				$ns['pt1'],
				$ns['pt2'],
				$ns['pt3'],
				$ns['pt4'],
				array(
					'public'              => ( isset( $ns['public'] ) && 1 === (int) $ns['public'] ) ? true : false,
					'publicly_queryable'  => ( isset( $ns['publicly_queryable'] ) && 1 === (int) $ns['publicly_queryable'] ) ? true : false,
					'exclude_from_search' => ( isset( $ns['exclude_from_search'] ) && 1 === (int) $ns['exclude_from_search'] ) ? true : false,
					'show_in_menu'        => ( isset( $ns['show_in_menu'] ) && 1 === (int) $ns['show_in_menu'] ) ? true : false,
					'show_ui'             => ( isset( $ns['show_ui'] ) && 1 === (int) $ns['show_ui'] ) ? true : false,
					'hierarchical'        => ( isset( $ns['hierarchical'] ) && 1 === (int) $ns['hierarchical'] ) ? true : false,
					'show_in_rest'        => ( isset( $ns['show_in_rest'] ) && 1 === (int) $ns['show_in_rest'] ) ? true : false,
					'menu_icon'           => ( ! isset( $ns['menu_icon'] ) || '' === $ns['menu_icon'] ) ? null : $ns['menu_icon'],
					'supports'            => $ns['supports'],
					'slug'                => $ns['slug'],
				),
			);

			$option['types'][ $type ] = $new;
			update_option( 'mcm_options', $option );
			set_transient( 'mcm_update_notice', __( 'Added new custom post type.', 'my-content-management' ), 10 );

		}
		// refresh permalinks.
		flush_rewrite_rules();
	}
}
add_action( 'admin_init', 'mcm_save_updates' );

/**
 * Update post type settings.
 */
function mcm_updater() {
	$types   = mcm_globals( 'mcm_types' );
	$checked = '';
	if ( isset( $_GET['mcm_edit'] ) ) {
		$type  = sanitize_key( $_GET['mcm_edit'] );
		$event = 'mcm_edit';
	} else {
		$type  = 'new';
		$event = 'mcm_add';
	}
	$notice = get_transient( 'mcm_update_notice' );
	if ( $notice ) {
		wp_admin_notice( $notice );
		delete_transient( 'mcm_update_notice' );
	}
	// Translators: post type name.
	$before      = "<div class='mcm_edit_post_type'><form method='post' action='" . add_query_arg( $event, $type, admin_url( 'options-general.php?page=mcm_settings' ) ) . "'><div><input type='hidden' name='_wpnonce' value='" . wp_create_nonce( 'my-content-management-nonce' ) . "' /></div><fieldset class='fields'><legend>" . sprintf( esc_html__( 'Edit "%s"', 'my-content-management' ), $type ) . '</legend>';
	$post_typing = "<div><input type='hidden' name='mcm_type' value='$type' /></div>";

	$after  = "</fieldset><p><input type='submit' value='" . esc_attr( esc_html__( 'Edit Post Type', 'my-content-management' ) ) . "' name='mcm_updater' class='button-primary' /> <a href='" . admin_url( 'options-general.php?page=mcm_settings&mcm_add=new' ) . "'>" . esc_html__( 'Add new post type', 'my-content-management' ) . '</a>
				</p>
			</form></div>';
	$return = '';
	if ( is_array( $types ) ) {
		if ( 'new' !== $type ) {
			$data = $types[ $type ];
		} else {
			$data = false;
		}
		if ( $data && isset( $_GET['mcm_edit'] ) ) {
			if ( ! isset( $data[4]['slug'] ) ) {
				$data[4]['slug'] = $type;
			}
			$return  = $before;
			$return .= $post_typing;
			$return .= "
			<p><label for='pt1'>" . esc_html__( 'Singular Name, lower', 'my-content-management' ) . "</label><br /><input type='text' name='" . esc_attr( "${type}[pt1]" ) . "' id='pt1' value='" . esc_attr( $data[0] ) . "' /></p>
			<p><label for='pt3'>" . esc_html__( 'Singular Name, upper', 'my-content-management' ) . "</label><br /><input type='text' name='" . esc_attr( "${type}[pt3]" ) . "' id='pt3' value='" . esc_attr( $data[2] ) . "' /></p>
			<p><label for='pt2'>" . esc_html__( 'Plural Name, lower', 'my-content-management' ) . "</label><br /><input type='text' name='" . esc_attr( "${type}[pt2]" ) . "' id='pt2' value='" . esc_attr( $data[1] ) . "' /></p>
			<p><label for='pt4'>" . esc_html__( 'Plural Name, upper', 'my-content-management' ) . "</label><br /><input type='text' name='" . esc_attr( "${type}[pt4]" ) . "' id='pt4' value='" . esc_attr( $data[3] ) . "' /></p>";

			$keys = array_keys( $data[4] );
			if ( ! in_array( 'show_in_rest', $keys, true ) ) {
				$data[4]['show_in_rest'] = false;
			}
			foreach ( $data[4] as $key => $value ) {
				$key = sanitize_key( $key );
				if ( is_bool( $value ) ) {
					$checked = ( true === (bool) $value ) ? ' checked="checked"' : '';
					if ( 'show_in_rest' !== $key ) {
						$return .= "<p><input type='checkbox' name='" . esc_attr( "${type}[$key]" ) . "' value='1' id='" . esc_attr( $key ) . "'$checked /> <label for='" . esc_attr( $key ) . "'>" . esc_html( ucwords( str_replace( '_', ' ', $key ) ) ) . '</label></p>';
					} else {
						$return .= "<p><input type='checkbox' name='" . esc_attr( "${type}[$key]" ) . "' value='1' id='" . esc_attr( $key ) . "'$checked /> <label for='" . esc_attr( $key ) . "'>" . esc_html__( 'Show in REST API and enable Block Editor', 'my-content-management' ) . '</label></p>';
					}
				} elseif ( is_array( $value ) ) {
					$return  .= "<p><label for='" . esc_attr( $key ) . "'>" . esc_html( ucwords( str_replace( '_', ' ', $key ) ) ) . "</label><br /><select multiple='multiple' name='" . esc_attr( "${type}[$key][]" ) . "' id='" . esc_attr( $key ) . "'>";
					$supports = array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats', 'publicize' );
					foreach ( $supports as $s ) {
						$selected = ( in_array( $s, $value, true ) ) ? ' selected="selected"' : '';
						$return  .= "<option value='" . esc_attr( $s ) . "'$selected>" . esc_html( $s ) . '</option>';
					}
					$return .= '</select></p>';
				} else {
					$defaults = array( 'mcm_faqs', 'mcm_people', 'mcm_testimonials', 'mcm_locations', 'mcm_quotes', 'mcm_glossary', 'mcm_portfolio', 'mcm_resources' );
					if ( ! $value && in_array( $type, $defaults, true ) && 'menu_icon' === $key ) {
						$value = plugins_url( 'images', __FILE__ ) . "/$type.png";
					}
					$return .= "<p><label for='" . esc_attr( $key ) . "'>" . esc_html( ucwords( str_replace( '_', ' ', $key ) ) ) . "</label> <input type='text' name='" . esc_attr( "${type}[$key]" ) . "' size='32' value='" . esc_attr( $value ) . "' /></p>";
				}
			}
			$return .= $after;
		}
	}
	if ( 'new' === $type && isset( $_GET['mcm_add'] ) && 'new' === $_GET['mcm_add'] ) {
		$return  = $before;
		$return .= "
		<p><label for='pt1'>" . esc_html__( 'Singular Name, lower', 'my-content-management' ) . "</label><br /><input type='text' name='new[pt1]' id='pt1' value='' /></p>
		<p><label for='pt3'>" . esc_html__( 'Singular Name, upper', 'my-content-management' ) . "</label><br /><input type='text' name='new[pt3]' id='pt3' value='' /></p>
		<p><label for='pt2'>" . esc_html__( 'Plural Name, lower', 'my-content-management' ) . "</label><br /><input type='text' name='new[pt2]' id='pt2' value='' /></p>
		<p><label for='pt4'>" . esc_html__( 'Plural Name, upper', 'my-content-management' ) . "</label><br /><input type='text' name='new[pt4]' id='pt4' value='' /></p>
		";

		$default_args = mcm_globals( 'mcm_args' );
		// Set up values from default post type arguments.
		foreach ( $default_args as $key => $value ) {
			if ( is_bool( $value ) ) {
				$checked = ( true === (bool) $value ) ? ' checked="checked"' : '';
				if ( 'show_in_rest' !== $key ) {
					$return .= "<p><input type='checkbox' name='new[" . esc_attr( $key ) . "]' value='1' id='" . esc_attr( $key ) . "'$checked /> <label for='" . esc_attr( $key ) . "'>" . ucwords( str_replace( '_', ' ', $key ) ) . '</label></p>';
				} else {
					$return .= "<p><input type='checkbox' name='new[" . esc_attr( $key ) . "]' value='1' id='" . esc_attr( $key ) . "'$checked /> <label for='" . esc_attr( $key ) . "'>" . esc_html__( 'Show in REST API and enable Block Editor', 'my-content-management' ) . '</label></p>';
				}
			} elseif ( is_array( $value ) ) {
				$return  .= "<p><label for='" . esc_attr( $key ) . "'>" . ucwords( str_replace( '_', ' ', $key ) ) . "</label><br /><select multiple='multiple' name='new[${key}][]' id='" . esc_attr( $key ) . "'>";
				$supports = array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats', 'publicize' );
				foreach ( $supports as $s ) {
					$selected = ( in_array( $s, $value, true ) ) ? ' selected="selected"' : '';
					$return  .= "<option value='$s'$selected>$s</option>";
				}
				$return .= '</select></p>';
			} else {
				$return .= "<p><label for='" . esc_attr( $key ) . "'>" . ucwords( str_replace( '_', ' ', $key ) ) . "</label> <input type='text' name='new[" . esc_attr( $key ) . "]' value='" . esc_attr( $value ) . "' /></p>";
			}
		}
		$return .= "<p>
				<input type='hidden' name='mcm_new' value='new' />
				<input type='submit' value='" . esc_attr__( 'Add New Custom Post Type', 'my-content-management' ) . "' name='mcm_updater' class='button-primary' />
			</p>
			</div>
		</form></div>";
	}
	echo $return;
}

/**
 * Delete a post type.
 *
 * @param string $type Post type.
 *
 * @return string
 */
function mcm_delete_type( $type ) {
	$options   = get_option( 'mcm_options' );
	$types     = $options['types'];
	$templates = $options['templates'];
	$enabled   = $options['enabled'];
	print_r( $types );
	if ( isset( $types[ $type ] ) ) {
		unset( $options['types'][ $type ] );
		unset( $options['templates'][ $type ] );
		$key = array_search( $type, $enabled, true );
		if ( $key ) {
			unset( $options['enabled'][ $key ] );
		}
		update_option( 'mcm_options', $options );
		// Translators: Custom post type name.
		return "<div class='updated fade'><p>" . sprintf( esc_html__( 'Custom post type "%1$s" has been deleted.', 'my-content-management' ), $type ) . '</p></div>';
	}

	// Translators: Post type name.
	return "<div class='error'><p>" . sprintf( esc_html__( 'Custom post type "%1$s" was not found, and could not be deleted.', 'my-content-management' ), $type ) . '</p></div>';
}

/**
 * Set templates.
 */
function mcm_template_setter() {
	if ( isset( $_POST['mcm_save_templates'] ) ) {
		$nonce = $_REQUEST['_wpnonce'];
		if ( ! wp_verify_nonce( $nonce, 'my-content-management-nonce' ) ) {
			wp_die( 'My Content Management: Security check failed' );
		}
		$type                         = sanitize_key( $_POST['mcm_post_type'] );
		$option                       = get_option( 'mcm_options' );
		$new                          = map_deep( $_POST['templates'], 'sanitize_textarea_field' );
		$option['templates'][ $type ] = $new[ $type ];
		update_option( 'mcm_options', $option );
		echo "<div class='updated fade'><p>" . esc_html__( 'Post Type templates updated', 'my-content-management' ) . '</p></div>';
	}
	$option    = get_option( 'mcm_options' );
	$templates = $option['templates'];
	$enabled   = $option['enabled'];
	$types     = $option['types'];
	$fields    = $option['fields'];
	$extras    = $option['extras'];
	$return    = '';
	$list      = array( 'div', 'ul', 'ol', 'dl', 'section' );
	$item      = array( 'div', 'li', 'article' );
	$default   = array(
		'full'    => '
<h2>{title}</h2>
{content}

<p>{link_title}</p>',
		'excerpt' => '
<h3>{title}</h3>
{excerpt}

<p>{link_title}</p>',
		'list'    => '{link_title}',
		'wrapper' => array(
			'item' => array(
				'full'    => 'div',
				'excerpt' => 'div',
				'list'    => 'li',
			),
			'list' => array(
				'full'    => 'div',
				'excerpt' => 'div',
				'list'    => 'ul',
			),
		),
	);
	if ( is_array( $enabled ) ) {

		$return = "<div class='postbox' id='mcm-template-settings'>
		<h2 class='hndle'><span>" . esc_html__( 'Template Manager', 'my-content-management' ) . "</span></h2>
			<div class='inside'>";
		$tabs   = "<ul class='tabs'>";
		foreach ( $enabled as $value ) {
			$tabs .= "<li><a href='#$value-container'>" . $types[ $value ][2] . '</a></li>';
		}
		$tabs   .= '</ul>';
		$return .= $tabs;
		foreach ( $enabled as $value ) {
			if ( isset( $types[ $value ] ) ) {
				$pointer       = array();
				$display_value = str_replace( 'mcm_', '', $value );
				$template      = ( isset( $templates[ $value ] ) ) ? $templates[ $value ] : $default;
				$label         = $types[ $value ];
				$extra_fields  = array();
				foreach ( $extras as $k => $v ) {
					if ( is_string( $v[0] ) && $v[0] === $value ) {
						$extra_fields[] = $fields[ $k ];
						$pointer[]      = $value;
					} else {
						if ( is_array( $v[0] ) ) {
							foreach ( $v[0] as $ka => $va ) {
								if ( $va === $value ) {
									$extra_fields[] = $fields[ $k ];
									$pointer[]      = $value;
								}
							}
						}
					}
				}
				if ( ! in_array( $value, $pointer, true ) ) {
					$extra_fields = false;
				}
				$show_fields = '';
				if ( is_array( $extra_fields ) ) {
					foreach ( $extra_fields as $k => $v ) {
						if ( is_array( $v ) ) {
							foreach ( $v as $f ) {
								$show_fields .= "<p><code>&#123;$f[0]&#125;</code>: $f[1]</p>";
							}
						} else {
							$show_fields .= "<p><code>&#123;$v[0]&#125;</code>: $v[1]</p>";
						}
					}
				} else {
					$show_fields = '';
				}
				$extension = '';
				if ( 'mcm_glossary' === $value && function_exists( 'mcm_set_glossary' ) ) {
					$extension = '<h4>Glossary Extension</h4>
						<p>' . __( 'The glossary extension to My Content Management is enabled.', 'my-content-management' ) . "</p>
						<ul>
						<li><code>[alphabet numbers='true']</code>: " . __( 'displays list of linked first characters represented in your Glossary. (Roman alphabet only, including numbers 0-9 by default.)', 'my-content-management' ) . "</li>
						<li><code>[term id='' term='']</code>: " . __( 'displays value of term attribute linked to glossary term with ID attribute.', 'my-content-management' ) . '</li>
						<li><strong>' . __( 'Feature', 'my-content-management' ) . ':</strong> ' . __( 'Adds links throughout content for each term in your glossary.', 'my-content-management' ) . '</li>
						<li><strong>' . __( 'Feature', 'my-content-management' ) . ':</strong> ' . __( 'Adds character headings to each section of your glossary list.', 'my-content-management' ) . '</li>
						</ul>';
				}
				if ( '' !== $show_fields ) {
					$show_attributes = '<h3>' . __( 'Fields attributes:', 'my-content-management' ) . '</h3><p><code>before</code>: ' . __( 'Add text before field', 'my-content-management' ) . '</p><p><code>after</code>: ' . __( 'Add text after field', 'my-content-management' ) . '</p><p>Sample: <code>{_field before=">>" after="<<"}</code></p>';
					$show_fields    .= $show_attributes;
					$show_fields     = "<div class='extra_fields'><h3>" . __( 'Added custom fields:', 'my-content-management' ) . "</h3>$show_fields</div>";
				}
				$extension = ( '' !== $extension ) ? "<div class='extra_fields'>$extension</div>" : '';
				// Translators: Post type name.
				$return .= "<div id='$value-container' class='wptab'><h3>" . sprintf( __( '%s Templates', 'my-content-management' ), $types[ $value ][2] ) . '</h3>
						<p>' . __( 'Example shortcode:', 'my-content-management' ) . "<br /><code>[my_content type='$display_value' display='full' taxonomy='mcm_category_$display_value' order='menu_order']</code></p>
						<form method='post' action='" . admin_url( 'options-general.php?page=mcm_settings' ) . "'>
						<div><input type='hidden' name='_wpnonce' value='" . wp_create_nonce( 'my-content-management-nonce' ) . "' /></div>
						<div><input type='hidden' name='mcm_post_type' value='$value' /></div>
						<div>
						<fieldset>
							<legend>" . __( 'Full', 'my-content-management' ) . "</legend>
							<p>
								<label for='mcm_full_list_wrapper_$value'>" . __( 'List Wrapper', 'my-content-management' ) . "</label> <select name='templates[$value][wrapper][list][full]' id='mcm_full_list_wrapper_$value'>" . mcm_option_list( $list, $template['wrapper']['list']['full'] ) . "</select> <label for='mcm_full_item_wrapper_$value'>" . __( 'Item Wrapper', 'my-content-management' ) . "</label> <select name='templates[$value][wrapper][item][full]' id='mcm_full_item_wrapper_$value'>" . mcm_option_list( $item, $template['wrapper']['item']['full'] ) . "</select>
							</p>
							<p>
								<label for='mcm_full_wrapper_$value'>" . __( 'Full Template', 'my-content-management' ) . "</label><br /> <textarea name='templates[$value][full]' id='mcm_full_wrapper_$value' rows='7' cols='60'>" . wp_unslash( esc_textarea( $template['full'] ) ) . '</textarea>
							</p>
						</fieldset>
						<fieldset>
							<legend>' . __( 'Excerpt', 'my-content-management' ) . "</legend>
							<p>
								<label for='mcm_excerpt_list_wrapper_$value'>" . __( 'List Wrapper', 'my-content-management' ) . "</label> <select name='templates[$value][wrapper][list][excerpt]' id='mcm_excerpt_list_wrapper_$value'>" . mcm_option_list( $list, $template['wrapper']['list']['excerpt'] ) . "</select> <label for='mcm_excerpt_item_wrapper_$value'>" . __( 'Item Wrapper', 'my-content-management' ) . "</label> <select name='templates[$value][wrapper][item][excerpt]' id='mcm_excerpt_item_wrapper_$value'>" . mcm_option_list( $item, $template['wrapper']['item']['excerpt'] ) . "</select>
							</p>
							<p>
								<label for='mcm_excerpt_wrapper_$value'>" . __( 'Excerpt Template', 'my-content-management' ) . "</label><br /> <textarea name='templates[$value][excerpt]' id='mcm_excerpt_wrapper_$value' rows='3' cols='60'>" . wp_unslash( esc_textarea( $template['excerpt'] ) ) . '</textarea>
							</p>
						</fieldset>
						<fieldset>
							<legend>' . __( 'List', 'my-content-management' ) . "</legend>
							<p>
								<label for='mcm_list_list_wrapper_$value'>" . __( 'List Wrapper', 'my-content-management' ) . "</label> <select name='templates[$value][wrapper][list][list]' id='mcm_list_list_wrapper_$value'>" . mcm_option_list( $list, $template['wrapper']['list']['list'] ) . "</select> <label for='mcm_list_item_wrapper_$value'>" . __( 'Item Wrapper', 'my-content-management' ) . "</label> <select name='templates[$value][wrapper][item][list]' id='mcm_list_item_wrapper_$value'>" . mcm_option_list( $item, $template['wrapper']['item']['list'] ) . "</select>
							</p>
							<p>
								<label for='mcm_list_wrapper_$value'>" . __( 'List Template', 'my-content-management' ) . "</label><br /> <textarea name='templates[$value][list]' id='mcm_list_wrapper_$value' rows='1' cols='60'>" . wp_unslash( esc_textarea( $template['list'] ) ) . '</textarea>
							</p>
						</fieldset>';
					// Translators: Template name.
					$return .= "<p><input type='submit' value='" . sprintf( __( 'Update %s Templates', 'my-content-management' ), $label[2] ) . "' name='mcm_save_templates' class='button-primary' />
						</p>
						</div>
						</form>
						$show_fields
						$extension
						<h3>" . __( 'Naming for theme templates', 'my-content-management' ) . '</h3>
						<ul>
							<li>' . __( 'Theme template for this taxonomy:', 'my-content-management' ) . " <code>taxonomy-mcm_category_$display_value.php</code></li>
							<li>" . __( 'Theme template for this custom post type:', 'my-content-management' ) . " <code>single-mcm_$display_value.php</code></li>
							<li>" . __( 'Theme template for archive pages with this post type:', 'my-content-management' ) . " <code>archive-mcm_$display_value.php</code></li>
						</ul>
					</div>";
			}
		}
		$return .= '</div>
			</div>';
	}
	echo $return;
}

/**
 * Return a list of options.
 *
 * @param array  $array Options.
 * @param string $current Selected option.
 *
 * @return string
 */
function mcm_option_list( $array, $current ) {
	$return = '';
	if ( is_array( $array ) ) {
		foreach ( $array as $key ) {
			$checked = ( $key === $current && '' !== $current ) ? ' selected="selected"' : '';
			$return .= "<option value='$key'$checked>&lt;$key&gt; </option>\n";
		}
	}
	$checked = ( '' === $current ) ? ' selected="selected"' : '';
	$return .= "<option value=''$checked>" . __( 'No wrapper', 'my-content-management' ) . '</option>';

	return $return;
}

/**
 * Output support information box.
 */
function mcm_show_support_box() {
	?>
	<div id="support">
		<div class="resources">
		<p>
		<a href="https://twitter.com/intent/follow?screen_name=joedolson" class="twitter-follow-button" data-size="small" data-related="joedolson">Follow @joedolson</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if (!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		</p>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<div>
			<input type="hidden" name="cmd" value="_s-xclick" />
			<input type="hidden" name="hosted_button_id" value="YP36SWZTDQAUL" />
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" name="submit" alt="Make a gift to support My Content Management!" />
			<img alt="" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" />
			</div>
		</form>
		</div>
	</div>
	<?php
}

/**
 * Output support column.
 */
function mcm_support_column() {
	?>
	<div class="postbox-container" style="width: 25%">
		<div class="metabox-holder">
			<div class="mcm-settings ui-sortable meta-box-sortables">
				<div class="mcm-template-guide postbox" id="get-support">
					<h2 class='hndle'><?php esc_html_e( 'Support This Plug-in', 'my-content-management' ); ?></h2>
					<div class="inside">
						<?php mcm_show_support_box(); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="metabox-holder">
			<div class="mcm-settings ui-sortable meta-box-sortables">
				<div class="mcm-template-guide postbox" id="get-support">
					<h2 class='hndle'><?php esc_html_e( 'No support', 'my-content-management' ); ?></h2>
					<div class="inside">
						<?php
							esc_html_e( 'My Content Management is available with no support. It is intended as a custom field and custom post type builder, and the templating functions are no longer maintained.', 'my-content-management' );
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Add custom field support to a post type.
 *
 * @param string $fieldset Fieldset name.
 * @param string $post_type Post type name.
 */
function mcm_add_custom_field_support( $fieldset, $post_type ) {
	$option = get_option( 'mcm_options' );
	$array  = isset( $option['extras'][ $fieldset ][0] ) ? $option['extras'][ $fieldset ][0] : array();

	if ( ! is_array( $array ) ) {
		$array = array( $array );
	}

	if ( ! in_array( $post_type, $array, true ) ) {
		$array[] = $post_type;
	}
	$option['extras'][ $fieldset ][0] = $array;
	update_option( 'mcm_options', $option );
}

/**
 * Remove custom field support from a post type.
 *
 * @param string $fieldset Fieldset name.
 * @param string $post_type Post type name.
 */
function mcm_delete_custom_field_support( $fieldset, $post_type ) {
	$option = get_option( 'mcm_options' );
	$option = get_option( 'mcm_options' );
	$array  = $option['extras'][ $fieldset ][0];
	if ( ! is_array( $array ) ) {
		$array = array();
	}
	if ( in_array( $post_type, $array, true ) ) {
		$key = array_search( $post_type, $array, true );
		unset( $array[ $key ] );
	}
	$option['extras'][ $fieldset ][0] = $array;
	update_option( 'mcm_options', $option );
}

/**
 * Show assigned fields for a given post type.
 *
 * @param string $show Form to show.
 * @param string $post_type Post type.
 * @param bool   $echo False to return.
 *
 * @return string
 */
function mcm_fields( $show = 'assign', $post_type = false, $echo = true ) {
	if ( isset( $_POST['mcm_custom_fields'] ) ) {
		$nonce = $_REQUEST['_wpnonce'];
		if ( ! wp_verify_nonce( $nonce, 'my-content-management-nonce' ) ) {
			wp_die( 'My Content Management: Security check failed' );
		}
		$extras = map_deep( $_POST['mcm_field_extras'], 'sanitize_textarea_field' );
		foreach ( $extras as $key => $value ) {
			if ( 'on' === $value ) {
				mcm_add_custom_field_support( $key, $post_type );
			} else {
				mcm_delete_custom_field_support( $key, $post_type );
			}
		}
		echo "<div class='updated fade'><p>" . esc_html__( 'Custom fields for this post type updated', 'my-content-management' ) . '</p></div>';
	}
	$option  = get_option( 'mcm_options' );
	$extras  = $option['extras'];
	$checked = '';
	$return  = '';
	if ( is_array( $extras ) ) {
		foreach ( $extras as $key => $value ) {
			$page        = $post_type;
			$checked_off = ' checked="checked"';
			if ( ! is_array( $value[0] ) ) {
				$checked_on = ( $value[0] === $page ) ? ' checked="checked"' : '';
			} elseif ( in_array( $page, $value[0], true ) ) {
				$checked_on = ( in_array( $page, $value[0], true ) ) ? ' checked="checked"' : '';
			} else {
				$checked_off = ' checked="checked"';
				$checked_on  = '';
			}
			$k     = urlencode( $key );
			$group = wp_unslash( $key );
			$key   = sanitize_key( $key );

			$current = '';
			if ( isset( $_GET['mcm_fields_edit'] ) && urlencode( sanitize_text_field( $_GET['mcm_fields_edit'] ) ) === $k ) {
				$current = ' aria-current="true"';
			}
			$return .= "<li><a class='button-secondary'$current href='" . esc_url( admin_url( "options-general.php?page=mcm_custom_fields&mcm_fields_edit=$k" ) ) . "'>$group</a></li>";
		}
	}
	$return = '<h3>' . __( 'Fieldsets', 'my-content-management' ) . "</h3><ul class='mcm_customfields'>" . $return . '</ul>';
	if ( ! $echo ) {
		return $return;
	} else {
		echo $return;
	}
}

/**
 * Pass field update to function that handles that type of update.
 */
function mcm_fields_updater() {
	if ( ! isset( $_GET['mcm_fields_edit'] ) ) {
		// Creating a new fieldset, so fetch the blank fieldset form.
		mcm_get_fieldset();
	}
	if ( isset( $_GET['mcm_fields_edit'] ) ) {
		// Editing a fieldset, so get the completed fieldset form.
		mcm_get_fieldset( sanitize_text_field( $_GET['mcm_fields_edit'] ) );
	}
}

/**
 * Create post type relations chooser.
 *
 * @param string $key Field type.
 * @param array  $choices Currently selected types.
 *
 * @return string
 */
function mcm_post_type_relation( $key, $choices ) {
	$post_types = get_post_types(
		array(
			'public' => 'true',
		),
		'names'
	);
	$list       = '';
	$output     = '';
	foreach ( $post_types as $types ) {
		if ( $choices === $types ) {
			$selected = ' selected="selected"';
		} else {
			$selected = '';
		}
		$list .= "<option value='$types'$selected>$types</option>";
	}
	$output .= "
	<select id='mcm_field_options$key' name='mcm_field_options[]'>
		<option value=''> -- </option>
		$list
	</select>";

	return $output;
}

/**
 * Clone of wp_dropdown_roles, except it returns.
 *
 * @param string $selected Selected role.
 *
 * @return string
 */
function mcm_dropdown_roles( $selected = false ) {
	$p = '';
	$r = '';

	$editable_roles = array_reverse( get_editable_roles() );

	foreach ( $editable_roles as $role => $details ) {
		$name = esc_html( translate_user_role( $details['name'] ) );
		if ( $selected === $role ) {
			$p = "\n\t<option selected='selected' value='" . esc_attr( $role ) . "'>$name</option>";
		} else {
			$r .= "\n\t<option value='" . esc_attr( $role ) . "'>$name</option>";
		}
	}

	return $p . $r;
}

/**
 * Create user relations chooser.
 *
 * @param string $key Field type.
 * @param array  $choices Currently selected types.
 *
 * @return string
 */
function mcm_user_type_relation( $key, $choices ) {
	$list   = mcm_dropdown_roles( $choices );
	$output = "
	<select id='mcm_field_options$key' name='mcm_field_options[]'>
		<option value=''> -- </option>
		$list
	</select>";

	return $output;
}

/**
 * Fetch a fieldset creation form.
 *
 * @param string $fieldset Saved fieldset key.
 */
function mcm_get_fieldset( $fieldset = false ) {
	$option     = get_option( 'mcm_options' );
	$posts      = get_post_types(
		array(
			'public' => 'true',
		),
		'object'
	);
	$post_types = '';

	if ( $fieldset ) {
		$location = ( isset( $option['extras'][ $fieldset ][1] ) ) ? $option['extras'][ $fieldset ][1] : 'side';
		$types    = ( isset( $option['extras'][ $fieldset ][0] ) ) ? $option['extras'][ $fieldset ][0] : array();
		$context  = ( isset( $option['extras'][ $fieldset ][2] ) ) ? $option['extras'][ $fieldset ][2] : '';
	} else {
		$location = 'side';
		$context  = '';
		$types    = array();
	}
	if ( ! is_array( $types ) ) {
		$types = array( $types );
	}
	foreach ( $posts as $value ) {
		$name  = $value->name;
		$label = $value->labels->name;
		if ( $fieldset ) {
			$checked = ( in_array( $name, $types, true ) ) ? ' checked="checked"' : '';
		} else {
			$checked = '';
		}
		$post_types .= "<input type='checkbox' $checked value='$name' name='mcm_assign_to[]' id='mcm_post_type_$name'> <label for='mcm_post_type_$name'>$label</label>\n";
	}
	$options = "<p>
				<label for='mcm_fieldset_context'>" . esc_html__( 'Restrict by IDs', 'my-content-management' ) . "</label> <input class='narrow' type='text' id='mcm_fieldset_context' name='mcm_fieldset_context' aria-describedby='mcm_context_description' value='$context' /> <em id='mcm_context_descrition'>Comma-separated list of post IDs</em>
			</p>
			<p>
				<label for='mcm_fieldset_location'>" . esc_html__( 'Fieldset Location', 'my-content-management' ) . "</label> <select id='mcm_fieldset_location' name='mcm_fieldset_location' />
					<option value='side'" . selected( $location, 'side', false ) . '>' . esc_html__( 'Side', 'my-content-management' ) . "</option>
					<option value='normal'" . selected( $location, 'normal', false ) . '>' . esc_html__( 'Normal', 'my-content-management' ) . "</option>
					<option value='advanced'" . selected( $location, 'advanced', false ) . '>' . esc_html__( 'Advanced', 'my-content-management' ) . '</option>
				</select>
			</p>
			<fieldset>
				<legend>' . esc_html__( 'Attach to', 'my-content-management' ) . "</legend>
				<p>
				$post_types
				</p>
			</fieldset>";

	if ( ! $fieldset ) {
		$fieldset_title = "
			<p>
				<label for='mcm_new_fieldset'>" . esc_html__( 'New Fieldset Title', 'my-content-management' ) . "</label> <input type='text' id='mcm_new_fieldset' name='mcm_new_fieldset' /><span id='warning' aria-live='assertive'></span>
			</p>
			$options";
	} else {
		$fieldset_title = $options;
	}
	$form = "<div class='mcm_fieldset_options'>
				$fieldset_title
			</div>" . '<table class="widefat">
				<thead>
					<tr><th scope="col">' . esc_html__( 'Move', 'my-content-management' ) . '</th><th scope="col">' . esc_html__( 'Field Label', 'my-content-management' ) . '</th><th scope="col">' . esc_html__( 'Input Type', 'my-content-management' ) . '</th><th scope="col">Description/Options</th><th scope="col">' . esc_html__( 'Repeatable', 'my-content-management' ) . '</th><th scope="col">' . esc_html__( 'Delete', 'my-content-management' ) . '</th>
				</tr>
				</thead>
				<tbody>';
	$odd  = 'odd';
	if ( isset( $option['fields'][ $fieldset ] ) ) {
		$fields = ( $fieldset ) ? $option['fields'][ urldecode( $fieldset ) ] : '';
	}
	$field_types = array(
		'text'          => esc_html__( 'Single line of text', 'my-content-management' ),
		'textarea'      => esc_html__( 'Multiple lines of text', 'my-content-management' ),
		'select'        => esc_html__( 'Select dropdown', 'my-content-management' ),
		'checkboxes'    => esc_html__( 'Set of checkboxes', 'my-content-management' ),
		'checkbox'      => esc_html__( 'Single checkbox', 'my-content-management' ),
		'upload'        => esc_html__( 'File upload', 'my-content-management' ),
		'chooser'       => esc_html__( 'Media chooser', 'my-content-management' ),
		'richtext'      => esc_html__( 'Rich Text Editor', 'my-content-management' ),
		'color'         => esc_html__( 'Color input', 'my-content-management' ),
		'date'          => esc_html__( 'Date input', 'my-content-management' ),
		'tel'           => esc_html__( 'Telephone', 'my-content-management' ),
		'time'          => esc_html__( 'Time', 'my-content-management' ),
		'url'           => esc_html__( 'URL', 'my-content-management' ),
		'email'         => esc_html__( 'Email', 'my-content-management' ),
		'post-relation' => esc_html__( 'Related Posts', 'my-content-management' ),
		'user-relation' => esc_html__( 'Related Users', 'my-content-management' ),
	);
	if ( $fieldset && isset( $option['fields'][ $fieldset ] ) ) {
		if ( count( $fields ) > 0 ) {
			foreach ( $fields as $key => $value ) {
				if ( is_array( $value[2] ) ) {
					$choices = esc_attr( wp_unslash( implode( ', ', $value[2] ) ) );
				} else {
					$choices = esc_attr( wp_unslash( $value[2] ) );
				}
				$field_type_select = '';
				foreach ( $field_types as $k => $v ) {
					$selected           = ( $value[3] === $k || ( 'text' === $k && 'mcm_text_field' === $value[3] ) ) ? ' selected="selected"' : '';
					$field_type_select .= "<option value='$k'$selected>$v</option>\n";
				}
				if ( 'select' === $value[3] ) {
					$labeled      = esc_html__( 'Options', 'my-content-management' );
					$choice_field = "<input type='text' name='mcm_field_options[]' id='mcm_field_options$key' value='$choices' />";
				} elseif ( 'post-relation' === $value[3] ) {
					$labeled      = esc_html__( 'Related post type', 'my-content-management' );
					$choice_field = mcm_post_type_relation( $key, $choices );
				} elseif ( 'user-relation' === $value[3] ) {
					$labeled      = esc_html__( 'Related user', 'my-content-management' );
					$choice_field = mcm_user_type_relation( $key, $choices );
				} else {
					$labeled      = esc_html__( 'Additional Text', 'my-content-management' );
					$choice_field = "<input type='text' name='mcm_field_options[]' id='mcm_field_options$key' value='$choices' />";
				}
				if ( isset( $value[4] ) && 'true' === $value[4] ) {
					$repeatability = " checked='checked'";
				} else {
					$repeatability = '';
				}
				$form .= "
				<tr class='mcm_custom_fields_form $odd'>
					<td>
						<a href='#' class='up'><span>" . esc_html__( 'Move Up', 'my-content-management' ) . "</span></a> <a href='#' class='down'><span>" . esc_html__( 'Move Down', 'my-content-management' ) . "</span></a>
					</td>
					<td>
						<input type='hidden' name='mcm_field_key[]'  value='$value[0]' />
						<label for='mcm_field_label$key'>" . esc_html__( 'Label', 'my-content-management' ) . "</label> <input type='text' name='mcm_field_label[]' id='mcm_field_label$key' value='" . esc_attr( wp_unslash( $value[1] ) ) . "' /><br /><small>{<code>$value[0]</code>}</small>
					</td>
					<td>
						<label for='mcm_field_type$key'>" . esc_html__( 'Type', 'my-content-management' ) . "</label>
							<select name='mcm_field_type[]' id='mcm_field_type$key'>
							$field_type_select
							</select>
					</td>
					<td>
						<label for='mcm_field_options$key'>$labeled</label> $choice_field
					</td>
					<td>
						<label for='mcm_field_repeatable$key'>" . esc_html__( 'Repeatable', 'my-content-management' ) . "</label> <input type='checkbox' name='mcm_field_repeatable[$key]' id='mcm_field_repeatable$key' class='mcm-repeatable' value='true'$repeatability />
					</td>
					<td>
						<label for='mcm_field_delete$key'>" . esc_html__( 'Delete', 'my-content-management' ) . "</label> <input type='checkbox' name='mcm_field_delete[$key]' id='mcm_field_delete$key' class='mcm-delete' value='delete' />
					</td>
				</tr>";
				$odd   = ( 'odd' === $odd ) ? 'even' : 'odd';
			}
		} else {
			// Translators: action to perform (html button).
			echo "<div class='mcm-notice'><p>" . sprintf( esc_html__( 'This fieldset has no fields defined. Do you want to %s?', 'my-content-management' ), "<input type='submit' class='button-primary' name='mcm_custom_fieldsets' value='" . esc_attr__( 'Delete the Fieldset', 'my-content-management' ) . "' />" ) . '</p></div>';
		}
	} elseif ( $fieldset && ! isset( $option['fields'][ $fieldset ] ) ) {
		echo "<div class='updated error'><p>" . esc_html__( 'There is no field set by that name', 'my-content-management' ) . '</p></div>';
	}
	$field_type_select = '';
	foreach ( $field_types as $k => $v ) {
		$field_type_select .= "<option value='" . esc_attr( $k ) . "'>" . esc_html( $v ) . '</option>';
	}
	$form     .= "
	<tr class='mcm_custom_fields_form clonedInput' id='field1'>
		<td></td>
		<td>
			<input type='hidden' name='mcm_field_key[]'  value='' />
			<label for='mcm_field_label'>" . esc_html__( 'Label', 'my-content-management' ) . "</label> <input type='text' name='mcm_field_label[]' id='mcm_field_label' value='' />
		</td>
		<td>
			<label for='mcm_field_type'>" . esc_html__( 'Type', 'my-content-management' ) . "</label>
				<select name='mcm_field_type[]' id='mcm_field_type'>
				$field_type_select
				</select>
		</td>
		<td>
			<label for='mcm_field_options'>" . esc_html__( 'Options/Additional Text', 'my-content-management' ) . "</label> <input type='text' name='mcm_field_options[]' id='mcm_field_options' value='' />
		</td>
		<td>
			<label for='mcm_field_repeatable'>" . esc_html__( 'Repeatable', 'my-content-management' ) . "</label> <input type='checkbox' name='mcm_field_repeatable[]' id='mcm_field_repeatable' value='true' />
		</td>
		<td></td>
	</tr>";
	$form     .= '</tbody></table>';
	$add_field = esc_html__( 'Add another field', 'my-content-management' );
	$del_field = esc_html__( 'Remove last field', 'my-content-management' );
	$form     .= '<p><input type="button" class="button-secondary add_field" value="' . esc_attr( $add_field ) . '" class="button" /> <input type="button" class="button-secondary del_field" value="' . esc_attr( $del_field ) . '" class="button" /></p>';

	echo $form;
}

/**
 * Execute update of a fieldset.
 *
 * @param array $post POST data.
 *
 * @return string
 */
function mcm_update_custom_fieldset( $post ) {
	$option     = get_option( 'mcm_options' );
	$array      = array();
	$simplified = array();
	if ( ! isset( $post['mcm_field_delete'] ) ) {
		$post['mcm_field_delete'] = array();
	}
	$delete       = is_array( $post['mcm_field_delete'] ) ? array_keys( $post['mcm_field_delete'] ) : array();
	$keys         = $post['mcm_field_key'];
	$labels       = $post['mcm_field_label'];
	$types        = $post['mcm_field_type'];
	$options      = $post['mcm_field_options'];
	$repeatable   = ( isset( $post['mcm_field_repeatable'] ) ) ? $post['mcm_field_repeatable'] : false;
	$count        = count( $labels );
	$delete_count = count( $delete );
	// ID fieldset.
	$fieldset = ( isset( $_GET['mcm_fields_edit'] ) ) ? sanitize_text_field( $_GET['mcm_fields_edit'] ) : false;
	if ( isset( $post['mcm_new_fieldset'] ) ) {
		$fieldset = $post['mcm_new_fieldset'];
		$added    = __( 'added', 'my-content-management' );
	} else {
		$added = __( 'updated', 'my-content-management' );
	}
	if ( ! empty( $option['extras'][ $fieldset ] ) && isset( $post['mcm_new_fieldset'] ) ) {
		$fieldset = $fieldset . ' (2)';
	}
	if ( ! $fieldset ) {
		return esc_html__( 'No custom field set was defined.', 'my-content-management' );
	} else {
		$fieldset = urldecode( $fieldset );
	}
	if ( isset( $post['mcm_new_fieldset'] ) ) {
		$mcm_assign_to                 = isset( $post['mcm_assign_to'] ) ? $post['mcm_assign_to'] : array();
		$mcm_location                  = isset( $post['mcm_fieldset_location'] ) ? $post['mcm_fieldset_location'] : 'side';
		$mcm_context                   = isset( $post['mcm_fieldset_context'] ) ? $post['mcm_fieldset_context'] : true;
		$option['extras'][ $fieldset ] = array( $mcm_assign_to, $mcm_location, $mcm_context );
	} else {
		$mcm_assign_to                 = isset( $post['mcm_assign_to'] ) ? $post['mcm_assign_to'] : $option['extras'][ $fieldset ][0];
		$mcm_location                  = isset( $post['mcm_fieldset_location'] ) ? $post['mcm_fieldset_location'] : $option['extras'][ $fieldset ][1];
		$mcm_context                   = isset( $post['mcm_fieldset_context'] ) ? $post['mcm_fieldset_context'] : $option['extras'][ $fieldset ][2];
		$option['extras'][ $fieldset ] = array( $mcm_assign_to, $mcm_location, $mcm_context );
	}

	for ( $i = 0; $i < $count; $i++ ) {
		if ( in_array( $i, $delete, true ) ) {
			// Nothing happens. Don't save changes if deleting.
		} else {
			$repetition = ( isset( $repeatable[ $i ] ) ) ? 'true' : '';
			if ( '' !== $keys[ $i ] ) {
				if ( 'select' === $types[ $i ] || 'checkboxes' === $types[ $i ] ) {
					$opt = explode( ',', $options[ $i ] );
				} else {
					$opt = $options[ $i ];
				}
				$array[ $i ] = array(
					$keys[ $i ],
					$labels[ $i ],
					$opt,
					$types[ $i ],
					$repetition,
				);
				// for now, this is secondary. Prep for simplifying and fixing data format.
				$simplified[] = array(
					'key'         => $keys[ $i ],
					'label'       => $labels[ $i ],
					'description' => $opt,
					'type'        => $types[ $i ],
					'repetition'  => $repetition,
					'fieldset'    => $fieldset,
				);
			} elseif ( '' !== $labels[ $i ] ) {
				if ( 'select' === $types[ $i ] || 'checkboxes' === $types[ $i ] ) {
					$opt = explode( ',', $options[ $i ] );
				} else {
					$opt = $options[ $i ];
				}
				$k            = '_' . sanitize_title( $labels[ $i ] );
				$array[ $i ]  = array(
					$k,
					$labels[ $i ],
					$opt,
					$types[ $i ],
					$repetition,
				);
				$simplified[] = array(
					'key'         => $k,
					'label'       => $labels[ $i ],
					'description' => $opt,
					'type'        => $types[ $i ],
					'repetition'  => $repetition,
					'fieldset'    => $fieldset,
				);
			} else {
				continue;
			}
		}
	}
	$simple               = ( isset( $option['simplified'] ) ) ? $option['simplified'] : array();
	$simplified           = (array) $simplified + (array) $simple;
	$option['simplified'] = $simplified;
	// Note: $count == 1 argument means any fieldset with only one value is automatically unset.
	if ( $count === $delete_count || $delete_count > $count || ( 1 === $count && ! isset( $post['mcm_new_fieldset'] ) ) ) {
		// if all fields are deleted, remove set.
		unset( $option['fields'][ $fieldset ] );
		unset( $option['extras'][ $fieldset ] );
		$added = __( 'deleted', 'my-content-management' );
	} else {
		$option['fields'][ $fieldset ] = $array;
	}
	update_option( 'mcm_options', $option );
	// Translators: 1) action taken 2) name of fieldset acted on.
	return sprintf( esc_html__( 'You have %1$s the %2$s group of custom fields.', 'my-content-management' ), $added, wp_unslash( $fieldset ) );
}

/**
 * Add the administrative settings to the "Settings" menu.
 */
function mcm_add_support_page() {
	// Use this filter to disable all access to admin pages.
	if ( apply_filters( 'mcm_show_administration_pages', true ) ) {
		$plugin_page = add_options_page( 'My Content Management', 'My Content Management', 'manage_options', 'mcm_settings', 'mcm_settings_page' );
		add_action( 'admin_head-' . $plugin_page, 'mcm_styles' );
		add_action( 'admin_print_styles-' . $plugin_page, 'mcm_add_scripts' );

		$plugin_page = add_options_page( 'My Custom Fields', 'My Custom Fields', 'manage_options', 'mcm_custom_fields', 'mcm_configure_custom_fields' );
		add_action( 'admin_head-' . $plugin_page, 'mcm_styles' );
		add_action( 'admin_print_styles-' . $plugin_page, 'mcm_add_scripts' );
	}
}
add_action( 'admin_menu', 'mcm_add_support_page' );

/**
 * Custom field management admin screen.
 */
function mcm_configure_custom_fields() {
	if ( isset( $_POST['mcm_custom_fieldsets'] ) ) {
		$message = mcm_update_custom_fieldset( map_deep( $_POST, 'sanitize_text_field' ) );
	} else {
		$message = false;
	}
	if ( $message ) {
		wp_admin_notice( $message );
	}
	if ( isset( $_GET['mcm_fields_edit'] ) ) {
		$append = '&mcm_fields_edit=' . urlencode( sanitize_text_field( $_GET['mcm_fields_edit'] ) );
	} else {
		$append = '';
	}
	$fields = mcm_fields( 'edit', false, false );
	?>
	<div class="wrap">
		<h2 class='hndle'><?php esc_html_e( 'My Content Management / Manage Custom Fields', 'my-content-management' ); ?></h2>
		<div class="postbox-container" style="width: 70%">
			<div class="metabox-holder">
				<div class="mcm-settings ui-sortable meta-box-sortables">
					<div class="postbox" id="mcm-settings">
						<h2 class='hndle'><?php esc_html_e( 'Manage Custom Fieldsets', 'my-content-management' ); ?></h2>
						<div class="inside">
							<p><?php esc_html_e( 'If the input type is a Select box, enter the selectable options as a comma-separated list in the Description/Options field.', 'my-content-management' ); ?></p>
							<?php echo $fields; ?>
							<form method='post' action='<?php echo esc_url( admin_url( "options-general.php?page=mcm_custom_fields$append" ) ); ?>'>
								<div><input type='hidden' name='_wpnonce' value='<?php echo esc_attr( wp_create_nonce( 'my-content-management-nonce' ) ); ?>' /></div>
								<div>
								<?php mcm_fields_updater(); ?>
								<p>
									<input type='submit' value='<?php esc_attr_e( 'Update Custom Fieldsets', 'my-content-management' ); ?>' name='mcm_custom_fieldsets' class='button-primary' /> <a href="<?php echo esc_url( admin_url( 'options-general.php?page=mcm_custom_fields&mcm_fields_add=new' ) ); ?>"><?php esc_html_e( 'Add new custom field set', 'my-content-management' ); ?></a>
								</p>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php mcm_support_column(); ?>
	</div>
	<?php
}

/**
 * Enqueue styles.
 */
function mcm_styles() {
	wp_enqueue_style( 'mcm.styles', plugins_url( 'css/mcm-styles.css', __FILE__ ) );
}

/**
 * Add plugin action links.
 *
 * @param array  $links Existing action links.
 * @param string $file Plugin file.
 *
 * @return array
 */
function mcm_plugin_action( $links, $file ) {
	if ( plugin_basename( dirname( __FILE__ ) . '/my-content-management.php' ) === $file ) {
		$links[] = "<a href='options-general.php?page=mcm_settings'>" . esc_html__( 'Settings', 'my-content-management' ) . '</a>';
		$links[] = "<a href='http://www.joedolson.com/donate/'>" . esc_html__( 'Donate', 'my-content-management' ) . '</a>';
	}

	return $links;
}
add_filter( 'plugin_action_links', 'mcm_plugin_action', 10, 2 );
