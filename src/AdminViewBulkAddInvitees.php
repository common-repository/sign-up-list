<?php
//Prevent direct requests
defined( 'ABSPATH' ) || exit;
/**
 * This view displays the form to add new invitees in the back-end
 * It requires the following variables
 * 
 * $bulkdata, an array containing existing values for the input fields
 * $saved, a boolean that is used to indicate that a new invitee has been saved
 * $message, a string containing validation / error messages
 * 
*/
if ( $message !== '' ) { ?>
    <div class="notice notice-error is-dismissible">
    <p><?php echo esc_html ( $message ); ?></p>
    </div>
    <?php
}

if ( $saved ) { ?>
    <script language='javascript'>window.location.href = '<?php echo esc_js( admin_url('/admin.php?page=sul-invitees') ); ?>';</script>
    <?php
} else {
    ?>
    <div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html( get_option( 'sul_general_admin' )['listname'] ); ?></h1>
    <h2><?php echo esc_html( __( 'Add invitee', 'sign-up-list' ) ); ?></h2>
    <form method="post">
    <?php wp_nonce_field( 'sul_invitees_bulk_add', 'sul_invitees_bulk_add_nonce'); ?>
    <table class="form-table" role="presentation">
    <tbody>
    <tr>
        <th scope="row"><label for="emails"><?php echo esc_html( __( 'Enter or paste one email address per line', 'sign-up-list' ) ); ?></label></th>
        <td>
            <textarea maxlength="36000" cols="50" rows="20" name="emails"><?php 
                    foreach ( $bulkdata as $data ) {
                        echo esc_html( $data )."\r\n";
                    }; 
                ?></textarea>
        </td>
    </tr>
    <tr>
        <td>
        <input type="submit" name="save" value="<?php echo esc_attr( __( 'Save invitees', 'sign-up-list' ) ); ?>" class="button-primary" />
        <a href="?page=sul-invitees" class="button-secondary"><?php echo esc_html( __( 'Cancel', 'sign-up-list' ) ); ?></a>
        </td>
    </tr>
    </tbody>
    </table>
    </form>
    </div>
    <?php
} 
?>