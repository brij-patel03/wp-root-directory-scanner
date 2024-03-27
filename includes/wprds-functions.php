<?php

/**
 * Load plugin textdomain.
 */
function wprds_load_text_domain()
{
    load_plugin_textdomain('wprds', false, WPRDS_PLUGIN_DIR_PATH . '/languages');
}

/**
 * This function called while activate this plugin
 */
function wprds_plugin_activation()
{
    wprds_create_db_tables();
    flush_rewrite_rules();
}

/**
 * This function called while deactivate this plugin
 */
function wprds_plugin_deactivation()
{
    flush_rewrite_rules();
}

/**
 * Add WP Root Directory Scanner menu in admin left panel
 */
function wprds_add_admin_menu()
{
    global $wprds_menu_page_capability;
    add_menu_page(
        __('WP Root Directory Scanner', WPRDS_TEXT_DOMAIN),
        __('WP Root Directory Scanner', WPRDS_TEXT_DOMAIN),
        $wprds_menu_page_capability,
        'wp-root-directory-scanner',
        'wprds_root_directory_scanner_menu_cb',
        'dashicons-search',
        21
    );
}

/**
 * Add required script and style
 */
function wprds_add_script_and_style()
{
    $scriptsArray = [
        'wprds-jquery-script'                => 'https://code.jquery.com/jquery-3.7.1.js',
        'wprds-twitter-bootstrap-script'     => 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js',
        'wprds-dataTables-script'            => 'https://cdn.datatables.net/2.0.3/js/dataTables.js',
        'wprds-bootstrap5-dataTables-script' => 'https://cdn.datatables.net/2.0.3/js/dataTables.bootstrap5.js',
        'wprds-script'                       => WPRDS_PLUGIN_ASSETS_URL . 'js/wprds-script.js'
    ];
    $stylesArray = [
        'wprds-twitter-bootstrap-style'     => 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css',
        'wprds-dataTables-bootstrap5-style' => 'https://cdn.datatables.net/2.0.3/css/dataTables.bootstrap5.css',
        'wprds-style'                       => WPRDS_PLUGIN_ASSETS_URL . 'css/wprds-style.css'
    ];  
    
    // our custom JS    
    foreach ($scriptsArray as $scriptKey => $scriptURL) {
        wp_enqueue_script(
            $scriptKey,
            $scriptURL,
            ['jquery']
        );
    }
    foreach ($stylesArray as $styleKey => $styleURL) {
        wp_enqueue_style(
            $styleKey,
            $styleURL,
            [],
            WPRDS_VERSION,
            'all'
        );
    }
    wp_localize_script( 'jquery', 'wprds_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}



/**
 * Get directories information recursively
 *
 * @param  str $dir Directory path that need to be scan
 * @return array $result Array of directories information recursively
 */
function get_directory_info($dir)
{
    $result = [];
    $size = 0;
    // Get the list of files and directories
    $items = scandir($dir);

    // Iterate through the list
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            $path = $dir . '/' . $item;

            // Get type
            $type = is_dir($path) ? 'directory' : 'file';

            // Get size
            //$size = is_file($path) ? filesize($path) : 0;
            $size = is_file($path) ? filesize($path) : get_directory_size($path);

            // Convert size to highest measuring unit
            $units = array('B', 'KB', 'MB', 'GB', 'TB');
            $unit_index = 0;
            while ($size >= 1024 && $unit_index < 4) {
                $size /= 1024;
                $unit_index++;
            }
            $size = round($size, 2) . ' ' . $units[$unit_index];

            // Get number of nodes
            $nodes = is_dir($path) ? count_nodes($path) : NULL;

            // Get absolute path
            $absolute_path = realpath($path);

            // Get file/directory name and extension
            $name = basename($path);
            $extension = is_file($path) ? pathinfo($path, PATHINFO_EXTENSION) : NULL;

            // Get file permissions
            //$permissions = is_file($path) ? substr(sprintf('%o', fileperms($path)), -4) : '';
            $permissions = substr(sprintf('%o', fileperms($path)), -4);

            // Add to result array
            $result[] = array(
                'type'                      => $type,
                'size'                      => $size,
                'number_of_nodes'           => $nodes,
                'absolute_path'             => $absolute_path,
                'file_or_directory_name'    => $name,
                'file_extension'            => $extension,
                'file_permissions'          => $permissions
            );

            // If it's a directory, recursively get its information
            if (is_dir($path)) {
                $result = array_merge($result, get_directory_info($path));
            }
        }
    }

    return $result;
}

/**
 * Function to count nodes recursively
 *
 * @param str $dir Directory path
 * @return int $total_nodes Return total number of node available in directory recursively
 */
function count_nodes($dir)
{
    $total_nodes = 0;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $total_nodes += 1 + count_nodes($path); // +1 for the directory itself
            } else {
                $total_nodes += 1; // +1 for each file
            }
        }
    }
    return $total_nodes;
}

/**
 * Function to get size of directory and files recursively
 *
 *  @param str $dir Directory path
 * @return int $total_size Return total size number of directory and files recursively
 */
function get_directory_size($dir) {
    $total_size = 0;

    // Open the directory
    if ($handle = opendir($dir)) {
        // Iterate through the directory
        while (false !== ($entry = readdir($handle))) {
            // Exclude special entries "." and ".."
            if ($entry != '.' && $entry != '..') {
                // Get the full path of the entry
                $entry_path = $dir . '/' . $entry;

                // If the entry is a directory, recursively calculate its size
                if (is_dir($entry_path)) {
                    $total_size += get_directory_size($entry_path);
                } else {
                    // If the entry is a file, add its size to the total
                    $total_size += filesize($entry_path);
                }
            }
        }
        // Close the directory handle
        closedir($handle);
    }

    return $total_size;
}

/**
 * Ajax callback function is used for scan root directory of WordPress is installed
 *
 * @return void
 */
function root_dir_scan_cb(){
    if ( !wp_verify_nonce( $_REQUEST['scanner_btn_nonce'], "scanner_btn_nonce")) {
        _e('Sorry, your nonce was not correct. Please try again.', WPRDS_TEXT_DOMAIN);
        wp_die();
    }
    global $wpdb;
    $directory_info = get_directory_info(ABSPATH);
    $wpdb->query('TRUNCATE TABLE ' . WPRDS_SCANNER_TABLE);
    foreach ($directory_info as $item) {
        $wpdb->insert(
            WPRDS_SCANNER_TABLE,
            $item,
            [
                '%s', '%s', '%d', '%s', '%s', '%s', '%d'
            ]
        );
    }
    $return = array(
        'message' => __( 'WP root directory scanned successfully.', WPRDS_TEXT_DOMAIN ),
        'status'      => 1
    );
    wp_send_json_success( $return );
    wp_die();
}