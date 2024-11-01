<?php

namespace SUL;
//Prevent direct requests
defined( 'ABSPATH' ) || exit;

/**
 * Contains the functions that are triggered on plug-in activation.
 * 
 * Public methods are statically invoked.
 */
class Activation {

	/**
	 * Invokes other functions by means of orchestration actions
	 * that need to happen when the plugin is activated.
	 * 
	 */
	public static function activate() {
		self::add_capability();
		Database::add_tables();
		self::set_default_options();	
	}

	/**
	 * Adds the capability sul_manage to the Administrator role
	 * 
	 * The user needs this capability  for all admin functions of the plugin  
	 */
	private static function add_capability () {
		$role = get_role( 'administrator' );
		if ( ! empty( $role ) ) {
			$role->add_cap( 'sul_manage' );
		}
	}

	/**
	 * Stores default values for the options of this plugin.
	 * 
	 */
	private static function set_default_options() {
		if ( ! get_option( 'sul_general_admin' ) ) {
			$options = array (
				'listname' => 'Sign-up list',
				'max_entries' => '50',
				'signupmode' => 'anyone',
				'publicvisibility' => 'fullname',
				'style' => 'sul-style-1',
				'extra_label' => '',
				'duplicates_allowed' => 'no'
			);
			update_option( 'sul_general_admin', $options );
		}
	}
} // Class Activation
