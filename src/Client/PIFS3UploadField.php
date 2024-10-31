<?php

namespace Piboutique\GravityS3Image\Client;

use Exception;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

/**
 * PIFS3UploadField
 */
class PIFS3UploadField {
    public function __construct() {
        add_shortcode('pifs3-uploader', [$this, 'pifs3Shortcode']);

        add_action('init', [$this, 'pifs3InputUploaderGetter']);

        add_action('wp_ajax_pisf3_file_upload', [
            $this,
            'pisf3HandleFileUpload'
        ]);

        add_action('wp_ajax_nopriv_pisf3_file_upload', [
            $this,
            'pisf3HandleFileUpload'
        ]);
    }

    /**
     * pifs3Shortcode
     * 
     * Adds a wordpress shortcode. File Uploader to upload files to AWS
     *
     * @return void
     */
    public function pifs3Shortcode() {
        // TODO: Make sure that security is taken care of
        //TODO: Test with s3 just like r2
        return '<input type="file" id="pifs3-uploader" name="pifs3-uploader" class="pifs3-input" />';
    }


    /**
     * pifs3_input_uploader_getter
     *
     * @return void
     */
    public function pifs3InputUploaderGetter() {
        wp_enqueue_script(
            'pisf3-script',
            plugins_url('/pi-forms-s3-uploads/assets/pifs3.js'),
            [],
            '1.0.0',
            [
                'in_footer' => true
            ]
        );

        wp_localize_script('pisf3-script', 'pisf3', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pi_ajax_nonce'),
        ]);
    }

    /**
     * pisf3_handle_file_upload
     *
     * @return void
     */
    public function pisf3HandleFileUpload() {
        // Verify nonce for security
        check_ajax_referer('pi_ajax_nonce', '_ajax_nonce');

        // Ensure a file is being uploaded
        if (empty($_FILES) || !isset($_FILES['file'])) {
            wp_send_json_error(['message' => 'No file uploaded.']);
            return;
        }

        $file = $_FILES['file'];
        // Basic security checks
        if ($file['type'] === 'application/php') {
            wp_send_json_error(['message' => 'This file type is not allowed.']);
            return;
        }

        $s3_settings = get_option('pi_forms_s3_uploads_options');
        $s3Client = new S3Client([
            'version' => 'latest',
            'region'  => 'auto',
            'endpoint' => $s3_settings['s3_bucket_endpoint'] ?? null,
            'credentials' => [
                'key'    => $s3_settings['s3_bucket_key'] ?? null,
                'secret' => $s3_settings['s3_bucket_secret'] ?? null,
            ],
            'use_path_style_endpoint' => true,
        ]);

        if (!$s3Client) {
            wp_send_json_error(['message' => 'File upload failed.']);
            return;
        }

        $custom_url_base = $s3_settings['s3_custom_domain'];
        $bucket_name = $s3_settings['s3_bucket_name'];

        if (!$custom_url_base || !$bucket_name) {
            wp_send_json_error(['message' => 'File upload failed.']);
            return;
        }

        // Initialize the WordPress filesystem
        global $wp_filesystem;
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        WP_Filesystem();

        $file_path = $file['tmp_name'];
        $file_name = sanitize_file_name($file['name']);

        // Read the file content using the WP filesystem
        $file_contents = $wp_filesystem->get_contents($file_path);

        if (!$file_contents) {
            wp_send_json_error(['message' => 'Failed to read file.']);
            return;
        }

        try {
            $s3Client->putObject([
                'Bucket' => $bucket_name,
                'Key'    => $file_name,
                'Body'   => $file_contents,
                'ACL'    => 'public-read',
            ]);
            wp_send_json_success([
                'message' => 'File uploaded successfully!',
                'filename' => $file_name,
            ]);
        } catch (AwsException $e) {
            error_log('S3 Upload Error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'File upload failed.']);
        }
    }
}
