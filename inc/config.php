<?php
/**
 * File Upload to S3 config
 * 
 * @package file-upload-to-s3
 * @author MH35
 */

if (!class_exists('FileUploadToS3PluginConfig')) {
    /**
     * Plugin configs
     */
    class FileUploadToS3PluginConfig {
        /**
         * @var string Credentials source
         * from_env: From environment
         * settings: From settings
         */
        public $credentials_source;
        /**
         * @var string AWS access key
         */
        public $access_key;
        /**
         * @var bool Whether access key is from constants
         */
        public $access_key_from_const;
        /**
         * @var string AWS secret access key
         */
        public $secret_key;
        /**
         * @var bool Whether secret access key is from constants
         */
        public $secret_key_from_const;
        /**
         * @var string Custom endpoint URL
         */
        public $endpoint;
        /**
         * @var bool Whether endpoint URL is from constants
         */
        public $endpoint_from_const;
        /**
         * @var string Region name
         */
        public $region;
        /**
         * @var string Amazon S3 bucket name
         */
        public $bucket_name;
        public function __construct() {
            $this->load_settings();
            add_action(
                'admin_init',
                array( $this, 'init_settings' )
            );
        }
        /**
         * Load settings
         */
        public function load_settings() {
            $this->credentials_source = get_option(
                'file_upload_to_s3_cred_src',
                'from_env'
            );
            if ( defined( 'FILE_UPLOAD_TO_S3_ACCESS_KEY' ) ) {
                $this->access_key = FILE_UPLOAD_TO_S3_ACCESS_KEY;
                $this->access_key_from_const = true;
            } elseif ( $this->credentials_source === 'from_env' ) {
                $this->access_key = '';
                $this->access_key_from_const = false;
            } else {
                $this->access_key = get_option(
                    'file_upload_to_s3_access_key',
                    ''
                );
                $this->access_key_from_const = false;
            }
            if ( defined( 'FILE_UPLOAD_TO_S3_SECRET_KEY' ) ) {
                $this->secret_key = FILE_UPLOAD_TO_S3_SECRET_KEY;
                $this->secret_key_from_const = true;
            } elseif ( $this->credentials_source === 'from_env' ) {
                $this->secret_key = '';
                $this->secret_key_from_const = false;
            } else {
                $this->secret_key = get_option(
                    'file_upload_to_s3_secret_key',
                    ''
                );
                $this->secret_key_from_const = false;
            }
            if ( defined( 'FILE_UPLOAD_TO_S3_ENDPOINT' ) ) {
                $this->endpoint = FILE_UPLOAD_TO_S3_ENDPOINT;
                $this->endpoint_from_const = true;
            } elseif ( $this->credentials_source === 'from_env' ) {
                $this->endpoint = '';
                $this->endpoint_from_const = false;
            } else {
                $this->endpoint = get_option(
                    'file_upload_to_s3_endpoint',
                    ''
                );
                $this->endpoint_from_const = false;
            }
            $this->region = get_option(
                'file_upload_to_s3_region', 'us-east-1'
            );
            $this->bucket_name = get_option(
                'file_upload_to_s3_bucket', ''
            );
        }
        /**
         * Sanitize credential source input value
         * @param string $input Input value
         * @return string Sanitized input value
         */
        public function sanitize_credentials_source( $input ) {
            if ( in_array(
                $input,
                array( 'from_env', 'settings' )
            ) ) {
                return $input;
            }
            if ( in_array(
                $this->credentials_source,
                array( 'from_env', 'settings' )
            ) ) {
                return $this->credentials_source;
            }
            return 'from_env';
        }
        /**
         * Sanitize AWS access key input value
         * @param string $input Input value
         * @return string Sanitized input value
         */
        public function sanitize_access_key( $input ) {
            if ( is_string($input) ) {
                $s_input = $input;
            } else {
                $s_input = $input . '';
            }
            $s_input = trim( $s_input );
            if ( $s_input === '' ) {
                return '';
            }
            if ( preg_match(
                '/^[A-Z0-9]{20}$/', $s_input
            ) ) {
                return $s_input;
            }
            if ( $this->access_key === '' ) {
                return '';
            }
            if ( preg_match(
                '/^[A-Z0-9]{20}$/', $this->access_key
            ) ) {
                return $this->access_key;
            }
            return '';
        }
        /**
         * Sanitize AWS secret access key input value
         * @param string $input Input value
         * @return string Sanitized input value
         */
        public function sanitize_secret_key( $input ) {
            if ( is_string($input) ) {
                $s_input = $input;
            } else {
                $s_input = $input . '';
            }
            $s_input = trim( $s_input );
            if ( $s_input === '' ) {
                return '';
            }
            if ( preg_match(
                '/^[A-Za-z0-9\/+=]{40}$/', $s_input
            ) ) {
                return $s_input;
            }
            if ( $this->secret_key === '' ) {
                return '';
            }
            if ( preg_match(
                '/^[A-Za-z0-9\/+=]{40}$/', $this->secret_key
            ) ) {
                return $this->secret_key;
            }
            return '';
        }
        /**
         * Sanitize custom endpoint URL input value
         * @param string $input Input value
         * @return string Sanitized input value
         */
        public function sanitize_endpoint( $input ) {
            if ( is_string($input) ) {
                $s_input = $input;
            } else {
                $s_input = $input . '';
            }
            $s_input = trim( $s_input );
            if ( $s_input === '' ) {
                return '';
            }
            if ( filter_var( $s_input, FILTER_VALIDATE_URL ) ) {
                $components = parse_url( $s_input );
                if ( $components !== false ) {
                    if (
                        isset( $components['scheme'] ) &&
                        in_array(
                            $components['scheme'],
                            array( 'http', 'https' )
                        )
                    ) {
                        return $s_input;
                    }
                }
            }
            if ( $this->endpoint === '' ) {
                return '';
            }
            if ( filter_var(
                $this->endpoint, FILTER_VALIDATE_URL
            ) ) {
                $components = parse_url( $this->endpoint );
                if ( $components !== false ) {
                    if (
                        isset( $components['scheme'] ) &&
                        in_array(
                            $components['scheme'],
                            array( 'http', 'https' )
                        )
                    ) {
                        return $this->endpoint;
                    }
                }
            }
            return '';
        }
        /**
         * Sanitize region name input value
         * @param string $input Input value
         * @return string Sanitized input value
         */
        public function sanitize_region( $input ) {
            if ( is_string($input) ) {
                $s_input = $input;
            } else {
                $s_input = $input . '';
            }
            $s_input = trim( $s_input );
            $allowed_regions = array(
                // AWS Global
                'af-south-1',
                'ap-east-1',
                'ap-northeast-1',
                'ap-northeast-2',
                'ap-northeast-3',
                'ap-south-1',
                'ap-south-2',
                'ap-southeast-1',
                'ap-southeast-2',
                'ap-southeast-3',
                'ap-southeast-4',
                'ap-southeast-5',
                'ap-southeast-7',
                'ca-central-1',
                'ca-west-1',
                'eu-central-1',
                'eu-central-2',
                'eu-north-1',
                'eu-south-1',
                'eu-south-2',
                'eu-west-1',
                'eu-west-2',
                'eu-west-3',
                'il-central-1',
                'me-central-1',
                'me-south-1',
                'mx-central-1',
                'sa-east-1',
                'us-east-1',
                'us-east-2',
                'us-west-1',
                'us-west-2',
                // AWS China
                'cn-north-1',
                'cn-northwest-1',
                // AWS GovCloud
                'us-gov-east-1',
                'us-gov-west-1',
            );
            if ( in_array( $s_input, $allowed_regions ) ) {
                return $s_input;
            }
            if ( in_array( $this->region, $allowed_regions ) ) {
                return $this->region;
            }
            return 'us-east-1';
        }
        /**
         * Sanitize bucket name input value
         * @param string $input Input value
         * @return string Sanitized input value
         */
        public function sanitize_bucket_name( $input ) {
            if ( is_string($input) ) {
                $s_input = $input;
            } else {
                $s_input = $input . '';
            }
            $s_input = trim( $s_input );
            if ( $s_input === '' ) {
                return '';
            }
            if (
                strlen( $s_input ) >= 3 &&
                strlen( $s_input ) <= 63 &&
                preg_match(
                    '/^[a-z0-9][a-z0-9-.]+[a-z0-9]$/', $s_input
                ) &&
                strpos( $s_input, '..' ) === false &&
                !str_starts_with( $s_input, 'xn--' ) &&
                !str_starts_with( $s_input, 'sthree-' ) &&
                !str_starts_with( $s_input, 'amzn-s3-demo-' ) &&
                !str_ends_with( $s_input, '-s3alias' ) &&
                !str_ends_with( $s_input, '--ol-s3' ) &&
                !str_ends_with( $s_input, '.mrap' ) &&
                !str_ends_with( $s_input, '--x-s3' ) &&
                preg_match( '/^\d+\.\d+\.\d+\.\d+$/', $s_input ) === 0
            ) {
                return $s_input;
            }
            if (
                strlen( $this->bucket_name ) >= 3 &&
                strlen( $this->bucket_name ) <= 63 &&
                preg_match(
                    '/^[a-z0-9][a-z0-9-.]+[a-z0-9]$/',
                    $this->bucket_name
                ) &&
                strpos( $this->bucket_name, '..' ) === false &&
                !str_starts_with( $this->bucket_name, 'xn--' ) &&
                !str_starts_with( $this->bucket_name, 'sthree-' ) &&
                !str_starts_with(
                    $this->bucket_name, 'amzn-s3-demo-'
                ) &&
                !str_ends_with( $this->bucket_name, '-s3alias' ) &&
                !str_ends_with( $this->bucket_name, '--ol-s3' ) &&
                !str_ends_with( $this->bucket_name, '.mrap' ) &&
                !str_ends_with( $this->bucket_name, '--x-s3' ) &&
                preg_match(
                    '/^\d+\.\d+\.\d+\.\d+$/', $this->bucket_name
                ) === 0
            ) {
                return $this->bucket_name;
            }
            return '';
        }
        /**
         * Output credentials source input field.
         */
        public function credentials_source_field() {
            ?>
            <select name="file_upload_to_s3_cred_src"
            id="file_upload_to_s3-cred_src">
                <option value="from_env"<?php
                if ( $this->credentials_source === 'from_env' ) {
                    ?> selected="selected"<?php
                } ?>><?php _e(
                    'From environment',
                    'file-upload-to-s3'
                ); ?></option>
                <option value="settings"<?php
                if ( $this->credentials_source === 'settings' ) {
                    ?> selected="selected"<?php
                } ?>><?php _e(
                    'From constants or settings',
                    'file-upload-to-s3'
                ); ?></option>
            </select>
            <?php
        }
        /**
         * Outputs AWS access key input field.
         */
        public function access_key_field() {
            ?>
            <input type="text" name="file_upload_to_s3_access_key"
            id="file_upload_to_s3_config-access_key"
            value="<?php echo esc_attr( $this->access_key ); ?>"
            <?php if ( $this->access_key_from_const ) {
                ?> disabled="disabled"<?php
            } ?> pattern="[A-Z0-9]{20}"
            title="<?php esc_attr_e(
                'Input valid AWS access key',
                'file-upload-to-s3'
            ); ?>" />
            <?php
        }
        /**
         * Outputs AWS secret access key input field.
         */
        public function secret_key_field() {
            ?>
            <input type="text" name="file_upload_to_s3_secret_key"
            id="file_upload_to_s3_config-secret_key"
            value="<?php echo esc_attr( $this->secret_key ); ?>"
            <?php if ( $this->secret_key_from_const ) {
                ?> disabled="disabled"<?php
            } ?> pattern="[A-Za-z0-9/+=]{40}"
            title="<?php esc_attr_e(
                'Input valid AWS secret access key',
                'file-upload-to-s3'
            ); ?>" />
            <?php
        }
        /**
         * Outputs custom endpoint URL input field.
         */
        public function endpoint_field() {
            ?>
            <input type="url" name="file_upload_to_s3_endpoint"
            id="file_upload_to_s3_config-endpoint"
            value="<?php echo esc_attr( $this->endpoint ); ?>"
            <?php if ( $this->endpoint_from_const ) {
                ?> disabled="disabled"<?php
            } ?> />
            <?php
        }
        /**
         * Outputs region input field.
         */
        public function region_field() {
            // Partition names
            $partitions = array(
                'aws' => __( 'AWS Standard', 'file-upload-to-s3' ),
                'aws-cn' => __( 'AWS China', 'file-upload-to-s3' ),
                'aws-us-gov' => __(
                    'AWS GovCloud (US)', 'file-upload-to-s3'
                ),
                'aws-iso' => __(
                    'AWS ISO (US)', 'file-upload-to-s3'
                ),
                'aws-iso-b' => __(
                    'AWS ISOB (US)', 'file-upload-to-s3'
                ),
                'aws-iso-e' => __( 'EU ISOE', 'file-upload-to-s3' ),
                'aws-iso-f' => __( 'AWS ISOF', 'file-upload-to-s3' ),
            );
            // Region names
            $regions = array(
                'aws' => array(
                    'us-east-1' => __(
                        'US East (N. Virginia)',
                        'file-upload-to-s3'
                    ),
                    'us-east-2' => __(
                        'US East (Ohio)',
                        'file-upload-to-s3'
                    ),
                    'us-west-1' => __(
                        'US West (N. California)',
                        'file-upload-to-s3'
                    ),
                    'us-west-2' => __(
                        'US West (Oregon)',
                        'file-upload-to-s3'
                    ),
                    'ap-south-1' => __(
                        'Asia Pacific (Mumbai)',
                        'file-upload-to-s3'
                    ),
                    'ap-south-2' => __(
                        'Asia Pacific (Hyderabad)',
                        'file-upload-to-s3'
                    ),
                    'ap-northeast-1' =>  __(
                        'Asia Pacific (Tokyo)',
                        'file-upload-to-s3'
                    ),
                    'ap-northeast-2' =>  __(
                        'Asia Pacific (Seoul)',
                        'file-upload-to-s3'
                    ),
                    'ap-northeast-3' =>  __(
                        'Asia Pacific (Osaka)',
                        'file-upload-to-s3'
                    ),
                    'ap-southeast-1' =>  __(
                        'Asia Pacific (Singapore)',
                        'file-upload-to-s3'
                    ),
                    'ap-southeast-2' =>  __(
                        'Asia Pacific (Sydney)',
                        'file-upload-to-s3'
                    ),
                    'ap-southeast-3' =>  __(
                        'Asia Pacific (Jakarta)',
                        'file-upload-to-s3'
                    ),
                    'ap-southeast-4' =>  __(
                        'Asia Pacific (Melbourne)',
                        'file-upload-to-s3'
                    ),
                    'ap-southeast-5' =>  __(
                        'Asia Pacific (Malaysia)',
                        'file-upload-to-s3'
                    ),
                    'ap-southeast-7' =>  __(
                        'Asia Pacific (Thailand)',
                        'file-upload-to-s3'
                    ),
                    'ca-central-1' =>  __(
                        'Canada (Central)',
                        'file-upload-to-s3'
                    ),
                    'ca-west-1' =>  __(
                        'Canada West (Calgary)',
                        'file-upload-to-s3'
                    ),
                    'eu-central-1' => __(
                        'Europe (Frankfurt)',
                        'file-upload-to-s3'
                    ),
                    'eu-central-2' => __(
                        'Europe (Zurich)',
                        'file-upload-to-s3'
                    ),
                    'eu-west-1' => __(
                        'Europe (Ireland)',
                        'file-upload-to-s3'
                    ),
                    'eu-west-2' => __(
                        'Europe (London)',
                        'file-upload-to-s3'
                    ),
                    'eu-west-3' => __(
                        'Europe (Paris)',
                        'file-upload-to-s3'
                    ),
                    'eu-north-1' => __(
                        'Europe (Stockholm)',
                        'file-upload-to-s3'
                    ),
                    'eu-south-1' => __(
                        'Europe (Milan)',
                        'file-upload-to-s3'
                    ),
                    'eu-south-2' => __(
                        'Europe (Spain)',
                        'file-upload-to-s3'
                    ),
                    'sa-east-1' => __(
                        'South America (Sao Paulo)',
                        'file-upload-to-s3'
                    ),
                    'af-south-1' => __(
                        'Africa (Cape Town)',
                        'file-upload-to-s3'
                    ),
                    'il-central-1' => __(
                        'Israel (Tel Aviv)',
                        'file-upload-to-s3'
                    ),
                    'me-central-1' => __(
                        'Middle East (UAE)',
                        'file-upload-to-s3'
                    ),
                    'me-south-1' => __(
                        'Middle East (Bahrain)',
                        'file-upload-to-s3'
                    ),
                    'mx-central-1' => __(
                        'Mexico (Central)',
                        'file-upload-to-s3'
                    ),
                ),
                'aws-cn' => array(
                    'cn-north-1' => __(
                        'China (Beijing)',
                        'file-upload-to-s3'
                    ),
                    'cn-northwest-1' => __(
                        'China (Ningxia)',
                        'file-upload-to-s3'
                    ),
                ),
                'aws-us-gov' => array(
                    'us-gov-east-1' => __(
                        'AWS GovCloud (US-East)',
                        'file-upload-to-s3'
                    ),
                    'us-gov-west-1' => __(
                        'AWS GovCloud (US-West)',
                        'file-upload-to-s3'
                    ),
                ),
            );
            ?>
            <select name="file_upload_to_s3_region"
            id="file_upload_to_s3_config-region">
            <?php foreach ( $regions as $pkey => $p_regions ) {
                ?>
                <optgroup label="<?php echo esc_attr(
                    $partitions[$pkey]
                ); ?>">
                <?php foreach ( $p_regions as $rkey => $rname ) {
                    ?>
                    <option value="<?php echo esc_attr($rkey);
                    ?>"<?php if ( $rkey === $this->region ) {
                        ?> selected="selected"<?php
                    } ?>><?php echo esc_html( $rname ); ?></option>
                    <?php
                }  ?>
                </optgroup>
                <?php
            } ?>
            </select>
            <?php
        }
        /**
         * Outputs bucket name input field.
         */
        public function bucket_field() {
            $prohibited_prefixes = array(
                'xn--', 'sthree-', 'amzn-s3-demo-'
            );
            $prohibited_suffixes = array(
                '-s3alias', '--ol-s3', '\.mrap', '--x-s3'
            );
            $ip_regex = '[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$';
            ?>
            <input type="text" name="file_upload_to_s3_bucket"
            id="file_upload_to_s3_config-bucket"
            value="<?php echo esc_attr($this->bucket_name); ?>"
            pattern="(?!(<?php
            echo implode( '|', $prohibited_prefixes );
            ?>))(?!.*\.\.)(?!.*(<?php
            echo implode( '|', $prohibited_suffixes );
            ?>)$)(?!<?php echo $ip_regex;
            ?>)[a-z0-9][a-z0-9\-.]{1,61}[a-z0-9]"
            title="<?php esc_attr_e(
                'Input valid S3 bucket name',
                'file-upload-to-s3'
            ); ?>" />
            <?php
        }
        /**
         * Output settings section page section
         * @param array $args Section arguments
         */
        public function output_settings_section( $args ) {
            ?><h2><?php echo esc_html( $args['title'] ); ?></h2><?php
        }
        /**
         * Settings page
         */
        public function settings_page() {
            if ( !current_user_can( 'manage_options' ) ) {
                wp_die(
                    __( 'You need a higher level of permission.' )
                );
            }
            if ( isset( $_GET['settings-updated'] ) ) {
                add_settings_error(
                    'file_upload_to_s3_options',
                    'file_upload_to_s3_options-updated',
                    __(
                        'Settings are updated',
                        'file-upload-to-s3'
                    ),
                    'success'
                );
            }
            settings_errors( 'file_upload_to_s3_options' );
            ?>
            <div class="wrap">
                <h1><?php
                echo esc_html( get_admin_page_title() );
                ?></h1>
                <form action="options.php" method="post">
                    <?php
                    settings_fields( 'file_upload_to_s3_options' );
                    do_settings_sections( 'file_upload_to_s3_config' );
                    submit_button( __(
                        'Save settings',
                        'file-upload-to-s3'
                    ) );
                    ?>
                </form>
            </div>
            <?php
        }
        /**
         * Initialize settings
         */
        public function init_settings() {
            // Register settings
            register_setting(
                'file_upload_to_s3_options',
                'file_upload_to_s3_cred_src',
                array(
                    'type' => 'string',
                    'label' => __(
                        'Credentials source',
                        'file-upload-to-s3'
                    ),
                    'description' => __(
                        'AWS credentials source',
                        'file-upload-to-s3'
                    ),
                    'sanitize_callback' => array(
                        $this, 'sanitize_credentials_source'
                    ),
                    'default' => 'from_env'
                )
            );
            register_setting(
                'file_upload_to_s3_options',
                'file_upload_to_s3_access_key',
                array(
                    'type' => string,
                    'label' => __(
                        'Access key',
                        'file-upload-to-s3'
                    ),
                    'description' => __(
                        'AWS access key',
                        'file-upload-to-s3'
                    ),
                    'sanitize_callback' => array(
                        $this, 'sanitize_access_key'
                    ),
                    'default' => ''
                )
            );
            register_setting(
                'file_upload_to_s3_options',
                'file_upload_to_s3_secret_key',
                array(
                    'type' => 'string',
                    'label' => __(
                        'Secret access key',
                        'file-upload-to-s3'
                    ),
                    'description' => __(
                        'AWS secret access key',
                        'file-upload-to-s3'
                    ),
                    'sanitize_callback' => array(
                        $this, 'sanitize_secret_key'
                    ),
                    'default' => ''
                )
            );
            register_setting(
                'file_upload_to_s3_options',
                'file_upload_to_s3_endpoint',
                array(
                    'type' => 'string',
                    'label' => __(
                        'Custom endpoint URL',
                        'file-upload-to-s3'
                    ),
                    'description' => __(
                        'Custom S3 endpoint URL',
                        'file-upload-to-s3'
                    ),
                    'sanitize_callback' => array(
                        $this, 'sanitize_endpoint'
                    ),
                    'default' => ''
                )
            );
            register_setting(
                'file_upload_to_s3_options',
                'file_upload_to_s3_region',
                array(
                    'type' => 'string',
                    'label' => __(
                        'Region',
                        'file-upload-to-s3'
                    ),
                    'description' => __(
                        'Amazon S3 region',
                        'file-upload-to-s3'
                    ),
                    'sanitize_callback' => array(
                        $this, 'sanitize_region'
                    ),
                    'default' => 'us-east-1'
                )
            );
            register_setting(
                'file_upload_to_s3_options',
                'file_upload_to_s3_bucket',
                array(
                    'type' => 'string',
                    'label' => __(
                        'Bucket name',
                        'file-upload-to-s3'
                    ),
                    'description' => __(
                        'Amazon S3 bucket name',
                        'file-upload-to-s3'
                    ),
                    'sanitize_callback' => array(
                        $this, 'sanitize_bucket_name'
                    ),
                    'default' => ''
                )
            );
            // Create options page
            add_options_page(
                __(
                    'File Upload to S3 settings', 'file-upload-to-s3'
                ),
                __(
                    'Upload S3 settings', 'file-upload-to-s3'
                ),
                'manage_options',
                'file_upload_to_s3_config',
                array( $this, 'settings_page' )
            );
            // Create options setting section
            add_settings_section(
                'file_upload_to_s3_config_sec',
                __('Access config', 'file-upload-to-s3'),
                array( $this, 'output_settings_section' ),
                'file_upload_to_s3_config',
                array()
            );
            // Define options setting fields
            add_settings_field(
                'file_upload_to_s3_config-cred_src',
                __( 'Credentials source', 'file-upload-to-s3' ),
                array( $this, 'credentials_source_field' ),
                'file_upload_to_s3_config_sec',
                array(
                    'label_for' => 'file_upload_to_s3-cred_src'
                )
            );
            add_setting_field(
                'file_upload_to_s3_config-access_key',
                __( 'Access key', 'file-upload-to-s3' ),
                array( $this, 'access_key_field' ),
                'file_upload_to_s3_config_sec',
                array(
                    'label_for' => 'file_upload_to_s3_config-access_key'
                )
            );
            add_setting_field(
                'file_upload_to_s3_config-secret_key',
                __( 'Secret access key', 'file-upload-to-s3' ),
                array( $this, 'secret_key_field' ),
                'file_upload_to_s3_config_sec',
                array(
                    'label_for' => 'file_upload_to_s3_config-secret_key'
                )
            );
            add_setting_field(
                'file_upload_to_s3_config-endpoint',
                __( 'Custom endpoint URL', 'file-upload-to-s3' ),
                array( $this, 'endpoint_field' ),
                'file_upload_to_s3_config_sec',
                array(
                    'label_for' => 'file_upload_to_s3_config-endpoint'
                )
            );
            add_setting_field(
                'file_upload_to_s3_config-region',
                __( 'Region', 'file-upload-to-s3' ),
                array( $this, 'region_field' ),
                'file_upload_to_s3_config_sec',
                array(
                    'label_for' => 'file_upload_to_s3_config-region'
                )
            );
            add_setting_field(
                'file_upload_to_s3_config-bucket',
                __( 'Bucket name', 'file-upload-to-s3' ),
                array( $this, 'bucket_field' ),
                'file_upload_to_s3_config_sec',
                array(
                    'label_for' => 'file_upload_to_s3_config-bucket'
                )
            );
        }
    }
}
