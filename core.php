<?php
/*
 * Plugin Name: Medical Booking Internal Plugin
 * Plugin URI: https://facebook.com/khanhecb
 * Description: Custom Login Page WP. Có trang cấu hình ở Setting admin
 * Version: 1.1.0
 * Author: KhanhECB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define('WP_CUSTOM_LOGIN_PAGE_DIR_PATH', plugin_dir_path(__FILE__));
define('WP_CUSTOM_LOGIN_PAGE_URL', plugin_dir_url(__FILE__));

require_once ABSPATH . 'wp-content/plugins/medical-booking-internal/includes/' . 'custom-login-wp-admin.php';