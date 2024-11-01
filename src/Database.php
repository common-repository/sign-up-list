<?php

namespace SUL;
//Prevent direct requests
defined( 'ABSPATH' ) || exit;
/**
 * Contains all database functions.
 * 
 * Data definition.
 * Data retrieval, updating, inserting and deleting.
 * Helper functions.
 * 
 * Public methods are statically invoked.
 */
class Database {
    
    /**
     * Defines the custom database tables for this plugin. 
     * 
     * The function creates the tabels sul_entries and sul_invitees.
     * The function stores the database version as a WordPress option.
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @global string $sul_db_version Database version as defined in the main plugin file.
     * 
     */
    public static function add_tables() {
        global $wpdb;
		global $sul_db_version;
		
		/* Add table for all entries */
		$table_name = $wpdb->prefix . 'sul_entries';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
            firstname tinytext DEFAULT '' NOT NULL,
            lastname tinytext DEFAULT '' NOT NULL,
            email varchar(360) DEFAULT '' NOT NULL,
            extra_1 varchar(80) DEFAULT '' NOT NULL,
            created timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		
		/* Add table for invited (allowed) persons */
		$table_name = $wpdb->prefix . 'sul_invitees';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			email varchar(360) DEFAULT '' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		dbDelta(  $sql );
		add_option( 'sul_db_version', $sul_db_version );
	}

    /**
     * Deletes custom tables when the plugin is removed from WordPress.
     * 
     * This function deletes the custom tables sul_entries and sul_invitees.
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * 
     */
    public static function delete_tables() {
        global $wpdb;
       
        $entries_table = $wpdb->prefix . 'sul_entries';
        $invitees_table = $wpdb->prefix . 'sul_invitees';
        $sql = "DROP TABLE IF EXISTS $entries_table, $invitees_table;";
        $wpdb->query ( $sql );        
    }

