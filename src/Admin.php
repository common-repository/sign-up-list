<?php

namespace SUL;

//Prevent direct requests
defined( 'ABSPATH' ) || exit;

/**
 * Class to implement the administrator functions.
 * 
 * This class contains controller functions, helper functions and functions that 
 * invoke the Settings API.
 * 
 * It relies on include files that render most of the HTML. These lines of codes are separated
 * to reduce the size of this file and keep a clear overview.
 * 
 * It also relies on two implementations of WP_List_Table for the listing of entries and invitees.
 * Public methods are statically invoked.
 * 
 */

class Admin {
    /**
     * This version of the plug-in is designed for max 100 entries. There is no pagination or search 
     * mechanism in the front-end, which is the main reason to restrict the list size.
     */
    const MAX_ENTRIES = 200;

    /**
     * Returns new value over old value (over empty string).
     * 
     * @param string $key The array key for which the value needs to be returned.
     * @param array $new Array with new values in which an element with the key may be present.
     * @param array $original Array with orginal values in which an element with the key may be present.
     * @param boolean $empty_allowed Whether the new value is allowed to be empty or not
     * 
     * @return string The value that is the result of the input and the rules. 
     * 
     */
    private static function safe_value( $key, $new, $original = array (), $empty_allowed = true ) {
        //Get the original value if it exists
        $original_value = array_key_exists( $key, $original ) ? $original[ $key ] : ''; 
        
        if ( $empty_allowed ) {
            //New value takes precedence if it exists, otherwise return original value
            return esc_attr( array_key_exists( $key, $new ) ? $new[ $key ] : $original_value );
        } else {
            return esc_attr( ( array_key_exists( $key, $new ) && ! empty( $new[ $key ] ) )
                                ? $new[ $key ] : $original_value );            
        }
    }
    
