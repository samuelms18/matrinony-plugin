<?php
class MatrimonyAdmin_Search {
    
    private $database;
    
    public function __construct() {
        $this->database = new MatrimonyAdmin_Database();
        
        // AJAX actions
        add_action('wp_ajax_matrimony_search_profiles', [$this, 'ajax_search_profiles']);
        add_action('wp_ajax_matrimony_export_profiles', [$this, 'ajax_export_profiles']);
        add_action('wp_ajax_matrimony_bulk_action', [$this, 'ajax_bulk_action']);
    }
    
    public function render_search_page() {
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'matrimony-admin'));
        }
        
        // Get caste options from database
        global $wpdb;
        $castes = $wpdb->get_col(
            "SELECT DISTINCT caste 
             FROM {$wpdb->prefix}matrimony_profiles 
             WHERE deleted_at IS NULL 
             ORDER BY caste"
        );
        
        // Include template
        include MATRIMONY_ADMIN_PATH . 'templates/search.php';
    }
    
    public function ajax_search_profiles() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'matrimony-admin-nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'matrimony-admin')]);
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'matrimony-admin')]);
        }
        
        // Prepare search args
        $args = [
            'first_name' => sanitize_text_field($_POST['first_name'] ?? ''),
            'last_name' => sanitize_text_field($_POST['last_name'] ?? ''),
            'age_min' => intval($_POST['age_min'] ?? 18),
            'age_max' => intval($_POST['age_max'] ?? 100),
            'gender' => sanitize_text_field($_POST['gender'] ?? ''),
            'marital_status' => sanitize_text_field($_POST['marital_status'] ?? ''),
            'caste' => sanitize_text_field($_POST['caste'] ?? ''),
            'sub_caste' => sanitize_text_field($_POST['sub_caste'] ?? ''),
            'per_page' => intval($_POST['per_page'] ?? 10),
            'page' => intval($_POST['page'] ?? 1),
            'orderby' => sanitize_text_field($_POST['orderby'] ?? 'created_at'),
            'order' => sanitize_text_field($_POST['order'] ?? 'DESC')
        ];
        
        // Perform search
        $results = $this->database->search_profiles($args);
        
        // Prepare response
        $response = [
            'profiles' => [],
            'pagination' => [
                'total' => $results['total_items'],
                'per_page' => $args['per_page'],
                'current_page' => $args['page'],
                'total_pages' => $results['total_pages']
            ]
        ];
        
        // Format profiles for response
        foreach ($results['profiles'] as $profile) {
            $response['profiles'][] = $this->format_profile_for_display($profile);
        }
        
        wp_send_json_success($response);
    }
    
    public function ajax_export_profiles() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'matrimony-admin-nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'matrimony-admin')]);
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'matrimony-admin')]);
        }
        
        // Get profile IDs to export
        $profile_ids = isset($_POST['profile_ids']) ? array_map('sanitize_text_field', (array)$_POST['profile_ids']) : [];
        
        if (empty($profile_ids)) {
            wp_send_json_error(['message' => __('No profiles selected for export', 'matrimony-admin')]);
        }
        
        // Get profiles data
        global $wpdb;
        $table = $wpdb->prefix . 'matrimony_profiles';
        $placeholders = implode(',', array_fill(0, count($profile_ids), '%s'));
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table 
             WHERE profile_id IN ($placeholders) 
             AND deleted_at IS NULL",
            $profile_ids
        );
        
        $profiles = $wpdb->get_results($query, ARRAY_A);
        
        if (empty($profiles)) {
            wp_send_json_error(['message' => __('No profiles found', 'matrimony-admin')]);
        }
        
        // Generate CSV
        $filename = 'matrimony-profiles-' . date('Y-m-d-H-i-s') . '.csv';
        $output = fopen('php://output', 'w');
        
        // Set headers for download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        // CSV header
        $headers = [
            __('Profile ID', 'matrimony-admin'),
            __('First Name', 'matrimony-admin'),
            __('Last Name', 'matrimony-admin'),
            __('Date of Birth', 'matrimony-admin'),
            __('Age', 'matrimony-admin'),
            __('Gender', 'matrimony-admin'),
            __('Marital Status', 'matrimony-admin'),
            __('Caste', 'matrimony-admin'),
            __('Sub Caste', 'matrimony-admin'),
            __('Phone', 'matrimony-admin'),
            __('Created At', 'matrimony-admin')
        ];
        
        fputcsv($output, $headers);
        
        // CSV rows
        foreach ($profiles as $profile) {
            $row = [
                $profile['profile_id'],
                $profile['first_name'],
                $profile['last_name'],
                $profile['dob'],
                $profile['age'],
                ucfirst($profile['gender']),
                ucfirst($profile['marital_status']),
                $profile['caste'],
                $profile['sub_caste'],
                $profile['phone'],
                $profile['created_at']
            ];
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    public function ajax_bulk_action() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'matrimony-admin-nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'matrimony-admin')]);
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'matrimony-admin')]);
        }
        
        // Validate action
        $action = sanitize_text_field($_POST['bulk_action'] ?? '');
        if (!in_array($action, ['delete', 'export'])) {
            wp_send_json_error(['message' => __('Invalid action', 'matrimony-admin')]);
        }
        
        // Get profile IDs
        $profile_ids = isset($_POST['profile_ids']) ? array_map('sanitize_text_field', (array)$_POST['profile_ids']) : [];
        if (empty($profile_ids)) {
            wp_send_json_error(['message' => __('No profiles selected', 'matrimony-admin')]);
        }
        
        // Process action
        if ($action === 'delete') {
            $success_count = 0;
            $current_user_id = get_current_user_id();
            
            foreach ($profile_ids as $profile_id) {
                if ($this->database->soft_delete_profile($profile_id, $current_user_id)) {
                    $success_count++;
                }
            }
            
            wp_send_json_success([
                'message' => sprintf(_n('%d profile moved to recycle bin', '%d profiles moved to recycle bin', $success_count, 'matrimony-admin'), $success_count)
            ]);
        } elseif ($action === 'export') {
            // This is handled by ajax_export_profiles, we shouldn't reach here
            wp_send_json_error(['message' => __('Invalid action', 'matrimony-admin')]);
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
            'phone' => $profile['phone'],
            'photo_url' => $photo_url,
            'created_at' => date('d M Y H:i', strtotime($profile['created_at'])),
            'actions' => [
                'preview' => admin_url('admin.php?page=matrimony-admin-manage&action=preview&profile_id=' . $profile['profile_id']),
                'download' => admin_url('admin.php?page=matrimony-admin-manage&action=download&profile_id=' . $profile['profile_id']),
                'delete' => admin_url('admin.php?page=matrimony-admin-manage&action=delete&profile_id=' . $profile['profile_id'])
            ]
        ];
    }
}