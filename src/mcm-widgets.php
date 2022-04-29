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

/**
 * My Content Management search widget class.
 *
 * @category  Widgets
 * @package   My Content Management
 * @author    Joe Dolson
 * @copyright 2012
 * @license   GPLv2 or later
 * @version   1.0
 */
class Mcm_Search_Widget extends WP_Widget {
	/**
	 * Construct.
	 */
	function __construct() {
		parent::__construct( false, $name = __( 'Custom Post Search', 'my-content-management' ), array( 'customize_selective_refresh' => true ) );
	}

	/**
	 * Create widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Instance.
	 */
	function widget( $args, $instance ) {
		// TODO: eliminate extract.
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		$the_title    = apply_filters( 'widget_title', $instance['title'] );
		$widget_title = empty( $the_title ) ? '' : $the_title;
		$widget_title = ( '' !== $widget_title ) ? $before_title . $widget_title . $after_title : '';
		$post_type    = $instance['mcm_widget_post_type'];
		$search_form  = mcm_search_form( $post_type );
		echo $before_widget;
		echo $widget_title;
		echo $search_form;
		echo $after_widget;
	}

	/**
	 * Widget settings form.
	 *
	 * @param array $instance Instance data.
	 */
	function form( $instance ) {
		$post_type = isset( $instance['mcm_widget_post_type'] ) ? esc_attr( $instance['mcm_widget_post_type'] ) : '';
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'my-content-management' ); ?>:</label><br />
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'mcm_widget_post_type' ); ?>"><?php _e( 'Post type to search', 'my-content-management' ); ?></label> <select id="<?php echo $this->get_field_id( 'mcm_widget_post_type' ); ?>" name="<?php echo $this->get_field_name( 'mcm_widget_post_type' ); ?>">
			<?php
			$posts      = get_post_types(
				array(
					'public' => 'true',
				),
				'object'
			);
			$post_types = '';
			foreach ( $posts as $v ) {
				$name        = $v->name;
				$label       = $v->labels->name;
				$selected    = ( $post_type === $name ) ? ' selected="selected"' : '';
				$post_types .= "<option value='$name'$selected>$label</option>\n";
			}
			echo $post_types;
			?>
			</select>
		</p>
		<?php
	}

	/**
	 * Update widget instance.
	 *
	 * @param array $new_instance New data.
	 * @param array $old_instance Old data.
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance                         = $old_instance;
		$instance['mcm_widget_post_type'] = strip_tags( $new_instance['mcm_widget_post_type'] );
		$instance['title']                = strip_tags( $new_instance['title'] );

		return $instance;
	}
}

/**
 * My Content Management posts widget class.
 *
 * @category  Widgets
 * @package   My Content Management
 * @author    Joe Dolson
 * @copyright 2012
 * @license   GPLv2 or later
 * @version   1.0
 */
class Mcm_Posts_Widget extends WP_Widget {
	/**
	 * Construct.
	 */
	function __construct() {
		parent::__construct( false, $name = __( 'Custom Post List', 'my-content-management' ), array( 'customize_selective_refresh' => true ) );
	}

	/**
	 * Create widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Instance.
	 */
	function widget( $args, $instance ) {
		global $mcm_types;
		$types = array_keys( $mcm_types );
		// TODO: eliminate extract.
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		$the_title    = apply_filters( 'widget_title', $instance['title'] );
		$widget_title = empty( $the_title ) ? '' : $the_title;
		$widget_title = ( '' !== $widget_title ) ? $before_title . $widget_title . $after_title : '';
		$post_type    = $instance['mcm_posts_widget_post_type'];
		if ( in_array( $post_type, $types, true ) ) {
			$display = ( '' === $instance['display'] ) ? 'list' : $instance['display'];
		} else {
			$display = 'custom';
		}
		$count     = ( '' === $instance['count'] ) ? -1 : (int) $instance['count'];
		$template  = ( '' === $instance['template'] ) ? '<li>{link_title}</li>' : $instance['template'];
		$wrapper   = ( '' === $instance['wrapper'] ) ? '<ul>' : '<' . $instance['wrapper'] . '>';
		$unwrapper = ( '' === $instance['wrapper'] ) ? '</ul>' : '</' . $instance['wrapper'] . '>';
		$order     = ( '' === $instance['order'] ) ? 'menu_order' : $instance['order'];
		$direction = ( '' === $instance['direction'] ) ? 'asc' : $instance['direction'];
		$term      = ( ! isset( $instance['term'] ) ) ? '' : $instance['term'];
		$taxonomy  = str_replace( 'mcm_', 'mcm_category_', $post_type );

		if ( 'avl-video' === $post_type ) {
			$taxonomy = 'avl_category_avl-video';
		}
		if ( 'post' === $post_type ) {
			$taxonomy = 'category';
		}
		if ( 'page' === $post_type ) {
			$taxonomy = '';
		}
		if ( 'custom' !== $display ) {
			$template  = '';
			$wrapper   = '';
			$unwrapper = '';
		}
		$args   = array(
			'type'           => $post_type,
			'display'        => $display,
			'taxonomy'       => $taxonomy,
			'term'           => $term,
			'count'          => $count,
			'order'          => $order,
			'direction'      => $direction,
			'template'       => $template,
			'custom_wrapper' => 'div',
			'operator'       => 'IN',
		);
		$args   = apply_filters( 'mcm_custom_posts_widget_args', $args );
		$custom = mcm_get_show_posts( $args );
		echo $before_widget;
		echo $widget_title;
		echo $wrapper;
		echo $custom;
		echo $unwrapper;
		echo $after_widget;
	}

