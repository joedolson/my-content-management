<?php
/**
 * My Content Management posts widgets.
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
		$mcm_types     = mcm_globals( 'mcm_types' );
		$types         = array_keys( $mcm_types );
		$before_widget = $args['before_widget'];
		$after_widget  = $args['after_widget'];
		$before_title  = $args['before_title'];
		$after_title   = $args['after_title'];

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

		echo wp_kses( $before_widget . $widget_title . $wrapper . $custom . $unwrapper . $after_widget, mcm_kses_elements() );
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
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( wp_unslash( $title ) ); ?>"/>
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
				$post_types .= "<option value='" . esc_attr( $name ) . "'$selected>" . esc_html( wp_unslash( $label ) ) . "</option>\n";
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
		<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Number to display', 'my-content-management' ); ?></label> <input type="text" size="3" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" value="<?php echo (int) $count; ?>" /><br /><span>(<?php _e( '-1 to display all posts', 'my-content-management' ); ?>)</span>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'direction' ); ?>"><?php _e( 'Order direction', 'my-content-management' ); ?></label> <select id="<?php echo $this->get_field_id( 'direction' ); ?>" name="<?php echo $this->get_field_name( 'direction' ); ?>">
		<option value='asc'<?php echo ( 'asc' === $direction ) ? ' selected="selected"' : ''; ?>><?php _e( 'Ascending (A-Z)', 'my-content-management' ); ?></option>
		<option value='desc'<?php echo ( 'desc' === $direction ) ? ' selected="selected"' : ''; ?>><?php _e( 'Descending (Z-A)', 'my-content-management' ); ?></option>
		</select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'term' ); ?>"><?php _e( 'Category (single term or comma-separated list)', 'my-content-management' ); ?>:</label><br />
		<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'term' ); ?>" name="<?php echo $this->get_field_name( 'term' ); ?>" value="<?php echo esc_attr( wp_unslash( $term ) ); ?>"/>
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
		<textarea class="widefat" id="<?php echo $this->get_field_id( 'template' ); ?>" cols='40' rows='4' name="<?php echo $this->get_field_name( 'template' ); ?>"><?php echo wp_unslash( esc_textarea( $template ) ); ?></textarea>
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
		$instance['mcm_posts_widget_post_type'] = sanitize_text_field( $new_instance['mcm_posts_widget_post_type'] );
		$instance['display']                    = sanitize_text_field( $new_instance['display'] );
		$instance['order']                      = sanitize_text_field( $new_instance['order'] );
		$instance['direction']                  = sanitize_text_field( $new_instance['direction'] );
		$instance['count']                      = ( '' === $new_instance['count'] ) ? -1 : (int) $new_instance['count'];
		$instance['title']                      = sanitize_text_field( $new_instance['title'] );
		$instance['term']                       = sanitize_text_field( $new_instance['term'] );
		$instance['wrapper']                    = sanitize_text_field( $new_instance['wrapper'] );
		$instance['template']                   = wp_kses( $new_instance['template'], mcm_kses_elements() );

		return $instance;
	}
}
