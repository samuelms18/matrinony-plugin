<div class="wrap matrimony-admin">
    <h1><?php _e('Search Profiles', 'matrimony-admin'); ?></h1>
    
    <div class="search-container">
        <form id="matrimony-search-form" class="matrimony-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="search_first_name"><?php _e('First Name', 'matrimony-admin'); ?></label>
                    <input type="text" id="search_first_name" name="first_name">
                </div>
                
                <div class="form-group">
                    <label for="search_last_name"><?php _e('Last Name', 'matrimony-admin'); ?></label>
                    <input type="text" id="search_last_name" name="last_name">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="search_age_min"><?php _e('Age Range', 'matrimony-admin'); ?></label>
                    <div class="range-inputs">
                        <input type="number" id="search_age_min" name="age_min" placeholder="<?php _e('Min', 'matrimony-admin'); ?>" min="18" max="100">
                        <span class="range-separator">-</span>
                        <input type="number" id="search_age_max" name="age_max" placeholder="<?php _e('Max', 'matrimony-admin'); ?>" min="18" max="100">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="search_gender"><?php _e('Gender', 'matrimony-admin'); ?></label>
                    <select id="search_gender" name="gender">
                        <option value=""><?php _e('All', 'matrimony-admin'); ?></option>
                        <option value="male"><?php _e('Male', 'matrimony-admin'); ?></option>
                        <option value="female"><?php _e('Female', 'matrimony-admin'); ?></option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="search_marital_status"><?php _e('Marital Status', 'matrimony-admin'); ?></label>
                    <select id="search_marital_status" name="marital_status">
                        <option value=""><?php _e('All', 'matrimony-admin'); ?></option>
                        <option value="single"><?php _e('Single', 'matrimony-admin'); ?></option>
                        <option value="divorced"><?php _e('Divorced', 'matrimony-admin'); ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="search_caste"><?php _e('Caste', 'matrimony-admin'); ?></label>
                    <select id="search_caste" name="caste">
                        <option value=""><?php _e('All', 'matrimony-admin'); ?></option>
                        <option value="Iyer"><?php _e('Iyer', 'matrimony-admin'); ?></option>
                        <option value="Iyengar"><?php _e('Iyengar', 'matrimony-admin'); ?></option>
                        <option value="Kannada Brahmin"><?php _e('Kannada Brahmin', 'matrimony-admin'); ?></option>
                        <option value="Telugu Brahmin"><?php _e('Telugu Brahmin', 'matrimony-admin'); ?></option>
                        <option value="Others"><?php _e('Others', 'matrimony-admin'); ?></option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="search_sub_caste"><?php _e('Sub Caste', 'matrimony-admin'); ?></label>
                    <select id="search_sub_caste" name="sub_caste" disabled>
                        <option value=""><?php _e('All', 'matrimony-admin'); ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="search_per_page"><?php _e('Results per page', 'matrimony-admin'); ?></label>
                    <select id="search_per_page" name="per_page">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="button button-primary"><?php _e('Search', 'matrimony-admin'); ?></button>
                <button type="reset" class="button button-secondary"><?php _e('Reset', 'matrimony-admin'); ?></button>
            </div>
        </form>
        
        <div class="search-results-container">
            <div class="results-header">
                <h2><?php _e('Search Results', 'matrimony-admin'); ?></h2>
                <div class="results-actions">
                    <select id="bulk-action" class="bulk-action-select">
                        <option value=""><?php _e('Bulk Actions', 'matrimony-admin'); ?></option>
                        <option value="export"><?php _e('Export Selected', 'matrimony-admin'); ?></option>
                        <option value="delete"><?php _e('Move to Recycle Bin', 'matrimony-admin'); ?></option>
                    </select>
                    <button id="bulk-action-apply" class="button"><?php _e('Apply', 'matrimony-admin'); ?></button>
                </div>
            </div>
            
            <div class="results-table-wrapper">
                <table id="search-results-table" class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th class="check-column"><input type="checkbox" id="select-all"></th>
                            <th><?php _e('Profile ID', 'matrimony-admin'); ?></th>
                            <th><?php _e('Name', 'matrimony-admin'); ?></th>
                            <th><?php _e('Age', 'matrimony-admin'); ?></th>
                            <th><?php _e('Gender', 'matrimony-admin'); ?></th>
                            <th><?php _e('Caste', 'matrimony-admin'); ?></th>
                            <th><?php _e('Sub Caste', 'matrimony-admin'); ?></th>
                            <th><?php _e('Photo', 'matrimony-admin'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="no-results"><?php _e('Use the search form above to find profiles', 'matrimony-admin'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="search-pagination">
                <div class="tablenav-pages">
                    <span class="displaying-num">0 <?php _e('items', 'matrimony-admin'); ?></span>
                    <span class="pagination-links">
                        <a class="first-page button" href="#"><span class="screen-reader-text"><?php _e('First page', 'matrimony-admin'); ?></span><span aria-hidden="true">«</span></a>
                        <a class="prev-page button" href="#"><span class="screen-reader-text"><?php _e('Previous page', 'matrimony-admin'); ?></span><span aria-hidden="true">‹</span></a>
                        <span class="paging-input">
                            <label for="current-page-selector" class="screen-reader-text"><?php _e('Current Page', 'matrimony-admin'); ?></label>
                            <input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging">
                            <span class="tablenav-paging-text"> <?php _e('of', 'matrimony-admin'); ?> <span class="total-pages">1</span></span>
                        </span>
                        <a class="next-page button" href="#"><span class="screen-reader-text"><?php _e('Next page', 'matrimony-admin'); ?></span><span aria-hidden="true">›</span></a>
                        <a class="last-page button" href="#"><span class="screen-reader-text"><?php _e('Last page', 'matrimony-admin'); ?></span><span aria-hidden="true">»</span></a>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle caste/sub-caste relationship
    $('#search_caste').change(function() {
        var caste = $(this).val();
        var subCasteSelect = $('#search_sub_caste');
        
        subCasteSelect.empty().append('<option value=""><?php _e('All', 'matrimony-admin'); ?></option>');
        
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
    
    // Search form submission
    $('#matrimony-search-form').submit(function(e) {
        e.preventDefault();
        performSearch(1);
    });
    
    // Pagination handlers
    $('.first-page').click(function(e) {
        e.preventDefault();
        performSearch(1);
    });
    
    $('.prev-page').click(function(e) {
        e.preventDefault();
        var current = parseInt($('.current-page').val());
        if (current > 1) performSearch(current - 1);
    });
    
    $('.next-page').click(function(e) {
        e.preventDefault();
        var current = parseInt($('.current-page').val());
        var total = parseInt($('.total-pages').text());
        if (current < total) performSearch(current + 1);
    });
    
    $('.last-page').click(function(e) {
        e.preventDefault();
        var total = parseInt($('.total-pages').text());
        performSearch(total);
    });
    
    $('.current-page').keypress(function(e) {
        if (e.which == 13) { // Enter key
            e.preventDefault();
            var page = parseInt($(this).val());
            var total = parseInt($('.total-pages').text());
            if (page >= 1 && page <= total) {
                performSearch(page);
            }
        }
    });
    
    // Bulk actions
    $('#bulk-action-apply').click(function() {
        var action = $('#bulk-action').val();
        if (!action) return;
        
        var selected = [];
        $('.profile-checkbox:checked').each(function() {
            selected.push($(this).val());
        });
        
        if (selected.length === 0) {
            alert('<?php _e('Please select at least one profile', 'matrimony-admin'); ?>');
            return;
        }
        
        if (action === 'export') {
            exportProfiles(selected);
        } else if (action === 'delete') {
            if (confirm('<?php _e('Are you sure you want to move the selected profiles to recycle bin?', 'matrimony-admin'); ?>')) {
                bulkAction('delete', selected);
            }
        }
    });
    
    // Select all checkbox
    $('#select-all').click(function() {
        $('.profile-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    // Perform search via AJAX
    function performSearch(page) {
        var formData = $('#matrimony-search-form').serializeArray();
        formData.push({name: 'page', value: page});
        formData.push({name: 'action', value: 'matrimony_search_profiles'});
        formData.push({name: 'nonce', value: matrimonyAdmin.nonce});
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#search-results-table tbody').html('<tr><td colspan="8" class="loading"><?php _e('Loading...', 'matrimony-admin'); ?></td></tr>');
            },
            success: function(response) {
                if (response.success) {
                    updateResultsTable(response.data);
                    updatePagination(response.data.pagination, page);
                } else {
                    $('#search-results-table tbody').html('<tr><td colspan="8" class="no-results"><?php _e('Error loading results', 'matrimony-admin'); ?></td></tr>');
                }
            }
        });
    }
    
    // Update results table with new data
    function updateResultsTable(data) {
        var tbody = $('#search-results-table tbody');
        tbody.empty();
        
        if (data.profiles.length === 0) {
            tbody.append('<tr><td colspan="8" class="no-results"><?php _e('No profiles found', 'matrimony-admin'); ?></td></tr>');
            return;
        }
        
        $.each(data.profiles, function(i, profile) {
            var row = `
                <tr>
                    <th scope="row" class="check-column">
                        <input type="checkbox" class="profile-checkbox" value="${profile.profile_id}">
                    </th>
                    <td>${profile.profile_id}</td>
                    <td>${profile.first_name} ${profile.last_name}</td>
                    <td>${profile.age}</td>
                    <td>${profile.gender}</td>
                    <td>${profile.caste}</td>
                    <td>${profile.sub_caste}</td>
                    <td>
                        ${profile.photo_url ? `<img src="${profile.photo_url}" alt="${profile.first_name}" width="50">` : '<span class="dashicons dashicons-format-image"></span>'}
                    </td>
                </tr>
            `;
            
            tbody.append(row);
        });
    }
    
    // Update pagination controls
    function updatePagination(pagination, currentPage) {
        $('.displaying-num').text(pagination.total + ' <?php _e('items', 'matrimony-admin'); ?>');
        $('.current-page').val(currentPage);
        $('.total-pages').text(pagination.total_pages);
        
        // Enable/disable pagination buttons
        $('.first-page, .prev-page').toggleClass('disabled', currentPage === 1);
        $('.next-page, .last-page').toggleClass('disabled', currentPage === pagination.total_pages);
    }
    
    // Export profiles to CSV
    function exportProfiles(profileIds) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'matrimony_export_profiles',
                nonce: matrimonyAdmin.nonce,
                profile_ids: profileIds
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function(response) {
                var blob = new Blob([response]);
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'matrimony-profiles-' + new Date().toISOString().slice(0, 10) + '.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });
    }
    
    // Perform bulk action
    function bulkAction(action, profileIds) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'matrimony_bulk_action',
                nonce: matrimonyAdmin.nonce,
                bulk_action: action,
                profile_ids: profileIds
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    // Refresh current page
                    performSearch(parseInt($('.current-page').val()));
                }
            }
        });
    }
});
</script>