<?php
/*
Plugin Name: Matrimony Admin
Description: A complete matrimonial profile management system for WordPress admins
Version: 1.0.0
Author: Your Name
Text Domain: matrimony-admin
Domain Path: /languages
*/

defined('ABSPATH') or die('No direct access allowed!');

// Define plugin constants
define('MATRIMONY_ADMIN_VERSION', '1.0.0');
define('MATRIMONY_ADMIN_PATH', plugin_dir_path(__FILE__));
define('MATRIMONY_ADMIN_URL', plugin_dir_url(__FILE__));

// Updated to use default WordPress upload folder structure
define('MATRIMONY_ADMIN_UPLOAD_DIR', wp_upload_dir()['basedir'] . '/');
define('MATRIMONY_ADMIN_UPLOAD_URL', wp_upload_dir()['baseurl'] . '/');

// Include required files
require_once MATRIMONY_ADMIN_PATH . 'includes/class-database.php';
require_once MATRIMONY_ADMIN_PATH . 'includes/class-profile-handler.php';
require_once MATRIMONY_ADMIN_PATH . 'includes/class-search.php';
require_once MATRIMONY_ADMIN_PATH . 'includes/class-views.php';
require_once MATRIMONY_ADMIN_PATH . 'includes/class-recycle-bin.php';
require_once MATRIMONY_ADMIN_PATH . 'includes/class-dashboard.php';
require_once MATRIMONY_ADMIN_PATH . 'includes/class-download.php';
require_once MATRIMONY_ADMIN_PATH . 'includes/class-settings.php';
require_once MATRIMONY_ADMIN_PATH . 'includes/class-utilities.php';

// Register activation/deactivation hooks
register_activation_hook(__FILE__, ['MatrimonyAdmin_Database', 'activate']);
register_deactivation_hook(__FILE__, ['MatrimonyAdmin_Database', 'deactivate']);

class MatrimonyAdmin_Plugin {
    
    private static $instance = null;
    private $database;
    private $profile_handler;
    private $search;
    private $views;
    private $recycle_bin;
    private $dashboard;
    private $download;
    private $settings;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Initialize components
        $this->database = new MatrimonyAdmin_Database();
        $this->profile_handler = new MatrimonyAdmin_ProfileHandler();
        $this->search = new MatrimonyAdmin_Search();
        $this->views = new MatrimonyAdmin_Views();
        $this->recycle_bin = new MatrimonyAdmin_RecycleBin();
        $this->dashboard = new MatrimonyAdmin_Dashboard();
        $this->download = new MatrimonyAdmin_Download();
        $this->settings = new MatrimonyAdmin_Settings();
        
        // Setup admin menu
        add_action('admin_menu', [$this, 'setup_admin_menu']);
        
        // Load assets
        add_action('admin_enqueue_scripts', [$this, 'load_assets']);
        
        // Load text domain
        add_action('plugins_loaded', [$this, 'load_textdomain']);
    }
    
    public function setup_admin_menu() {
        $capability = 'manage_options';
        $parent_slug = 'matrimony-admin';
        
        add_menu_page(
            __('Matrimony Admin', 'matrimony-admin'),
            __('Matrimony Admin', 'matrimony-admin'),
            $capability,
            $parent_slug,
            [$this->dashboard, 'render_dashboard_page'],
            'dashicons-heart',
            30
        );
        
        // Submenu items
        add_submenu_page(
            $parent_slug,
            __('Dashboard', 'matrimony-admin'),
            __('Dashboard', 'matrimony-admin'),
            $capability,
            $parent_slug,
            [$this->dashboard, 'render_dashboard_page']
        );
        
        add_submenu_page(
            $parent_slug,
            __('Add New Profile', 'matrimony-admin'),
            __('Add New Profile', 'matrimony-admin'),
            $capability,
            'matrimony-admin-add',
            [$this->profile_handler, 'render_add_profile_page']
        );
        
        add_submenu_page(
            $parent_slug,
            __('Search Profiles', 'matrimony-admin'),
            __('Search Profiles', 'matrimony-admin'),
            $capability,
            'matrimony-admin-search',
            [$this->search, 'render_search_page']
        );
        
        add_submenu_page(
            $parent_slug,
            __('Manage Profiles', 'matrimony-admin'),
            __('Manage Profiles', 'matrimony-admin'),
            $capability,
            'matrimony-admin-manage',
            [$this->views, 'render_manage_page']
        );
        
        add_submenu_page(
            $parent_slug,
            __('Recycle Bin', 'matrimony-admin'),
            __('Recycle Bin', 'matrimony-admin'),
            $capability,
            'matrimony-admin-recycle',
            [$this->recycle_bin, 'render_recycle_page']
        );
        
        add_submenu_page(
            $parent_slug,
            __('Settings', 'matrimony-admin'),
            __('Settings', 'matrimony-admin'),
            $capability,
            'matrimony-admin-settings',
            [$this->settings, 'render_settings_page']
        );
    }
    
    public function load_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'matrimony-admin') === false) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'matrimony-admin-css',
            MATRIMONY_ADMIN_URL . 'assets/css/admin.css',
            [],
            MATRIMONY_ADMIN_VERSION
        );
        
        wp_enqueue_style(
            'matrimony-admin-chart-css',
            MATRIMONY_ADMIN_URL . 'assets/css/chart.css',
            [],
            MATRIMONY_ADMIN_VERSION
        );
        
        // JS
        wp_enqueue_script(
            'matrimony-admin-js',
            MATRIMONY_ADMIN_URL . 'assets/js/admin.js',
            ['jquery', 'wp-util'],
            MATRIMONY_ADMIN_VERSION,
            true
        );
        
        wp_enqueue_script(
            'matrimony-admin-chart-js',
            MATRIMONY_ADMIN_URL . 'assets/js/chart.js',
            ['jquery'],
            MATRIMONY_ADMIN_VERSION,
            true
        );
        
        wp_enqueue_script(
            'matrimony-admin-search-js',
            MATRIMONY_ADMIN_URL . 'assets/js/search.js',
            ['jquery'],
            MATRIMONY_ADMIN_VERSION,
            true
        );
        
        // Localize scripts
        wp_localize_script('matrimony-admin-js', 'matrimonyAdmin', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('matrimony-admin-nonce'),
            'i18n' => [
                'confirmDelete' => __('Are you sure you want to delete this profile?', 'matrimony-admin'),
                'confirmRestore' => __('Are you sure you want to restore this profile?', 'matrimony-admin'),
                'confirmPermanentDelete' => __('This will permanently delete the profile. Continue?', 'matrimony-admin')
            ]
        ]);
        
        // Enqueue media for file uploads
        if ($hook === 'matrimony-admin_page_matrimony-admin-add') {
            wp_enqueue_media();
        }
        
        // Chart.js library
        wp_enqueue_script(
            'chart-js',
            'https://cdn.jsdelivr.net/npm/chart.js',
            [],
            '3.7.1',
            true
        );
    }
    
    public function load_textdomain() {
        load_plugin_textdomain(
            'matrimony-admin',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
}

// Initialize the plugin
MatrimonyAdmin_Plugin::get_instance();
