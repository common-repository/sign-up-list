<?php 

namespace SUL;
//Prevent direct requests
defined( 'ABSPATH' ) || exit;

/**
 * Contains functions for creating menu items in the administration ui.
 * 
 * Public methods are statically invoked.
 */
class Menu {

    /**
     * Creates one top-level menu with to submenu items
     * 
     * For this plug-in a seperate top-level menu is added to the admin ui.
     * The top-level item is also the first submenu item.
     * 
     */
    public static function create_menu () {
        //create custom top-level menu
       add_menu_page ( __( 'Sign-up List', 'sign-up-list' ) , __( 'Sign-up List', 'sign-up-list' ) , 
            'sul_manage' , 'sul-entries' , 'SUL\\Admin::list_admin', 'dashicons-list-view' , 99);

        //create submenu items
        add_submenu_page ( 'sul-entries' , __( 'Invitees', 'sign-up-list' ) , 
            __( 'Invitees', 'sign-up-list' ), 'sul_manage', 
            'sul-invitees', 'SUL\\Admin::invitees_admin');
    
        add_submenu_page ( 'sul-entries' , __( 'General Settings', 'sign-up-list' ) , 
            __( 'General Settings', 'sign-up-list' ), 'sul_manage', 
            'sul-general-settings', 'SUL\\Admin::general_admin');        
    }
}
?>