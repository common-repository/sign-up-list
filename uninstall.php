<?php 

namespace SUL;
//Prevent direct requests
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    wp_die( sprintf('%s should only be called when uninstalling the plugin.', __FILE__) );
    exit;   
}

//Remove the role
$role = get_role( 'administrator' );
    if ( ! empty( $role ) && $role->has_cap( 'sul_manage' ) ) {
			$role->remove_cap( 'sul_manage ');
	}

//Remove registered setting groups
unregister_setting( 'sul_general_admin', 'sul_general_admin' );

//Remove saved options from the database
delete_option( 'sul_general_admin' );
delete_option( 'sul_db_version' );

//Remove tables from database
require_once plugin_dir_path(__FILE__) . 'src/Database.php';
Database::delete_tables();
?>