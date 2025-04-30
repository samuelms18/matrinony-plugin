<?php
class MatrimonyAdmin_Utilities {
    
    public static function display_admin_notice() {
        if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_GET['profile_id'])) {
            $profile_id = sanitize_text_field($_GET['profile_id']);
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php printf(__('Profile %s added successfully!', 'matrimony-admin'), '<strong>' . $profile_id . '</strong>'); ?></p>
            </div>
            <?php
        }
        
        if (isset($_GET['error']) && $_GET['error'] == '1') {
            $errors = get_transient('matrimony_profile_errors');
            if ($errors && is_array($errors)) {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong><?php _e('Error(s) occurred:', 'matrimony-admin'); ?></strong></p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php
                delete_transient('matrimony_profile_errors');
            }
        }
        
        if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('Profile moved to recycle bin.', 'matrimony-admin'); ?></p>
            </div>
            <?php
        }
        
        if (isset($_GET['restored']) && $_GET['restored'] == '1') {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('Profile restored from recycle bin.', 'matrimony-admin'); ?></p>
            </div>
            <?php
        }
        
        if (isset($_GET['bulk_restored']) && is_numeric($_GET['bulk_restored'])) {
            $count = intval($_GET['bulk_restored']);
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php printf(_n('%d profile restored from recycle bin.', '%d profiles restored from recycle bin.', $count, 'matrimony-admin'), $count); ?></p>
            </div>
            <?php
        }
        
        if (isset($_GET['bulk_deleted']) && is_numeric($_GET['bulk_deleted'])) {
            $count = intval($_GET['bulk_deleted']);
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php printf(_n('%d profile permanently deleted.', '%d profiles permanently deleted.', $count, 'matrimony-admin'), $count); ?></p>
            </div>
            <?php
        }
        
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == '1') {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('Settings saved successfully.', 'matrimony-admin'); ?></p>
            </div>
            <?php
        }
    }
    
    public static function get_caste_display_name($caste) {
        $names = [
            'Iyer' => __('Iyer', 'matrimony-admin'),
            'Iyengar' => __('Iyengar', 'matrimony-admin'),
            'Kannada Brahmin' => __('Kannada Brahmin', 'matrimony-admin'),
            'Telugu Brahmin' => __('Telugu Brahmin', 'matrimony-admin'),
            'Others' => __('Others', 'matrimony-admin')
        ];
        
        return $names[$caste] ?? $caste;
    }
    
    public static function get_sub_caste_display_name($sub_caste) {
        $names = [
            'Vadamal' => __('Vadamal', 'matrimony-admin'),
            'Brahacharanam' => __('Brahacharanam', 'matrimony-admin'),
            'Thenkalai' => __('Thenkalai', 'matrimony-admin'),
            'Vadakalai' => __('Vadakalai', 'matrimony-admin')
        ];
        
        return $names[$sub_caste] ?? $sub_caste;
    }
    
    public static function get_gender_display_name($gender) {
        $names = [
            'male' => __('Male', 'matrimony-admin'),
            'female' => __('Female', 'matrimony-admin')
        ];
        
        return $names[$gender] ?? $gender;
    }
    
    public static function get_marital_status_display_name($status) {
        $names = [
            'single' => __('Single', 'matrimony-admin'),
            'divorced' => __('Divorced', 'matrimony-admin')
        ];
        
        return $names[$status] ?? $status;
    }
    
    public static function get_age_range_options() {
        return [
            '18-25' => '18-25',
            '26-30' => '26-30',
            '31-35' => '31-35',
            '36-40' => '36-40',
            '41-45' => '41-45',
            '46-50' => '46-50',
            '51+' => '51+'
        ];
    }
}