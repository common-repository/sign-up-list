<?php
/**
 * Plugin Name:       Sign-up List
 * Plugin URI:        https://wordpress.org/sign-up-list
 * Description:       Customizable sign-up lists for events.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.3
 * Author:            De Internet Managers
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sign-up-list
 * Domain Path:       /public/lang
 */

/*
 Copyright (C)2023 Robin Lopulalan
 
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
namespace SUL; 
//Prevent direct requests
defined( 'ABSPATH' ) || exit;

/*
* Helper constants
*/
 define( 'SUL_DIR' , plugin_dir_path(__FILE__) );
 define( 'SUL_URL' , plugin_dir_url(__FILE__) );
 define( 'SUL_VERSION', '1.0.0');

/*
* Set the database version in a global
* Load the database include
* Apply database updates if relevant
*/
$sul_db_version = '1.0.0';
require_once SUL_DIR . 'src/Database.php';
add_action( 'plugins_loaded', 'SUL\Database::update_tables' );

/*
* Load text domain for i18n and l10n 
*/
add_action( 'init', 'SUL\\loadTextdomain');

/**
 * Trigger function for activation of plugin
 */
register_activation_hook( __FILE__ , function () {
    require_once SUL_DIR . 'src/Activation.php';
    Activation::activate();
});

/**
 * Trigger function for deactivation of plugin
 */
register_deactivation_hook( __FILE__ , function () {
    require_once SUL_DIR . 'src/Deactivation.php';
    Deactivation::deactivate();
});

/**
 * Callback function to load translations
 */
function loadTextdomain() {
    load_plugin_textdomain ( 'sign-up-list', false, 'sign-up-list/languages' );
} 

/**
* Load menu and admin functions if it is a back-end page
* Add admin related actions to hooks 
*/
if (is_admin()) {
    require_once SUL_DIR . 'src/Menu.php';
    require_once SUL_DIR . 'src/Admin.php';
    require_once SUL_DIR . 'src/Export.php';
    add_action( 'admin_menu', 'SUL\\Menu::create_menu' );
    add_action( 'admin_init', 'SUL\\Admin::general_admin_init' );
    add_action( 'wp_ajax_csv_pull', 'SUL\\Export::export_csv' );
} 
/**
 * Else we are not in an admin page, so it is front-end or API.
 */
else {
    require_once SUL_DIR . 'src/API.php';
    add_action( 'rest_api_init', 'SUL\\API::create_endpoints' );
}

/**
 * Register Gutenberg blocks for the list and the sign-up form.
 */
function sul_entries_block_init() {
	register_block_type( __DIR__ . '/blocks/build/sul-entries/', 
                        array(
                              'render_callback' => 'SUL\\Shortcodes::render_entries_without_style',
                        ) );
    register_block_type( __DIR__ . '/blocks/build/sul-sign-up/', 
                        array(
                            'render_callback' => 'SUL\\Shortcodes::render_sign_up_without_style',
                        ) );
}
add_action( 'init', 'SUL\\sul_entries_block_init' );

/**
 * Register jQuery-based script to add an entry: public form -> REST API.
 */
function sul_register_scripts() {
    wp_register_script( 'sul-sign-up', SUL_URL.'public/js/sign-up.js',
        array( 'jquery' ), SUL_VERSION, false );
}
add_action( 'init', 'SUL\\sul_register_scripts');

function sul_enqueue_signup_script() {
    wp_enqueue_script( 'sul-sign-up' );
}
add_action( 'wp_enqueue_scripts', 'SUL\\sul_enqueue_signup_script');

/**
 * Add privacy-related functions.  
 */
require_once SUL_DIR . 'src/Privacy.php';
add_action( 'admin_init', 'SUL\\Privacy::add_privacy_policy_content' );
add_filter( 'wp_privacy_personal_data_exporters', 'SUL\\Privacy::register_user_data_exporters' );
add_filter( 'wp_privacy_personal_data_erasers', 'SUL\\Privacy::register_user_data_erasers' );

/**
 * Add shortcodes for users of the classic editor
 */
require_once SUL_DIR . 'src/Shortcodes.php';

add_action( 'init', 'SUL\\sul_register_shortcodes');

function sul_register_shortcodes() {
    add_shortcode( 'sul_entries', 'SUL\\Shortcodes::render_entries_with_style');
    add_shortcode( 'sul_sign_up', 'SUL\\Shortcodes::render_sign_up_with_style');
}
?>
