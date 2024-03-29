<?php defined('ABSPATH') or die("No script kiddies please!");?>
<div class="wrap">
    <?php
    if (isset($_GET['action'], $_GET['_wpnonce'], $_GET['si_id']) && wp_verify_nonce($_GET['_wpnonce'], 'aps-edit-nonce')) {
        include('edit-icon-set.php');
    } else {
        ?>
        <?php
        if (isset($_SESSION['aps_message'])) {
            ?>
            <div class="aps-message aps-message-success updated">
                <p>
                    <?php
                    echo $_SESSION['aps_message'];
                    unset($_SESSION['aps_message']);
                    ?>
                </p>
            </div>
            <?php
        }
        ?>
        <div class="aps-add-set-wrapper clearfix">
            <div class="aps-panel">
                <?php include('panel-head.php');?>
            <div class="aps-panel-body">
                <h2>AccessPress Social Icons <a href="<?php echo admin_url() . 'admin.php?page=aps-social-add' ?>" class="add-new-h2">Add New</a></h2>
                <table class="wp-list-table widefat fixed posts">
                    <thead>
                        <tr>
                            <th scope="col" id="title" class="manage-column column-title sortable asc" style="">
                                <a href="javascript:void(0)"> <span><?php _e('Title', 'aps-social'); ?></span> </a>
                            </th>
                            <th scope="col" id="shortcode" class="manage-column column-shortcode" style="">
                                <?php _e('Shortcode', 'aps-social'); ?>
                            </th>
                            <th scope="col" id="template-shortcode" class="manage-column column-shortcode" style="">
                                <?php _e('Template Shortcode', 'aps-social'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th scope="col" class="manage-column column-title sortable asc" style=""><a href="javascript:void(0)"><span><?php _e('Title', 'aps-social'); ?></span></a></th>
                            <th scope="col" class="manage-column column-shortcode" style=""><?php _e('Shortcode', 'aps-social'); ?></th>
                            <th scope="col" id="template-shortcode" class="manage-column column-shortcode" style="">
                                <?php _e('Template Shortcode', 'aps-social'); ?>
                            </th>
                        </tr>
                    </tfoot>
                    <?php
                    global $wpdb;
                    $table_name = $table_name = $wpdb->prefix . "aps_social_icons";
                    $icon_sets = $wpdb->get_results("SELECT * FROM $table_name");
                        //$this->print_array($icon_sets);
                    ?>
                    <tbody id="the-list" data-wp-lists="list:post">
                        <?php
                        if (count($icon_sets) > 0) {
                            $icon_set_counter = 1;
                            foreach ($icon_sets as $icon_set) {
                                $edit_nonce = wp_create_nonce('aps-edit-nonce');
                                $delete_nonce = wp_create_nonce('aps-delete-nonce');
                                $copy_nonce = wp_create_nonce('aps-copy-nonce');
                                ?>
                                <tr <?php if ($icon_set_counter % 2 != 0) { ?>class="alternate"<?php } ?>>
                                    <td class="title column-title">
                                        <strong>
                                            <a class="row-title" href="<?php echo admin_url() . 'admin.php?page=aps-social&action=edit_si&si_id=' . $icon_set->si_id . '&_wpnonce=' . $edit_nonce; ?>" title="Edit">
                                                <?php echo esc_attr($icon_set->icon_set_name); ?>
                                            </a>
                                        </strong>
                                        <div class="row-actions">
                                            <span class="edit"><a href="<?php echo admin_url() . 'admin.php?page=aps-social&action=edit_si&si_id=' . $icon_set->si_id . '&_wpnonce=' . $edit_nonce; ?>">Edit</a> | </span>
                                            <span class="copy"><a href="<?php echo admin_url() . 'admin-post.php?action=aps_copy_action&si_id=' . $icon_set->si_id . '&_wpnonce=' . $copy_nonce; ?>" onclick="return confirm('<?php _e('Are you sure you want to copy this icon set?', 'aps-social'); ?>')">Copy</a> | </span>
                                            <span class="delete"><a href="<?php echo admin_url() . 'admin-post.php?action=aps_delete_action&si_id=' . $icon_set->si_id . '&_wpnonce=' . $delete_nonce; ?>" onclick="return confirm('<?php _e('Are you sure you want to delete this icon set?', 'aps-social'); ?>')">Delete</a></span>
                                        </div>
                                    </td>
                                    <td class="shortcode column-shortcode"><input type="text" onFocus="this.select();" readonly="readonly" value="[aps-social id=&quot;<?php echo $icon_set->si_id; ?>&quot;]" class="shortcode-in-list-table wp-ui-text-highlight code"></td>
                                    <td class="shortcode column-shortcode"><input type="text" onFocus="this.select();" readonly="readonly" value="&lt;?php echo do_shortcode('[aps-social id=&quot;<?php echo $icon_set->si_id; ?>&quot;]')?&gt;" class="shortcode-in-list-table wp-ui-text-highlight code"></td>
                                </tr>
                                <?php
                                $icon_set_counter++;
                            }
                        } else {
                            ?>
                            <tr><td colspan="2"><div class="aps-noresult"><?php _e('Icon sets not added yet', 'aps-social'); ?></div></td></tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include_once('promobar.php'); ?>
    <?php
}
?>
</div>