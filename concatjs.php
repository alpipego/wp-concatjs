<?php
/**
 * Plugin Name: Concat JS
 * Description: Concat js files as they are about to be enquequed
 * Version: 0.1
 * Author: alpipego
 * Author URI: http://alpipego.com/
*/

include 'File.class.php';
include 'ConcatJs.class.php';

add_action( 'wp_print_scripts', function() {
    global $pagenow;
    if (!is_admin() && $pagenow !== 'wp-login.php' && (defined('WP_STAGE') && WP_STAGE !== 'local')) {
        new \ConcatJS\ConcatJs();
    }
});
