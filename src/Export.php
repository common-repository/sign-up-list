<?php

namespace SUL;
//Prevent direct requests
defined( 'ABSPATH' ) || exit;

/**
 * Contains functions for exporting data to files.
 * 
 * Public methods are statically invoked.
 */
class Export {

/**
 * Creates a file as a browser triggered download containing list entries.
 * 
 */
public static function export_csv () {
    if ( current_user_can( 'sul_manage' ) && ( absint( $_GET['sul'] ) === 1 ) ) {
        $entries = Database::get_entries();
        $filename = 'sign-up-list.csv';
        $out = fopen( 'php://memory', 'w' );
        fputcsv( $out, array ( __( 'First name', 'sign-up-list' ),
                               __( 'Last name', 'sign-up-list' ), 
                               __( 'Email address', 'sign-up-list' ),
                               __( 'Extra info', 'sign-up-list' ),
                               __( 'Date and time', 'sign-up-list' ) ) );
        foreach ( $entries as $entry ) {
            unset( $entry['id'] );
            fputcsv( $out, $entry );    
        }
        // reset the file pointer to the start of the file
        fseek( $out, 0 );
        // tell the browser it's going to be a csv file
        header( 'Content-Type: text/csv' );
        // tell the browser we want to save it instead of displaying it
        header( 'Content-Disposition: attachment; filename="'.$filename.'";' );
        // write the buffered data
        fpassthru($out);                        
        fclose($out);
        //prevent any further output
        die();
    }
}

} // Class export
?>