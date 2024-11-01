<?php
//Prevent direct requests
defined( 'ABSPATH' ) || exit;
/**
 * This view displays the form to reset the sign-up list in the back-end
 * It requires the following variables
 * 
 * $reset, a boolean that is used to indicate that a reset has taken place
 * $message, a string containing validation / error messages
 * 
*/
if ( $message !== '' ) { 
    ?>
    <div class="notice notice-error is-dismissible">
    <p><?php echo esc_html( $message ); ?></p>
    </div>
    <?php
}

if ( $reset ) { ?>
    <script language='javascript'>window.location.href = '<?php echo esc_js( admin_url('/admin.php?page=sul-entries') ); ?>';</script>
    <?php
} else { 
    ?>
    <div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html( get_option( 'sul_general_admin' )['listname'] ); ?></h1>        
    <h2><?php echo esc_html( __( 'Reset list', 'sign-up-list' ) ); ?></h2>
    <p><?php echo esc_html( __( 'Reset the list to start from scratch. This action cannot be undone.', 'sign-up-list' ) ); ?></p>
    <form method="post">
    <?php wp_nonce_field( 'sul_entries_reset', 'sul_entries_reset_nonce'); ?>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row"><?php echo esc_html( __( 'Delete all entries', 'sign-up-list' ) ); ?></th>
            <td><input type="checkbox" name="delete_all_entries" id="delete_all_entries" value="1" /></td>
        </tr>
        <tr>
            <th scope="row"><?php echo esc_html( __( 'Reset special invitation link', 'sign-up-list' ) ); ?></th>
            <td><input type="checkbox" name="reset_special_link" id="reset_special_link" value="1" /></td>
        </tr>
    </table>
    <p class="submit">
        <input type="submit" name="reset" value="<?php echo esc_attr( __( 'Reset list', 'sign-up-list' ) ); ?>" class="button-primary" />
        <a href="?page=sul-entries" class="button-secondary"><?php echo esc_html( __( 'Cancel', 'sign-up-list' ) ); ?></a>
    </p>
    </form>
    </div>
    <?php
} 
?>