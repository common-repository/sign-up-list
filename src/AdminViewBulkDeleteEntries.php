<?php
//Prevent direct requests
defined( 'ABSPATH' ) || exit;
/**
 * This view displays the form to confirm bulk deletion in the back-end
 * It requires the following variable
 * 
 * $ids, an array containing the selected entries to bulk delete
 *  
*/
?>
<h2><?php echo esc_html( __( 'Delete selected entries', 'sign-up-list' ) ); ?></h2>
<p><?php echo esc_html( __( 'Please confirm deletion of selected entries', 'sign-up-list' ) ); ?></p>
<form method="post">
    <?php wp_nonce_field( 'sul_entries_delete_selected', 'sul_entries_delete_selected_nonce'); ?>
    <?php 
    foreach ( $ids as $id ) {
        echo '<input type="hidden" name="delete_ids[]" value="'.esc_attr( absint( $id ) ).'" />';
    } 
    ?>
    <input type="submit" name="delete-selected" value="<?php echo esc_attr( __( 'Delete entries', 'sign-up-list' ) ); ?>" class="button-primary" />
    <a href="?page=sul-entries" class="button-secondary"><?php echo esc_html( __('Cancel', 'sign-up-list') ); ?></a>
</form>