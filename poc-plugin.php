<?php

/**
 * Plugin Name: POC Plugin
 * Description: A proof of concept plugin to interact with roots-ualib-theme.
 * Version: 1.0.0
 * Author: Dane Tsao
 * Author URI: https://github.com/danetsao
 * Text Domain: poc-plugin
 */

// Note, excessive commenting is for educational and note taking purposes only.

// Exit if file is accessed directly.
if (!defined('ABSPATH')) {
    echo 'Cannot access this file directly.';
    exit;
}

class POCPlugin
{
    // Constructor
    function __construct()
    {
        add_action('admin_menu', [$this, 'poc_plugin_menu']);
        add_action('admin_notices', [$this, 'admin_notification']);
        add_action('init', [$this, 'interact_with_theme_elements']);
    }

    // Runs when the plugin is activated.
    public function activate()
    {
    }

    // Runs when the plugin is deactivated.
    public function deactivate()
    {
    }


    // Adds a menu item to the admin dashboard.
    public function poc_plugin_menu()
    {
        add_menu_page('POC Plugin', 'POC Plugin', 'manage_options', 'poc-plugin', [$this, 'poc_plugin_options']);
    }

    // Displays the content of the menu item.
    public function poc_plugin_options()
    {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }
        echo '<h1>POC Plugin</h1>';
        echo '<p>POC Plugin content.</p>';
    }

    // Displays a notification on the admin dashboard that this plugin is active.
    public function admin_notification()
    {
        echo '<div class="notice notice-success is-dismissible">
        <p>POC Plugin is active.</p>
        </div>';
    }

    // Function that holds all the interactions with the theme (head, body, footer, etc).
    public function interact_with_theme_elements()
    {
        add_action('wp_footer', [$this, 'add_to_footer']);
        add_action('wp_head', [$this, 'add_to_head']);
        // interact with page body
        add_action('wp_body_open', [$this, 'add_to_body']);
    }

    // Individual functions to add content to the theme, could just use one return_content function.
    public function add_to_footer()
    {
        echo '<h1>POC Plugin Footer</h1>';
    }

    public function add_to_head()
    {
        echo '<h1>POC Plugin Header</h1>';
    }

    public function add_to_body()
    {
        echo '<h1>POC Plugin Body</h1>';
    }
}


// Create an instance of the class.
if (class_exists('POCPlugin')) {
    $POCPlugin = new POCPlugin();
}

// activation
register_activation_hook(__FILE__, array($POCPlugin, 'activate'));

// deactivation
register_activation_hook(__FILE__, array($POCPlugin, 'deactivate'));
