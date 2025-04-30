<div class="wrap matrimony-admin">
    <h1><?php _e('Recycle Bin', 'matrimony-admin'); ?></h1>
    
    <?php MatrimonyAdmin_Utilities::display_admin_notice(); ?>
    
    <?php if (!empty($formatted_profiles)): ?>
    <form method="post" action="<?php echo admin_url('admin.php?page=matrimony-admin-recycle&action=bulk_restore'); ?>">
        <?php wp_nonce_field('matrimony_recycle_action', '_wpnonce'); ?>
        
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text"><?php _e('Select bulk action', 'matrimony-admin'); ?></label>
                <select name="action" id="bulk-action-selector-top">
                    <option value="-1"><?php _e('Bulk Actions', 'matrimony-admin'); ?></option>
                    <option value="restore"><?php _e('Restore', 'matrimony-admin'); ?></option>
                    <option value="delete"><?php _e('Delete Permanently', 'matrimony-admin'); ?></option>
                </select>
                <input type="submit" id="doaction" class="button action" value="<?php _e('Apply', 'matrimony-admin'); ?>">
            </div>
            <div class="tablenav-pages">
                <span class="displaying-num"><?php echo count($formatted_profiles); ?> <?php _e('items', 'matrimony-admin'); ?></span>
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-1"><?php _e('Select All', 'matrimony-admin'); ?></label>
                        <input id="cb-select-all-1" type="checkbox">
                    </td>
                    <th><?php _e('Profile', 'matrimony-admin'); ?></th>
                    <th><?php _e('Deleted On', 'matrimony-admin'); ?></th>
                    <th><?php _e('Deleted By', 'matrimony-admin'); ?></th>
                    <th><?php _e('Purge Date', 'matrimony-admin'); ?></th>
                    <th><?php _e('Actions', 'matrimony-admin'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($formatted_profiles as $profile): ?>
                <tr>
                    <th scope="row" class="check-column">
                        <label class="screen-reader-text" for="cb-select-<?php echo $profile['profile_id']; ?>">
                            <?php printf(__('Select %s', 'matrimony-admin'), $profile['first_name']); ?>
                        </label>
                        <input id="cb-select-<?php echo $profile['profile_id']; ?>" type="checkbox" name="profile_ids[]" value="<?php echo $profile['profile_id']; ?>">
                    </th>
                    <td class="profile-info">
                        <div class="profile-photo">
                            <?php if (!empty($profile['photo_url'])): ?>
                                <img src="<?php echo esc_url($profile['photo_url']); ?>" alt="<?php echo esc_attr($profile['first_name']); ?>" width="50">
                            <?php else: ?>
                                <span class="dashicons dashicons-format-image"></span>
                            <?php endif; ?>
                        </div>
                        <div class="profile-details">
                            <strong><?php echo esc_html($profile['first_name'] . ' ' . $profile['last_name']); ?></strong>
                            <div class="row-actions">
                                <span class="profile-id"><?php echo esc_html($profile['profile_id']); ?></span> |
                                <span class="age"><?php echo esc_html($profile['age']); ?> <?php _e('years', 'matrimony-admin'); ?></span> |
                                <span class="caste"><?php echo esc_html($profile['caste']); ?></span>
                                <?php if (!empty($profile['sub_caste'])): ?>
                                | <span class="sub-caste"><?php echo esc_html($profile['sub_caste']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><?php echo esc_html($profile['deleted_at']); ?></td>
                    <td><?php echo esc_html($profile['deleted_by']); ?></td>
                    <td>
                        <?php echo esc_html($profile['purge_date']); ?>
                        <div class="row-actions">
                            <small><?php printf(_n('%d day remaining', '%d days remaining', $profile['days_remaining'], 'matrimony-admin'), $profile['days_remaining']); ?></small>
                        </div>
                    </td>
                    <td>
                        <a href="<?php echo esc_url($profile['actions']['restore']); ?>" class="button button-small">
                            <?php _e('Restore', 'matrimony-admin'); ?>
                        </a>
                        <a href="<?php echo esc_url($profile['actions']['delete']); ?>" class="button button-small button-delete">
                            <?php _e('Delete Permanently', 'matrimony-admin'); ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-2"><?php _e('Select All', 'matrimony-admin'); ?></label>
                        <input id="cb-select-all-2" type="checkbox">
                    </td>
                    <th><?php _e('Profile', 'matrimony-admin'); ?></th>
                    <th><?php _e('Deleted On', 'matrimony-admin'); ?></th>
                    <th><?php _e('Deleted By', 'matrimony-admin'); ?></th>
                    <th><?php _e('Purge Date', 'matrimony-admin'); ?></th>
                    <th><?php _e('Actions', 'matrimony-admin'); ?></th>
                </tr>
            </tfoot>
        </table>
    </form>
    <?php else: ?>
    <div class="notice notice-info">
        <p><?php _e('The recycle bin is empty.', 'matrimony-admin'); ?></p>
    </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    $('form').submit(function(e) {
        var action = $(this).find('select[name="action"]').val();
        
        if (action === '-1') {
            e.preventDefault();
            return;
        }

        if ($('input[name="profile_ids[]"]:checked').length === 0) {
            e.preventDefault();
            alert('<?php _e('Please select at least one profile.', 'matrimony-admin'); ?>');
            return;
        }

        if (action === 'delete') {
            if (!confirm('<?php _e('Are you sure you want to permanently delete the selected profiles? This action cannot be undone.', 'matrimony-admin'); ?>')) {
                e.preventDefault();
            }
        }
    });

    $('#cb-select-all-1, #cb-select-all-2').click(function() {
        var isChecked = $(this).prop('checked');
        $('input[name="profile_ids[]"]').prop('checked', isChecked);
    });
});
</script>
