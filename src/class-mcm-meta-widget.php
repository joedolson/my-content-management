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
			echo wp_kses( $args['before_widget'] . $widget_title . $fieldset . $args['after_widget'], mcm_kses_elements() );
		}
	}

	/**
	 * Widget settings form.
	 *
	 * @param array $instance Instance data.
	 */
	function form( $instance ) {
		$mcm_extras      = mcm_globals( 'mcm_extras' );
		$types           = array_keys( $mcm_extras );
		$title           = isset( $instance['title'] ) ? $instance['title'] : '';
		$fieldset        = isset( $instance['fieldset'] ) ? $instance['fieldset'] : '';
		$display         = isset( $instance['display'] ) ? $instance['display'] : '';
		$left_column     = isset( $instance['left_column'] ) ? $instance['left_column'] : '';
		$right_column    = isset( $instance['right_column'] ) ? $instance['right_column'] : '';
		$custom_template = isset( $instance['custom_template'] ) ? $instance['custom_template'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', 'my-content-management' ); ?>:</label><br />
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo wp_unslash( esc_attr( $title ) ); ?>"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'fieldset' ); ?>"><?php esc_html_e( 'Fieldset to display', 'my-content-management' ); ?></label> <select id="<?php echo $this->get_field_id( 'fieldset' ); ?>" name="<?php echo $this->get_field_name( 'fieldset' ); ?>">
			<?php
			$fieldsets = '';
			foreach ( $types as $v ) {
				$name       = esc_attr( $v );
				$label      = esc_html( $v );
				$selected   = ( $fieldset === $name ) ? ' selected="selected"' : '';
				$fieldsets .= "<option value='" . esc_attr( $name ) . "'$selected>" . esc_html( $label ) . "</option>\n";
			}
			echo $fieldsets;
			?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'display' ); ?>"><?php esc_html_e( 'Display style', 'my-content-management' ); ?></label> <select id="<?php echo $this->get_field_id( 'display' ); ?>" name="<?php echo $this->get_field_name( 'display' ); ?>">
				<option value='list'<?php selected( $display, 'list' ); ?>><?php esc_html_e( 'List', 'my-content-management' ); ?></option>
				<option value='table'<?php selected( $display, 'table' ); ?>><?php esc_html_e( 'Table', 'my-content-management' ); ?></option>
				<option value='custom'<?php selected( $display, 'custom' ); ?>><?php esc_html_e( 'Custom', 'my-content-management' ); ?></option>
			</select>
		</p>
		<?php
		if ( 'table' === $display ) {
			?>
			<p>
			<label for="<?php echo $this->get_field_id( 'left_column' ); ?>"><?php esc_html_e( 'Left column header', 'my-content-management' ); ?>:</label><br />
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'left_column' ); ?>" name="<?php echo $this->get_field_name( 'left_column' ); ?>" value="<?php echo wp_unslash( esc_attr( $left_column ) ); ?>"/>
			</p>
			<p>
			<label for="<?php echo $this->get_field_id( 'right_column' ); ?>"><?php esc_html_e( 'Right column header', 'my-content-management' ); ?>:</label><br />
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'right_column' ); ?>" name="<?php echo $this->get_field_name( 'right_column' ); ?>" value="<?php echo wp_unslash( esc_attr( $right_column ) ); ?>"/>
			</p>
			<?php
		}
		if ( 'custom' === $display ) {
			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'custom_template' ); ?>"><?php esc_html_e( 'Template', 'my-content-management' ); ?>:</label><br />
				<textarea class="widefat" cols="70" rows="6" id="<?php echo $this->get_field_id( 'custom_template' ); ?>" name="<?php echo $this->get_field_name( 'custom_template' ); ?>"><?php echo wp_unslash( esc_textarea( $custom_template ) ); ?></textarea>
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
		$instance['fieldset']        = sanitize_text_field( $new_instance['fieldset'] );
		$instance['display']         = sanitize_text_field( $new_instance['display'] );
		$instance['title']           = sanitize_text_field( $new_instance['title'] );
		$instance['left_column']     = isset( $new_instance['left_column'] ) ? sanitize_text_field( $new_instance['left_column'] ) : '';
		$instance['right_column']    = isset( $new_instance['right_column'] ) ? sanitize_text_field( $new_instance['right_column'] ) : '';
		$instance['custom_template'] = isset( $new_instance['custom_template'] ) ? wp_kses_post( $new_instance['custom_template'] ) : '';

		return $instance;
	}
}