    /**
     * Function to control all actions related to administrating list entries.
     * 
     * This function implements the viewing of list entries using a WP_List_Table.
     * It implements viewing the entries as a list with pagination and search functions.
     * It implements add, edit and delete including the associated screens.
     * It implements a bulk delete for entries.
     * 
     * @global string $_GET['action'] The action specifies which function to call within the controller.
     * 
     */
    public static function list_admin() {
        if ( current_user_can( 'sul_manage' ) ) {
            require_once SUL_DIR . 'src/EntriesListTable.php';
            $table = new Entries_List_Table();
        
            /* Check the action in the GET parameter and act accordingly*/
            if  ( isset ($_GET['action'] ) ) {
                $action = sanitize_text_field( $_GET['action'] );
                switch ( $action ) {

                    //ADD an entry
                    case 'add':
                        $saved = false;
                        $data = array();
                        $message = '';

                        //Validate and save if we have post data
                        if ( isset( $_POST['sul_entries_add_nonce']) ) {
                            //Validate nonce
                            if ( ! wp_verify_nonce( sanitize_text_field( $_POST['sul_entries_add_nonce'] ), 'sul_entries_add')) {
                                wp_die( 'Your nonce could not be verified' );
                            }
                            //Sanitize fields and load in data array
                            $data['firstname'] = isset( $_POST['firstname'] ) ? 
                                sanitize_text_field( $_POST['firstname'] ) : '';
                            $data['lastname'] = isset( $_POST['lastname'] ) ? 
                                sanitize_text_field( $_POST['lastname'] ) : '';
                            $data['email'] = isset( $_POST['email'] ) ? 
                                sanitize_email( $_POST['email'] ) : '';
                            $data['extra_1'] = isset( $_POST['extra_1'] ) ? 
                                sanitize_text_field( $_POST['extra_1'] ) : '';
                            
                            //Validate fields
                            $message = Database::validate_new_entry( $data ); 
                            
                            //No message means success so  we save the entry, else we provide an error message
                            if ( $message === '') {
                                $id = Database::add_entry( $data );
                                //Handle any database error
                                if ( $id === false ) {
                                    wp_die( 'Database error when adding new entry' );
                                } else {
                                //Successful save
                                    $saved = true;
                                }
                            } 
                        }
                        //Render the view including the form
                        require_once SUL_DIR . 'src/AdminViewAddEntry.php';
                        break;

                    //EDIT
                    case 'edit':
                        $saved = false;
                        $data = array();
                        $message = '';

                        //Validate and save if we have post data
                        if ( isset( $_POST['sul_entries_edit_nonce']) ) {
                            //Validate nonce
                            if ( ! wp_verify_nonce( sanitize_text_field ( $_POST['sul_entries_edit_nonce'] ), 'sul_entries_edit')) {
                                wp_die( 'Your nonce could not be verified' );
                            }
                            //Sanitize fields and load in data array
                            $data['id'] = isset( $_GET['id'] ) ? 
                                absint( $_GET['id'] ) : 0;
                            $data['firstname'] = isset( $_POST['firstname'] ) ? 
                                sanitize_text_field( $_POST['firstname'] ) : '';
                            $data['lastname'] = isset( $_POST['lastname'] ) ? 
                                sanitize_text_field( $_POST['lastname'] ) : '';
                            $data['email'] = isset( $_POST['email'] ) ? 
                                sanitize_email( $_POST['email'] ) : '';
                            $data['extra_1'] = isset( $_POST['extra_1'] ) ? 
                                sanitize_text_field( $_POST['extra_1'] ) : '';
                            
                            //Validate fields for an update (second parameter true)
                            $message = Database::validate_new_entry($data, true); 
                            
                            //No message means success so we save the entry, else we provide an error message
                            if ( $message === '') {
                                $number_of_cols = Database::update_entry($data);

                                //Handle any database error
                                if ( $number_of_cols === false ) {
                                    wp_die( 'Database error when updating entry' );
                                } else {
                                    //Successful save
                                    $saved = true;
                                }
                            } 
                        }

                        // If there has been no data saved, we provide additional checks and load entry
                        if ( ! $saved ) {
                            $entry = NULL;

                            //Check for an id in the query string
                            if  ( isset ( $_GET['id'] ) ) {
                                $get_id = absint( $_GET['id'] );
                                $entry = Database::get_entry( $get_id );
                            } else {
                                wp_die ( 'No id provided' );
                            }

                            //If id is invalid we stop, otherwise continue
                            if ( empty( $entry ) ) {
                                wp_die ( 'Invalid id' );
                            } 
                        }
                        //Render the view including the form
                        require_once SUL_DIR . 'src/AdminViewEditEntry.php';
                        break;

                    //DELETE
                    case 'delete':
                        $deleted = false;
                        $message = '';
                        
                        if ( isset( $_POST['sul_entries_delete_nonce']) ) {
                            //Validate nonce
                            if ( ! wp_verify_nonce( sanitize_text_field( $_POST['sul_entries_delete_nonce'] ), 'sul_entries_delete')) {
                                wp_die( 'Your nonce could not be verified' );
                            }
                            $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
                            
                            if ( Database::entry_exists( $id ) ) {
                                $number_of_rows = Database::delete_entry( $id );
                                
                                //Handle any database error
                                if ( $number_of_rows == 0 ) {
                                    wp_die( 'Database error when deleting entry' );
                                } else {
                                //Successful deletion
                                    $deleted = true;
                                }
                            } // Entry found
                        } // Nonce set

                        // If there has been no deletion,perform additional checks and load entry
                        if ( ! $deleted ) {
                            $entry = NULL;

                            //Check for an id in the query string
                            if  ( isset ( $_GET['id'] ) ) {
                                $get_id = absint( $_GET['id'] );
                                $entry = Database::get_entry( $get_id );
                            } else {
                                wp_die ( 'No id provided' );
                            }

                            //If id is invalid we stop, otherwise continue
                            if ( empty( $entry ) ) {
                                wp_die ( 'Invalid id' );
                            }
                        }    
                        //Render the view including the form
                        require_once SUL_DIR . 'src/AdminViewDeleteEntry.php';
                        break;
                    case 'reset':
                        $reset = false;
                        $message = '';
                        if ( isset( $_POST['sul_entries_reset_nonce']) ) {
                            //Validate nonce
                            if ( ! wp_verify_nonce( sanitize_text_field( $_POST['sul_entries_reset_nonce'] ), 'sul_entries_reset')) {
                                wp_die( 'Your nonce could not be verified' );
                            }
                            
                            //Delete all entries if checkbox is checked
                            $delete_all_entries = isset( $_POST['delete_all_entries'] ) ? 
                                                ( absint( $_POST['delete_all_entries'] ) === 1 ) 
                                                : false;
                            if ( $delete_all_entries ) {
                                $entries = Database::get_entries();
                                foreach ( $entries as $entry ) {
                                    $number_of_rows = Database::delete_entry( $entry['id'] );
                                
                                    //Handle any database error
                                    if ( $number_of_rows == 0 ) {
                                        wp_die( 'Database error when deleting entry' );
                                    } 
                                } // foreach
                            } // delete_all_entries
                            
                            $reset_special_link = isset( $_POST['reset_special_link'] ) ? 
                            ( absint( $_POST['reset_special_link'] ) === 1 ) 
                            : false;
                            if ( $reset_special_link ) {
                                $link_uid = uniqid();
						        update_option( 'sul_link_uid', $link_uid );
                            }

                            //Successful reset is the case
                            $reset = true;
                        } // nonce
                        
                        //Render the view including the form
                        require_once SUL_DIR . 'src/AdminViewResetList.php';
                        break;
                    default:
                        _e('Invalid action', 'sign-up-list'); 
                }
            } else {
                //No action in the query string has been provided
                //We now check for bulk actions that need processing
                
                //Bulk action delete-selected
                if ( ! empty ( $_POST['delete_ids'] ) ) {
                    if ( isset( $_POST['sul_entries_delete_selected_nonce']) ) {
                        //Validate nonce
                        if ( ! wp_verify_nonce( sanitize_text_field( $_POST['sul_entries_delete_selected_nonce'] ), 'sul_entries_delete_selected')) {
                            wp_die( 'Your nonce could not be verified' );
                        }
                        $ids = $_POST['delete_ids'];
                        $deleted = 0;
                        foreach ( $ids as $id ) {
                            //Sanitize id's here
                            $id = absint ( $id );
                            if ( Database::entry_exists( $id ) ) {
                                $number_of_rows = Database::delete_entry( $id );
                                
                                //Handle any database error
                                if ( $number_of_rows == 0 ) {
                                    wp_die( 'Database error when deleting entry' );
                                } else {
                                $deleted = $deleted + 1;
                                } //Successful deletion
                            }
                        }
                    }
                }
            
                //After the bulk actions, we display the list of entries using WP Table
                require_once SUL_DIR . 'src/AdminViewListEntries.php';
            }
        } 
    } // List admin

