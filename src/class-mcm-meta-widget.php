<?php
/**
 * My Content Management meta widget.
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