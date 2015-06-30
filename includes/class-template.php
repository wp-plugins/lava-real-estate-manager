<?php

class Lava_RealEstate_Manager_template
{




	/**
	 *	Constructor
	 *
	 *
	 *	@return	void
	 */
	public function __construct()
	{
		$this->post_type											= constant( 'Lava_RealEstate_Manager_Func::SLUG' );

		/* Common hooks */ {
			add_filter( 'template_include'							, Array( $this, 'load_templates' ) );
		}

		/** Single page template */ {

			add_action(
				"lava_{$this->post_type}_single_container_after"
				, Array( $this, 'single_script_params' ), 20
			);

			add_action(
				"lava_{$this->post_type}_single_container_after"
				, Array( $this, 'single_script' ), 30
			);
		}

		/** Map page template  */ {
			add_action( "lava_{$this->post_type}_map_container_after" , Array( $this, 'print_map_templates' ) );
		}

		/** Add form template */ {
			add_action( "lava_add_{$this->post_type}_form_before"	, Array( $this	, 'author_user_email' ), 20 );
			add_action( "lava_add_{$this->post_type}_form_after"	, Array( $this	, 'extend_form' ) );
			add_filter( "lava_add_{$this->post_type}_terms"			, Array( $this	, 'addItem_terms' ), 9 );

			foreach(
				Array( 'category', 'type' )
				as $key
			) add_filter( "lava_map_meta_{$key}"					, Array( $this, "map_meta_{$key}" ), 10, 2 );
		}

		/** Shortcode - listings */ {

			// Output Templates
			add_action( "lava_{$this->post_type}_listings_after"	, Array( $this, 'print_listings_templates' ) );

			// Output Variables
			add_action( "lava_{$this->post_type}_listings_after"	, Array( $this, 'print_listings_var' ) );
		}
	}




	/**
	 *
	 *
	 *
	 *	@param	array
	 *	@return	array
	 */
	public static function addItem_terms( $args ) {
		global $lava_realestate_manager_func;

		$lava_exclude					= Array( 'post_tag' );

		$lava_taxonomies				= $lava_realestate_manager_func->lava_extend_item_taxonomies();

		if( empty( $lava_taxonomies ) || !is_Array( $lava_taxonomies ) )
			return $args;

		if( !empty( $lava_exclude ) ) : foreach( $lava_exclude as $terms ) {
			if( in_Array( $terms, $lava_taxonomies ) )
				unset( $lava_taxonomies[ $terms] );
		} endif;

		return wp_parse_args( Array_Keys( $lava_taxonomies ), $args );
	}




	/**
	 *
	 *
	 *	@param	string	template path
	 *	@return	string	template path
	 */
	public function load_templates( $template )
	{
		global
			$wp_query
			, $lava_realestate_manager;

		$post		= $wp_query->queried_object;

		if( is_object( $post ) ) {

			/* Single Template */ {

				if( $wp_query->is_single && $post->post_type == $this->post_type ) {

					if(  $__template = locate_template(
							Array(
								"single-{$this->post_type}.php"
								, $lava_realestate_manager->folder . "/single-{$this->post_type}.php"
							)
						)
					) $template = $__template;
				}
			}

			/* Map Template */ {

				if( $wp_query->is_page && $post->ID == lava_realestate_manager_get_option( 'map_page', 0 ) ) {

					add_action( 'wp_enqueue_scripts'		, Array( $this, 'map_template_enqueues' ) );

					lava_realestate_mapdata( $post );
					$GLOBALS[ 'post' ] = $post;

					$template			= $lava_realestate_manager->template_path . "/template-map.php";
					if(  $__template = locate_template(
							Array(
								"lava-map-template.php"
								, $lava_realestate_manager->folder . "/lava-map-template.php"
							)
						)
					) $template = $__template;
				}

			}
		}
		return $template;
	}




	/**
	 *
	 *
	 *	@param	object
	 *	@return	void
	 */
	public function extend_form( $edit )
	{
		global $lava_realestate_manager;

		$arrPartFiles	= apply_filters(
			'lava_realestate_manager_add_item_extends'
			, Array(
				'lava-add-item-terms.php'
				, 'lava-add-item-file.php'
				, 'lava-add-item-location.php'
				, 'lava-add-item-meta.php'
			)
		);

		if( !empty( $arrPartFiles ) ) :  foreach( $arrPartFiles as $filename ) {
			$filepath	= trailingslashit( $lava_realestate_manager->template_path ) . "form/{$filename}";
			if( file_exists( $filepath ) ) require_once $filepath;
		} endif;
	}




	/**
	 *
	 *
	 *	@param	array
	 *	@return	void
	 */
	public function author_user_email( $edit )
	{
		global $lava_realestate_manager;

		$lava_loginURL			= apply_filters( "lava_{$this->post_type}_login_url", wp_login_url() );

		if( is_user_logged_in() )
			return;

		$filepath				= trailingslashit( $lava_realestate_manager->template_path ) . "form/lava-add-item-user.php";
		if( file_exists( $filepath ) ) require_once $filepath;
	}




