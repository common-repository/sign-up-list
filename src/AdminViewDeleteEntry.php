<?php
//Prevent direct requests
defined( 'ABSPATH' ) || exit;
/**
 * This view displays the form to delete an entry in the back-end
 * It requires the following variables
 * 
 * $entry, an array containing stored values for the input fields
 * $deleted, a boolean that is used to indicate that a new entry has been saved
 * $message, a string containing validation / error messages
 * 
*/
$options = get_option( 'sul_general_admin' );
if ( $message !== '' ) { 
    ?>
    <div class="notice notice-error is-dismissible">
    <p><?php echo esc_html( $message ); ?></p>
    </div>
    <?php
}

if ( $deleted ) { ?>
    <script language='javascript'>window.location.href = '<?php echo esc_js( admin_url('/admin.php?page=sul-entries') ); ?>';</script>
    <?php
} else { 
    ?>
    <div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html( $options['listname'] ); ?></h1>        
    <h2><?php echo esc_html( __( 'Delete entry', 'sign-up-list' ) ); ?></h2>
    <p><?php echo esc_html( __( 'Please confirm deletion of the following entry', 'sign-up-list' ) ); ?></p>
    <form method="post">
    <?php wp_nonce_field( 'sul_entries_delete', 'sul_entries_delete_nonce'); ?>
    <table class="form-table" role="presentation">
    <tbody>
    <tr>
        <th scope="row"><label for="firstname"><?php echo esc_html( __( 'First Name', 'sign-up-list' ) ); ?></label></th>
        <td><input readonly maxlength="255" size="50" name="firstname" type="text" 
            value="<?php echo esc_attr( self::safe_value( 'firstname', $entry ) ); ?>" /></td>
    </tr>
    <tr>
        <th scope="row"><label for="lastname"><?php echo esc_html( __( 'Last Name', 'sign-up-list' ) ); ?></label></th>
        <td><input readonly maxlength="255" size="50" name="lastname" type="text" 
            value="<?php echo esc_attr( self::safe_value( 'lastname', $entry ) ); ?>" /></td>
    </tr>
    <tr>
        <th scope="row"><label for="email"><?php echo esc_html( __('Email Address', 'sign-up-list' ) ); ?></label></th>
        <td><input readonly maxlength="360" size="50" name="email" type="text" 
            value="<?php echo esc_attr( self::safe_value( 'email', $entry ) ); ?>" /></td>
    </tr>
    <?php if ( ! empty( $options['extra_label'] ) ) { ?>
    <tr>
        <th scope="row"><label for="extra_1"><?php echo esc_html( $options['extra_label'] ); ?></label></th>
        <td><input readonly maxlength="1024" size="50" name="extra_1" type="text" 
            value="<?php echo esc_attr( self::safe_value( 'extra_1', $entry ) ); ?>" /></td>
    </tr>
    <?php 
    } 
    ?>
    <tr>
        <td>
        <input type="submit" name="delete" value="<?php echo esc_attr( __( 'Delete entry', 'sign-up-list' ) ); ?>" class="button-primary" />
        <a href="?page=sul-entries" class="button-secondary"><?php echo esc_html( __( 'Cancel', 'sign-up-list' ) ); ?></a>
        </td>
    </tr>
    </tbody>
    </table>
    </form>
    </div>
    <?php
} 
?>