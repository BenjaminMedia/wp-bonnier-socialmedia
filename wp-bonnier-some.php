<?php
/**
 * Plugin Name: WP Bonnier SoMe
 * Version: 1.0.0
 * Plugin URI: https://github.com/BenjaminMedia/wp-bonnier-some
 * Description: Plugin for integrating social media into the application.
 * Author: Bonnier
 * License: GPL v3
 */

if(!defined('ABSPATH')) {
    exit;
}

spl_autoload_register(function($className){
    $namespace = 'Bonnier\\WP\\SoMe\\';
    if(str_contains($className, $namespace)) {
        $className = str_replace([$namespace, '\\'], [__DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $className);
        
        $file = $className . '.php';
        
        if(file_exists($file)) {
            require_once $file;
        } else {
            throw new Exception(sprintf('\'%s\' does not exist', $file));
        }
    }
});

function register_bonnier_some_plugin()
{
    return \Bonnier\WP\SoMe\SoMe::instance();
}

add_action('plugins_loaded', 'register_bonnier_some_plugin');
