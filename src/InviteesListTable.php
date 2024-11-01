<?php

namespace SUL;
//Prevent direct requests
defined( 'ABSPATH' ) || exit;

// Loading WP_List_Table class file
// We need to load it as it's not automatically loaded by WordPress
if ( !class_exists('WP_List_Table') ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * This class extends WP_List_Table for displaying and managing the list of invitees
 * 
 * Based on https://supporthost.com/wp-list-table-tutorial/
 */
class Invitees_List_Table extends \WP_List_Table{
    private $table_data;
    
    /**
     * Define table columns
     * 
     * @return array Columns to use and labels to display. 
     * */ 
    function get_columns()
    {
        $columns = array(
                'cb'        => '<input type="checkbox" />',
                'email'     => __( 'Email address', 'sign-up-list')
        );
        return $columns;
    }

    /**
     * Bind table with columns, data and all
     * 
     */
    function prepare_items()
    {
        if ( isset( $_POST['s'] ) ) {
            $search = sanitize_text_field ( $_POST['s'] );
            $this->table_data = Database::get_invitees( $search );
        } else {
            $this->table_data = Database::get_invitees();
        }

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        
        usort( $this->table_data, array( &$this, 'usort_reorder' ) );
        
        /* pagination */
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = count( $this->table_data );

        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
                'total_items' => $total_items, // total number of items
                'per_page'    => $per_page, // items to show on a page
                'total_pages' => ceil( $total_items / $per_page ) // use ceil to round up
        ));
        /* end pagination */

        $this->items = $this->table_data;
    }

    /**
     * Echo value for each column by default.
     * 
     * Define which value goes to each column in our table for any column that is not seperately 
     * specified.
     * 
     * Because we have used the database column names in the list table, no mapping is needed.
     * 
     * @param array $item One row in the table.
     * @param string $column_name The column for which the value is requested.
     * 
     * @return string The string to echo for the specified item in the specified column. 
     */

    function column_default ($item, $column_name )
    {
        switch ( $column_name ) {
            case 'id':
            case 'email':
            default:
                return $item[ $column_name ];
        }
    }

    /**
     * Echo string for the cb column.
     *  
     * The cb (checkbox) column needs to display a special value, which is a checkbox. This is 
     * needed so that a user can select specific rows for bulk actions.
     * 
     * @param array $item One row in the table.
     * 
     * @return string The string to echo for the cb column. 
     * 
     */
    function column_cb( $item )
    {
        return sprintf(
                '<input type="checkbox" name="element[]" value="%s" />',
                $item['id']
        );
    }

    /**
     * Specifies which columns in the table are sortable
     * 
     * @return array Sortable columns: column name and a boolean that flags initial sort order. 
     * false = ascending, true = descending.
     * 
     */
    protected function get_sortable_columns()
    {
      $sortable_columns = array(
            'email'   => array('email', false)
      );
      return $sortable_columns;
    }

    /**
     * Core sorting function
     * 
     * Compares two rows and returns -1, 0, or 1 so it known which row should preceed the other
     * See https://www.php.net/manual/en/function.strcmp.php for the explanation of -1, 0 or 1.
     * 
     * @param array $a A table row.
     * @param array $b Another table row.
     * 
     * @return int The result of strcmp, multiplied by -1 if sort order is descending.
     */
    function usort_reorder($a, $b)
    {
        $orderby_input = isset ( $_GET['orderby'] ) ? sanitize_text_field ( $_GET['orderby'] ) : '';
        $order_input = isset ( $_GET['order'] ) ? sanitize_text_field ( $_GET['order'] ) : '';

        // If no sort, default to id
        $orderby = ( ! empty( $orderby_input ) ) ? $orderby_input : 'id';

        // If no order, default to asc
        $order = ( ! empty ( $order_input ) ) ? $order_input : 'asc';

        // Determine sort order
        $result = strcmp($a[ $orderby ], $b[ $orderby ]);

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }

    /**
     * Return rendered action links per row
     * 
     * @param array $item The table row for which the action links need to be rendered.
     * 
     * @return array Rendered action links.
     */
    private function get_actions( $item ) {
        $page_input = sanitize_text_field( $_REQUEST['page'] );
        return array(
            'edit'   => sprintf( '<a href="?page=%s&action=%s&id=%s">' . 
                __( 'Edit', 'sign-up-list' ) . '</a>', $page_input, 'edit', $item['id'] ),
            'delete' => sprintf( '<a href="?page=%s&action=%s&id=%s">' . 
                __( 'Delete', 'sign-up-list' ) . '</a>', $page_input, 'delete', $item['id'] ),
            );
    }

    /**  
     * Adding action links to column email.
     * 
     * @param array $item. The table row.
     * 
     * @return string The characters to echo for the column value with action links attached.
     * 
     */
    function column_email( $item )
    {
        $actions = $this->get_actions( $item );
        return sprintf('%1$s %2$s', $item['email'], $this->row_actions($actions));
    }

    /**
     * To show bulk action dropdown values.
     * 
     * @return array Values to display in bulk action dropdown.
     */
    function get_bulk_actions()
    {
            $actions = array(
                    'delete_selected'    => __('Delete', 'sign-up-list')
            );
            return $actions;
    }

    /**
     * Processes form submit for a bulk action.
     * 
     * The form post is evaluated.
     * Check for wp_nonce.
     * Switch per action and invoke corresponding function.
     * 
     * @return boolean True if an action has been processed.
     */

    public function process_bulk_action() {

        // security check!
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

            $nonce  = filter_input( INPUT_POST, '_wpnonce' );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Your nonce could not be verified' );

        }

        $action = $this->current_action();

        switch ( $action ) {

            case 'delete_selected':
                if ( isset( $_POST['element'] ) ) {
                    Admin::delete_invitees( $_POST['element'] );
                    return true;
                } else {
                    return false;
                }
                break;

            default:
                // do nothing or something else
                return false;
                break;
        }

        return false;
    }
}
?>