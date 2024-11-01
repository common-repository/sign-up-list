<?php
//Prevent direct requests
defined( 'ABSPATH' ) || exit;
/**
 * This view displays the list of entries in the back-end
 * It requires the following variable
 * 
 * $table, the object that contains an instance of WP_List_Table 
*/
?>
<div class="wrap">
<h1 class="wp-heading-inline"><?php echo esc_html( get_option( 'sul_general_admin' )['listname'] ); ?></h1>        
<a href="?page=sul-entries&action=add" class="page-title-action">
<?php echo esc_html (__( 'Add new entry', 'sign-up-list') ); ?></a>
<a href="<?php echo esc_attr( admin_url('admin-ajax.php?action=csv_pull&sul=1') ); ?>" class="page-title-action">
<?php echo esc_html (__( 'Export to CSV', 'sign-up-list') ); ?></a>
<a href="?page=sul-entries&action=reset" class="page-title-action">
<?php echo esc_html (__( 'Reset', 'sign-up-list') ); ?></a>
<form method="post" action="?page=sul-entries">
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