<div class="wrap matrimony-admin">
    <h1><?php _e('Manage Profiles - Folder View', 'matrimony-admin'); ?></h1>

    <?php MatrimonyAdmin_Utilities::display_admin_notice(); ?>

    <div class="view-switcher">
        <a href="<?php echo admin_url('admin.php?page=matrimony-admin-manage&view=list'); ?>" class="button">
            <?php _e('List View', 'matrimony-admin'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=matrimony-admin-manage&view=folder'); ?>" class="button button-primary">
            <?php _e('Folder View', 'matrimony-admin'); ?>
        </a>
    </div>

    <div class="folder-view-container">
        <?php 
        $structure = $this->get_folder_structure();

        foreach (['male', 'female'] as $gender): 
            if (empty($structure[$gender])) continue;

            $gender_name = MatrimonyAdmin_Utilities::get_gender_display_name($gender);
        ?>
        <div class="gender-section">
            <h2 class="gender-header"><?php echo $gender_name; ?></h2>

            <div class="caste-folders">
                <?php foreach ($structure[$gender] as $caste => $caste_data): ?>
                <div class="caste-folder">
                    <div class="folder-header" data-caste="<?php echo esc_attr($caste); ?>" data-gender="<?php echo esc_attr($gender); ?>">
                        <h3>
                            <?php echo MatrimonyAdmin_Utilities::get_caste_display_name($caste); ?>
                            <span class="count">(<?php echo $caste_data['count']; ?>)</span>
                        </h3>
                        <span class="toggle">+</span>
                    </div>

                    <div class="sub-caste-folders">
                        <?php if (!empty($caste_data['sub_castes'])): ?>
                            <?php foreach ($caste_data['sub_castes'] as $sub_caste => $sub_caste_data): ?>
                            <div class="sub-caste-folder">
                                <div class="folder-header" data-sub-caste="<?php echo esc_attr($sub_caste); ?>" data-caste="<?php echo esc_attr($caste); ?>" data-gender="<?php echo esc_attr($gender); ?>">
                                    <h4>
                                        <?php echo MatrimonyAdmin_Utilities::get_sub_caste_display_name($sub_caste); ?>
                                        <span class="count">(<?php echo $sub_caste_data['count']; ?>)</span>
                                    </h4>
                                    <span class="toggle">+</span>
                                </div>

                                <div class="folder-content" style="display: none;">
                                    <?php 
                                    $profiles = $this->get_profiles_by_folder($gender, $caste, $sub_caste);

                                    if (empty($profiles)) : ?>
                                        <div class="notice notice-info">
                                            <p><?php _e('No profiles found in this category.', 'matrimony-admin'); ?></p>
                                        </div>
                                    <?php else : ?>
                                        <div class="profile-grid">
                                            <?php foreach ($profiles as $profile) : 
                                                $photo_url = '';
                                                if (!empty($profile['photo'])) {
                                                    $photo_path = MATRIMONY_ADMIN_UPLOAD_DIR . $profile['photo'];
                                                    $thumb_path = preg_replace('/(\.[^.]+)$/', '-thumb$1', $photo_path);
                                                    $photo_url = file_exists($thumb_path) 
                                                        ? MATRIMONY_ADMIN_UPLOAD_URL . preg_replace('/(\.[^.]+)$/', '-thumb$1', $profile['photo'])
                                                        : MATRIMONY_ADMIN_UPLOAD_URL . $profile['photo'];
                                                }
                                                $profile['photo_url'] = $photo_url;
                                            ?>
                                            <div class="profile-card">
                                                <div class="profile-photo">
                                                    <?php if ($profile['photo_url']): ?>
                                                        <img src="<?php echo esc_url($profile['photo_url']); ?>" alt="<?php echo esc_attr($profile['first_name']); ?>">
                                                    <?php else: ?>
                                                        <div class="no-photo">
                                                            <span class="dashicons dashicons-format-image"></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="profile-info">
                                                    <h4><?php echo esc_html($profile['first_name'] . ' ' . $profile['last_name']); ?></h4>
                                                    <p>
                                                        <?php echo esc_html($profile['age']); ?> yrs, 
                                                        <?php echo MatrimonyAdmin_Utilities::get_caste_display_name($profile['caste']); ?>
                                                        <?php if (!empty($profile['sub_caste'])) : ?>
                                                            (<?php echo MatrimonyAdmin_Utilities::get_sub_caste_display_name($profile['sub_caste']); ?>)
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                                <div class="profile-actions">
                                                    <a href="<?php echo admin_url('admin.php?page=matrimony-admin-manage&action=preview&profile_id=' . $profile['profile_id']); ?>" class="button button-small"><?php _e('View', 'matrimony-admin'); ?></a>
                                                    <?php if (!empty($profile['document'])) : ?>
                                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=matrimony-admin-manage&action=download&profile_id=' . $profile['profile_id']), 'matrimony_profile_action'); ?>" class="button button-small"><?php _e('Download', 'matrimony-admin'); ?></a>
                                                    <?php endif; ?>
                                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=matrimony-admin-manage&action=delete&profile_id=' . $profile['profile_id']), 'matrimony_profile_action'); ?>" class="button button-small button-delete"><?php _e('Delete', 'matrimony-admin'); ?></a>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.folder-header').on('click', function() {
        var header = $(this);
        var content = header.next('.folder-content');

        header.toggleClass('open');
        content.slideToggle();
        header.find('.toggle').text(header.hasClass('open') ? '−' : '+');

        if (header.hasClass('loaded')) return;

        var gender = header.data('gender');
        var caste = header.data('caste');
        var subCaste = header.data('sub-caste');

        if (!gender || !caste) return;

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'matrimony_search_profiles',
                nonce: matrimonyAdmin.nonce,
                gender: gender,
                caste: caste,
                sub_caste: subCaste || '',
                per_page: 100
            },
            beforeSend: function() {
                content.html('<div class="loading"><?php _e('Loading...', 'matrimony-admin'); ?></div>');
            },
            success: function(response) {
                if (response.success) {
                    var html = '<div class="profile-grid">';
                    $.each(response.data.profiles, function(i, profile) {
                        html += `
                            <div class="profile-card">
                                <div class="profile-photo">
                                    ${profile.photo_url ? `<img src="${profile.photo_url}" alt="${profile.first_name}">` : `<div class="no-photo"><span class="dashicons dashicons-format-image"></span></div>`}
                                </div>
                                <div class="profile-info">
                                    <h4>${profile.first_name} ${profile.last_name}</h4>
                                    <p>${profile.age} yrs, ${profile.caste}${profile.sub_caste ? ' (' + profile.sub_caste + ')' : ''}</p>
                                </div>
                                <div class="profile-actions">
                                    <a href="${profile.actions.preview}" class="button button-small"><?php _e('View', 'matrimony-admin'); ?></a>
                                    <a href="${profile.actions.download}" class="button button-small"><?php _e('Download', 'matrimony-admin'); ?></a>
                                    <a href="${profile.actions.delete}" class="button button-small button-delete"><?php _e('Delete', 'matrimony-admin'); ?></a>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    content.html(html);
                    header.addClass('loaded');
                }
            }
        });
    });
});
</script>
