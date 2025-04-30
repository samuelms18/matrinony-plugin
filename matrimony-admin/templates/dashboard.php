<div class="wrap matrimony-admin">
    <h1><?php _e('Matrimony Admin Dashboard', 'matrimony-admin'); ?></h1>
    
    <?php MatrimonyAdmin_Utilities::display_admin_notice(); ?>
    
    <div class="dashboard-grid">
        <!-- Quick Stats -->
        <div class="dashboard-card stats-container">
            <h2><?php _e('Quick Stats', 'matrimony-admin'); ?></h2>
            <div class="stats-grid">
                <?php foreach ($this->get_quick_stats() as $stat): ?>
                <div class="stat-item">
                    <div class="stat-icon">
                        <span class="dashicons dashicons-<?php echo $stat['icon']; ?>"></span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stat['value']; ?></div>
                        <div class="stat-label"><?php echo $stat['label']; ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Gender Distribution Chart -->
        <div class="dashboard-card chart-container">
            <h2><?php _e('Gender Distribution', 'matrimony-admin'); ?></h2>
            <div class="chart-wrapper">
                <canvas id="genderChart" height="200"></canvas>
            </div>
        </div>
        
        <!-- Caste Distribution Chart -->
        <div class="dashboard-card chart-container">
            <h2><?php _e('Caste Distribution', 'matrimony-admin'); ?></h2>
            <div class="chart-wrapper">
                <canvas id="casteChart" height="200"></canvas>
            </div>
        </div>
        
        <!-- Monthly Growth Chart -->
        <div class="dashboard-card chart-container wide">
            <h2><?php _e('Monthly Profile Growth', 'matrimony-admin'); ?></h2>
            <div class="chart-wrapper">
                <canvas id="growthChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Load charts
    function loadChart(chartId, chartType) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'matrimony_get_chart_data',
                nonce: matrimonyAdmin.nonce,
                chart_type: chartType
            },
            success: function(response) {
                if (response.success) {
                    new Chart(
                        document.getElementById(chartId),
                        response.data
                    );
                }
            }
        });
    }
    
    // Initialize charts
    loadChart('genderChart', 'gender_dist');
    loadChart('casteChart', 'caste_dist');
    loadChart('growthChart', 'monthly_growth');
});
</script>