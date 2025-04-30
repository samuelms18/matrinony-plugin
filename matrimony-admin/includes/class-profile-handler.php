<?php
class MatrimonyAdmin_ProfileHandler {
    
    private $database;
    
    public function __construct() {
        $this->database = new MatrimonyAdmin_Database();
        
        // Handle form submission
        add_action('admin_post_matrimony_add_profile', [$this, 'handle_add_profile']);
        
        // AJAX actions
        add_action('wp_ajax_matrimony_check_duplicate', [$this, 'ajax_check_duplicate']);
    }
    
    public function render_add_profile_page() {
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'matrimony-admin'));
        }
        
        // Get settings for reCAPTCHA
        $settings = get_option('matrimony_admin_settings');
        $recaptcha_enabled = $settings['recaptcha_enabled'] ?? false;
        
        // Include template
        include MATRIMONY_ADMIN_PATH . 'templates/add-profile.php';
    }
    
    public function handle_add_profile() {
        // Verify nonce
        if (!isset($_POST['matrimony_nonce']) || !wp_verify_nonce($_POST['matrimony_nonce'], 'matrimony_add_profile')) {
            wp_die(__('Security check failed', 'matrimony-admin'));
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'matrimony-admin'));
        }
        
        // Validate required fields
        $errors = [];
        $required = ['first_name', 'dob', 'gender', 'marital_status', 'caste'];
        
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $errors[] = sprintf(__('%s is required', 'matrimony-admin'), ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        // Validate DOB (must be at least 18 years old)
        if (!empty($_POST['dob'])) {
            $dob = new DateTime($_POST['dob']);
            $now = new DateTime();
            $age = $now->diff($dob)->y;
            
            if ($age < 18) {
                $errors[] = __('Profile must be at least 18 years old', 'matrimony-admin');
            }
        }
        
        // Validate phone number if provided
        if (!empty($_POST['phone']) && !preg_match('/^\+?[0-9\s\-\(\)]{7,20}$/', $_POST['phone'])) {
            $errors[] = __('Invalid phone number format', 'matrimony-admin');
        }
        
        // Validate reCAPTCHA if enabled
        $settings = get_option('matrimony_admin_settings');
        if ($settings['recaptcha_enabled'] ?? false) {
            $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
            
            if (empty($recaptcha_response)) {
                $errors[] = __('Please complete the reCAPTCHA verification', 'matrimony-admin');
            } else {
                $verify = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
                    'body' => [
                        'secret' => $settings['recaptcha_secret_key'],
                        'response' => $recaptcha_response,
                        'remoteip' => $_SERVER['REMOTE_ADDR']
                    ]
                ]);
                
                if (is_wp_error($verify)) {
                    $errors[] = __('reCAPTCHA verification failed', 'matrimony-admin');
                } else {
                    $response = json_decode($verify['body'], true);
                    if (!$response['success']) {
                        $errors[] = __('reCAPTCHA verification failed', 'matrimony-admin');
                    }
                }
            }
        }
        
        // Handle file uploads
        $photo = $document = null;
        
        if (!empty($_FILES['photo']['name'])) {
            $photo = $_FILES['photo'];
            // Additional validation happens in database class
        }
        
        if (!empty($_FILES['document']['name'])) {
            $document = $_FILES['document'];
            // Additional validation happens in database class
        }
        
        // If no errors, proceed to save
        if (empty($errors)) {
            $data = [
                'first_name' => sanitize_text_field($_POST['first_name']),
                'last_name' => sanitize_text_field($_POST['last_name'] ?? ''),
                'dob' => sanitize_text_field($_POST['dob']),
                'gender' => sanitize_text_field($_POST['gender']),
                'marital_status' => sanitize_text_field($_POST['marital_status']),
                'caste' => sanitize_text_field($_POST['caste']),
                'sub_caste' => sanitize_text_field($_POST['sub_caste'] ?? ''),
                'phone' => sanitize_text_field($_POST['phone'] ?? ''),
                'admin_notes' => wp_kses_post($_POST['admin_notes'] ?? ''),
                'photo' => $photo,
                'document' => $document
            ];
            
            $profile_id = $this->database->insert_profile($data);
            
            if (is_wp_error($profile_id)) {
                $errors[] = $profile_id->get_error_message();
            } else {
                // Success - redirect with success message
                wp_redirect(add_query_arg([
                    'page' => 'matrimony-admin-add',
                    'success' => 1,
                    'profile_id' => urlencode($profile_id)
                ], admin_url('admin.php')));
                exit;
            }
        }
        
        // If we got here, there were errors
        set_transient('matrimony_profile_errors', $errors, 30);
        wp_redirect(add_query_arg([
            'page' => 'matrimony-admin-add',
            'error' => 1
        ], admin_url('admin.php')));
        exit;
    }
    
    public function ajax_check_duplicate() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'matrimony-admin-nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'matrimony-admin')]);
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'matrimony-admin')]);
        }
        
        // Validate input
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');
        $gender = sanitize_text_field($_POST['gender'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        
        if (empty($first_name) || empty($gender)) {
            wp_send_json_error(['message' => __('First name and gender are required', 'matrimony-admin')]);
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'matrimony_profiles';
        
        // Check for duplicates
        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table 
             WHERE first_name = %s 
             AND last_name = %s 
             AND gender = %s 
             AND phone = %s 
             AND deleted_at IS NULL",
            $first_name,
            $last_name,
            $gender,
            $phone
        );
        
        $count = $wpdb->get_var($query);
        
        if ($count > 0) {
            wp_send_json_success(['is_duplicate' => true]);
        } else {
            wp_send_json_success(['is_duplicate' => false]);
        }
    }
    
    public function get_sub_caste_options($caste) {
        $options = [];
        
        switch ($caste) {
            case 'Iyer':
                $options = ['Vadamal', 'Brahacharanam'];
                break;
            case 'Iyengar':
                $options = ['Thenkalai', 'Vadakalai'];
                break;
            default:
                $options = [];
        }
        
        return $options;
    }
}