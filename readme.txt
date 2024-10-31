=== Pi AWS Form Submissions ===

Contributors: abellowins
Donate link: https://www.paypal.com/donate/?hosted_button_id=62X9KRLACJDQN
Tags: S3, S3 file upload, AWS S3
Requires at least: 4.7
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.2.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Short Description: Provides a seamless integration between your WordPress site's Forms and Amazon Web Services (AWS) S3 Bucket

== Description ==

S3 File Uploads for Forms, including Gravity Forms, provides a seamless integration between your WordPress site's Forms and Amazon Web Services (AWS) S3, enabling direct file uploads from your forms to an AWS S3 bucket.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/pi-forms-s3-uploads` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. For Gravity Forms:
4. Use the Forms -> Settings -> S3 Bucket Settings to add yout AWS Bucket Information. You will also need the AWS Token and Secret
5. Under Forms -> Forms choose or create the Form you want to add the functionality to. 
6. Add or Update the File Upload Field you want to add S3 Uploads to.
7. Go to "Advanced" under the "Field Settings" tab
8. Check the "S3 Upload Field Value" option
9. The Form will now upload to your desired s3 bucket

== Frequently Asked Questions ==

= Can this plugin work with other form plugins? =

Currently, it is designed exclusively for Gravity Forms. But we are extending the functionality to other plugins as well as custom forms.

== Screenshots ==

1. The settings page where you configure your AWS S3 details.
2. The page for AWS S3 details needed for the plugin to work.

== Changelog ==

= 1.0.0 =
- Initial release.

== Upgrade Notice ==

= 1.0.0 =
- Initial release. Please let us know if you encounter any issues.

== Additional Notes ==

This plugin requires an AWS account and Gravity Forms plugin to be installed and activated.
