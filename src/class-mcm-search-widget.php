<?php
/**
 * My Content Management search widget.
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