<?php
class MatrimonyAdmin_Database {
    
    private static $table_profiles = 'matrimony_profiles';
    private static $table_deleted = 'matrimony_deleted_profiles';
    
    public static function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Main profiles table
        $table_name = $wpdb->prefix . self::$table_profiles;
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            profile_id varchar(50) NOT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) DEFAULT '',
            dob date NOT NULL,
            age tinyint(3) NOT NULL,
            gender enum('male','female') NOT NULL,
            marital_status enum('single','divorced') NOT NULL,
            caste varchar(50) NOT NULL,
            sub_caste varchar(50) DEFAULT '',
            photo varchar(255) DEFAULT '',
            document varchar(255) DEFAULT '',
            phone varchar(20) DEFAULT '',
            admin_notes longtext DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY profile_id (profile_id),
            KEY gender (gender),
            KEY caste (caste),
            KEY sub_caste (sub_caste),
            KEY marital_status (marital_status),
            KEY age (age),
            KEY deleted_at (deleted_at)
        ) $charset_collate;";
        
        // Deleted profiles table (for tracking)
        $table_deleted = $wpdb->prefix . self::$table_deleted;
        
        $sql .= "CREATE TABLE $table_deleted (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            profile_id varchar(50) NOT NULL,
            deleted_by int(11) NOT NULL,
            deleted_at datetime DEFAULT CURRENT_TIMESTAMP,
            purge_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY profile_id (profile_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Create upload directory if it doesn't exist
        if (!file_exists(MATRIMONY_ADMIN_UPLOAD_DIR)) {
            wp_mkdir_p(MATRIMONY_ADMIN_UPLOAD_DIR);
        }
        
        // Add default options if they don't exist
    add_option('matrimony_admin_settings', [
        'upload_dir' => MATRIMONY_ADMIN_UPLOAD_DIR,
        'recaptcha_enabled' => false,
        'recaptcha_site_key' => '',
        'recaptcha_secret_key' => '',
        'auto_purge_days' => 30,
        'profile_id_format' => 'GENDER_LETTER+CAST3+SUBCAST3+SERIAL_NO/DOB_YEAR', // Old format - can remove or keep
        'watermark_enabled' => false,
        'watermark_text' => 'Confidential - Matrimony Admin',
        'download_expiry' => 24 // hours
    ]);
    }
    
    public static function deactivate() {
        // We don't drop tables on deactivation to preserve data
        // But we can clean up options if needed
        // delete_option('matrimony_admin_settings');
    }

    private function ensure_upload_dir() {
    $upload_dir = wp_upload_dir();
    $matrimony_dir = $upload_dir['basedir'] . '/matrimony';
    
    if (!file_exists($matrimony_dir)) {
        wp_mkdir_p($matrimony_dir);
    }
    
    // Add index.php for security
    if (!file_exists($matrimony_dir . '/index.php')) {
        file_put_contents($matrimony_dir . '/index.php', '<?php // Silence is golden');
    }
}
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_profiles = $wpdb->prefix . self::$table_profiles;
        $this->table_deleted = $wpdb->prefix . self::$table_deleted;
    }
    
    public function insert_profile($data) {
        // Calculate age from DOB
        $dob = new DateTime($data['dob']);
        $now = new DateTime();
        $age = $now->diff($dob)->y;
        
        // Generate profile ID
        $profile_id = $this->generate_profile_id($data);
        
        $insert_data = [
            'profile_id' => $profile_id,
            'first_name' => sanitize_text_field($data['first_name']),
            'last_name' => sanitize_text_field($data['last_name']),
            'dob' => $data['dob'],
            'age' => $age,
            'gender' => $data['gender'],
            'marital_status' => $data['marital_status'],
            'caste' => $data['caste'],
            'sub_caste' => $data['sub_caste'] ?? '',
            'phone' => sanitize_text_field($data['phone']),
            'admin_notes' => wp_kses_post($data['admin_notes'] ?? ''),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];
        
        // Handle file uploads if present
        if (!empty($data['photo'])) {
            $insert_data['photo'] = $this->handle_file_upload($data['photo'], $profile_id, 'photo');
        }
        
        if (!empty($data['document'])) {
            $insert_data['document'] = $this->handle_file_upload($data['document'], $profile_id, 'document');
        }
        
        $result = $this->wpdb->insert(
            $this->table_profiles,
            $insert_data,
            [
                '%s', // profile_id
                '%s', // first_name
                '%s', // last_name
                '%s', // dob
                '%d', // age
                '%s', // gender
                '%s', // marital_status
                '%s', // caste
                '%s', // sub_caste
                '%s', // phone
                '%s', // admin_notes
                '%s', // created_at
                '%s'  // updated_at
            ]
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to insert profile into database', 'matrimony-admin'));
        }
        
        return $profile_id;
    }
    
    private function generate_profile_id($data) {
    $settings = get_option('matrimony_admin_settings');
    
    // Get last serial number for this gender+caste combination
    $last_serial = $this->wpdb->get_var(
        $this->wpdb->prepare(
            "SELECT COUNT(*) FROM $this->table_profiles 
             WHERE gender = %s AND caste = %s",
            $data['gender'],
            $data['caste']
        )
    );
    
    $serial_no = str_pad($last_serial + 1, 3, '0', STR_PAD_LEFT);
    
    // Gender first letter (M/F)
    $gender_letter = strtoupper(substr($data['gender'], 0, 1));
    
    // Caste first 3 letters (uppercase)
    $caste_abbr = strtoupper(substr($data['caste'], 0, 3));
    
    // Sub-caste first 3 letters (if exists)
    $sub_caste_abbr = '';
    if (!empty($data['sub_caste'])) {
        $sub_caste_abbr = strtoupper(substr($data['sub_caste'], 0, 3));
    }
    
    // Year from DOB
    $dob_year = date('Y', strtotime($data['dob']));
    
    // Build profile ID in the new format: MIYEVAD0011948
    $profile_id = $gender_letter . $caste_abbr . $sub_caste_abbr . $serial_no . $dob_year;
    
    return $profile_id;
}
    
    private function handle_file_upload($file, $profile_id, $type) {
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    // Validate file type
    $allowed_mimes = [
        'photo' => ['jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png'],
        'document' => ['pdf' => 'application/pdf', 'doc|docx' => 'application/msword']
    ];

    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $mime_type = false;

    foreach ($allowed_mimes[$type] as $ext_pattern => $mime) {
        if (preg_match('/^(' . $ext_pattern . ')$/i', $file_ext)) {
            $mime_type = $mime;
            break;
        }
    }

    if (!$mime_type) {
        error_log('Invalid file type: ' . $file['name']);
        return new WP_Error('invalid_file', __('Invalid file type', 'matrimony-admin'));
    }

    // Set custom upload directory
    add_filter('upload_dir', [$this, 'custom_upload_dir']);

    $upload = wp_handle_upload($file, [
        'test_form' => false,
        'action' => 'matrimony_upload',
        'mimes' => array_flip(explode('|', array_search($mime_type, $allowed_mimes[$type])))
    ]);

    remove_filter('upload_dir', [$this, 'custom_upload_dir']);

    if (isset($upload['error'])) {
        error_log('Upload error: ' . $upload['error']);
        return new WP_Error('upload_error', $upload['error']);
    }

    // Generate safe filename
    $filename = $profile_id . '_' . sanitize_file_name($file['name']);
    $new_path = trailingslashit(dirname($upload['file'])) . $filename;
    rename($upload['file'], $new_path);

    // Get relative path (from uploads folder)
    $relative_path = str_replace(wp_upload_dir()['basedir'] . '/', '', $new_path);
    
    // For photos, generate thumbnail
    if ($type === 'photo') {
        $this->generate_thumbnail($new_path);
    }

    return $relative_path; // Store relative path in database
}

