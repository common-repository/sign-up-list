<?php 

namespace SUL;
//Prevent direct requests
defined( 'ABSPATH' ) || exit;

/**
 * Contains functions that implement action and filter hooks for privacy functions in WordPress
 * 
 * Add content to privacy policy guidance
 * Export personal data
 * Erase personal data
 * 
 */

class Privacy {

    /**
     * Add content to the guidance for a privacy policy statement.
     * 
     */
    public static function add_privacy_policy_content() {
        if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
            return;
        }
        $content = '<p class="privacy-policy-tutorial">' . __( 'Personal data processed by Sign-up List', 'sign-up-list' ) . '</p>'
                . '<strong class="privacy-policy-tutorial">' . __( 'Personal data processed by Sign-up List', 'sign-up-list' ) . '</strong> '
                .__( 'This web site uses the plug-in Sign-up List. When you sign up to this list as a visitor, your first name, last name and email address are stored in a database for as long as the list is active. Your data may be manually deleted by the administrator. Next to that, your email adress may be added by the administrator to a list of allowed persons to sign up. When the plugin is removed from this web site, all related data will be deleted including your personal data. If you request your data to be manually removed, please contact the administrator of this web site.', 'sign-up-list' );
        wp_add_privacy_policy_content( 'Sign-up List' , wp_kses_post( wpautop( $content, false ) ) );
    }

    /**
     * Export list records and invitee records for a user using the supplied email.
     *
     * @param string $email Email address to use for identification.
     *
     * @return array Records found.
     */
    public static function export_user_data_by_email( $email ) {
        $export_items = array();
        $entries = Database::get_entries( $email );
        foreach ( (array) $entries as $entry ) {
            $item_id = "Sign-up list record ".$entry['id'];
            $group_id = 'sign-up-list-entries';
            $group_label = __( 'Sign-up List records', 'sign-up-list' );
            $data = array(
                array(
                    'name'  => __( 'Record ID', 'sign-up-list' ),
                    'value' => $entry['id'],
                ),
                array(
                    'name'  => __( 'First name', 'sign-up-list' ),
                    'value' => $entry['firstname'],
                ),
                array(
                    'name'  => __( 'Last name', 'sign-up-list' ),
                    'value' => $entry['lastname'],
                ),
                array(
                    'name'  => __( 'Email address', 'sign-up-list' ),
                    'value' => $entry['email'],
                )
            );

            $export_items[] = array(
                'group_id'    => $group_id,
                'group_label' => $group_label,
                'item_id'     => $item_id,
                'data'        => $data,
            );
        }

        $invitees = Database::get_invitees( $email );
        foreach ( (array) $invitees as $invitee ) {
            $item_id = "Invitation list record ".$invitee['id'];
            $group_id = 'sign-up-list-invitees';
            $group_label = __( 'Sign-up List Invitation records', 'sign-up-list' );
            $data = array(
                array(
                    'name'  => __( 'Record ID', 'sign-up-list' ),
                    'value' => $invitee['id'],
                ),
                array(
                    'name'  => __( 'Email address', 'sign-up-list' ),
                    'value' => $invitee['email'],
                )
            );
            $export_items[] = array(
                'group_id'    => $group_id,
                'group_label' => $group_label,
                'item_id'     => $item_id,
                'data'        => $data,
            );
        }

        return array(
            'data' => $export_items,
            'done' => true,
        );
    }

    /**
     * Adds an exporter to the array of exporters.
     * 
     * @param array $exporters Array of exporters to add a new one to.
     * 
     * @return array New exporter added.
     */
    public static function register_user_data_exporters( $exporters ) {
        $exporters['sign-up-list'] = array(
            'exporter_friendly_name' => __( 'Sign-up List', 'sign-up-list' ),
            'callback'               => 'SUL\\Privacy::export_user_data_by_email',
        );
        return $exporters;
    }


    /**
     * Erase list records and invitee records for a user using the supplied email.
     *
     * @param string $email Email address to identify user.
     *
     * @return array Result of actions.
     */
    public static function erase_user_data_by_email( $email ) {
        $items_removed = false;
        $items_retained = false;
        $messages = array();
        
        $entries = Database::get_entries( $email );
        foreach ( (array) $entries as $entry ) {
            //Only if the email address is in the email address field will it be erased
            if ( $entry['email'] == $email ) {
                Database::delete_entry( $entry['id']);
                $items_removed = true;
            } else {
                $messages[] = __( sprintf( 'Sign-up List entry %s has been retained; the email address was not in the expected data field. Please follow up manually.', $entry['id'] ), 'sign-up-list' );
                $items_retained = true;
            }
        }

        $invitees = Database::get_invitees( $email );
        foreach ( (array) $invitees as $invitee ) {
            //There is only one field, so no additional checking is needed.
            Database::delete_invitee( $invitee['id']);
            $items_removed = true;
        }
        
        return array(
            'items_removed' => $items_removed,
            'items_retained' => $items_retained,
            'messages' => $messages,
            'done' => true
        );
    }


    /**
     * Adds an eraser to the array of erasers.
     * 
     * @param array $erasers Array of erasers to add a new one to.
     * 
     * @return array New eraser added.
     */

    public static function register_user_data_erasers( $erasers ) {
        $erasers['sign-up-list'] = array(
            'eraser_friendly_name' => __( 'Sign-up List', 'sign-up-list' ),
            'callback'               => 'SUL\\Privacy::erase_user_data_by_email',
        );
        return $erasers;
    }
} //Class Privacy
?>
