<?php
class MatrimonyAdmin_RecycleBin {
    
    private $database;
    
    public function __construct() {
        $this->database = new MatrimonyAdmin_Database();
        
        // Handle actions
        add_action('admin_init', [$this, 'handle_recycle_actions']);
        
        // Schedule daily purge
        add_action('matrimony_admin_purge_expired_profiles', [$this, 'purge_expired_profiles']);
        
        if (!wp_next_scheduled('matrimony_admin_purge_expired_profiles')) {
            wp_schedule_event(time(), 'daily', 'matrimony_admin_purge_expired_profiles');
        }
    }
    
    public function render_recycle_page() {
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'matrimony-admin'));
        }
        global $wpdb;
    $table_profiles = $wpdb->prefix . 'matrimony_profiles';
    $table_deleted = $wpdb->prefix . 'matrimony_deleted_profiles';

    $query = $wpdb->prepare(
        "SELECT p.*, d.deleted_at as deletion_date, d.deleted_by, d.purge_at, u.user_login as deleted_by_name
         FROM $table_profiles p
         JOIN $table_deleted d ON p.profile_id = d.profile_id
         LEFT JOIN {$wpdb->users} u ON d.deleted_by = u.ID
         WHERE p.deleted_at IS NOT NULL
         ORDER BY d.deleted_at DESC"
    );

    $profiles = $wpdb->get_results($query, ARRAY_A);
        // Get deleted profiles
        global $wpdb;
        $table_profiles = $wpdb->prefix . 'matrimony_profiles';
        $table_deleted = $wpdb->prefix . 'matrimony_deleted_profiles';
        
        $query = "SELECT p.*, d.deleted_at as deletion_date, d.purge_at, u.user_login as deleted_by 
                  FROM $table_profiles p
                  JOIN $table_deleted d ON p.profile_id = d.profile_id
                  LEFT JOIN {$wpdb->users} u ON d.deleted_by = u.ID
                  WHERE p.deleted_at IS NOT NULL
                  ORDER BY d.deleted_at DESC";
        
        $profiles = $wpdb->get_results($query, ARRAY_A);
        
        // Format profiles for display
        $formatted_profiles = [];
        foreach ($profiles as $profile) {
            $formatted_profiles[] = $this->format_profile_for_display($profile);
        }
        
        // Include template
        include MATRIMONY_ADMIN_PATH . 'templates/recycle-bin.php';
    }
    
    public function handle_recycle_actions() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'matrimony-admin-recycle' || !isset($_GET['action'])) {
            return;
        }
        
        // Verify nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'matrimony_recycle_action')) {
            wp_die(__('Security check failed', 'matrimony-admin'));
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'matrimony-admin'));
        }
        
        $profile_id = isset($_GET['profile_id']) ? sanitize_text_field($_GET['profile_id']) : '';
        if (empty($profile_id)) {
            wp_die(__('No profile specified', 'matrimony-admin'));
        }
        
        switch ($_GET['action']) {
            case 'restore':
                $this->handle_restore_profile($profile_id);
                break;
                
            case 'delete':
                $this->handle_permanent_delete($profile_id);
                break;
                
            case 'bulk_restore':
                $this->handle_bulk_restore();
                break;
                
            case 'bulk_delete':
                $this->handle_bulk_delete();
                break;
                
            default:
                wp_die(__('Invalid action', 'matrimony-admin'));
        }
    }
    
    private function handle_restore_profile($profile_id) {
        if ($this->database->restore_profile($profile_id)) {
            wp_redirect(add_query_arg([
                'page' => 'matrimony-admin-recycle',
                'restored' => 1
            ], admin_url('admin.php')));
            exit;
        } else {
            wp_die(__('Failed to restore profile', 'matrimony-admin'));
        }
    }
    
    private function handle_permanent_delete($profile_id) {
        if ($this->database->permanently_delete_profile($profile_id)) {
            wp_redirect(add_query_arg([
                'page' => 'matrimony-admin-recycle',
                'deleted' => 1
            ], admin_url('admin.php')));
            exit;
        } else {
            wp_die(__('Failed to permanently delete profile', 'matrimony-admin'));
        }
    }
    
    private function handle_bulk_restore() {
        if (!isset($_POST['profile_ids']) || !is_array($_POST['profile_ids'])) {
            wp_die(__('No profiles selected', 'matrimony-admin'));
        }
        
        $profile_ids = array_map('sanitize_text_field', $_POST['profile_ids']);
        $success_count = 0;
        
        foreach ($profile_ids as $profile_id) {
            if ($this->database->restore_profile($profile_id)) {
                $success_count++;
            }
        }
        
        wp_redirect(add_query_arg([
            'page' => 'matrimony-admin-recycle',
            'bulk_restored' => $success_count
        ], admin_url('admin.php')));
        exit;
    }
    
    private function handle_bulk_delete() {
        if (!isset($_POST['profile_ids']) || !is_array($_POST['profile_ids'])) {
            wp_die(__('No profiles selected', 'matrimony-admin'));
        }
        
        $profile_ids = array_map('sanitize_text_field', $_POST['profile_ids']);
        $success_count = 0;
        
        foreach ($profile_ids as $profile_id) {
            if ($this->database->permanently_delete_profile($profile_id)) {
                $success_count++;
            }
        }
        
        wp_redirect(add_query_arg([
            'page' => 'matrimony-admin-recycle',
            'bulk_deleted' => $success_count
        ], admin_url('admin.php')));
        exit;
    }
    
    public function purge_expired_profiles() {
        $purged_count = $this->database->purge_expired_profiles();
        
        if ($purged_count > 0) {
            error_log(sprintf('[Matrimony Admin] Purged %d expired profiles from recycle bin', $purged_count));
        }
    }
    
    private function format_profile_for_display($profile) {
        $photo_url = '';
        if (!empty($profile['photo'])) {
            $photo_path = MATRIMONY_ADMIN_UPLOAD_DIR . $profile['photo'];
            $thumb_path = preg_replace('/(\.[^.]+)$/', '-thumb$1', $photo_path);
            
            if (file_exists($thumb_path)) {
                $photo_url = MATRIMONY_ADMIN_UPLOAD_URL . basename($thumb_path);
            } elseif (file_exists($photo_path)) {
                $photo_url = MATRIMONY_ADMIN_UPLOAD_URL . $profile['photo'];
            }
        }
        
        $purge_date = date('d M Y', strtotime($profile['purge_at']));
        $days_remaining = max(0, ceil((strtotime($profile['purge_at']) - time()) / DAY_IN_SECONDS));
        
        return [
            'profile_id' => $profile['profile_id'],
            'first_name' => $profile['first_name'],
            'last_name' => $profile['last_name'],
            'dob' => date('d M Y', strtotime($profile['dob'])),
            'age' => $profile['age'],
            'gender' => ucfirst($profile['gender']),
            'marital_status' => ucfirst($profile['marital_status']),
            'caste' => $profile['caste'],
            'sub_caste' => $profile['sub_caste'],
            'photo_url' => $photo_url,
            'deleted_at' => date('d M Y H:i', strtotime($profile['deletion_date'])),
            'deleted_by' => $profile['deleted_by'] ?? __('System', 'matrimony-admin'),
            'purge_date' => $purge_date,
            'days_remaining' => $days_remaining,
            'actions' => [
                'restore' => wp_nonce_url(admin_url('admin.php?page=matrimony-admin-recycle&action=restore&profile_id=' . $profile['profile_id']), 'matrimony_recycle_action'),
                'delete' => wp_nonce_url(admin_url('admin.php?page=matrimony-admin-recycle&action=delete&profile_id=' . $profile['profile_id']), 'matrimony_recycle_action')
            ]
        ];
    }
}