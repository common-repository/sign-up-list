<?php

namespace SUL;
//Prevent direct requests
defined( 'ABSPATH' ) || exit;
/**
 * Contains functions to expose plugin functionality via the WP REST API.
 * 
 * Public methods are statically invoked.
 */

class API {
    const PLUGIN_NAME = 'sign-up-list';
    const PLUGIN_VERSION = 'v1';

    public static function create_endpoints() {
        
        /**
         * Registers the REST route to list entries
         * 
         */
        register_rest_route ( self::PLUGIN_NAME.'/'.self::PLUGIN_VERSION, '/entries', 
            array (
                'methods'  => 'GET',
                'callback' => 'SUL\\API::get_entries'
            ) 
        );

        /**
         * Registers the REST route to add an entry
         */
        register_rest_route ( self::PLUGIN_NAME.'/'.self::PLUGIN_VERSION, '/entries/add', 
            array (
                'methods'  => 'POST',
                'callback' => 'SUL\\API::add_entry'
            ) 
        ); 
    }

    /**
     * Callback function to retrieve entries to be sent via the REST API
     * 
     * The results of this functions depend on the value for the option publicvisibility.
     * 
     * @return array Contains metadata about the entries and the entries according to 
     * publicvisibility.
     */
    public static function get_entries() {

        $entries = Database::get_entries();
        $options = get_option( 'sul_general_admin' );
        
        $metadata = array ();
        $metadata['entries_count'] = count($entries);
        $entries_left = max( ( $options['max_entries'] - $metadata['entries_count'] ), 0 );
        $metadata['entries_left'] = $entries_left;
        switch( $entries_left ) {
            case 0:
                $metadata['footer'] = __( 'There are no spaces left', 'sign-up-list' );
                break;
            case 1:
                $metadata['footer'] = __( 'Only', 'sign-up-list' ).' '.$entries_left.' '. __('space left', 'sign-up-list');
                break;
            default:
                $metadata['footer'] = $entries_left.' '.__( 'spaces left', 'sign-up-list' );
        }

        $result = array ();

        if ( $options['publicvisibility'] == 'firstname' ) {
            foreach ( $entries as $entry ) {
                $result[] = $entry['firstname'];
            } 
        }

        if ( $options['publicvisibility'] == 'fullname' ) {
            foreach ( $entries as $entry ) {
                $result[] = $entry['firstname'].' '.$entry['lastname'];
            } 
        }

        if ( $options['publicvisibility'] == 'fullname_extra' ) {
            foreach ( $entries as $entry ) {
                if ( empty( $entry['extra_1'] ) ) {
                    $result[] = $entry['firstname'].' '.$entry['lastname'];
                } else {
                    $result[] = $entry['firstname'].' '.$entry['lastname'].' ('.$entry['extra_1'].')';
                }
            }
        }

        if ( $options['publicvisibility'] == 'firstname_extra' ) {
            foreach ( $entries as $entry ) {
                if ( empty( $entry['extra_1'] ) ) {
                    $result[] = $entry['firstname'];
                } else {
                    $result[] = $entry['firstname'].' ('.$entry['extra_1'].')';
                }
            }
        }

        return array ($metadata, $result);
    }
    
