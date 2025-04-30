<?php
// Check capabilities
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'matrimony-admin'));
}
?>

<div class="wrap matrimony-admin profile-preview">
    <h1><?php printf(__('Profile Preview: %s', 'matrimony-admin'), $profile['profile_id']); ?></h1>
    
    <div class="preview-header">
        <a href="<?php echo admin_url('admin.php?page=matrimony-admin-manage&view=list'); ?>" class="button">
            <?php _e('Back to List', 'matrimony-admin'); ?>
        </a>
    </div>
    
    <div class="profile-photo">
    <?php 
    if (!empty($profile['photo'])) {
        $photo_path = MATRIMONY_ADMIN_UPLOAD_DIR . $profile['photo'];
        $thumb_path = preg_replace('/(\.[^.]+)$/', '-thumb$1', $photo_path);
        
        // Check if thumbnail exists, otherwise use original
        if (file_exists($thumb_path)) {
            $photo_url = MATRIMONY_ADMIN_UPLOAD_URL . preg_replace('/(\.[^.]+)$/', '-thumb$1', $profile['photo']);
        } else {
            $photo_url = MATRIMONY_ADMIN_UPLOAD_URL . $profile['photo'];
        }
        ?>
        <img src="<?php echo $photo_url; ?>" alt="<?php echo esc_attr($profile['first_name']); ?>">
    <?php } else { ?>
        <div class="no-photo">
            <span class="dashicons dashicons-format-image"></span>
        </div>
    <?php } ?>
</div>
        
        <div class="profile-info">
            <h2><?php echo esc_html($profile['first_name'] . ' ' . $profile['last_name']); ?></h2>
            
            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label"><?php _e('Profile ID', 'matrimony-admin'); ?>:</span>
                    <span class="info-value"><?php echo $profile['profile_id']; ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label"><?php _e('Date of Birth', 'matrimony-admin'); ?>:</span>
                    <span class="info-value"><?php echo date('d M Y', strtotime($profile['dob'])); ?> (<?php echo $profile['age']; ?> <?php _e('years', 'matrimony-admin'); ?>)</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label"><?php _e('Gender', 'matrimony-admin'); ?>:</span>
                    <span class="info-value"><?php echo MatrimonyAdmin_Utilities::get_gender_display_name($profile['gender']); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label"><?php _e('Marital Status', 'matrimony-admin'); ?>:</span>
                    <span class="info-value"><?php echo MatrimonyAdmin_Utilities::get_marital_status_display_name($profile['marital_status']); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label"><?php _e('Caste', 'matrimony-admin'); ?>:</span>
                    <span class="info-value"><?php echo MatrimonyAdmin_Utilities::get_caste_display_name($profile['caste']); ?></span>
                </div>
                
                <?php if (!empty($profile['sub_caste'])): ?>
                <div class="info-row">
                    <span class="info-label"><?php _e('Sub Caste', 'matrimony-admin'); ?>:</span>
                    <span class="info-value"><?php echo MatrimonyAdmin_Utilities::get_sub_caste_display_name($profile['sub_caste']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($profile['phone'])): ?>
                <div class="info-row">
                    <span class="info-label"><?php _e('Contact Number', 'matrimony-admin'); ?>:</span>
                    <span class="info-value"><?php echo esc_html($profile['phone']); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="info-row">
                    <span class="info-label"><?php _e('Created On', 'matrimony-admin'); ?>:</span>
                    <span class="info-value"><?php echo date('d M Y H:i', strtotime($profile['created_at'])); ?></span>
                </div>
            </div>
            
            <?php if (!empty($profile['document'])): ?>
            <div class="document-download">
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=matrimony-admin-manage&action=download&profile_id=' . $profile['profile_id']), 'matrimony_profile_action'); ?>" class="button button-primary">
                    <?php _e('Download Profile Document', 'matrimony-admin'); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($profile['admin_notes'])): ?>
    <div class="admin-notes">
        <h3><?php _e('Admin Notes', 'matrimony-admin'); ?></h3>
        <div class="notes-content">
            <?php echo wpautop(wp_kses_post($profile['admin_notes'])); ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="profile-actions">
        <a href="<?php echo admin_url('admin.php?page=matrimony-admin-manage&view=list'); ?>" class="button">
            <?php _e('Back to List', 'matrimony-admin'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=matrimony-admin-manage&action=delete&profile_id=' . $profile['profile_id'] . '&_wpnonce=' . wp_create_nonce('matrimony_profile_action')); ?>" class="button button-delete">
            <?php _e('Delete Profile', 'matrimony-admin'); ?>
        </a>
    </div>
</div>