    /**
     * Displays confirmation screen after checking permissions and ids.
     * 
     * @param array $ids The selected record id's that require deletion.
     *  
     */
    public static function delete_entries ( $ids ) {
        if ( current_user_can( 'sul_manage' ) ) {
            if ( ! empty ( $ids ) ) {
                //Display confirmation
                require_once SUL_DIR . 'src/AdminViewBulkDeleteEntries.php';
            }
        }
    }

    /**
     * Function to control all actions related to administrating invitees.
     * 
     * Invitees are uses when the sign-up mode is set to Only invitees.
     * In that case, a visitor can only sign up if the associated email address is on the list of 
     * invitees.
     * 
     * This function implements the viewing of invitees using a WP_List_Table.
     * It implements viewing the invitees as a list with pagination and search functions.
     * It implements add, edit and delete including the associated screens.
     * It implements a bulk delete for invitees.
     * 
     * @global string $_GET['action'] The action specifies which function to call within the controller.
     * 
     */

    public static function invitees_admin () {
        if ( current_user_can( 'sul_manage' ) ) {
            require_once SUL_DIR . 'src/InviteesListTable.php';
            $table = new Invitees_List_Table();
        
            /* Check the action in the GET parameter and act accordingly*/
            if  ( isset ( $_GET['action'] ) ) {
                $action = sanitize_text_field( $_GET['action'] );
                switch ( $action ) {

                    //ADD one invitee
                    case 'add':
                        $saved = false;
                        $data = array();
                        $message = '';

                        //Validate and save if we have post data
                        if ( isset( $_POST['sul_invitees_add_nonce']) ) {
                            //Validate nonce
                            if ( ! wp_verify_nonce( sanitize_text_field ( $_POST['sul_invitees_add_nonce'] ), 'sul_invitees_add')) {
                                wp_die( 'Your nonce could not be verified' );
                            }
                            //Sanitize fields and load in data array
                            $data['email'] = isset( $_POST['email'] ) ? 
                                sanitize_email( $_POST['email'] ) : '';
                            
                            //Validate fields
                            $message = Database::validate_new_invitee( $data ); 
                            
                            //No message means success so we save the invitee, else we provide an error message
                            if ( $message === '') {
                                $id = Database::add_invitee( $data );
                                //Handle any database error
                                if ( $id === false ) {
                                    wp_die( 'Database error when adding new invitee' );
                                } else {
                                //Successful save
                                    $saved = true;
                                }
                            } 
                        }
                        //Render the view including the form
                        require_once SUL_DIR . 'src/AdminViewAddInvitee.php';
                        break;
                    
                      //ADD multiple invitees in one form
                      case 'bulk-add':
                        $saved = false;
                        $bulkdata = array();
                        $message = '';

                        //Validate and save if we have post data
                        if ( isset( $_POST['sul_invitees_bulk_add_nonce']) ) {
                            //Validate nonce
                            if ( ! wp_verify_nonce( sanitize_text_field ( $_POST['sul_invitees_bulk_add_nonce'] ), 'sul_invitees_bulk_add')) {
                                wp_die( 'Your nonce could not be verified' );
                            }
                            //Load form input in bulkdata array and do a first sanitization
                            $bulkdata = isset( $_POST['emails'] ) ? 
                                               explode( "\r\n", 
                                               sanitize_textarea_field( $_POST['emails'] ) )
                                               : array ();
                            
                            //Remove empty elements
                            $bulkdata = array_filter( $bulkdata );
                            
                            //Validate the invitees and sanitize for email
                            foreach ($bulkdata as $data) { 
                                $clean_data = sanitize_email( $data );
                                $message = Database::validate_new_invitee( array( 'email' => $clean_data ) ); 
                                if ( $message !== '') {
                                    $message .= ' ('.$data.')';
                                    // Break the loop at the first validation message
                                    break;
                                } 
                            }

                            //No message means success so we save the invitees
                            if ( $message === '') {
                                foreach ($bulkdata as $data) {
                                    $data = sanitize_email( $data );
                                    $id = Database::add_invitee( array( 'email' => $data ) );
                                    //Handle any database error
                                    if ( $id === false ) {
                                        wp_die( 'Database error when adding new invitee' );
                                    }
                                } 
                                $saved = true;
                            }
                        }
                        //Render the view including the form
                        require_once SUL_DIR . 'src/AdminViewBulkAddInvitees.php';
                        break;

                    //EDIT
                    case 'edit':
                        $saved = false;
                        $data = array();
                        $message = '';

                        //Validate and save if we have post data
                        if ( isset( $_POST['sul_invitees_edit_nonce']) ) {
                            //Validate nonce
                            if ( ! wp_verify_nonce( sanitize_text_field( $_POST['sul_invitees_edit_nonce'] ), 'sul_invitees_edit')) {
                                wp_die( 'Your nonce could not be verified' );
                            }
                            //Sanitize fields and load in data array
                            $data['id'] = isset( $_GET['id'] ) ? 
                                absint( $_GET['id'] ) : 0;
                            $data['email'] = isset( $_POST['email'] ) ? 
                                sanitize_email( $_POST['email'] ) : '';
                            
                            //Validate fields for an update (second parameter true)
                            $message = Database::validate_new_invitee( $data, true ); 
                            
                            //No message means success so we save the invitee, else we provide an error message
                            if ( $message === '') {
                                $number_of_cols = Database::update_invitee( $data );

                                //Handle any database error
                                if ( $number_of_cols === false ) {
                                    wp_die( 'Database error when updating invitee' );
                                } else {
                                    //Successful save
                                    $saved = true;
                                }
                            } 
                        }

                        // If there has been no data saved, we provide additional checks and load invitee
                        if ( ! $saved ) {
                            $invitee = NULL;

                            //Check for an id in the query string
                            if  ( isset ($_GET['id'] ) ) {
                                $get_id = absint($_GET['id']);
                                $invitee = Database::get_invitee( $get_id );
                            } else {
                                wp_die ( 'No id provided' );
                            }

                            //If id is invalid we stop, otherwise continue
                            if ( empty( $invitee ) ) {
                                wp_die ( 'Invalid id' );
                            } 
                        }
                        //Render the view including the form
                        require_once SUL_DIR . 'src/AdminViewEditInvitee.php';
                        break;

                    //DELETE
                    case 'delete':
                        $deleted = false;
                        $message = '';
                        
                        if ( isset( $_POST['sul_invitees_delete_nonce']) ) {
                            //Validate nonce
                            if ( ! wp_verify_nonce( sanitize_text_field( $_POST['sul_invitees_delete_nonce'] ), 'sul_invitees_delete')) {
                                wp_die( 'Your nonce could not be verified' );
                            }
                            $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
                            
                            if ( Database::invitee_exists( $id ) ) {
                                $number_of_rows = Database::delete_invitee( $id );
                                
                                //Handle any database error
                                if ( $number_of_rows == 0 ) {
                                    wp_die( 'Database error when deleting invitee' );
                                } else {
                                //Successful deletion
                                    $deleted = true;
                                }
                            } // Invitee found
                        } // Nonce set

                        // If there has been no deletion, perform additional checks and load invitee
                        if ( ! $deleted ) {
                            $invitee = NULL;

                            //Check for an id in the query string
                            if  ( isset ( $_GET['id'] ) ) {
                                $get_id = absint( $_GET['id'] );
                                $invitee = Database::get_invitee( $get_id );
                            } else {
                                wp_die ( 'No id provided' );
                            }

                            //If id is invalid we stop, otherwise continue
                            if ( empty( $invitee ) ) {
                                wp_die ( 'Invalid id' );
                            }
                        }    
                        //Render the view including the form
                        require_once SUL_DIR . 'src/AdminViewDeleteInvitee.php';
                        break;
                    default:
                        _e('Invalid action', 'sign-up-list'); 
                }
            } else {
                //No action in the query string has been provided
                //We now check for bulk actions that need processing
                
                //Bulk action delete-selected
                if ( ! empty ( $_POST['delete_ids'] ) ) {
                    if ( isset( $_POST['sul_invitees_delete_selected_nonce']) ) {
                        //Validate nonce
                        if ( ! wp_verify_nonce( sanitize_text_field( $_POST['sul_invitees_delete_selected_nonce'] ), 'sul_invitees_delete_selected')) {
                            wp_die( 'Your nonce could not be verified' );
                        }
                        $ids =$_POST['delete_ids'];
                        $deleted = 0;
                        foreach ( $ids as $id ) {
                            //Sanitize id's here
                            $id = absint ( $id );
                            if ( Database::invitee_exists( $id ) ) {
                                $number_of_rows = Database::delete_invitee( $id );
                                
                                //Handle any database error
                                if ( $number_of_rows == 0 ) {
                                    wp_die( 'Database error when deleting invitee' );
                                } else {
                                $deleted = $deleted + 1;
                                } //Successful deletion
                            }
                        }
                    }
                }
            
                //After the bulk actions, we display the list of entries using WP Table
                require_once SUL_DIR . 'src/AdminViewListInvitees.php';
            }
        } 
    }