	/**
	 * Widget settings form.
	 *
	 * @param array $instance Instance data.
	 */
	function form( $instance ) {
		$post_type = isset( $instance['mcm_posts_widget_post_type'] ) ? esc_attr( $instance['mcm_posts_widget_post_type'] ) : '';
		$display   = isset( $instance['display'] ) ? esc_attr( $instance['display'] ) : '';
		$count     = isset( $instance['count'] ) ? (int) $instance['count'] : -1;
		$direction = isset( $instance['direction'] ) ? esc_attr( $instance['direction'] ) : 'asc';
		$order     = isset( $instance['order'] ) ? esc_attr( $instance['order'] ) : '';
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$term      = isset( $instance['term'] ) ? esc_attr( $instance['term'] ) : '';
		$template  = isset( $instance['template'] ) ? esc_attr( $instance['template'] ) : '';
		$wrapper   = isset( $instance['wrapper'] ) ? esc_attr( $instance['wrapper'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'my-content-management' ); ?>:</label><br />
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $title; ?>"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'mcm_posts_widget_post_type' ); ?>"><?php _e( 'Post type to list', 'my-content-management' ); ?></label> <select id="<?php echo $this->get_field_id( 'mcm_posts_widget_post_type' ); ?>" name="<?php echo $this->get_field_name( 'mcm_posts_widget_post_type' ); ?>">
			<?php
			$posts      = get_post_types(
				array(
					'public' => 'true',
				),
				'object'
			);
			$post_types = '';
			foreach ( $posts as $v ) {
				$name        = $v->name;
				$label       = $v->labels->name;
				$selected    = ( $post_type === $name ) ? ' selected="selected"' : '';
				$post_types .= "<option value='$name'$selected>$label</option>\n";
			}
			echo $post_types;
			?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'display' ); ?>"><?php _e( 'Template Model', 'my-content-management' ); ?></label> <select id="<?php echo $this->get_field_id( 'display' ); ?>" name="<?php echo $this->get_field_name( 'display' ); ?>">
			<option value='list'<?php selected( $display, 'list' ); ?>><?php _e( 'List', 'my-content-management' ); ?></option>
			<option value='excerpt'<?php selected( $display, 'excerpt' ); ?>><?php _e( 'Excerpt', 'my-content-management' ); ?></option>
			<option value='full'<?php selected( $display, 'full' ); ?>><?php _e( 'Full', 'my-content-management' ); ?></option>
			<option value='custom'<?php selected( $display, 'custom' ); ?>><?php _e( 'Custom', 'my-content-management' ); ?></option>
			</select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Display order', 'my-content-management' ); ?></label> <select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
			<option value='menu_order'<?php selected( $order, 'menu_order' ); ?>><?php _e( 'Menu Order', 'my-content-management' ); ?></option>
			<option value='none'<?php selected( $order, 'none' ); ?>><?php _e( 'None', 'my-content-management' ); ?></option>
			<option value='ID'<?php selected( $order, 'id' ); ?>><?php _e( 'Post ID', 'my-content-management' ); ?></option>
			<option value='author'<?php selected( $order, 'author' ); ?>><?php _e( 'Author', 'my-content-management' ); ?></option>
			<option value='title'<?php selected( $order, 'title' ); ?>><?php _e( 'Post Title', 'my-content-management' ); ?></option>
			<option value='date'<?php selected( $order, 'date' ); ?>><?php _e( 'Post Date', 'my-content-management' ); ?></option>
			<option value='modified'<?php selected( $order, 'modified' ); ?>><?php _e( 'Post Modified Date', 'my-content-management' ); ?></option>
			<option value='rand'<?php selected( $order, 'rand' ); ?>><?php _e( 'Random', 'my-content-management' ); ?></option>
			<option value='comment_count'<?php selected( $order, 'comment_count' ); ?>><?php _e( 'Number of comments', 'my-content-management' ); ?></option>
		</select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Number to display', 'my-content-management' ); ?></label> <input type="text" size="3" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" value="<?php echo $count; ?>" /><br /><span>(<?php _e( '-1 to display all posts', 'my-content-management' ); ?>)</span>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'direction' ); ?>"><?php _e( 'Order direction', 'my-content-management' ); ?></label> <select id="<?php echo $this->get_field_id( 'direction' ); ?>" name="<?php echo $this->get_field_name( 'direction' ); ?>">
		<option value='asc'<?php echo ( 'asc' === $direction ) ? ' selected="selected"' : ''; ?>><?php _e( 'Ascending (A-Z)', 'my-content-management' ); ?></option>
		<option value='desc'<?php echo ( 'desc' === $direction ) ? ' selected="selected"' : ''; ?>><?php _e( 'Descending (Z-A)', 'my-content-management' ); ?></option>
		</select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'term' ); ?>"><?php _e( 'Category (single term or comma-separated list)', 'my-content-management' ); ?>:</label><br />
		<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'term' ); ?>" name="<?php echo $this->get_field_name( 'term' ); ?>" value="<?php echo $term; ?>"/>
		</p>
		<?php
		if ( 'custom' === $display ) {
			?>
		<fieldset>
		<legend><strong><?php _e( 'Custom Templating', 'my-content-management' ); ?></strong></legend>
		<p>
		<label for="<?php echo $this->get_field_id( 'wrapper' ); ?>"><?php _e( 'Wrapper', 'my-content-management' ); ?>:</label><br />
		<select id="<?php echo $this->get_field_id( 'wrapper' ); ?>" name="<?php echo $this->get_field_name( 'wrapper' ); ?>">
			<option value=''><?php _e( 'None', 'my-content-management' ); ?></option>
			<option value='ul'<?php selected( 'ul', $wrapper ); ?>><?php _e( 'Unordered list', 'my-content-management' ); ?></option>
			<option value='ol'<?php selected( 'ol', $wrapper ); ?>><?php _e( 'Ordered list', 'my-content-management' ); ?></option>
			<option value='div'<?php selected( 'div', $wrapper ); ?>><?php _e( 'Div', 'my-content-management' ); ?></option>
		</select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e( 'Custom Template', 'my-content-management' ); ?></label><br />
		<textarea class="widefat" id="<?php echo $this->get_field_id( 'template' ); ?>" cols='40' rows='4' name="<?php echo $this->get_field_name( 'template' ); ?>"><?php echo stripslashes( esc_textarea( $template ) ); ?></textarea>
		</p>
		</fieldset>
			<?php
		} else {
			?>
		<input type='hidden' name='<?php echo $this->get_field_name( 'wrapper' ); ?>' value='<?php echo esc_attr( $wrapper ); ?>' />
		<input type='hidden' name='<?php echo $this->get_field_name( 'template' ); ?>' value='<?php echo esc_attr( $template ); ?>' />
			<?php
		}
	}

	/**
	 * Update widget instance.
	 *
	 * @param array $new_instance New data.
	 * @param array $old_instance Old data.
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance                               = $old_instance;
		$instance['mcm_posts_widget_post_type'] = strip_tags( $new_instance['mcm_posts_widget_post_type'] );
		$instance['display']                    = strip_tags( $new_instance['display'] );
		$instance['order']                      = strip_tags( $new_instance['order'] );
		$instance['direction']                  = strip_tags( $new_instance['direction'] );
		$instance['count']                      = ( '' === $new_instance['count'] ) ? -1 : (int) $new_instance['count'];
		$instance['title']                      = strip_tags( $new_instance['title'] );
		$instance['term']                       = strip_tags( $new_instance['term'] );
		$instance['wrapper']                    = esc_attr( $new_instance['wrapper'] );
		$instance['template']                   = $new_instance['template'];

		return $instance;
	}
}

/**
 * My Content Management metadata widget class.
 *
 * @category  Widgets
 * @package   My Content Management
 * @author    Joe Dolson
 * @copyright 2012
 * @license   GPLv2 or later
 * @version   1.0
 */
