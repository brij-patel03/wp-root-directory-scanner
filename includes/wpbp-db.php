<?php
/**
 * Create table in database while activating plugin
 */
function wprds_create_db_tables()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $wprds_installed_db_version = get_option( "wprds_db_version");
    
    if($wprds_installed_db_version != WPRDS_DB_VERSION){
        $sql_queries_for_create_tables = [
            "CREATE TABLE ".WPRDS_SCANNER_TABLE." (
                ID BIGINT(20) NOT NULL AUTO_INCREMENT , 
                type ENUM('file','directory') NOT NULL , 
                size VARCHAR(10) NOT NULL , 
                number_of_nodes INT(10) NULL , 
                absolute_path VARCHAR(250) NOT NULL , 
                file_or_directory_name VARCHAR(100) NOT NULL , 
                file_extension VARCHAR(20) NULL , 
                file_permissions VARCHAR(5) NOT NULL , 
                created_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (ID)
            ) $charset_collate;"
        ];        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        foreach ($sql_queries_for_create_tables as $create_table_sql_query){
            dbDelta( $create_table_sql_query );
        }                    
        update_option( 'wprds_db_version', WPRDS_DB_VERSION);
    }
}
?>