    /**
     * Displays confirmation screen after checking permissions and ids.
     * 
     * @param array $ids The selected record id's that require deletion.
     *  
     */
    public static function delete_invitees ( $ids ) {
        if ( current_user_can( 'sul_manage' ) ) {
            if ( ! empty ( $ids ) ) {
            //Display confirmation form
            require_once SUL_DIR . 'src/AdminViewBulkDeleteInvitees.php';
            }
        }
    }

    /**
     * Displays the settings screen with the options for this plug-in.
     * 
     */
    public static function general_admin () {
       if ( current_user_can( 'sul_manage' ) ) {
        ?>
        <div class="wrap">
            <?php 
            settings_errors( 'sul_general_admin' );
            ?>
            <h1><?php echo esc_html( __('Sign-up List - General Settings', 'sign-up-list') ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'sul_general_admin' );
                do_settings_sections( 'sul-general-settings' );
                submit_button( __( 'Save changes', 'sign-up-list' ), 'primary' );
                ?>
            </form>
        </div>
    <?php
       }
    }

    /**
     * Prepares the settings screen.
     * 
     * This function is based on the Settings API, which allows admin pages containing settings 
     * forms to be managed semi-automatically. It lets you define settings pages, sections within 
     * those pages and fields within the sections.
     * Registers sanitization callback, the setting, the setting section (one) and all settings 
     * fields.
     * 
     */
    public static function general_admin_init () {
        $args = array (
            'type' => 'string',
            'sanitize_callback' => 'SUL\\Admin::general_admin_sanitize',
            'default' => NULL
        );
        
        register_setting( 'sul_general_admin' , 'sul_general_admin' , $args );
        
        add_settings_section ( 'sul_general_admin_section',                 //HTML ID tag
                                __( 'List', 'sign-up-list' ),               //Title displayed
                                'SUL\\Admin::general_admin_section_text',   //Callback to echo additional explanation
                                'sul-general-settings' );                   //Page name
        
        //Create a field for the listname
        add_settings_field ( 'sul_general_admin_listname',                  //HTML ID tag
                             __('List name', 'sign-up-list' ),              //Field label
                             'SUL\\Admin::general_admin_listname',          //Callback to render field
                             'sul-general-settings',                        //Page name
                             'sul_general_admin_section');                  //Section name

        //Create a field for maxiumum entries
        add_settings_field ( 'sul_general_admin_max_entries',                //HTML ID tag
                             __('Maximum entries', 'sign-up-list' ),         //Field label
                             'SUL\\Admin::general_admin_max_entries',        //Callback to render field
                             'sul-general-settings',                        //Page name
                             'sul_general_admin_section');                  //Section name

        //Create a field for duplicates allowed
        add_settings_field ( 'sul_general_admin_duplicates_allowed',         //HTML ID tag
                             __( 'Duplicates allowed', 'sign-up-list' ),      //Field label
                             'SUL\\Admin::general_admin_duplicates_allowed', //Callback to render field
                             'sul-general-settings',                        //Page name
                             'sul_general_admin_section');                  //Section name
        
        //Create a field for the signupmode
        add_settings_field ( 'sul_general_admin_signupmode',                //HTML ID tag
                              __( 'Who can sign up', 'sign-up-list' ),       //Field label
                              'SUL\\Admin::general_admin_signupmode',       //Callback to render field
                              'sul-general-settings',                       //Page name
                              'sul_general_admin_section');                 //Section name
        
        //Create a field for the label of the extra info field
        add_settings_field ( 'sul_general_admin_extra_label',                //HTML ID tag
                             __('Label for extra info field (empty = hidden)', 'sign-up-list' ),    //Field label
                             'SUL\\Admin::general_admin_extra_label',        //Callback to render field
                             'sul-general-settings',                        //Page name
                             'sul_general_admin_section');                  //Section name

        //Create a field to trigger public visibility of list entries
        add_settings_field ( 'sul_general_admin_publicvisibility',           //HTML ID tag
                             __( 'List entries public?', 'sign-up-list' ),   //Field label
                             'SUL\\Admin::general_admin_publicvisibility',   //Callback to render field
                             'sul-general-settings',                         //Page name
                             'sul_general_admin_section');                   //Section name

        //Create a field to change the styling of the sign-up list blocks
        add_settings_field ( 'sul_general_admin_style',                      //HTML ID tag
                             __( 'Style', 'sign-up-list' ),                  //Field label
                             'SUL\\Admin::general_admin_style',              //Callback to render field
                             'sul-general-settings',                         //Page name
                             'sul_general_admin_section');                   //Section name
    }