class Mcm_Meta_Widget extends WP_Widget {
	/**
	 * Construct.
	 */
	function __construct() {
		parent::__construct(
			false,
			$name = __( 'Custom Post Data', 'my-content-management' ),
			array(
				'description'              => __( 'Widget displaying data entered in a specific fieldset.', 'my-content-management' ),
				'customize_select_refresh' => true,
			)
		);
	}

	/**
	 * Create widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Instance.
	 */
	function widget( $args, $instance ) {
		global $mcm_extras;
		$the_title     = apply_filters( 'widget_title', $instance['title'] );
		$fieldset_name = $instance['fieldset'];
		$display       = $instance['display'];

		$widget_title = empty( $the_title ) ? '' : $the_title;
		$widget_title = ( '' !== $widget_title ) ? $args['before_title'] . $widget_title . $args['after_title'] : '';

		$left_column  = isset( $instance['left_column'] ) ? $instance['left_column'] : __( 'Label', 'my-content-management' );
		$right_column = isset( $instance['right_column'] ) ? $instance['right_column'] : __( 'Value', 'my-content-management' );

		$custom_template = isset( $instance['custom_template'] ) ? $instance['custom_template'] : false;

		$fieldset = mcm_get_fieldset_values( $fieldset_name );
		$fieldset = mcm_format_fieldset(
			$fieldset,
			$display,
			array(
				'label'  => $left_column,
				'values' => $right_column,
			),
			$fieldset_name,
			$custom_template
		);

		if ( $fieldset ) {
			echo $args['before_widget'];
			echo $widget_title;
			echo $fieldset;
			echo $args['after_widget'];
		}
	}

