<?php
/**
 * Plugin Name: WP Bonnier SoMe
 * Version: 2.0.0
 * Plugin URI: https://github.com/BenjaminMedia/wp-bonnier-some
 * Description: Plugin for integrating social media into the application.
 * Author: Bonnier
 * License: GPL v3
 */

if (!defined('ABSPATH')) {
    exit;
}

function register_bonnier_some_plugin()
{
    return \Bonnier\WP\SoMe\SoMe::instance();
}

add_action('plugins_loaded', 'register_bonnier_some_plugin');