// Custom upload directory handler
public function custom_upload_dir($dirs) {
    $dirs['path'] = str_replace('/uploads', '/uploads/matrimony', $dirs['path']);
    $dirs['url'] = str_replace('/uploads', '/uploads/matrimony', $dirs['url']);
    $dirs['subdir'] = '/matrimony';
    return $dirs;
}
    
    private function generate_thumbnail($file_path) {
        if (!function_exists('wp_get_image_editor')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        
        $editor = wp_get_image_editor($file_path);
        if (is_wp_error($editor)) {
            error_log('Image editor error: ' . $editor->get_error_message());
            return $editor;
        }
        
        $result = $editor->resize(300, 300, true);
    if (is_wp_error($result)) {
        error_log('Resize error: ' . $result->get_error_message());
        return $result;
    }

    $file_info = pathinfo($file_path);
    $thumb_path = $file_info['dirname'] . '/' . $file_info['filename'] . '-thumb.' . $file_info['extension'];
    
    $result = $editor->save($thumb_path);
    if (is_wp_error($result)) {
        error_log('Save thumbnail error: ' . $result->get_error_message());
        return $result;
    }

    error_log('Thumbnail generated at: ' . $thumb_path);
    return true;
}
    
    public function get_profile($profile_id) {
        $query = $this->wpdb->prepare(
            "SELECT * FROM $this->table_profiles WHERE profile_id = %s AND deleted_at IS NULL",
            $profile_id
        );
        
        return $this->wpdb->get_row($query, ARRAY_A);
    }
    
    public function search_profiles($args) {
        $defaults = [
            'first_name' => '',
            'last_name' => '',
            'age_min' => 18,
            'age_max' => 100,
            'gender' => '',
            'marital_status' => '',
            'caste' => '',
            'sub_caste' => '',
            'per_page' => 10,
            'page' => 1,
            'orderby' => 'created_at',
            'order' => 'DESC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $where = ["deleted_at IS NULL"];
        $params = [];
        
        if (!empty($args['first_name'])) {
            $where[] = "first_name LIKE %s";
            $params[] = '%' . $this->wpdb->esc_like($args['first_name']) . '%';
        }
        
        if (!empty($args['last_name'])) {
            $where[] = "last_name LIKE %s";
            $params[] = '%' . $this->wpdb->esc_like($args['last_name']) . '%';
        }
        
        if (!empty($args['age_min'])) {
            $where[] = "age >= %d";
            $params[] = (int)$args['age_min'];
        }
        
        if (!empty($args['age_max'])) {
            $where[] = "age <= %d";
            $params[] = (int)$args['age_max'];
        }
        
        if (!empty($args['gender'])) {
            $where[] = "gender = %s";
            $params[] = $args['gender'];
        }
        
        if (!empty($args['marital_status'])) {
            $where[] = "marital_status = %s";
            $params[] = $args['marital_status'];
        }
        
        if (!empty($args['caste'])) {
            $where[] = "caste = %s";
            $params[] = $args['caste'];
        }
        
        if (!empty($args['sub_caste'])) {
            $where[] = "sub_caste = %s";
            $params[] = $args['sub_caste'];
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Count total rows for pagination
        $count_query = "SELECT COUNT(*) FROM $this->table_profiles WHERE $where_clause";
        if (!empty($params)) {
            $count_query = $this->wpdb->prepare($count_query, $params);
        }
        
        $total_items = $this->wpdb->get_var($count_query);
        
        // Get data
        $offset = ($args['page'] - 1) * $args['per_page'];
        $orderby = in_array($args['orderby'], ['first_name', 'last_name', 'age', 'created_at']) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
        
        $data_query = "SELECT * FROM $this->table_profiles WHERE $where_clause 
                      ORDER BY $orderby $order 
                      LIMIT %d, %d";
        
        $params[] = $offset;
        $params[] = $args['per_page'];
        
        $data_query = $this->wpdb->prepare($data_query, $params);
        $profiles = $this->wpdb->get_results($data_query, ARRAY_A);
        
        return [
            'profiles' => $profiles,
            'total_items' => $total_items,
            'total_pages' => ceil($total_items / $args['per_page'])
        ];
    }
    
    public function soft_delete_profile($profile_id, $user_id) {
        // Update main table
        $result = $this->wpdb->update(
            $this->table_profiles,
            ['deleted_at' => current_time('mysql')],
            ['profile_id' => $profile_id],
            ['%s'],
            ['%s']
        );
        
        if ($result === false) {
            return false;
        }
        
        // Add to deleted table for tracking
        $purge_days = get_option('matrimony_admin_settings')['auto_purge_days'] ?? 30;
        $purge_at = date('Y-m-d H:i:s', strtotime("+$purge_days days"));
        
        $this->wpdb->insert(
            $this->table_deleted,
            [
                'profile_id' => $profile_id,
                'deleted_by' => $user_id,
                'deleted_at' => current_time('mysql'),
                'purge_at' => $purge_at
            ],
            ['%s', '%d', '%s', '%s']
        );
        
        return true;
    }
    
    public function restore_profile($profile_id) {
        // Update main table
        $result = $this->wpdb->update(
            $this->table_profiles,
            ['deleted_at' => NULL],
            ['profile_id' => $profile_id],
            ['%s'],
            ['%s']
        );
        
        if ($result === false) {
            return false;
        }
        
        // Remove from deleted table
        $this->wpdb->delete(
            $this->table_deleted,
            ['profile_id' => $profile_id],
            ['%s']
        );
        
        return true;
    }
    
    public function permanently_delete_profile($profile_id) {
        // First get profile to delete files
        $profile = $this->get_profile($profile_id);
        
        if ($profile) {
            // Delete photo and document files
            if (!empty($profile['photo'])) {
                $photo_path = MATRIMONY_ADMIN_UPLOAD_DIR . $profile['photo'];
                $thumb_path = preg_replace('/(\.[^.]+)$/', '-thumb$1', $photo_path);
                
                if (file_exists($photo_path)) {
                    unlink($photo_path);
                }
                if (file_exists($thumb_path)) {
                    unlink($thumb_path);
                }
            }
            
            if (!empty($profile['document'])) {
                $doc_path = MATRIMONY_ADMIN_UPLOAD_DIR . $profile['document'];
                if (file_exists($doc_path)) {
                    unlink($doc_path);
                }
            }
        }
        
        // Delete from main table
        $result = $this->wpdb->delete(
            $this->table_profiles,
            ['profile_id' => $profile_id],
            ['%s']
        );
        
        if ($result === false) {
            return false;
        }
        
        // Delete from deleted table
        $this->wpdb->delete(
            $this->table_deleted,
            ['profile_id' => $profile_id],
            ['%s']
        );
        
        return true;
    }
    
    public function get_stats() {
        $stats = [];
        
        // Total profiles
        $stats['total_profiles'] = $this->wpdb->get_var(
            "SELECT COUNT(*) FROM $this->table_profiles WHERE deleted_at IS NULL"
        );
        
        // Gender distribution
        $stats['gender_dist'] = $this->wpdb->get_results(
            "SELECT gender, COUNT(*) as count 
             FROM $this->table_profiles 
             WHERE deleted_at IS NULL 
             GROUP BY gender",
            ARRAY_A
        );
        
        // Caste distribution
        $stats['caste_dist'] = $this->wpdb->get_results(
            "SELECT caste, COUNT(*) as count 
             FROM $this->table_profiles 
             WHERE deleted_at IS NULL 
             GROUP BY caste",
            ARRAY_A
        );
        
        // Monthly growth
        $stats['monthly_growth'] = $this->wpdb->get_results(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
             FROM $this->table_profiles 
             WHERE deleted_at IS NULL 
             GROUP BY month 
             ORDER BY month DESC 
             LIMIT 12",
            ARRAY_A
        );
        
        // Deleted count
        $stats['deleted_count'] = $this->wpdb->get_var(
            "SELECT COUNT(*) FROM $this->table_profiles WHERE deleted_at IS NOT NULL"
        );
        
        return $stats;
    }
    
    public function purge_expired_profiles() {
        $now = current_time('mysql');
        
        // Get profiles to purge
        $profiles = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT d.profile_id 
                 FROM $this->table_deleted d
                 JOIN $this->table_profiles p ON d.profile_id = p.profile_id
                 WHERE d.purge_at <= %s",
                $now
            ),
            ARRAY_A
        );
        
        $purged = 0;
        
        foreach ($profiles as $profile) {
            if ($this->permanently_delete_profile($profile['profile_id'])) {
                $purged++;
            }
        }
        
        return $purged;
    }
}