<div class="wrap matrimony-admin">
    <h1><?php _e('Add New Profile', 'matrimony-admin'); ?></h1>
    
    <?php MatrimonyAdmin_Utilities::display_admin_notice(); ?>
    
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data" class="matrimony-form">
        <input type="hidden" name="action" value="matrimony_add_profile">
        <?php wp_nonce_field('matrimony_add_profile', 'matrimony_nonce'); ?>
        
        <div class="form-section">
            <h2><?php _e('Basic Information', 'matrimony-admin'); ?></h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name"><?php _e('First Name', 'matrimony-admin'); ?> *</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name"><?php _e('Last Name', 'matrimony-admin'); ?></label>
                    <input type="text" id="last_name" name="last_name">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="dob"><?php _e('Date of Birth', 'matrimony-admin'); ?> *</label>
                    <input type="date" id="dob" name="dob" required>
                    <p class="description"><?php _e('Must be at least 18 years old', 'matrimony-admin'); ?></p>
                </div>
                
                <div class="form-group">
                    <label for="gender"><?php _e('Gender', 'matrimony-admin'); ?> *</label>
                    <select id="gender" name="gender" required>
                        <option value=""><?php _e('Select Gender', 'matrimony-admin'); ?></option>
                        <option value="male"><?php _e('Male', 'matrimony-admin'); ?></option>
                        <option value="female"><?php _e('Female', 'matrimony-admin'); ?></option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="marital_status"><?php _e('Marital Status', 'matrimony-admin'); ?> *</label>
                    <select id="marital_status" name="marital_status" required>
                        <option value=""><?php _e('Select Status', 'matrimony-admin'); ?></option>
                        <option value="single"><?php _e('Single', 'matrimony-admin'); ?></option>
                        <option value="divorced"><?php _e('Divorced', 'matrimony-admin'); ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="phone"><?php _e('Contact Number', 'matrimony-admin'); ?></label>
                    <input type="tel" id="phone" name="phone" placeholder="+CountryCode Number">
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h2><?php _e('Caste Information', 'matrimony-admin'); ?></h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="caste"><?php _e('Caste', 'matrimony-admin'); ?> *</label>
                    <select id="caste" name="caste" required>
                        <option value=""><?php _e('Select Caste', 'matrimony-admin'); ?></option>
                        <option value="Iyer"><?php _e('Iyer', 'matrimony-admin'); ?></option>
                        <option value="Iyengar"><?php _e('Iyengar', 'matrimony-admin'); ?></option>
                        <option value="Kannada Brahmin"><?php _e('Kannada Brahmin', 'matrimony-admin'); ?></option>
                        <option value="Telugu Brahmin"><?php _e('Telugu Brahmin', 'matrimony-admin'); ?></option>
                        <option value="Others"><?php _e('Others', 'matrimony-admin'); ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="sub_caste"><?php _e('Sub Caste', 'matrimony-admin'); ?></label>
                    <select id="sub_caste" name="sub_caste" disabled>
                        <option value=""><?php _e('Select Sub Caste', 'matrimony-admin'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h2><?php _e('Profile Media', 'matrimony-admin'); ?></h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="photo"><?php _e('Profile Photo', 'matrimony-admin'); ?></label>
                    <input type="file" id="photo" name="photo" accept="image/jpeg,image/png">
                    <p class="description"><?php _e('JPEG or PNG, max 2MB', 'matrimony-admin'); ?></p>
                </div>
                
                <div class="form-group">
                    <label for="document"><?php _e('Profile Document', 'matrimony-admin'); ?></label>
                    <input type="file" id="document" name="document" accept=".pdf,.doc,.docx">
                    <p class="description"><?php _e('PDF, DOC or DOCX, max 5MB', 'matrimony-admin'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h2><?php _e('Additional Information', 'matrimony-admin'); ?></h2>
            
            <div class="form-group">
                <label for="admin_notes"><?php _e('Admin Notes', 'matrimony-admin'); ?></label>
                <?php 
                wp_editor('', 'admin_notes', [
                    'textarea_name' => 'admin_notes',
                    'media_buttons' => false,
                    'textarea_rows' => 5,
                    'teeny' => true
                ]); 
                ?>
            </div>
        </div>
        
        <?php 
        $settings = get_option('matrimony_admin_settings');
        if ($settings['recaptcha_enabled'] ?? false): 
        ?>
        <div class="form-section">
            <div class="form-group">
                <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($settings['recaptcha_site_key']); ?>"></div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="form-actions">
            <button type="submit" class="button button-primary"><?php _e('Save Profile', 'matrimony-admin'); ?></button>
            <button type="reset" class="button button-secondary"><?php _e('Reset', 'matrimony-admin'); ?></button>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle caste/sub-caste relationship
    $('#caste').change(function() {
        var caste = $(this).val();
        var subCasteSelect = $('#sub_caste');
        
        subCasteSelect.empty().append('<option value=""><?php _e('Select Sub Caste', 'matrimony-admin'); ?></option>');
        
        if (caste === 'Iyer') {
            subCasteSelect.prop('disabled', false);
            subCasteSelect.append('<option value="Vadamal"><?php _e('Vadamal', 'matrimony-admin'); ?></option>');
            subCasteSelect.append('<option value="Brahacharanam"><?php _e('Brahacharanam', 'matrimony-admin'); ?></option>');
        } else if (caste === 'Iyengar') {
            subCasteSelect.prop('disabled', false);
            subCasteSelect.append('<option value="Thenkalai"><?php _e('Thenkalai', 'matrimony-admin'); ?></option>');
            subCasteSelect.append('<option value="Vadakalai"><?php _e('Vadakalai', 'matrimony-admin'); ?></option>');
        } else {
            subCasteSelect.prop('disabled', true);
        }
    });
    
    // Check for duplicates on form submission
    $('form').submit(function(e) {
        var first_name = $('#first_name').val();
        var last_name = $('#last_name').val();
        var gender = $('#gender').val();
        var phone = $('#phone').val();
        
        if (first_name && gender) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                async: false,
                data: {
                    action: 'matrimony_check_duplicate',
                    nonce: matrimonyAdmin.nonce,
                    first_name: first_name,
                    last_name: last_name,
                    gender: gender,
                    phone: phone
                },
                success: function(response) {
                    if (response.success && response.data.is_duplicate) {
                        if (!confirm('<?php _e('A similar profile already exists. Are you sure you want to proceed?', 'matrimony-admin'); ?>')) {
                            e.preventDefault();
                        }
                    }
                }
            });
        }
    });
});

<?php if ($settings['recaptcha_enabled'] ?? false): ?>
// Load reCAPTCHA script
var recaptchaScript = document.createElement('script');
recaptchaScript.src = 'https://www.google.com/recaptcha/api.js';
document.head.appendChild(recaptchaScript);
<?php endif; ?>
</script>