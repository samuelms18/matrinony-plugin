<?php
class MatrimonyAdmin_Settings {
    
    public function __construct() {
        // Handle form submission
        add_action('admin_post_matrimony_save_settings', [$this, 'handle_save_settings']);
    }
    
    public function render_settings_page() {
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'matrimony-admin'));
        }
        
        $settings = get_option('matrimony_admin_settings');
        $caste_options = $this->get_caste_options();
        
        // Include template
        include MATRIMONY_ADMIN_PATH . 'templates/settings.php';
    }
    
    public function handle_save_settings() {
        // Verify nonce
        if (!isset($_POST['matrimony_settings_nonce']) || !wp_verify_nonce($_POST['matrimony_settings_nonce'], 'matrimony_save_settings')) {
            wp_die(__('Security check failed', 'matrimony-admin'));
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'matrimony-admin'));
        }
        
        $current_settings = get_option('matrimony_admin_settings');
        $new_settings = [];
        
        // General settings
        $new_settings['upload_dir'] = sanitize_text_field($_POST['upload_dir'] ?? $current_settings['upload_dir']);
        $new_settings['auto_purge_days'] = intval($_POST['auto_purge_days'] ?? $current_settings['auto_purge_days']);
        $new_settings['profile_id_format'] = sanitize_text_field($_POST['profile_id_format'] ?? $current_settings['profile_id_format']);
        
        // reCAPTCHA settings
        $new_settings['recaptcha_enabled'] = isset($_POST['recaptcha_enabled']) ? 1 : 0;
        $new_settings['recaptcha_site_key'] = sanitize_text_field($_POST['recaptcha_site_key'] ?? $current_settings['recaptcha_site_key']);
        $new_settings['recaptcha_secret_key'] = sanitize_text_field($_POST['recaptcha_secret_key'] ?? $current_settings['recaptcha_secret_key']);
        
        // Download settings
        $new_settings['watermark_enabled'] = isset($_POST['watermark_enabled']) ? 1 : 0;
        $new_settings['watermark_text'] = sanitize_text_field($_POST['watermark_text'] ?? $current_settings['watermark_text']);
        $new_settings['download_expiry'] = intval($_POST['download_expiry'] ?? $current_settings['download_expiry']);
        
        // Update settings
        update_option('matrimony_admin_settings', $new_settings);
        
        // Redirect with success message
        wp_redirect(add_query_arg([
            'page' => 'matrimony-admin-settings',
            'settings-updated' => '1'
        ], admin_url('admin.php')));
        exit;
    }
    
    private function get_caste_options() {
        global $wpdb;
        return $wpdb->get_col(
            "SELECT DISTINCT caste 
             FROM {$wpdb->prefix}matrimony_profiles 
             ORDER BY caste"
        );
    }
}