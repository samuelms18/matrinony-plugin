<?php
class MatrimonyAdmin_Views {
    
    private $database;
    
    public function __construct() {
        $this->database = new MatrimonyAdmin_Database();
        add_action('admin_init', [$this, 'handle_profile_actions']);
    }
    
    public function render_manage_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'matrimony-admin'));
        }

        $view_mode = isset($_GET['view']) && in_array($_GET['view'], ['list', 'folder']) 
            ? sanitize_text_field($_GET['view']) 
            : 'list';
        
        global $wpdb;
        $castes = $wpdb->get_col(
            "SELECT DISTINCT caste 
             FROM {$wpdb->prefix}matrimony_profiles 
             WHERE deleted_at IS NULL 
             ORDER BY caste"
        );
        
        if ($view_mode === 'folder') {
            include MATRIMONY_ADMIN_PATH . 'templates/folder-view.php';
        } else {
            include MATRIMONY_ADMIN_PATH . 'templates/list-view.php';
        }
    }

    public function handle_profile_actions() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'matrimony-admin-manage' || !isset($_GET['action'])) {
            return;
        }

        if (in_array($_GET['action'], ['delete', 'download']) && 
            (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'matrimony_profile_action'))) {
            wp_die(__('Security check failed', 'matrimony-admin'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'matrimony-admin'));
        }

        $profile_id = isset($_GET['profile_id']) ? sanitize_text_field($_GET['profile_id']) : '';
        if (empty($profile_id)) {
            wp_die(__('No profile specified', 'matrimony-admin'));
        }

        switch ($_GET['action']) {
            case 'preview':
                return;

            case 'delete':
                $this->handle_delete_profile($profile_id);
                break;

            case 'download':
                $this->handle_download_profile($profile_id);
                break;

            default:
                wp_die(__('Invalid action', 'matrimony-admin'));
        }
    }

    private function handle_delete_profile($profile_id) {
        $current_user_id = get_current_user_id();
        
        if ($this->database->soft_delete_profile($profile_id, $current_user_id)) {
            wp_redirect(add_query_arg([
                'page' => 'matrimony-admin-manage',
                'deleted' => 1
            ], admin_url('admin.php')));
            exit;
        } else {
            wp_die(__('Failed to delete profile', 'matrimony-admin'));
        }
    }

    private function handle_download_profile($profile_id) {
        $profile = $this->database->get_profile($profile_id);

        if (!$profile || empty($profile['document'])) {
            wp_die(__('No document available for download', 'matrimony-admin'));
        }

        $file_path = MATRIMONY_ADMIN_UPLOAD_DIR . $profile['document'];

        if (!file_exists($file_path)) {
            wp_die(__('File not found', 'matrimony-admin'));
        }

        $filename = $profile['profile_id'] . '_' . $profile['first_name'] . '_' . $profile['last_name'] . '.' . pathinfo($file_path, PATHINFO_EXTENSION);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));

        ob_clean();
        flush();
        readfile($file_path);
        exit;
    }

    public function get_folder_structure() {
        global $wpdb;
        $table = $wpdb->prefix . 'matrimony_profiles';

        $query = "SELECT 
                    gender, 
                    caste, 
                    sub_caste, 
                    COUNT(*) as count 
                  FROM $table 
                  WHERE deleted_at IS NULL 
                  GROUP BY gender, caste, sub_caste 
                  ORDER BY gender DESC, caste, sub_caste";

        $results = $wpdb->get_results($query, ARRAY_A);

        $structure = [
            'male' => [],
            'female' => []
        ];

        foreach ($results as $row) {
            $gender = $row['gender'];
            $caste = $row['caste'];
            $sub_caste = $row['sub_caste'];

            if (!isset($structure[$gender][$caste])) {
                $structure[$gender][$caste] = [
                    'name' => $caste,
                    'count' => 0,
                    'sub_castes' => []
                ];
            }

            if (!empty($sub_caste)) {
                $structure[$gender][$caste]['sub_castes'][$sub_caste] = [
                    'name' => $sub_caste,
                    'count' => $row['count']
                ];
            }

            $structure[$gender][$caste]['count'] += $row['count'];
        }

        return $structure;
    }

    public function get_profiles_by_folder($gender, $caste, $sub_caste = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'matrimony_profiles';

        $where = [
            "gender = %s",
            "caste = %s",
            "deleted_at IS NULL"
        ];

        $params = [$gender, $caste];

        if (!empty($sub_caste)) {
            $where[] = "sub_caste = %s";
            $params[] = $sub_caste;
        }

        $where_clause = implode(' AND ', $where);

        $query = $wpdb->prepare(
            "SELECT * FROM $table 
             WHERE $where_clause 
             ORDER BY first_name, last_name",
            $params
        );

        $profiles = $wpdb->get_results($query, ARRAY_A);

        return $profiles;
    }

    protected function format_profile_for_display($profile) {
        $photo_url = '';
        if (!empty($profile['photo'])) {
            $base_url = wp_upload_dir()['baseurl'];
            $photo_url = $base_url . '/' . $profile['photo'];
            
            // Check for thumbnail
            $thumb_url = preg_replace('/(\.[^.]+)$/', '-thumb$1', $photo_url);
            $thumb_path = preg_replace('/(\.[^.]+)$/', '-thumb$1', wp_upload_dir()['basedir'] . '/' . $profile['photo']);
            $photo_url = file_exists($thumb_path) ? $thumb_url : $photo_url;
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
            'document_url' => !empty($profile['document']) ? wp_upload_dir()['baseurl'] . '/' . $profile['document'] : '',
            'created_at' => date('d M Y H:i', strtotime($profile['created_at'])),
            'actions' => [
                'preview' => admin_url('admin.php?page=matrimony-admin-manage&action=preview&profile_id=' . $profile['profile_id']),
                'download' => wp_nonce_url(admin_url('admin.php?page=matrimony-admin-manage&action=download&profile_id=' . $profile['profile_id']), 'matrimony_profile_action'),
                'delete' => wp_nonce_url(admin_url('admin.php?page=matrimony-admin-manage&action=delete&profile_id=' . $profile['profile_id']), 'matrimony_profile_action')
            ]
        ];
    }
}