    /**
     * Applies changes in tables structure from previous db versions.
     * 
     * Compares database-version as specified in main plug-in file with the version used 
     * until now to define the database.
     * If the versions are not equal, then the database needs to be updated.
     * The new database definition is defined in the SQL statement and the wp function
     * wpDelta ensures that the database structure comply with this definition.
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @global string $sul_db_version Database version as defined in the main plugin file.
     */
    public static function update_tables() {
        global $wpdb;
        global $sul_db_version;

        $installed_ver = get_option( "sul_db_version" );
        if ( $installed_ver != $sul_db_version ) {
            /* Define the new structure here similar to addTables */
            /* Add table for all entries */
            $table_name = $wpdb->prefix . 'sul_entries';
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                firstname tinytext DEFAULT '' NOT NULL,
                lastname tinytext DEFAULT '' NOT NULL,
                email varchar(360) DEFAULT '' NOT NULL,
                extra_1 varchar(80) DEFAULT '' NOT NULL,
                created timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );
            
            /* Add table for invited (allowed) persons */
            $table_name = $wpdb->prefix . 'sul_invitees';
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                email varchar(360) DEFAULT '' NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            dbDelta(  $sql );

            /* Finally, update the database version */
            update_option("sul_db_version", $sul_db_version);
        }
    }

    /**
     * Retrieves records from sul_entries.
     * 
     * Retrieves records from custom table sul_entries.
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @param string $search Optional. Contains string which must match with a column of an entry. 
     * Default empty. 
     * @return array Entries as key => value arrays. 
     */
    public static function get_entries( $search = '' ) {
		global $wpdb;
        $entries_table = $wpdb->prefix . 'sul_entries';
        
        if ( !empty ( $search ) ) {
            
            //Do something special for the like clause because % conflict with placeholder
            $like = '%'.$wpdb->esc_like( $search ).'%';
            $sql = "SELECT * FROM $entries_table WHERE firstname LIKE %s OR lastname LIKE %s OR email LIKE %s OR extra_1 LIKE %s ORDER BY created";
            
            $sql = $wpdb->prepare( $sql, $like, $like, $like, $like );
        } else {
            $sql = "SELECT * FROM $entries_table ORDER BY created";
        }
        return $wpdb->get_results( $sql, ARRAY_A );        
    }

    /**
     * Add a record to sul_entries.
     *  
     * Adds a record to sul_entries without the columns id (autonum) 
     * and created (default current_timestamp).
     * Assumes that sanitization and validation has taken place.
     * Never call this function without checking data first.
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @param array $data Contains the data to be added. Expected to be validated and sanitized.
     * @return int/bool Record id of new record or false if database operation failed.
     */
    public static function add_entry( $data ) {
        global $wpdb;
        $entries_table = $wpdb->prefix . 'sul_entries';
        return $wpdb->insert( $entries_table, $data, '%s');
    }

    /**
     * Update a record of sul_entries.
     * 
     * Updates a record of sul_entries without id and created (both columns are not to be updated).
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @param array $data Contains the data to be updated. Expected to be validated and sanitized.
     * @return int/bool Number of columns that have been updated or false 
     * if database operation failed.
     */
    public static function update_entry( $data ) {
        global $wpdb;
        $entries_table = $wpdb->prefix . 'sul_entries';
        $where = array (
            'id' => $data['id']
        );

        //remove the entry for id from $data, we do not want it updated
        unset($data['id']);

        return $wpdb->update( $entries_table, $data, $where, '%s', '%d');
    }

    /**
     * Retrieves one record from sul_entries.
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @param int $id The id of the record to retrieve.
     * @return array The record as a key => value array.
     */
    public static function get_entry( $id ) {
        global $wpdb;
        $entries_table = $wpdb->prefix . 'sul_entries';
        return $wpdb->get_row( "SELECT * FROM $entries_table WHERE id = $id", ARRAY_A );
    }
    
    /**
     * Deletes one record from sul_entries.
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @param int $id The id of the record to delete.
     * @return int/bool Number of deleted records or false if the operation failed.
     */
    public static function delete_entry( $id ) {
        global $wpdb;
        $entries_table = $wpdb->prefix . 'sul_entries';
        return $wpdb->delete( $entries_table, array ( 'id' => $id ), '%d' );
    }
    
    /**
     * Helper function to validate data entered for a record of sul_entries.
     * 
     * Validates data based on known requirements for each column of sul_entries.
     * Provides a user friendly and specific error message if validation fails.
     * In case of an update: checks if record id exists as expected.
     * In case of an insert: checks optionally for existing record with same email address.
     *  
     * @param array $data The data to be validated. Structured as a potential record for sul_entries.
     * @param boolean $is_update Optional. Flag to indicate update instead of insert. Default false.
     * 
     * @return string Validation error message or an empty string if validation succeeded. 
     */
    public static function validate_new_entry( $data, $is_update = false ) {
        
        // firstname must be between 1 and 255 characters
        if ( strlen( $data['firstname'] ) < 1  ) return __( 'First name missing', 'sign-up-list' );
        if ( strlen( $data['firstname'] ) > 255 ) return __( 'First name too long', 'sign-up-list' );
        
        // lastname must be between 1 and 255 characters
        if ( strlen( $data['lastname'] ) < 1 ) return __( 'Last name missing', 'sign-up-list' );
        if ( strlen( $data['lastname'] ) > 255 ) return __( 'Last name too long', 'sign-up-list' );

        // email must have a valid syntax
        if ( ! is_email ( $data['email'] ) ) return __( 'Invalid email address', 'sign-up-list' );
        
        // extra_1 must have no more than 80 characters
        if ( array_key_exists( 'extra_1', $data ) ) {
            if ( strlen( $data['extra_1'] ) > 80  ) {
                return get_option('sul_general_admin')['extra_label'].__(' too long');
            }
        }

        //check whether the entry exists if we are supposed to update an entry
        if ( $is_update ) {
            if ( ! self::entry_exists( $data['id'] ) ) {
                return __( 'Invalid record id', 'sign-up-list' );
              }
        } else {
            //check whether the entry already exists if indicated by administrator
            if ( get_option('sul_general_admin')['duplicates_allowed'] == 'no' ) {
                if ( self::entry_exists_by_email ( $data['email'] ) ) {
                    return __( 'Email address is already on the list', 'sign-up-list');
                }
            }
        }
        //validation succeeded, return an empty string
        return '';   
    }

    /**
     * Checks the existence of a record in sul_entries via the record id. 
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @param int $id The id of the record to check.
     * @return bool True if record exists, false otherwise.
     */
    public static function entry_exists ( $id ) {
        global $wpdb;
        $entries_table = $wpdb->prefix . 'sul_entries';
        if ( $wpdb->get_var( "SELECT id FROM $entries_table WHERE id = $id " ) == NULL)  {
            return false;
        }
        return true;
    }

    /**
     * Checks the existence of a record in sul_entries via an email address.
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @param string $email The email address of the record to check.
     * @return bool True if record exists, false otherwise.
     */
    public static function entry_exists_by_email ( $email ) {
        global $wpdb;
        $entries_table = $wpdb->prefix . 'sul_entries';
        if ( $wpdb->get_row( "SELECT id FROM $entries_table WHERE email = '$email' " ) == NULL )  {
            return false;
        }
        return true;
    }
    
    /**
     * Retrieves records from sul_invitees.
     * 
     * Retrieves records from custom table sul_invitees.
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @param string $search Optional. Contains string which must match with the email address. 
     * Default empty. 
     * @return array Invitees as key => value arrays. 
     */
    public static function get_invitees( $search = '' ) {
		global $wpdb;
        $invitees_table = $wpdb->prefix . 'sul_invitees';
        if ( !empty ( $search ) ) {
            
            //Do something special for the like clause because % conflict with placeholder
            $like = '%'.$wpdb->esc_like( $search ).'%';
            $sql = "SELECT * FROM $invitees_table WHERE email LIKE %s ORDER BY id";
            
            $sql = $wpdb->prepare( $sql, $like );
        } else {
            $sql = "SELECT * FROM $invitees_table ORDER BY id";
        }
        return $wpdb->get_results ( $sql, ARRAY_A );        
    }

    /**
     * Add a record to sul_invitees.
     *  
     * Adds a record to sul_invitees without the column id (autonum) 
     * Assumes that sanitization and validation has taken place.
     * Never call this function without checking data first.
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @param array $data Contains the data to be added. Expected to be validated and sanitized.
     * @return int/bool Record id of new record or false if database operation failed.
     */
    public static function add_invitee( $data ) {
        global $wpdb;
        $invitees_table = $wpdb->prefix . 'sul_invitees';
        return $wpdb->insert( $invitees_table, $data, '%s');
    }

    /**
     * Update a record of sul_invitees.
     * 
     * Updates a record of sul_invitees without id (column is not to be updated).
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @param array $data Contains the data to be updated. Expected to be validated and sanitized.
     * @return int/bool Number of columns that have been updated or false 
     * if database operation failed.
     */
    public static function update_invitee( $data ) {
        global $wpdb;
        $invitees_table = $wpdb->prefix . 'sul_invitees';
        $where = array (
            'id' => $data['id']
        );

        //remove the element for id from $data, we do not want it updated
        unset($data['id']);

        return $wpdb->update( $invitees_table, $data, $where, '%s', '%d');
    }

    /**
     * Retrieves one record from sul_invitees.
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @param int $id The id of the record to retrieve.
     * @return array The record as a key => value array.
     */
    public static function get_invitee( $id ) {
        global $wpdb;
        $invitees_table = $wpdb->prefix . 'sul_invitees';
        return $wpdb->get_row( "SELECT * FROM $invitees_table WHERE id = $id", ARRAY_A );
    }
    
    /**
     * Deletes one record from sul_invitees.
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @param int $id The id of the record to delete.
     * @return int/bool Number of deleted records or false if the operation failed.
     */
    public static function delete_invitee( $id ) {
        global $wpdb;
        $invitees_table = $wpdb->prefix . 'sul_invitees';
        return $wpdb->delete( $invitees_table, array ( 'id' => $id ), '%d' );
    }
    
    /**
     * Helper function to validate data entered for a record of sul_invitees.
     * 
     * Validates data based on known requirements for each column of sul_invitees.
     * Provides a user friendly and specific error message if validation fails.
     * In case of an update: checks if record id exists as expected.
     *  
     * @param array $data The data to be validated. Structured as a potential record for sul_entries.
     * @param boolean $is_update Optional. Flag to indicate update instead of insert. Default false.
     * 
     * @return string Validation error message or an empty string if validation succeeded. 
     */
    public static function validate_new_invitee( $data, $is_update = false ) {
        
        /* email must be valid */
        if ( ! is_email ( $data['email'] ) ) return __( 'Invalid email address', 'sign-up-list' );
        
        //Check whether the invitee exists if we are supposed to update an invitee
        if ( $is_update ) {
            if ( ! self::invitee_exists( $data['id'] ) ) {
                return __( 'Invalid record id', 'sign-up-list' );
              }
        } else {
            //Check whether the invitee is already on the list
            if ( self::invitee_exists_by_email( $data['email'] ) ) {
                return __( 'Email address is already on the list', 'sign-up-list' );
            }
        }
        
       //validation succeeded, return an empty string
        return '';   
    }

    /**
     * Checks the existence of a record in sul_invitees via an email address.
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @param string $email The email address of the record to check.
     * @return bool True if record exists, false otherwise.
     */
    public static function invitee_exists_by_email ( $email ) {
        global $wpdb;
        $invitees_table = $wpdb->prefix . 'sul_invitees';
        if ( $wpdb->get_row( "SELECT email FROM $invitees_table WHERE email = '$email' " ) == NULL )  {
            return false;
        }
        return true;
    }

    /**
     * Checks the existence of a record in sul_invitees via the record id. 
     * 
     * @global wpdb $wpdb The object that connects to the database.
     * @param int $id The id of the record to check.
     * @return bool True if record exists, false otherwise.
     */
    public static function invitee_exists ( $id ) {
        global $wpdb;
        $invitees_table = $wpdb->prefix . 'sul_invitees';
        if ( $wpdb->get_var( "SELECT id FROM $invitees_table WHERE id = $id " ) == NULL)  {
            return false;
        }
        return true;
    }
}
?>