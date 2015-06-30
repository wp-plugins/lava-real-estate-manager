<?php




/**
 *
 *
 * @param	Integer	Default choise page ID
 * @return	String	Select tag options
 */
if( ! function_exists( 'getOptionsPagesLists' ) ) :

	function getOptionsPagesLists( $default=0 )
	{
		global $lava_realestate_manager_admin;
		return $lava_realestate_manager_admin->getOptionsPagesLists( $default );
	}

endif;




/**
 * Get manager setting options
 *
 * @param	String	Option Key name
 * @param	Mixed	Result value null, return
 * @return	Mixed	String or default value
 */
if( ! function_exists( 'lava_realestate_manager_get_option' ) ) :

	function lava_realestate_manager_get_option( $key, $default=false )
	{
		global $lava_realestate_manager_admin;
		if( empty( $lava_realestate_manager_admin ) )
			$lava_realestate_manager_admin	= new Lava_RealEstate_Manager_Admin;

		return $lava_realestate_manager_admin->get_settings( $key, $default );
	}

endif;