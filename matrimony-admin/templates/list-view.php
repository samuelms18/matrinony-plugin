<div class="wrap matrimony-admin">
    <h1><?php _e('Manage Profiles', 'matrimony-admin'); ?></h1>

    <?php MatrimonyAdmin_Utilities::display_admin_notice(); ?>

    <div class="view-switcher">
        <a href="<?php echo admin_url('admin.php?page=matrimony-admin-manage&view=list'); ?>" class="button button-primary">
            <?php _e('List View', 'matrimony-admin'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=matrimony-admin-manage&view=folder'); ?>" class="button">
            <?php _e('Folder View', 'matrimony-admin'); ?>
        </a>
    </div>

    <?php 
    if (isset($_GET['action']) && $_GET['action'] === 'preview' && isset($_GET['profile_id'])) {
        $profile_id = sanitize_text_field($_GET['profile_id']);
        $profile = $this->database->get_profile($profile_id);

        if ($profile) {
            include MATRIMONY_ADMIN_PATH . 'templates/profile-preview.php';
            return;
        }
    }
    ?>

    <div class="profiles-list-container">
        <div class="list-filters">
            <form method="get" action="<?php echo admin_url('admin.php'); ?>">
                <input type="hidden" name="page" value="matrimony-admin-manage">
                <input type="hidden" name="view" value="list">

                <div class="filter-row">
                    <div class="filter-group">
                        <label for="filter_gender"><?php _e('Gender', 'matrimony-admin'); ?></label>
                        <select id="filter_gender" name="gender">
                            <option value=""><?php _e('All', 'matrimony-admin'); ?></option>
                            <option value="male" <?php selected(isset($_GET['gender']) && $_GET['gender'] === 'male'); ?>>
                                <?php _e('Male', 'matrimony-admin'); ?>
                            </option>
                            <option value="female" <?php selected(isset($_GET['gender']) && $_GET['gender'] === 'female'); ?>>
                                <?php _e('Female', 'matrimony-admin'); ?>
                            </option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter_caste"><?php _e('Caste', 'matrimony-admin'); ?></label>
                        <select id="filter_caste" name="caste">
                            <option value=""><?php _e('All', 'matrimony-admin'); ?></option>
                            <?php foreach ($castes as $caste): ?>
                            <option value="<?php echo esc_attr($caste); ?>" <?php selected(isset($_GET['caste']) && $_GET['caste'] === $caste); ?>>
                                <?php echo MatrimonyAdmin_Utilities::get_caste_display_name($caste); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter_age"><?php _e('Age Range', 'matrimony-admin'); ?></label>
                        <select id="filter_age" name="age">
                            <option value=""><?php _e('All', 'matrimony-admin'); ?></option>
                            <?php foreach (MatrimonyAdmin_Utilities::get_age_range_options() as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected(isset($_GET['age']) && $_GET['age'] === $value); ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="button button-primary"><?php _e('Filter', 'matrimony-admin'); ?></button>
                    <a href="<?php echo admin_url('admin.php?page=matrimony-admin-manage&view=list'); ?>" class="button">
                        <?php _e('Reset', 'matrimony-admin'); ?>
                    </a>
                </div>
            </form>
        </div>

        <div class="profiles-list-table">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Profile ID', 'matrimony-admin'); ?></th>
                        <th><?php _e('Name', 'matrimony-admin'); ?></th>
                        <th><?php _e('Age', 'matrimony-admin'); ?></th>
                        <th><?php _e('Gender', 'matrimony-admin'); ?></th>
                        <th><?php _e('Caste', 'matrimony-admin'); ?></th>
                        <th><?php _e('Sub Caste', 'matrimony-admin'); ?></th>
                        <th><?php _e('Photo', 'matrimony-admin'); ?></th>
                        <th><?php _e('Actions', 'matrimony-admin'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $args = [
                        'gender' => $_GET['gender'] ?? '',
                        'caste' => $_GET['caste'] ?? '',
                        'age_min' => isset($_GET['age']) ? explode('-', $_GET['age'])[0] : '',
                        'age_max' => isset($_GET['age']) ? (strpos($_GET['age'], '+') !== false ? 100 : explode('-', $_GET['age'])[1]) : '',
                        'per_page' => 20,
                        'page' => isset($_GET['paged']) ? intval($_GET['paged']) : 1
                    ];

                    $results = $this->database->search_profiles($args);
                    $profiles = $results['profiles'];

                    if (empty($profiles)): ?>
                    <tr>
                        <td colspan="8"><?php _e('No profiles found.', 'matrimony-admin'); ?></td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($profiles as $profile): ?>
                        <tr>
                            <td><?php echo $profile['profile_id']; ?></td>
                            <td><?php echo esc_html($profile['first_name'] . ' ' . $profile['last_name']); ?></td>
                            <td><?php echo esc_html($profile['age']); ?></td>
                            <td><?php echo MatrimonyAdmin_Utilities::get_gender_display_name($profile['gender']); ?></td>
                            <td><?php echo MatrimonyAdmin_Utilities::get_caste_display_name($profile['caste']); ?></td>
                            <td><?php echo MatrimonyAdmin_Utilities::get_sub_caste_display_name($profile['sub_caste']); ?></td>
                            <td>
                                <?php if (!empty($profile['photo_url'])): ?>
                                    <img src="<?php echo esc_url($profile['photo_url']); ?>" alt="<?php echo esc_attr($profile['first_name']); ?>" width="50">
                                <?php else: ?>
                                    <span class="dashicons dashicons-format-image"></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=matrimony-admin-manage&action=preview&profile_id=' . $profile['profile_id']); ?>" class="button button-small">
                                    <?php _e('View', 'matrimony-admin'); ?>
                                </a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=matrimony-admin-manage&action=download&profile_id=' . $profile['profile_id']), 'matrimony_profile_action'); ?>" class="button button-small">
                                    <?php _e('Download', 'matrimony-admin'); ?>
                                </a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=matrimony-admin-manage&action=delete&profile_id=' . $profile['profile_id']), 'matrimony_profile_action'); ?>" class="button button-small button-delete">
                                    <?php _e('Delete', 'matrimony-admin'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8">
                            <div class="tablenav-pages">
                                <?php 
                                $total_pages = $results['total_pages'];
                                $current_page = $args['page'];
                                $base_url = admin_url('admin.php?page=matrimony-admin-manage&view=list');

                                if (isset($_GET['gender'])) {
                                    $base_url .= '&gender=' . urlencode($_GET['gender']);
                                }
                                if (isset($_GET['caste'])) {
                                    $base_url .= '&caste=' . urlencode($_GET['caste']);
                                }
                                if (isset($_GET['age'])) {
                                    $base_url .= '&age=' . urlencode($_GET['age']);
                                }

                                echo paginate_links([
                                    'base' => $base_url . '%_%',
                                    'format' => '&paged=%#%',
                                    'current' => max(1, $current_page),
                                    'total' => $total_pages,
                                    'prev_text' => __('&laquo; Previous'),
                                    'next_text' => __('Next &raquo;')
                                ]);
                                ?>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