    /**
     * Callback function to display introductory text for this page.
     * 
     */
    public static function general_admin_section_text() {
       echo '<p>'.esc_html(__( 'Change the settings of the sign-up list here', 'sign-up-list' ) ).'</p>';
    }

    /**
     * Callback function to display the listname setting.
     * 
     */
    public static function general_admin_listname() {
        //get option from the database
        $options = get_option( 'sul_general_admin' );
        $listname = $options['listname'];

        //echo the field
        echo "<input id='listname' name='sul_general_admin[listname]' type='text' 
        value = '" . esc_attr( $listname ) . "' />";
    }

    /**
     * Callback function to display the max_entries setting.
     * 
     */
    public static function general_admin_max_entries() {
        //get option from the database
        $options = get_option( 'sul_general_admin' );
        $max_entries = $options['max_entries'];

        //echo the field
        echo "<input id='max_entries' name='sul_general_admin[max_entries]' type='text' size='5' 
        value = '" . esc_attr( $max_entries ) . "' />";
    }

    /**
     * Callback function to display the duplicates_allowed setting.
     * 
     */
    public static function general_admin_duplicates_allowed() {
        
        //get option from the database
        $options = get_option( 'sul_general_admin' );
        $duplicates_allowed = $options['duplicates_allowed'];
        $modes = array ( 'no' => __( 'No, unique email addresses only', 'sign-up-list' ), 
                         'yes' => __( 'Yes, duplicate email adressess allowed', 'sign-up-list'), 
                        );
                         
        //initiate the dropdown field and mark the current value as selected
        echo "<select id='duplicates_allowed' name='sul_general_admin[duplicates_allowed]'>";
        foreach ( $modes as $mode => $description ) {
            echo "<option value='".esc_attr( $mode )."' "
                 .selected( $duplicates_allowed, $mode, false ).">"
                 .esc_html( $description )."</option>";
        }
        echo "</select>";
    }

