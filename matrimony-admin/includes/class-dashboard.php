<?php
class MatrimonyAdmin_Dashboard {
    
    private $database;
    
    public function __construct() {
        $this->database = new MatrimonyAdmin_Database();
        
        // AJAX actions
        add_action('wp_ajax_matrimony_get_chart_data', [$this, 'ajax_get_chart_data']);
    }
    
    public function render_dashboard_page() {
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'matrimony-admin'));
        }
        
        // Get stats
        $stats = $this->database->get_stats();
        
        // Include template
        include MATRIMONY_ADMIN_PATH . 'templates/dashboard.php';
    }
    
    public function ajax_get_chart_data() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'matrimony-admin-nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'matrimony-admin')]);
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'matrimony-admin')]);
        }
        
        $chart_type = sanitize_text_field($_POST['chart_type'] ?? 'gender_dist');
        
        $stats = $this->database->get_stats();
        
        switch ($chart_type) {
            case 'gender_dist':
                $data = $this->prepare_gender_distribution_data($stats['gender_dist']);
                break;
                
            case 'caste_dist':
                $data = $this->prepare_caste_distribution_data($stats['caste_dist']);
                break;
                
            case 'monthly_growth':
                $data = $this->prepare_monthly_growth_data($stats['monthly_growth']);
                break;
                
            default:
                wp_send_json_error(['message' => __('Invalid chart type', 'matrimony-admin')]);
        }
        
        wp_send_json_success($data);
    }
    
    private function prepare_gender_distribution_data($gender_data) {
        $labels = [];
        $values = [];
        $backgrounds = [];
        
        foreach ($gender_data as $item) {
            $labels[] = ucfirst($item['gender']);
            $values[] = $item['count'];
            $backgrounds[] = $item['gender'] === 'male' ? '#36a2eb' : '#ff6384';
        }
        
        return [
            'type' => 'pie',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $values,
                    'backgroundColor' => $backgrounds,
                    'hoverOffset' => 4
                ]]
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => [
                        'position' => 'right'
                    ],
                    'title' => [
                        'display' => true,
                        'text' => __('Gender Distribution', 'matrimony-admin')
                    ]
                ]
            ]
        ];
    }
    
    private function prepare_caste_distribution_data($caste_data) {
        $labels = [];
        $values = [];
        $backgrounds = [];
        
        // Generate distinct colors for each caste
        $colors = [
            '#4bc0c0', '#ff9f40', '#9966ff', '#ffcd56', '#c9cbcf',
            '#36a2eb', '#ff6384', '#4bc0c0', '#ff9f40', '#9966ff'
        ];
        
        usort($caste_data, function($a, $b) {
            return $b['count'] - $a['count'];
        });
        
        foreach ($caste_data as $index => $item) {
            $labels[] = $item['caste'];
            $values[] = $item['count'];
            $backgrounds[] = $colors[$index % count($colors)];
        }
        
        return [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $values,
                    'backgroundColor' => $backgrounds,
                    'hoverOffset' => 4
                ]]
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => [
                        'position' => 'right'
                    ],
                    'title' => [
                        'display' => true,
                        'text' => __('Caste Distribution', 'matrimony-admin')
                    ]
                ]
            ]
        ];
    }
    
    private function prepare_monthly_growth_data($growth_data) {
        $labels = [];
        $values = [];
        
        // Sort by month ascending
        usort($growth_data, function($a, $b) {
            return strcmp($a['month'], $b['month']);
        });
        
        foreach ($growth_data as $item) {
            $labels[] = $item['month'];
            $values[] = $item['count'];
        }
        
        return [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => __('Profiles Added', 'matrimony-admin'),
                    'data' => $values,
                    'fill' => false,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => 0.1
                ]]
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => __('Monthly Profile Growth', 'matrimony-admin')
                    ]
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'ticks' => [
                            'precision' => 0
                        ]
                    ]
                ]
            ]
        ];
    }
    
    public function get_quick_stats() {
        $stats = $this->database->get_stats();
        
        $quick_stats = [
            'total_profiles' => [
                'value' => $stats['total_profiles'] ?? 0,
                'label' => __('Total Profiles', 'matrimony-admin'),
                'icon' => 'groups'
            ],
            'male_profiles' => [
                'value' => 0,
                'label' => __('Male Profiles', 'matrimony-admin'),
                'icon' => 'male'
            ],
            'female_profiles' => [
                'value' => 0,
                'label' => __('Female Profiles', 'matrimony-admin'),
                'icon' => 'female'
            ],
            'deleted_profiles' => [
                'value' => $stats['deleted_count'] ?? 0,
                'label' => __('In Recycle Bin', 'matrimony-admin'),
                'icon' => 'trash'
            ]
        ];
        
        // Calculate male/female counts
        if (!empty($stats['gender_dist'])) {
            foreach ($stats['gender_dist'] as $gender) {
                if ($gender['gender'] === 'male') {
                    $quick_stats['male_profiles']['value'] = $gender['count'];
                } elseif ($gender['gender'] === 'female') {
                    $quick_stats['female_profiles']['value'] = $gender['count'];
                }
            }
        }
        
        return $quick_stats;
    }
}