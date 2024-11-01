<?php
//Prevent direct requests
defined( 'ABSPATH' ) || exit;
/**
 * This view displays the form to confirm bulk deletion in the back-end
 * It requires the following variable
 * 
 * $ids, an array containing the selected invitees to bulk delete
 *  
*/
?>
<h2><?php echo esc_html ( __( 'Delete selected invitees', 'sign-up-list' ) ); ?></h2>
<p><?php echo esc_html ( __( 'Please confirm deletion of selected invitees', 'sign-up-list' ) ); ?></p>
<form method="post">
    <?php wp_nonce_field( 'sul_invitees_delete_selected', 'sul_invitees_delete_selected_nonce'); ?>
    <?php 
    foreach ( $ids as $id ) {
        echo '<input type="hidden" name="delete_ids[]" value="'.esc_attr( absint ($id ) ).'" />';
    } 
    ?>
    <input type="submit" name="delete-selected" value="<?php echo esc_attr( __( 'Delete invitees', 'sign-up-list' ) ); ?>" class="button-primary" />
    <a href="?page=sul-invitees" class="button-secondary"><?php echo esc_html( __( 'Cancel', 'sign-up-list' ) ); ?></a>
</form>