	/**
	 * Widget settings form.
	 *
	 * @param array $instance Instance data.
	 */
	function form( $instance ) {
		global $mcm_extras;
		$types           = array_keys( $mcm_extras );
		$title           = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$fieldset        = isset( $instance['fieldset'] ) ? esc_attr( $instance['fieldset'] ) : '';
		$display         = isset( $instance['display'] ) ? esc_attr( $instance['display'] ) : '';
		$left_column     = isset( $instance['left_column'] ) ? esc_attr( $instance['left_column'] ) : '';
		$right_column    = isset( $instance['right_column'] ) ? esc_attr( $instance['right_column'] ) : '';
		$custom_template = isset( $instance['custom_template'] ) ? esc_attr( $instance['custom_template'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'my-content-management' ); ?>:</label><br />
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo stripslashes( esc_attr( $title ) ); ?>"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'fieldset' ); ?>"><?php _e( 'Fieldset to display', 'my-content-management' ); ?></label> <select id="<?php echo $this->get_field_id( 'fieldset' ); ?>" name="<?php echo $this->get_field_name( 'fieldset' ); ?>">
			<?php
			$fieldsets = '';
			foreach ( $types as $v ) {
				$name       = esc_attr( $v );
				$label      = esc_html( $v );
				$selected   = ( $fieldset === $name ) ? ' selected="selected"' : '';
				$fieldsets .= "<option value='$name'$selected>$label</option>\n";
			}
			echo $fieldsets;
			?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'display' ); ?>"><?php _e( 'Display style', 'my-content-management' ); ?></label> <select id="<?php echo $this->get_field_id( 'display' ); ?>" name="<?php echo $this->get_field_name( 'display' ); ?>">
				<option value='list'<?php selected( $display, 'list' ); ?>><?php _e( 'List', 'my-content-management' ); ?></option>
				<option value='table'<?php selected( $display, 'table' ); ?>><?php _e( 'Table', 'my-content-management' ); ?></option>
				<option value='custom'<?php selected( $display, 'custom' ); ?>><?php _e( 'Custom', 'my-content-management' ); ?></option>
			</select>
		</p>
		<?php
		if ( 'table' === $display ) {
			?>
			<p>
			<label for="<?php echo $this->get_field_id( 'left_column' ); ?>"><?php _e( 'Left column header', 'my-content-management' ); ?>:</label><br />
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'left_column' ); ?>" name="<?php echo $this->get_field_name( 'left_column' ); ?>" value="<?php echo $left_column; ?>"/>
			</p>
			<p>
			<label for="<?php echo $this->get_field_id( 'right_column' ); ?>"><?php _e( 'Right column header', 'my-content-management' ); ?>:</label><br />
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'right_column' ); ?>" name="<?php echo $this->get_field_name( 'right_column' ); ?>" value="<?php echo $right_column; ?>"/>
			</p>
			<?php
		}
		if ( 'custom' === $display ) {
			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'custom_template' ); ?>"><?php _e( 'Template', 'my-content-management' ); ?>:</label><br />
				<textarea class="widefat" cols="70" rows="6" id="<?php echo $this->get_field_id( 'custom_template' ); ?>" name="<?php echo $this->get_field_name( 'custom_template' ); ?>"><?php echo stripslashes( esc_attr( $custom_template ) ); ?></textarea>
			</p>
			<?php
		}
		?>
		</fieldset>
		<?php
	}

	/**
	 * Update widget instance.
	 *
	 * @param array $new_instance New data.
	 * @param array $old_instance Old data.
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance                    = $old_instance;
		$instance['fieldset']        = strip_tags( $new_instance['fieldset'] );
		$instance['display']         = strip_tags( $new_instance['display'] );
		$instance['title']           = strip_tags( $new_instance['title'] );
		$instance['left_column']     = isset( $new_instance['left_column'] ) ? strip_tags( $new_instance['left_column'] ) : '';
		$instance['right_column']    = isset( $new_instance['right_column'] ) ? strip_tags( $new_instance['right_column'] ) : '';
		$instance['custom_template'] = isset( $new_instance['custom_template'] ) ? wp_kses_post( $new_instance['custom_template'] ) : '';

		return $instance;
	}
}

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
		if ( '' !== $group[4] ) {
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
		foreach ( $values as $value ) {
			if ( ! empty( $value['value'] ) ) {
				if ( is_array( $value['value'] ) ) {
					$i = 1;
					foreach ( $value['value'] as $val ) {
						$label  = apply_filters( 'mcm_widget_data_label', esc_html( stripslashes( $value['label'] ) ), $val, $display, $fieldset );
						$output = apply_filters( 'mcm_widget_data_value', mcm_format_value( $val, $value['type'], $value['label'] ), $val, $display, $fieldset );
						if ( 'table' === $display ) {
							$list .= '<tr><td class="mcm_field_label">' . $label . ' <span class="mcm-repeater-label">(' . $i . ')</span></td><td class="mcm_field_value">' . wp_kses_post( $output ) . '</td></tr>';
						} else {
							$list .= '<li><strong class="mcm_field_label">' . $label . ' <span class="mcm-repeater-label">(' . $i . ')</span></strong> <div class="mcm_field_value">' . wp_kses_post( $output ) . '</div></li>';
						}
						$i++;
					}
				} else {
					$label  = apply_filters( 'mcm_widget_data_label', esc_html( stripslashes( $value['label'] ) ), $value, $display, $fieldset );
					$output = apply_filters( 'mcm_widget_data_value', mcm_format_value( $value['value'], $value['type'], $value['label'] ), $value, $display, $fieldset );
					if ( 'table' === $display ) {
						$list .= '<tr><td class="mcm_field_label">' . $label . '</td><td class="mcm_field_value">' . wp_kses_post( $output ) . '</td></tr>';
					} else {
						$list .= '<li><strong class="mcm_field_label">' . $label . '</strong> <div class="mcm_field_value">' . wp_kses_post( $output ) . '</div></li>';
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
			$list .= '<p>' . __( "You've selected a custom view. Here is the data you're working with on this post:", 'my-content-management' ) . '</p>';
			$list .= '<pre>';
			$list .= print_r( $values, 1 );
			$list .= '</pre>';
			$list .= '<p>' . __( 'Use the filter <code>mcm_custom_widget_data</code> to create a custom view.', 'my-content-management' ) . '</p>';
		}
	}

	if ( $list ) {
		$list = $before . $list . $after;
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
			$return = stripslashes( $value );
			break;
		case 'textarea':
		case 'richtext':
			$return = wpautop( stripslashes( $value ) );
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
				$return = stripslashes( $value );
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