    /**
     * Callback function to display the extra_label setting.
     * 
     */
    public static function general_admin_extra_label() {
        //get option from the database
        $options = get_option( 'sul_general_admin' );
        $extra_label = $options['extra_label'];

        //echo the field
        echo "<input id='extra_label' name='sul_general_admin[extra_label]' type='text' 
        value = '" . esc_attr( $extra_label ) . "' />";
    }
    
    /**
     * Callback function to display the signupmode setting.
     * 
     * @global WP_Rewrite $wp_rewrite Used to check whether permalinks are used.
     */
    public static function general_admin_signupmode() {      
        global $wp_rewrite;
        
        //get option from the database
        $options = get_option( 'sul_general_admin' );
        $signupmode = $options['signupmode'];
        $modes = array ( 'anyone' => __( 'Anyone (with CAPTCHA)', 'sign-up-list' ), 
                         'email' => __( 'Only invitees (see Invitees)', 'sign-up-list'), 
                         'link' => __( 'Anyone with the special link', 'sign-up-list' ) 
                        );
                         
        //initiate the dropdown field and mark the current value as selected
        echo "<select id='signupmode' name='sul_general_admin[signupmode]'>";
        foreach ( $modes as $mode => $description ) {
            echo "<option value='".esc_attr( $mode )."' "
                 .selected( $signupmode, $mode, false ).">"
                 .esc_html( $description )."</option>";
        }
        echo "</select>";
        if ( $signupmode == 'link' ) {
            if ( get_option( 'sul_link_uid' ) ){
                $link_uid = get_option( 'sul_link_uid' );
            } else {
                $link_uid = uniqid();
                update_option( 'sul_link_uid', $link_uid );
            }
            $separator = $wp_rewrite->using_permalinks() ? '?' : '&';
            echo '<p>Add this code to the end of the URL of the public sign-up form<br/><strong>'.
                 $separator.'uid='.esc_html( $link_uid ).'</strong></p>';
        }
    }

