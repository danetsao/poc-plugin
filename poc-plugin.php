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
    private $db_table_name;
    private $db_version;
    private $namespace;

    // Constructor
    function __construct()
    {
        // Define constants
        global $wpdb;
        $this->namespace = 'poc-plugin/v1';
        $this->db_version = '1.0';
        $this->db_table_name = $wpdb->prefix . 'my_shortcode_db';

        add_action('init', [$this, 'create_custom_post_type']);



        add_action('rest_api_init', array($this, 'register_rest_api'));

        add_action('wp_enqueue_scripts', array($this, 'load_assets'));

        add_action('admin_menu', [$this, 'poc_plugin_menu']);

        add_action('init', [$this, 'interact_with_theme_elements']);
        add_shortcode('shortcode_button', [$this, 'shortcode_button']);
        add_shortcode('shortcode_form', [$this, 'shortcode_form']);

        add_action('wp_footer', [$this, 'load_scripts']);


    }

    // Test function to interact with theme templates but idt it will work
    public function custom_template_part_include($template)
    {
        if (is_single() && get_post_type() == 'post') {
            // Get the path to the template part
            $template_file = 'templates/content-single.php';
            $template_filename = locate_template($template_file);

            // Modify the contents of the template part
            $template_contents = file_get_contents($template_filename);
            $template_contents = str_replace('Old Text', 'New Text', $template_contents);

            // Return the modified template part contents
            return $template_contents;
        }

        // Return the original template
        return $template;
    }

    public function load_assets()
    {
        // enqueue css
        wp_enqueue_style(
            'simple-contact-form',
            plugin_dir_url(__FILE__) . '/css/poc-plugin.css',
            array(),
            '1',
            'all'
        );
        wp_enqueue_script(
            'poc-plugin',
            plugin_dir_url(__FILE__) . '/js/poc-plugin.js',
            array('jquery'),
            '1',
            true
        );
    }
    // Adds a menu item to the admin dashboard.
    public function poc_plugin_menu()
    {
        add_menu_page(
            'POC Plugin',
            'POC Plugin',
            'manage_options',
            'poc-plugin',
            [$this, 'poc_plugin_options'],
            'dashicons-plugins-checked'
        );
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

    public function load_scripts() {
        wp_enqueue_script('jquery');
        $rest_url = get_rest_url(null, 'poc-plugin/v1/');
    ?>
        <script>
            var nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
    
            jQuery(document).ready(function(t) {
                t('#increment_button').click(function() {
                    jQuery.ajax({
                        method: 'POST',
                        url: '<?php echo $rest_url; ?>' + 'click',
                        success: function(data) {
                            // update button with new count 
                            var count = parseInt(data);
                            t('#increment_button').siblings('.count-display').text('Count: ' + count);
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                });
                t('#decrement_button').click(function() {
                    jQuery.ajax({
                        method: 'POST',
                        url: '<?php echo $rest_url; ?>' + 'unclick',
                        success: function(data) {
                            // update button with new count 
                            var count = parseInt(data);
                            t('#increment_button').siblings('.count-display').text('Count: ' + count);
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                });
            });
        </script>
    <?php
    }
    
    public function shortcode_button() {
        global $wpdb;
    
        $shortcodeHeader = "
            <div class='containerp'>
                <h2>Clicker Button</h2>
                <p>Click the button to increment the count.</p>
                <p>This uses a simple rest api with database storage</p>
        ";
        
        $count = intval($wpdb->get_var("SELECT count FROM $this->db_table_name WHERE id=1"));
    
        $increment_button = "<button id='increment_button' class='my-button-class'>Increment</button>";
        $decrement_button = "<button id='decrement_button' class='my-button-class'>Decrement</button>";
    
        $count_display = '<p class="count-display">Count: ' . $count . '</p>';
    
        $shortcodeHeader .= $increment_button;
        $shortcodeHeader .= $decrement_button;
        $shortcodeHeader .= $count_display;
        $shortcodeHeader .= "</div>";
    
        echo $shortcodeHeader;
    }
    

    public function shortcode_db()
    {
        //init a database to store a count
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $this->db_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            count mediumint(9) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        // query to make id 1 of count 0

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function increment_button_count()
    {
        //init a database to store a count
        global $wpdb;
        //get the current count from the database
        $count = intval($wpdb->get_var("SELECT count FROM $this->db_table_name WHERE id=1"));

        //increment the count
        $count++;

        //update the count in the database
        $wpdb->update(
            $this->db_table_name,
            array('count' => $count),
            array('id' => 1)
        );

        $response = new WP_REST_Response($count, 200);

        return $response;
    }

    public function decrement_button_count()
    {
        //init a database to store a count
        global $wpdb;
        //get the current count from the database
        $count = intval($wpdb->get_var("SELECT count FROM $this->db_table_name WHERE id=1"));

        //increment the count
        $count--;

        //update the count in the database
        $wpdb->update(
            $this->db_table_name,
            array('count' => $count),
            array('id' => 1)
        );

        $response = new WP_REST_Response($count, 200);

        return $response;
    }

    public function get_button_count()
    {
        //init a database to store a count
        global $wpdb;
        //get the current count from the database
        $count = intval($wpdb->get_var("SELECT count FROM $this->db_table_name WHERE id=1"));

        $response = new WP_REST_Response($count, 200);
        return $response;
    }

    public function register_rest_api()
    {
        // endpoints for incrementing and getting count
        register_rest_route($this->namespace, '/click', array(
            'methods' => 'POST',
            'callback' => [$this, 'increment_button_count'],
            'permission_callback' => '__return_true'
        ));

        register_rest_route($this->namespace, '/unclick', array(
            'methods' => 'POST',
            'callback' => [$this, 'decrement_button_count'],
            'permission_callback' => '__return_true'
        ));

        register_rest_route($this->namespace, '/get-count', array(
            'methods' => 'POST',
            'callback' => [$this, 'get-button-count'],
            'permission_callback' => '__return_true'
        ));


    }
    /**
     * Adding custom post type
     */
    public function create_custom_post_type()
    {
        $args = array(
            'public' => true,
            'label'  => 'Plugin Reviews',
            'supports' => array('title', 'custom-fields'),
        );

        register_post_type('plugin_review_post', $args);
    }

    public function shortcode_form()
    {
        echo '<div class="container">
            <h2>Leave your feedback</h2>
            <form class="simple-contact-form__form">
                <div class="form-group mb-2">
                    <input name="name" type="text" class="form-control" placeholder="Name">
                </div>
                <div class="form-group mb-2">
                    <input name="name" type="text" class="form-control" placeholder="Details">
                </div>
                <div class="form-group mb-2">
                <input type="hidden" name="star-rating" id="star-rating">
                <label for="star-rating" class="star-rating-label">Rating</label>
                <div class="star-rating">
                  <span class="fa fa-star"></span>
                  <span class="fa fa-star"></span>
                  <span class="fa fa-star"></span>
                  <span class="fa fa-star"></span>
                  <span class="fa fa-star"></span>
                </div>
              </div>
              
                <div class="form-group mb-2">
                    <button type="submit" class="btn btn-success btn-block w-100">Send</button>
                </div>
            </form>
        </div>';
    }

    /**
     * Adding content to the theme
     * ie. header, footer, body
     */
    public function interact_with_theme_elements()
    {
        add_action('wp_footer', [$this, 'content_echo']);
        add_action('wp_head', [$this, 'content_echo']);
        // interact with page body
        add_action('wp_body_open', [$this, 'content_echo']);
    }

    // Function that adds content to whatever page the hook is called on.
    public function content_echo()
    {
        echo '<h1>POC Plugin content.</h1>';
    }
}

// Create an instance of the class.
if (class_exists('POCPlugin')) {
    $POCPlugin = new POCPlugin();
}
