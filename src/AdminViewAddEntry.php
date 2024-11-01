<?php
//Prevent direct requests
defined( 'ABSPATH' ) || exit;

/**
 * This view displays the form to add a new entry in the back-end
 * It requires the following variables
 * 
 * $data, an array containing existing values for the input fields
 * $saved, a boolean that is used to indicate that a new entry has been saved
 * $message, a string containing validation / error messages
 * 
*/
$options = get_option( 'sul_general_admin' );
if ( $message !== '' ) { ?>
    <div class="notice notice-error is-dismissible">
    <p><?php echo esc_html( $message ); ?></p>
    </div>
    <?php
}

if ( $saved ) { ?>
    <script language='javascript'>window.location.href = '<?php echo esc_js( admin_url('/admin.php?page=sul-entries') ); ?>';</script>
    <?php
} else {
    ?>
    <div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html( $options['listname'] ); ?></h1>
    <h2><?php echo esc_html( __( 'Add entry', 'sign-up-list' ) ); ?></h2>
    <form method="post">
    <?php wp_nonce_field( 'sul_entries_add', 'sul_entries_add_nonce'); ?>
    <table class="form-table" role="presentation">
    <tbody>
    <tr>
        <th scope="row"><label for="firstname"><?php echo esc_html( __( 'First Name', 'sign-up-list' ) ); ?></label></th>
        <td><input maxlength="255" size="50" name="firstname" type="text" value="<?php echo esc_attr( self::safe_value( 'firstname', $data ) ); ?>" /></td>
    </tr>
    <tr>
        <th scope="row"><label for="lastname"><?php echo esc_html( __('Last Name', 'sign-up-list' ) ); ?></label></th>
        <td><input maxlength="255" size="50" name="lastname" type="text" 
            value="<?php echo esc_attr( self::safe_value( 'lastname', $data ) ); ?>" /></td>
    </tr>
    <tr>
        <th scope="row"><label for="email"><?php echo esc_html( __('Email Address', 'sign-up-list' ) ); ?></label></th>
        <td><input maxlength="360" size="50" name="email" type="text" 
            value="<?php echo esc_attr( self::safe_value( 'email', $data, $_POST, false ) ); ?>" /></td>
    </tr>
    <?php if ( ! empty( $options['extra_label'] ) ) { ?>
    <tr>
        <th scope="row"><label for="extra_1"><?php echo esc_html( $options['extra_label'] ); ?></label></th>
        <td><input maxlength="80" size="50" name="extra_1" type="text" 
            value="<?php echo esc_attr( self::safe_value( 'extra_1', $data ) ); ?>" /></td>
    </tr>
    <?php 
    } 
    ?> 
    </tbody>
    </table>
    <p class="submit"><input type="submit" name="save" value="<?php echo esc_attr( __( 'Save entry', 'sign-up-list' ) ); ?>" class="button-primary" />
        <a href="?page=sul-entries" class="button-secondary"><?php echo esc_html( __( 'Cancel', 'sign-up-list' ) ); ?></a>
    </p>
    </form>
    </div>
    <?php
} 
?>