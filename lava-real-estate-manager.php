<?php
/**
 * Plugin Name: Lava Real Estate Manager
 * Plugin URL : http://lava-code.com/real-estate/
 * Description: Lava Real Estate Manager Plugin
 * Version: 0.1.0
 * Author: lavacode
 * Author URL: http://lava-code.com/
 * Text Domain: Lavacode
 * Domain Path: /languages/
 */
/*
    Copyright Automattic and many other contributors.

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if( ! defined( 'ABSPATH' ) )
	die();

if( ! class_exists( 'Lava_RealEstate_Manager' ) ) :

	class Lava_RealEstate_Manager
	{
		private $version = '0.1.0';
		private static $instance;

		public function __construct( $file )
		{
			$this->file				= $file;
			$this->folder			= basename( dirname( $this->file ) );
			$this->path				= dirname( $this->file );
			$this->template_path	= trailingslashit( $this->path ) . 'templates';
			$this->assets_url		= esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
			$this->image_url		= esc_url( trailingslashit( $this->assets_url . 'images/' ) );

			$this->load_files();

			$this->register_hooks();

			new Lava_RealEstate_Manager_Enqueues;
		}

		public function load_files()
		{
			require_once 'includes/class-lava-array.php';
			require_once 'includes/class-core.php';
			require_once 'includes/class-admin.php';
			require_once 'includes/class-enqueues.php';
			require_once 'includes/class-widgets.php';
			require_once 'includes/class-shortcodes.php';
			require_once 'includes/class-template.php';
			require_once 'includes/class-submit.php';

			$GLOBALS[ 'lava_realestate_manager_func' ]		= new Lava_RealEstate_Manager_Func;
		}
		public function register_hooks()
		{
			add_action( 'init'						, Array( $this, 'initialize' ) );
			add_action( 'init'						, Array( $this, 'register_admin_panel' ) );
			add_action( 'widgets_init'				, Array( $this, 'register_sidebar' ) );
			add_action( 'widgets_init'				, Array( $this, 'register_widgets' ) );
		}

		public function initialize()
		{
			add_rewrite_tag('%edit%', '([^&]+)');
			do_action( 'lava_realestate_manager_init' );

			new Lava_RealEstate_Manager_Submit;
		}

		public static function get_instance( $file )
		{
			if( null === self::$instance )
				self::$instance = new Lava_RealEstate_Manager( $file );

			return self::$instance;
		}

		public function register_admin_panel() {
			$GLOBALS[ 'lava_realestate_manager_admin' ] = new Lava_RealEstate_Manager_Admin;
		}

		public function register_sidebar()
		{
			$post_type		= constant( 'Lava_RealEstate_Manager_Func::SLUG' );

			register_sidebar(
				Array(
					'name'	=> __( "Lava Single Sidebar ({$post_type})", 'Lavacode' )
					, 'id'	=> "lava-{$post_type}-single-sidebar"
				)
			);
		}

		public function register_widgets() {
			new Lava_RealEstate_Manager_widgets;
		}

		public static function register_role() {
			new Lava_RealEstate_Manager_role;
		}
	}
endif;

$GLOBALS[ 'lava_realestate_manager' ] = Lava_RealEstate_Manager::get_instance( __FILE__ );