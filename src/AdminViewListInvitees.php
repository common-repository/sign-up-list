<?php
//Prevent direct requests
defined( 'ABSPATH' ) || exit;
/**
 * This view displays the list of invitees in the back-end
 * It requires the following variable
 * 
 * $table, the object that contains an instance of WP_List_Table 
*/
?>
<div class="wrap">
<h1 class="wp-heading-inline"><?php echo esc_html( get_option( 'sul_general_admin' )['listname'] ); ?></h1>        
<a href="?page=sul-invitees&action=add" class="page-title-action">
<?php echo esc_html( __( 'Add one invitee', 'sign-up-list') ); ?></a>
<a href="?page=sul-invitees&action=bulk-add" class="page-title-action">
<?php echo esc_html( __( 'Add multiple invitees', 'sign-up-list') ); ?></a>
<form method="post" action="?page=sul-invitees">
<?php 
//We will redirect to a specific bulk action page if needed
if (! $table->process_bulk_action() ) {
    $table->prepare_items();
    $table->search_box( __( 'Search', 'sign-up-list' ), 'search_id');
    $table->display();
}
?>
</form>
</div>