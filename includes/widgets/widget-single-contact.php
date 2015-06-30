<?php
class Lava_Contact_Single_Page extends WP_Widget
{
	static $load_script;
	function __construct()
	{
		parent::__construct(
			'Lava_Contact_Single_Page'
			, __( "[Lava] Realestate Single Contact", 'Lavacode' )
			, array( 'description' => __( "Single property contact widget", 'Lavacode' ) )
		);

		$this->post_type									= constant( 'Lava_RealEstate_Manager_Func::SLUG' );
		add_action( 'wp_footer'								, Array( __CLASS__, 'scripts' ) );
	}

	public function widget( $args, $instance )
	{
		global
			$post
			, $lava_realestate_manager;

		self::$load_script	= true;
		lava_realestate_setupdata( $post );

		if( empty( $post ) ) {
			echo '<h4>' . __( "Invalid post ID.", 'Lavacode') . '</h4>';
			return;
		}

		if( $post->post_type != $this->post_type )
			return;

		$output_filename		= "lava-single-contact-content.php";

		if(
			! $template_file = locate_template(
				Array(
					$output_filename
					, "{$lava_realestate_manager->folder}/{$output_filename}"
					, "{$lava_realestate_manager->folder}/html/{$output_filename}"
				)
			)
		){
			$template_file = dirname( __FILE__ ) . "/html/{$output_filename}";
		}
		ob_start();
			echo $args[ 'before_widget' ];
			require_once $template_file;
			echo $args[ 'after_widget' ];
		ob_end_flush();
	}

	public static function scripts()
	{
		if( ! self::$load_script )
			return;
		wp_enqueue_script( 'jquery-lava-msg-js' );
	}

	public function form( $instance )
	{
		$lava_wg_fields					= Array(

			'contact_type'				=>
				Array(
					'label'				=> __( "Form Type", 'Lavacode' )
					, 'type'			=> 'radio'
					, 'value'			=>
						Array(
							''			=> __( "None", 'Lavacode' )
							, 'ninja'	=> __( "Ninja Form", 'Lavacode' )
							, 'contact'	=> __( "Contact Form", 'Lavacode' )

						)
				)
			, 'contact_id'				=>
				Array(
					'label'				=> __( "Form ID", 'Lavacode' )
					, 'type'			=> 'number'
				)
			, 'separate'

			, 'report_type'				=>
				Array(
					'label'				=> __( "Report Type", 'Lavacode' )
					, 'type'			=> 'radio'
					, 'value'			=>
						Array(
							''			=> __( "None", 'Lavacode' )
							, 'ninja'	=> __( "Ninja Form", 'Lavacode' )
							, 'contact'	=> __( "Contact Form", 'Lavacode' )

						)
				)
			, 'report_id'				=>
				Array(
					'label'				=> __( "Report ID", 'Lavacode' )
					, 'type'			=> 'number'
				)
		);

		$output_html			= Array();

		if( !empty( $lava_wg_fields ) )
		{
			foreach( $lava_wg_fields as $id => $options )
			{
				if( $options === 'separate' ) {
					$output_html[]	= "<hr>"; continue;
				}
				$values				= isset( $instance[ $id ] ) ? esc_attr( $instance[ $id ] ) : null;
				$output_html[]		= "<p>";
				$output_html[]		= "<label for=\"" . $this->get_field_id( $id ) . "\">{$options['label']}</label>";

				switch( $options['type'] )
				{
					case 'radio':
						if( !empty( $options['value'] ) )
							foreach( $options['value'] as $value => $label )
								$output_html[]	= "<input id=\"" . $this->get_field_id( $id ) . "\" name=\"" . $this->get_field_name( $id ) . "\" type=\"{$options['type']}\" value=\"{$value}\"" . checked( $values == $value, true, false ) . "> {$label}";
					break;

					case 'number':
					case 'text':
					default:
						$output_html[]	= "<input id=\"" . $this->get_field_id( $id ) . "\" name=\"" . $this->get_field_name( $id ) . "\" type=\"{$options['type']}\" value=\"{$values}\">";
				}

				$output_html[]	= "</p>";
			}

			echo @implode( "\n", $output_html );
		}
	}

	public function update( $new_instance, $old_instance ) { return $new_instance; }

} // class Foo_Widget