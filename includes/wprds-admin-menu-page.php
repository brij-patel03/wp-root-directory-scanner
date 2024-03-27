<?php
/**
 * Add directory scanner menu page
 */
function wprds_root_directory_scanner_menu_cb()
{
    global $wpdb;
    $i = 1;
    $getScannedDatas = $wpdb->get_results("SELECT * FROM ".WPRDS_SCANNER_TABLE, ARRAY_A);    
?>
    <div class="wrap wprds-scanner-wrap">
        <h1 class="wp-heading-inline"><?php  _e('WP Root Directory Scanner', WPRDS_TEXT_DOMAIN); ?></h1>
        <a href="javascript:;" data-scan-btn-nonce = "<?php echo wp_create_nonce('scanner_btn_nonce'); ?>" class="page-title-action root-dir-scan-action-btn"><?php _e('Scan', WPRDS_TEXT_DOMAIN); ?></a>
        <?php //print_r(dirToArray(WPRDS_PLUGIN_DIR_PATH)); ?>
        <table id="scannedData" class="table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th><?php _e('No.', WPRDS_TEXT_DOMAIN); ?></th>
                    <th><?php _e('Type', WPRDS_TEXT_DOMAIN); ?></th>
                    <th><?php _e('Size', WPRDS_TEXT_DOMAIN); ?></th>
                    <th><?php _e('Number of Nodes', WPRDS_TEXT_DOMAIN); ?></th>
                    <th><?php _e('Absolute Path', WPRDS_TEXT_DOMAIN); ?></th>
                    <th><?php _e('File/Directory Name', WPRDS_TEXT_DOMAIN); ?></th>
                    <th><?php _e('File Extension', WPRDS_TEXT_DOMAIN); ?></th>
                    <th><?php _e('File Permissions', WPRDS_TEXT_DOMAIN); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $getScannedDatas as $scannedData ){ ?>
                <tr>
                    <td class="wprds-center"><?php echo $i; ?></td>
                    <td class="wprds-center"><?php echo $scannedData['type']; ?></td>
                    <td><?php echo $scannedData['size']; ?></td>
                    <td class="wprds-center"><?php echo ( $scannedData['number_of_nodes'] == NULL ) ? '-' : $scannedData['number_of_nodes']; ?></td>
                    <td><?php echo $scannedData['absolute_path']; ?></td>
                    <td class="wprds-center"><?php echo $scannedData['file_or_directory_name']; ?></td>
                    <td class="wprds-center"><?php echo ( $scannedData['file_extension'] == NULL ) ? '-' : $scannedData['file_extension']; ?></td>
                    <td class="wprds-center"><?php echo $scannedData['file_permissions']; ?></td>
                </tr>
                <?php 
                        $i++;
                    } 
                ?>
            </tbody>
        </table>
        <div class="loaderWrap wprds-hide">
            <span class="loaderText"><?php _e('Scaning...', WPRDS_TEXT_DOMAIN); ?></span>
        </div>
    </div>
<?php
}
?>