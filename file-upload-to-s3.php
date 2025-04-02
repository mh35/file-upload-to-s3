<?php
/**
 * Plugin Name: File Upload to S3
 * Plugin URI: https://github.com/mh35/file-upload-to-s3
 * Description: Upload uploaded files to Amazon S3 bucket.
 * Version: 0.0.1
 * Requires PHP: 8.1
 * Author: MH35
 * Author URI: https://github.com/mh35
 * Text Domain: file-upload-to-s3
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * @package file-upload-to-s3
 * @author MH35
 * @license GPL-2.0+
 */

if (!class_exists('Aws\S3\S3Client', true)) {
    require_once(dirname(__FILE__) . '/vendor/autoload.php');
}

if (!class_exists('FileUploadToS3Plugin')) {
    class FileUploadToS3Plugin {
        /**
         * @var FileUploadToS3Plugin Plugin instance
         */
        public static $instance;
        /**
         * Initialize plugin.
         */
        public static function init_plugin() {
            self::$instance = new self();
        }
        /**
         * Initialize plugin singleton instance.
         */
        private function __construct() {
            add_action(
                'plugins_loaded',
                array( $this, 'load_textdomain' )
            );
        }
        /**
         * Load textdomain
         */
        public function load_textdomain() {
            load_plugin_textdomain( 'file-upload-to-s3' );
        }
    }
}
FileUploadToS3Plugin::init_plugin();
