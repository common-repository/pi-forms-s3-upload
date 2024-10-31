<?php

namespace Piboutique\GravityS3Image\Admin;

use \GFAPI;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class PIFS3GravityUploadSetting extends \GFAddOn implements UploadInterface {
    private static $instance = null;

    /**
     * _version
     *
     * @var string
     */
    protected $_version = PIFS3_GRAVITY_S3_VERSION;

    /**
     * _min_gravityforms_version
     *
     * @var string
     */
    protected $_min_gravityforms_version = '1.7.9999';

    /**
     * _url
     *
     * @var string
     */
    protected $_url = 'https://www.piboutique.com';

    /**
     * _title
     *
     * @var string
     */
    protected $_title = 'Pi Forms S3 Bucket';

    /**
     * _short_title
     *
     * @var string
     */
    protected $_short_title = 'S3 Bucket Settings';

    /**
     * _slug
     *
     * @var string
     */
    protected $_slug = 's3upload';

    /**
     * _path
     *
     * @var string
     */
    protected $_path = 'forms-s3-images/src/Admin/PIFS3GravityUploadSetting.php';

    /**
     * _full_path
     *
     * @var undefined
     */
    protected $_full_path = __FILE__;


    // Returns the single instance of this class.
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * Configures the settings which should be rendered on the add-on settings tab.
     *
     * @return array
     */
    public function plugin_settings_fields() {
        return [
            [
                'title' => esc_html__('S3 Bucket Settings', 'pi-forms-s3-uploads'),
                'id' => 'gform_section_s3_upload',
                'description' => esc_html__('S3 Bucket Settings for file uploads to S3', 'pi-forms-s3-uploads'),
                'fields' => [
                    [
                        'name' => 's3_bucket_key',
                        'label' => esc_html__('S3 Bucket Key', 'pi-forms-s3-uploads'),
                        'type' => 'text',
                        'input_type' => 'password',
                        'class' => 'medium',
                    ],
                    [
                        'name' => 's3_bucket_secret',
                        'label' => esc_html__('S3 Bucket Secret', 'pi-forms-s3-uploads'),
                        'type' => 'text',
                        'input_type' => 'password',
                        'class' => 'medium',
                    ],
                    [
                        'name'  => 's3_bucket_endpoint',
                        'label' => esc_html__('S3 Bucket Endpoint', 'pi-forms-s3-uploads'),
                        'type' => 'text',
                        'class' => 'medium',
                    ],
                    [
                        'name'  => 's3_bucket_name',
                        'label' => esc_html__('S3 Bucket name', 'pi-forms-s3-uploads'),
                        'type' => 'text',
                        'class' => 'medium',
                    ],
                    [
                        'name'  => 's3_custom_domain',
                        'label' => esc_html__('S3 Custom Domain Name', 'pi-forms-s3-uploads'),
                        'type' => 'text',
                        'class' => 'medium',
                    ]
                ]
            ],
            [
                'fields' => [
                    [
                        'id' => 'save_button',
                        'type' => 'save',
                        'value' => esc_attr__('Update', 'pi-forms-s3-uploads'),
                    ],
                ]
            ],
        ];
    }

    /**
     * get_setting_by_name
     *
     * @param  mixed $field_name
     * @return string
     */
    public function get_setting_by_name(string $field_name): string {
        $settings = $this->get_plugin_settings();
        return rgar($settings, $field_name);
    }

    /**
     * gravity_upload_handler
     *
     * @return void
     */
    public function pifs3_upload_handler() {
        add_action('gform_after_submission', [$this, 'pifs3_graivity_upload_to_s3'], 10, 2);
    }

    /**
     * pifs3_graivity_upload_to_s3
     *
     * @param  mixed $entry
     * @param  mixed $form
     * @return void
     */
    public function pifs3_graivity_upload_to_s3($entry, $form) {
        global $wp_filesystem;
        WP_Filesystem();

        $s3settings = PIFS3GravityUploadSetting::getInstance();

        $s3Client = new S3Client([
            'version' => 'latest',
            'region'  => 'auto', // e.g., us-west-2
            'endpoint' => $s3settings->get_setting_by_name('s3_bucket_endpoint'),
            'credentials' => [
                'key'    => $s3settings->get_setting_by_name('s3_bucket_key'),
                'secret' => $s3settings->get_setting_by_name('s3_bucket_secret'),
            ],
            'use_path_style_endpoint' => true,
        ]);

        $custom_url_base = $s3settings->get_setting_by_name('s3_custom_domain');
        $bucket_name = $s3settings->get_setting_by_name('s3_bucket_name');

        foreach ($form['fields'] as $field) {
            // Only perform the following action when the form field is an upload field 
            // and then the upload to s3 option is on
            if (
                $field->type === 'fileupload' &&
                !empty($field->s3Field) && $field->s3Field
            ) {
                $field_id = $field->id;
                $file_url = rgar($entry, (string)$field_id);

                if (!empty($file_url)) {
                    $file_path = wp_parse_url($file_url, PHP_URL_PATH);
                    $file_name = basename($file_path);
                    $file_path = ltrim($file_path, '/');
                    $file_path = ABSPATH . $file_path;
                    if ($wp_filesystem->exists($file_path)) {
                        try {
                            $file_contents = $wp_filesystem->get_contents($file_path);
                            $result = $s3Client->putObject([
                                'Bucket' =>  $bucket_name,
                                'Key'    => $file_name,
                                'Body'   => $file_contents,
                                'ACL'    => 'public-read', // Adjust the ACL as needed
                            ]);

                            // Check for successful upload
                            if ($result["@metadata"]["statusCode"] == 200) {
                                // Update entry with new S3 URL
                                $new_url = "$custom_url_base/$file_name";
                                GFAPI::update_entry_field($entry['id'], $field_id, $new_url);
                            }
                            $wp_filesystem->delete($file_path);
                        } catch (AwsException $e) {
                            error_log('S3 Upload Error: ' . $e->getMessage());
                        }
                    }
                }
            }
        }
    }
}
