<?php

/**
 * Plugin Name: Pi AWS Form Submissions
 * Description: Stores any file submitted through forms in AWS S3
 * Version: 1.0.0
 * Requires at least: 5.2
 * Requires PHP: 7.2.5
 * Author: Piboutique Software LLC
 * Author URI: https://piboutique.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pi-forms-s3-uploads
 * Domain Path: /languages
 * 
 */

use Piboutique\GravityS3Image\Client\PIFS3UploadField;
use Piboutique\GravityS3Image\Admin\PIFS3UploadSetting;
use Piboutique\GravityS3Image\Admin\PIFS3GravityUploadSetting;

if (!defined('ABSPATH')) {
    exit;
}

define('PIFS3_GRAVITY_S3_VERSION', '1.0.0');
define('PIFS3_GRAVITY_S3_PLUGIN_PATH', plugin_dir_path(__FILE__) . '');

/*
----------------------------------------
 Dependencies for the app.
----------------------------------------
*/
require_once PIFS3_GRAVITY_S3_PLUGIN_PATH . 'vendor/autoload.php';
include_once(ABSPATH . 'wp-admin/includes/plugin.php');

/**
 * pifs3_plugin_admin_notice
 *
 * @return void
 */
function pifs3_plugin_admin_notice() {
    if (!is_plugin_active('gravityforms/gravityforms.php') || !class_exists('GFForms')) {
        echo '<div class="error"><p><strong>Pi AWS Form Submissions</strong> works with <strong>Gravity Forms</strong>. Install and activate the plugin in order to setup those settings. Make sure to deactivate this plugin before you install and activate gravity.</p></div>';
    }
}
add_action('admin_notices', 'pifs3_plugin_admin_notice');

if (is_plugin_active('gravityforms/gravityforms.php') && class_exists('GFForms')) {
    // if this has gravity forms then we are adding gravity forms specific settings here
    GFForms::include_addon_framework();
    $gravity_obj = PIFS3GravityUploadSetting::getInstance();
    $gravity_obj->pifs3_upload_handler();
} else {
    // No gravity then use the regular upload field
    (new PIFS3UploadSetting)->pifs3_upload_handler();
    // client side functionality 
    (new PIFS3UploadField());
}

/*
----------------------------------------
 Wordpress functions
----------------------------------------
*/


/**
 * load_pi_plugin_textdomain
 *
 * @return void
 */
function pifs3_load_plugin_textdomain() {
    load_plugin_textdomain('pi-forms-s3-uploads', false, basename(dirname(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'pifs3_load_plugin_textdomain');


/**
 * pifs3_upload_advanced_settings
 * Create settings on position 50 (right after Admin Label)
 * @param  mixed $position
 * @param  mixed $form_id
 * @return void
 */
function pifs3_upload_advanced_settings($position, $form_id) {
    if ($position == 50) {
?>
        <li class="s3_setting field_setting">
            <input type="checkbox" id="field_s3_value" onclick="SetFieldProperty('s3Field', this.checked);" />
            <label for="field_s3_value" style="display:inline;">
                <?php esc_html_e("S3 Upload Field Value", 'pi-forms-s3-uploads'); ?>
                <?php gform_tooltip("form_field_s3_value") ?>
            </label>
        </li>
    <?php
    }
}
add_action('gform_field_advanced_settings', 'pifs3_upload_advanced_settings', 10, 2);

/**
 * pifs3_editor_upload_script
 * Action to inject supporting script to the form editor page.
 * @return void
 */
function pifs3_editor_upload_script() {
    ?>
    <script type='text/javascript'>
        //adding setting to fields of type "text"
        fieldSettings.text += ", .s3_setting";
        //binding to the load field settings event to initialize the checkbox
        jQuery(document).on("gform_load_field_settings", function(event, field, form) {
            jQuery('#field_s3_value').prop('checked', Boolean(rgar(field, 's3Field')));
            if (field.type === "fileupload") {
                jQuery('#field_s3_value').closest('.s3_setting').show();
            } else {
                jQuery('#field_s3_value').closest('.s3_setting').hide();
            }
        });
    </script>
<?php
}
add_action('gform_editor_js', 'pifs3_editor_upload_script');

/**
 * pifs3_add_upload_tooltips
 * Filter to add a new tooltip.
 * @param  mixed $tooltips
 * @return void
 */
function pifs3_add_upload_tooltips($tooltips) {
    // TODO: add link to S3 Bucket setting
    $tooltips['form_field_s3_value'] = "<strong>S3 Upload</strong>Check this box to upload file to the S3 bucket that you setup here.";
    return $tooltips;
}
add_filter('gform_tooltips', 'pifs3_add_upload_tooltips');


/*
----------------------------------------
 Utility Functions.
----------------------------------------
*/