    /**
     * Function to add an entry as submitted via a form by an anonymous visitor.
     * 
     * The function performs checks depending on the signupmode.
     * Anyone: check CAPTCHA.
     * Link: check UID in Request.
     * Email: check if the email address is on the invitation list (invitees).
     * 
     * There is a standard check for wp_nonce.
     * There is as standard check for a full list.
     * There optional check for duplicate email addresses is contained in validate_new_entry.
     * There is a standard check for valid data values.
     * 
     * @param WP_REST_Request $request Contains the POST request data in an object.
     * 
     * @return WP_Error Error messages and codes are returned as objects of class WP_Error.
     * @return array If the addition is successfull, an array is returned which contains the record id
     * of the added entry.
     */
    public static function add_entry( $request ) {
        $options = get_option( 'sul_general_admin' );
		$signupmode = $options['signupmode'];

        // If anyone can sign up: check the CAPTCHA
        if ( $signupmode == 'anyone' ) {
            if ( ! empty ( $request->get_param( 'securitycode' ) ) && 
                 ! empty ( $request->get_param( 'securityhash' ) ) )  {
                $securitycode_input = sanitize_text_field( $request->get_param('securitycode') );
                $securityhash_input = sanitize_text_field( $request->get_param( 'securityhash' ) );
                if ( hash( 'sha256', $securitycode_input ) !=  $securityhash_input ) {
                    return new \WP_Error( 'invalid captcha', 
                                          esc_html( __( 'The entered characters do not match with the image.', 
                                                        'sign-up-list' ) ), 
                                                        array( 'status' => '403' ) );
                }
            } else {
                return new \WP_Error( 'no captcha', 
                                      esc_html( __( 'Please enter the characters in the image.', 'sign-up-list' ) ), 
                                      array( 'status' => '403' ) );
            }
        }

        // With link only: uid must exist and match, otherwise return error
        if ( $signupmode == 'link' ) {
            // Check if the uid exist
            if ( empty ( $request->get_param('uid') ) || ( ! get_option( 'sul_link_uid' ) ) )  {
                return new \WP_Error( 'invalid link', 
                                      esc_html( __( 'Sorry, you cannot sign up. The link is invalid.', 'sign-up-list' ) ), 
                                      array( 'status' => '403' ) );       
            }
            
            // Check uid match
            $uid_input = sanitize_text_field( $request->get_param('uid') ); 
            if ( $uid_input != get_option( 'sul_link_uid' ) ) {
                return new \WP_Error( 'invalid link', 
                                      esc_html( __( 'Sorry, you cannot sign up. The link is invalid.', 'sign-up-list' ) ),
                                      array( 'status' => '403' ) );
            }
        }
        
        // Invited email adressess only
        if ( $signupmode == 'email' ) {
            $email = sanitize_email( $request->get_param('email') );
            if ( ! Database::invitee_exists_by_email( $email ) ) {
                return new \WP_Error( 'not invited', 
                                      esc_html( __( 'You are not on the invitation list', 'sign-up-list' ) ), 
                                      array( 'status' => '403' ) );                   
            }
        } 
        
        //Check nonce
        if ( ! empty( $request->get_param( 'sul_sign_up_nonce' ) ) ) {       
            
            /** The user is not set in the API, so we must set it for logged in users 
            *   so that the nonce (which was generated for that user) can be verified
            *   Sanitization of the user param performed by absint()
            **/
            if ( absint( $request->get_param( 'user' ) ) > 0 ) {
                wp_set_current_user( absint( $request->get_param( 'user' ) ) );
            }

            if ( ! wp_verify_nonce( sanitize_text_field ( $request->get_param( 'sul_sign_up_nonce' ) ), 
                                    'sul_sign_up')) {
                return new \WP_Error( 'invalid_nonce', 
                                      esc_html( __( 'Invalid nonce', 'sign-up-list' ) ), 
                                      array( 'status' => '403' ) );
            }
        } else {
            return new \WP_Error( 'nonce_missing', 
                                  esc_html( __( 'Nonce missing', 'sign-up-list' ) ), 
                                  array( 'status' => '403' ) );
        }
        wp_set_current_user( null );

        //Check if the list is already full
        $entries = Database::get_entries();
        if ( ( $options['max_entries'] - count( $entries ) ) <= 0 ) {
            return new \WP_Error( 'list_full', 
                                   esc_html(__('Sorry, the list is full. It is no longer possible to sign up.') ), 
                                   array( 'status' => '400' ) );
        }

        //Sanitize
        //$all_data = array_map ( 'sanitize_text_field', $request->get_params() );
        
        $data['firstname'] = sanitize_text_field( $request->get_param( 'firstname' ) );
        $data['lastname'] = sanitize_text_field ( $request->get_param( 'lastname' ) );
        $data['email'] = sanitize_email ( $request->get_param( 'email' ) );
        $data['extra_1'] = sanitize_text_field( $request->get_param('extra_1') );

        //Validate
        $message = Database::validate_new_entry( $data );
        
        if ( $message === '') {
            $id = Database::add_entry( $data );
            if ( $id === false ) {
                return new \WP_Error( 'database_error', 
                                      esc_html( __( 'Database error when adding new entry', 'sign-up-list' ) ), 
                                      array( 'status' => '500' ) );
            } else {
                return array( 'id' => $id );
            }
        } else {
            return new \WP_Error( 'validation_error', 
                                  esc_html ( $message ), 
                                  array( 'status' => '400' ) );
        }
    }
}
?>