	/**
	 *
	 *
	 *	@param	array
	 *	@return	void
	 */
	public function single_script_params()
	{
		$post		= get_post();
		$options	= Array(
			'strNotLocation'			=> __( "There is no location information on this property.", 'Lavacode' )
			, 'strNotStreetview'		=> __( "This location is not supported by google StreetView or the location did not add.", 'Lavacode' )
		);

		echo "<fieldset class=\"lava-single-map-param hidden\">";

		echo "
			<!-- parameters -->
			<input type=\"hidden\" value=\"disable\" data-cummute-panel>
			<input type=\"hidden\" value=\"300\" data-map-height>
			<input type=\"hidden\" value=\"500\" data-street-height>
			<!-- end parameters -->
			";

		if( ! empty( $options ) ) : foreach( $options as $key => $value ) {
			echo "<input type='hidden' key=\"{$key}\" value=\"{$value}\">";
		} endif;

		foreach(
			Array( 'lat', 'lng', 'street_lat', 'street_lng', 'street_heading', 'street_pitch', 'street_zoom', 'street_visible' )
			as $key
		) printf(
			"<input type=\"hidden\" data-item-%s value=\"%s\">\n"
			, str_replace( '_', '-', $key )
			, floatVal( get_post_meta( $post->ID, "lv_item_{$key}", true ) )
		);
		echo "</fieldset>";
	}




	/**
	 *
	 *
	 *	@param	array
	 *	@return	void
	 */
	public function single_script()
	{
		echo "
			<script type=\"text/javascript\">
			jQuery( function($){
				jQuery.lava_single({
					map			: $( '#lava-single-map-area' )
					, street	: $( '#lava-single-streetview-area' )
					, slider	: $( '.lava-detail-images' )
					, param		: $( '.lava-single-map-param' )
				});
			} );
			</script>
			";
	}




	/**
	 *
	 *
	 *	@param	array
	 *	@return	void
	 */
	public function map_template_enqueues()
	{
		wp_enqueue_script( 'google-maps' );
		wp_enqueue_script( 'gmap-v3' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'google-map-infobubble-js' );
		wp_enqueue_script( 'lava-map-js' );
		do_action( "lava_{$this->post_type}_map_box_enqueue_scripts" );
	}




	/**
	 *
	 *
	 *	@return	void
	 */
	public function print_map_templates()
	{
		global $lava_realestate_manager;

		$load_map_htmls		= Array(
			'lava-map-output-template'	=> 'template-map-htmls.php'
			, 'lava-map-not-found-template'	=> 'template-not-list.php'
		);

		$load_map_htmls		= apply_filters( 'lava_{$this->post_type}_map_htmls', $load_map_htmls );
		$output_script		= Array();
		if( !empty( $load_map_htmls ) ) : foreach( $load_map_htmls as $sID => $strFilename ) {

			$output_script[]	= "<script type='text/html' id=\"{$sID}\">";
			ob_start();
			require_once $lava_realestate_manager->template_path . "/{$strFilename}";
			$output_script[]	= ob_get_clean();
			$output_script[]	= "</script>";

		} endif;
		echo @implode( "\n", $output_script );
	}




	/**
	 *
	 *
	 *	@return	void
	 */
	public function print_listings_templates()
	{
		global $lava_realestate_manager;

		$load_map_htmls		= Array(
			'lava-realstate-manager-listing-template'	=> 'template-listing-list.php'
		);

		$load_map_htmls		= apply_filters( 'lava_{$this->post_type}_map_htmls', $load_map_htmls );
		$output_script		= Array();
		if( !empty( $load_map_htmls ) ) : foreach( $load_map_htmls as $sID => $strFilename ) {

			$output_script[]	= "<script type='text/html' id=\"{$sID}\">";
			ob_start();
			require_once $lava_realestate_manager->template_path . "/{$strFilename}";
			$output_script[]	= ob_get_clean();
			$output_script[]	= "</script>";

		} endif;
		echo @implode( "\n", $output_script );
	}




	/**
	 *
	 *
	 *	@return	void
	 */
	public function print_listings_var()
	{
		$lava_script_param			= Array();
		$lava_script_param[]		= "<script type=\"text/javascript\">";
			$lava_script_param[]	= sprintf( "var ajaxurl=\"%s\";", admin_url( 'admin-ajax.php' ) );
			$lava_script_param[]	= sprintf( "var _jb_not_results=\"%s\";", __( "Not found results.", 'Lavacode' ) );
		$lava_script_param[]		= "</script>";

		echo @implode( "\n", $lava_script_param );
	}

}
new Lava_RealEstate_Manager_template;