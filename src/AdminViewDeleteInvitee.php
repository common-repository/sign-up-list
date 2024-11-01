<?php
//Prevent direct requests
defined( 'ABSPATH' ) || exit;
/**
 * This view displays the form to delete an invitee in the back-end
 * It requires the following variables
 * 
 * $invitee, an array containing stored values for the input fields
 * $deleted, a boolean that is used to indicate that a new invitee has been saved
 * $message, a string containing validation / error messages
 * 
*/
if ( $message !== '' ) { ?>
    <div class="notice notice-error is-dismissible">
    <p><?php echo esc_html( $message ); ?></p>
    </div>
<?php
}

if ( $deleted ) { 
?>
    <script language='javascript'>window.location.href = '<?php echo esc_js( admin_url('/admin.php?page=sul-invitees') ); ?>';</script>
<?php
} else { 
    ?>
    <div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html( get_option( 'sul_general_admin' )['listname'] ); ?></h1>        
    <h2><?php echo esc_html( __( 'Delete invitee', 'sign-up-list' ) ); ?></h2>
    <p><?php echo esc_html( __( 'Please confirm deletion of the following invitee', 'sign-up-list' ) ); ?></p>
    <form method="post">
    <?php wp_nonce_field( 'sul_invitees_delete', 'sul_invitees_delete_nonce'); ?>
    <table class="form-table" role="presentation">
    <tbody>
    <tr>
        <th scope="row"><label for="email"><?php echo esc_html( __( 'Email Address', 'sign-up-list' ) ); ?></label></th>
        <td><input readonly maxlength="360" size="50" name="email" type="text" 
            value="<?php echo esc_attr( self::safe_value( 'email', $invitee ) ); ?>" /></td>
    </tr>
    <tr>
        <td>
        <input type="submit" name="delete" value="<?php echo esc_attr( __( 'Delete invitee', 'sign-up-list' ) ); ?>" class="button-primary" />
        <a href="?page=sul-invitees" class="button-secondary"><?php echo esc_html( __('Cancel', 'sign-up-list' ) ); ?></a>
        </td>
    </tr>
    </tbody>
    </table>
    </form>
    </div>
    <?php
} 
?>