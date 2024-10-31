<?php

namespace Piboutique\GravityS3Image\Admin;

class PIFS3UploadSetting implements UploadInterface {
    /**
     * pifs3_upload_handler
     *
     * @return void
     */
    public function pifs3_upload_handler() {
        add_action('admin_menu', [$this, 'pi_forms_s3_uploads_add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'pi_enqueue_admin_styles']);
        add_action('admin_init', [$this, 'pi_forms_s3_uploads_register_settings']);
    }

    /**
     * pi_forms_s3_uploads_add_admin_menu
     *
     * @return void
     */
    public function pi_forms_s3_uploads_add_admin_menu() {
        add_menu_page(
            __('S3 Upload Settings', 'pi-forms-s3-uploads'),
            'S3 Uploads',
            'manage_options',
            'pi_forms_s3_uploads',
            [$this, 'pi_forms_s3_uploads_options_page']
        );
    }

    /**[]
     * pi_enqueue_admin_styles
     *
     * @param  mixed $hook_suffix
     * @return void
     */
    public function pi_enqueue_admin_styles($hook_suffix) {
        // Only enqueue on your plugin's settings page to avoid affecting other admin pages
        if ($hook_suffix == 'toplevel_page_pi_forms_s3_uploads') {
            wp_enqueue_style('pi-admin-style', plugins_url('/pi-forms-s3-uploads/assets/pifs3.css'), [], PIFS3_GRAVITY_S3_VERSION);
        }
    }


    /**
     * pi_forms_s3_uploads_register_settings
     *
     * @return void
     */
    public function pi_forms_s3_uploads_register_settings() {
        // Register a new setting for "pi_forms_s3_uploads" page.
        register_setting('pi_forms_s3_uploads', 'pi_forms_s3_uploads_options');

        // Register a new section in the "pi_forms_s3_uploads" page.
        add_settings_section(
            'pi_forms_s3_uploads_section_s3',
            __('S3 Bucket Settings', 'pi-forms-s3-uploads'),
            [$this, 'pi_forms_s3_uploads_section_s3_callback'],
            'pi_forms_s3_uploads'
        );

        // Register fields
        $fields = [
            ['s3_bucket_key', __('S3 Bucket Key', 'pi-forms-s3-uploads'), 'password'],
            ['s3_bucket_secret', __('S3 Bucket Secret', 'pi-forms-s3-uploads'), 'password'],
            ['s3_bucket_endpoint', __('S3 Bucket Endpoint', 'pi-forms-s3-uploads'), 'text'],
            ['s3_bucket_name', __('S3 Bucket name', 'pi-forms-s3-uploads'), 'text'],
            ['s3_custom_domain', __('S3 Custom Domain Name', 'pi-forms-s3-uploads'), 'text']
        ];

        foreach ($fields as $field) {
            add_settings_field(
                $field[0], // ID
                $field[1], // Title
                [$this, 'pi_forms_s3_uploads_field_callback'], // Callback
                'pi_forms_s3_uploads', // Page
                'pi_forms_s3_uploads_section_s3', // Section
                [
                    'label_for' => $field[0],
                    'class' => 'pifs3-uploads-row',
                    'pi_forms_s3_uploads_custom_data' => 'custom',
                    'type' => $field[2]
                ]
            );
        }
    }

    /**
     * pi_forms_s3_uploads_section_s3_callback
     *
     * @param  mixed $args
     * @return void
     */
    public function pi_forms_s3_uploads_section_s3_callback($args) {
        echo '<p id="' . esc_attr($args['id']) . '">' . esc_html__('S3 Bucket Settings for file uploads to S3', 'pi-forms-s3-uploads') . "</p>";
    }

    /**
     * pi_forms_s3_uploads_field_callback
     *
     * @param  mixed $args
     * @return void
     */
    public function pi_forms_s3_uploads_field_callback($args) {
        // Get the value of the setting we've registered with register_setting()
        $options = get_option('pi_forms_s3_uploads_options');
        echo '<input type="' . esc_attr($args['type']) . '" id="' . esc_attr($args['label_for']) . '" name="pi_forms_s3_uploads_options[' . esc_attr($args['label_for']) . ']" value="' . esc_attr($options[esc_attr($args['label_for'])] ?? '') . '" class="pifs3-uploads-row medium"/>';
    }


    /**
     * pi_forms_s3_uploads_options_page
     *
     * @return void
     */
    public function pi_forms_s3_uploads_options_page() {
?>
        <div class="wrap">
            <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
            <form action="options.php" method="post">
                <?php
                // Output security fields for the registered setting "pi_forms_s3_uploads"
                settings_fields('pi_forms_s3_uploads');
                // Output setting sections and their fields
                do_settings_sections('pi_forms_s3_uploads');
                // Output save settings button
                submit_button(__('Save Settings', 'pi-forms-s3-uploads'));
                ?>
            </form>
        </div>
<?php
    }
}
