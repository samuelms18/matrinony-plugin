<?php
class MatrimonyAdmin_Download {
    
    private $database;
    
    public function __construct() {
        $this->database = new MatrimonyAdmin_Database();
        
        // Handle download requests
        add_action('admin_post_matrimony_download_profile', [$this, 'handle_download_request']);
    }
    
   public function handle_download_request() {
    // Verify nonce
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'matrimony_download_profile')) {
        wp_die(__('Security check failed', 'matrimony-admin'));
    }

    // Check capabilities
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to perform this action.', 'matrimony-admin'));
    }

    $profile_id = isset($_GET['profile_id']) ? sanitize_text_field($_GET['profile_id']) : '';
    if (empty($profile_id)) {
        wp_die(__('No profile specified', 'matrimony-admin'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'matrimony_profiles';
    $profile = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE profile_id = %s", $profile_id),
        ARRAY_A
    );

    if (!$profile || empty($profile['document'])) {
        wp_die(__('No document available for download', 'matrimony-admin'));
    }

    $file_path = wp_upload_dir()['basedir'] . '/' . $profile['document'];
    
    if (!file_exists($file_path)) {
        error_log('File not found at: ' . $file_path);
        wp_die(__('File not found', 'matrimony-admin'));
    }

    // Get the original filename
    $original_filename = str_replace($profile['profile_id'] . '_', '', $profile['document']);

    header('Content-Description: File Transfer');
    header('Content-Type: ' . mime_content_type($file_path));
    header('Content-Disposition: attachment; filename="' . $original_filename . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));

    // Clear output buffer
    ob_clean();
    flush();
    
    readfile($file_path);
    exit;
}
    
    private function send_watermarked_pdf($file_path, $profile, $watermark_text) {
        if (!class_exists('FPDF')) {
            require_once MATRIMONY_ADMIN_PATH . 'includes/fpdf/fpdf.php';
        }
        
        if (!class_exists('FPDI')) {
            require_once MATRIMONY_ADMIN_PATH . 'includes/fpdi/src/autoload.php';
        }
        
        try {
            // Initiate FPDI
            $pdf = new \setasign\Fpdi\Fpdi();
            
            // Get page count
            $pageCount = $pdf->setSourceFile($file_path);
            
            // Add watermark to each page
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
                
                // Add watermark
                $pdf->SetFont('Arial', 'B', 50);
                $pdf->SetTextColor(200, 200, 200);
                $pdf->SetAlpha(0.3);
                
                // Calculate center position
                $x = ($size['width'] - $pdf->GetStringWidth($watermark_text)) / 2;
                $y = ($size['height'] - 50) / 2;
                
                $pdf->RotatedText($x, $y, $watermark_text, 45);
                $pdf->SetAlpha(1);
            }
            
            $filename = $this->generate_download_filename($profile);
            
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            
            // Output PDF
            $pdf->Output('D', $filename);
            exit;
            
        } catch (Exception $e) {
            // Fallback to direct download if watermarking fails
            error_log('PDF watermarking failed: ' . $e->getMessage());
            $this->send_file_direct($file_path, $profile);
        }
    }
    
    private function generate_download_filename($profile) {
        $ext = pathinfo($profile['document'], PATHINFO_EXTENSION);
        return sanitize_file_name(
            $profile['profile_id'] . '_' . 
            $profile['first_name'] . '_' . 
            $profile['last_name'] . '.' . $ext
        );
    }
}