    /**
     * Callback function to display the publicvisibility setting.
     * 
     */
    public static function general_admin_publicvisibility() {
        //get option from the database
        $options = get_option( 'sul_general_admin' );
        $publicvisibility = $options['publicvisibility'];
        $modes = array ( 'firstname' => __( 'First name', 'sign-up-list' ),
                         'fullname' => __( 'Full name', 'sign-up-list' ), 
                         'firstname_extra' => __( 'First name + extra info', 'sign-up-list' ),
                         'fullname_extra' => __( 'Full name + extra info', 'sign-up-list' ),
                         'invisible' => __( 'Nothing visible', 'sign-up-list' )
                       );
        
        $examples = array (
            'firstname' => __( 'Mary', 'sign-up-list' ),
            'fullname' => __( 'Mary Smith', 'sign-up-list' ),
            'firstname_extra' => __( 'Mary (some extra information)', 'sign-up-list' ),
            'fullname_extra' => __( 'Mary Smith (some extra information)', 'sign-up-list' ),
            'invisible' => ''
        );

        foreach ( $modes as $mode => $description ) {
            echo "<label><input id='publicvisibility' name='sul_general_admin[publicvisibility]' 
                  value='".esc_attr( $mode )."' ".checked( $publicvisibility, $mode, false )
                  ."type='radio' />".esc_html( $description )."</label><p><em>".esc_html( $examples[ $mode ] )."</em></p><br/>";
        }
    }

