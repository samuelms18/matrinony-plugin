<div class="wrap matrimony-admin">
    <h1><?php _e('Matrimony Admin Settings', 'matrimony-admin'); ?></h1>
    
    <?php MatrimonyAdmin_Utilities::display_admin_notice(); ?>
    
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="matrimony-form">
        <input type="hidden" name="action" value="matrimony_save_settings">
        <?php wp_nonce_field('matrimony_save_settings', 'matrimony_settings_nonce'); ?>
        
        <h2 class="title"><?php _e('General Settings', 'matrimony-admin'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="upload_dir"><?php _e('Upload Directory', 'matrimony-admin'); ?></label>
                </th>
                <td>
                    <input type="text" id="upload_dir" name="upload_dir" value="<?php echo esc_attr($settings['upload_dir']); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('Directory where profile photos and documents will be stored.', 'matrimony-admin'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="auto_purge_days"><?php _e('Auto Purge After', 'matrimony-admin'); ?></label>
                </th>
                <td>
                    <input type="number" id="auto_purge_days" name="auto_purge_days" value="<?php echo esc_attr($settings['auto_purge_days']); ?>" min="1" max="365">
                    <span class="description"><?php _e('days (profiles in recycle bin will be permanently deleted after this period)', 'matrimony-admin'); ?></span>
                </td>
            </tr>
            <tr>
    <th scope="row">
        <label for="profile_id_format"><?php _e('Profile ID Format', 'matrimony-admin'); ?></label>
    </th>
    <td>
        <input type="text" id="profile_id_format" name="profile_id_format" value="<?php echo esc_attr($settings['profile_id_format']); ?>" class="regular-text">
        <p class="description">
            <?php _e('Available placeholders: GENDER_LETTER, CAST3, SUBCAST3, SERIAL_NO, DOB_YEAR', 'matrimony-admin'); ?><br>
            <?php _e('Example: "GENDER_LETTERCAST3SUBCAST3SERIAL_NODOB_YEAR" (e.g., MIYEVAD0011948)', 'matrimony-admin'); ?>
        </p>
    </td>
</tr>
        </table>
        
        <h2 class="title"><?php _e('reCAPTCHA Settings', 'matrimony-admin'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="recaptcha_enabled"><?php _e('Enable reCAPTCHA', 'matrimony-admin'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="recaptcha_enabled" name="recaptcha_enabled" value="1" <?php checked($settings['recaptcha_enabled'], 1); ?>>
                    <label for="recaptcha_enabled"><?php _e('Enable Google reCAPTCHA on profile submission', 'matrimony-admin'); ?></label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="recaptcha_site_key"><?php _e('Site Key', 'matrimony-admin'); ?></label>
                </th>
                <td>
                    <input type="text" id="recaptcha_site_key" name="recaptcha_site_key" value="<?php echo esc_attr($settings['recaptcha_site_key']); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="recaptcha_secret_key"><?php _e('Secret Key', 'matrimony-admin'); ?></label>
                </th>
                <td>
                    <input type="text" id="recaptcha_secret_key" name="recaptcha_secret_key" value="<?php echo esc_attr($settings['recaptcha_secret_key']); ?>" class="regular-text">
                </td>
            </tr>
        </table>
        
        <h2 class="title"><?php _e('Download Settings', 'matrimony-admin'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="watermark_enabled"><?php _e('Enable Watermark', 'matrimony-admin'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="watermark_enabled" name="watermark_enabled" value="1" <?php checked($settings['watermark_enabled'], 1); ?>>
                    <label for="watermark_enabled"><?php _e('Add watermark to downloaded PDF documents', 'matrimony-admin'); ?></label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="watermark_text"><?php _e('Watermark Text', 'matrimony-admin'); ?></label>
                </th>
                <td>
                    <input type="text" id="watermark_text" name="watermark_text" value="<?php echo esc_attr($settings['watermark_text']); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="download_expiry"><?php _e('Download Link Expiry', 'matrimony-admin'); ?></label>
                </th>
                <td>
                    <input type="number" id="download_expiry" name="download_expiry" value="<?php echo esc_attr($settings['download_expiry']); ?>" min="1" max="168">
                    <span class="description"><?php _e('hours (download links will expire after this period)', 'matrimony-admin'); ?></span>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary"><?php _e('Save Settings', 'matrimony-admin'); ?></button>
        </p>
    </form>
</div>