    /**
     * Callback function to display the style setting.
     * 
     */
    public static function general_admin_style() {
        //get option from the database
        $options = get_option( 'sul_general_admin' );
        $style = $options['style'];
        $style_options = array ( 'sul-style-1' => __( 'Numbers, borders and dotted lines', 'sign-up-list' ),
                                 'min-style'   => __( 'Minimal style, let your theme decide the looks', 'sign-up-list' )
                                );

        foreach ( $style_options as $style_option => $description ) {
            // SUL_URL is defined in the main plugin file as plugin_dir_url( __FILE__ ).
            $img_path = SUL_URL.'admin/'.$style_option.'.png?v='.SUL_VERSION;
            echo "<p><label><input id='style' name='sul_general_admin[style]' 
                  value='".esc_attr( $style_option )."' ".checked( $style, $style_option, false )
                  ."type='radio' />".esc_html( $description )."</label><br/>
                  <img src='".esc_attr( $img_path )."' /></p>";
        }
    }

    /**
     * Callback function to sanitize settings after these have been submitted.
     * 
     * @param array $input Submitted setting values as key => value
     * 
     * @return array $valid Sanitized setting values as key => value
     */
    public static function general_admin_sanitize ( $input ) {    
        $valid = array();

        $valid['listname'] = sanitize_text_field( $input['listname'] );
        if ( $valid['listname'] !== $input['listname'] ) {
            add_settings_error(
                'sul_general_admin',
                'sul_general_admin_listname_invalid_characters', 
                'Invalid characters have been removed from the List name.',
                'update'
            );
        }
        
        $valid['signupmode'] = sanitize_text_field( $input['signupmode'] );
        
        $valid['publicvisibility'] = sanitize_text_field( $input['publicvisibility'] );

        $valid['style'] = sanitize_text_field( $input['style'] );

        $valid['max_entries'] = min( self::MAX_ENTRIES, max( 1, absint( $input['max_entries'] ) ) );
        if ( $valid['max_entries'] != $input['max_entries'] ) {
            add_settings_error(
                'sul_general_admin',
                'sul_general_admin_max_entries_invalid_value', 
                'Maximum entries adjusted, range 1 to 200.',
                'update'
            );
        }

        $valid['extra_label'] = sanitize_text_field( $input['extra_label'] );
        if ( $valid['extra_label'] != $input['extra_label'] ) {
            add_settings_error(
                'sul_general_admin',
                'sul_general_admin_extra_label_invalid_value', 
                'Invalid characters have been removed from the Label field.',
                'update'
            );
        }

        $valid['duplicates_allowed'] = sanitize_text_field( $input['duplicates_allowed'] );
        
        return $valid;
    }
}