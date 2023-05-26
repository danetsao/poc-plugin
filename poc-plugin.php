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

        //This is the best that I could figure out for the custom header

        add_action('rest_api_init', array($this, 'register_rest_api'));

        add_action('wp_enqueue_scripts', array($this, 'load_scripts'));
        // Why do we have to hook twice??
        add_action('wp_footer', [$this, 'load_scripts']);

        add_action('init', [$this, 'create_custom_post_type']);

        add_action('admin_menu', [$this, 'poc_plugin_menu']);

        add_action('init', [$this, 'interact_with_theme_elements']);
        add_shortcode('shortcode_button', [$this, 'shortcode_button']);
        add_shortcode('book_post_shortcode', [$this, 'book_post_shortcode']);
    }

    // Adds a menu item to the admin dashboard.
    public function poc_plugin_menu()
    {
        add_menu_page(
            'POC Plugin Admin',
            'POC Plugin Admin',
            'manage_options',
            'poc-plugin',
            [$this, 'poc_plugin_options'],
            'dashicons-plugins-checked'
        );
    }

    // Displays the content of the menu.
    public function poc_plugin_options()
    {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }

        // Define link to our 'showcase page'
        // define css stylesheet
        $css_url = "href='" . plugin_dir_url(__FILE__) . "css/poc-plugin.css'";
?>

        <head>
            <link rel="stylesheet" href=<?php echo plugin_dir_url(__FILE__) . 'css/poc-plugin.css'; ?> </head>
            <?php

            // Define link to our 'showcase page'
            $page_id = 65;
            $site_url = site_url();
            $link_url = add_query_arg(array('page_id' => $page_id), $site_url);
            $link_html = '<a class="menu-link" href="' . esc_url($link_url) . '">Go to Showcase Page</a>';
            // add a button and text field that can reset the button to that number
            $count_html = '
        <div class="menu-count-container">
            <p class="menu-count">Set button count: </p>
            <input type="text" id="reset_button_count" name="reset_button_count" value="0">
            <button id="reset_button_count_button" class="button">Set</button>
        </div>';
            $msg =  "
            <div class='menu-container'>
                <h1 class='menu-title'>POC Plugin Admin</h1>
                <p class='menu-count'>Features:</p>
                <ul class='menu-features'>
                    <li class='menu-feature'>Shortcode for button</li>
                        <ul class='menu-subfeatures'>
                            <li class='menu-subfeature'>[shortcode_button]</li>
                            <li class='menu-subfeature'>This is a simple SQL data item count that can be updated through the shortcode, via increment or decrement, or set by admin on the admi page</span></li>
                            <li class='menu-subfeature'>Button count:  <span id='button-count'>" . ($this->get_button_count())->data . " </span></li>
                            <ul class='menu-subfeatures'>
                        </ul>
                    </ul>
                    <span class='slider round'></span>
                    <li class='menu-feature'>Custom Post Type</li>
                        <ul class='menu-subfeatures'>
                            <li class='menu-subfeature'>I made a custom post type of book review under the book collections tab.</span></li>
                            <li class='menu-subfeature'>These posts are displayed through the shortcode mentioned below</span></li>
                            <ul class='menu-subfeatures'>
                        </ul>
                    </ul>
                    <li class='menu-feature'>Shortcode for custom posts display</li>
                        <ul class='menu-subfeatures'>
                            <li class='menu-subfeature'>[custom_post_shortcode]</li>
                            <li class='menu-subfeature'>This is a shortcode that dynamically displayed the 10 most recent posts in chronological order</span></li>
                            <li class='menu-subfeature'>It links to individual post page to each element</span></li>
                            <ul class='menu-subfeatures'>
                        </ul>
                    </ul>
                        ";
            $msg .= '</ul>';

            $msg .= $count_html;

            $msg .= '</div>';

            $msg .= $link_html;

            echo $msg;

            wp_enqueue_script('jquery');
            $rest_url = get_rest_url(null, 'poc-plugin/v1/');

            ?>
            <script>
                var nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';

                jQuery(document).ready(function(t) {
                    t('#reset_button_count_button').click(function() {
                        //alert('Resetting button count to ' + t('#reset_button_count').val());
                        var val = (t('#reset_button_count').val());

                        jQuery.ajax({
                            method: 'POST',
                            url: '<?php echo $rest_url; ?>' + 'update-count/' + val,
                            success: function(data) {
                                console.log('updated count to' + val);
                                // update text displaying the count
                                t('#button-count').text(val);
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

    /**
     * JS scripts for the showcase page, jquery for the 
     * 
     * I don't think we can refactor and put this in a separate file, 
     * b/c it js (client side) can't render php (server side)
     */
    public function load_scripts()
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

    public function shortcode_button()
    {
        global $wpdb;
        $count = intval($wpdb->get_var("SELECT count FROM $this->db_table_name WHERE id=1"));

        $shortcodeHeader = "
            <div class='clicker-showcase-container'>
                <h1 id='title'>Clicker Button</h1>
                <p id='caption'>Click the button to increment the count.</p>
                <p id='caption'>This uses a simple rest api with database storage.</p>
        ";
        $increment_button = "<button id='increment_button' class='my-button-class'>Increment</button>";
        $decrement_button = "<button id='decrement_button' class='my-button-class'>Decrement</button>";
        $count_display = '<p id="caption" class="count-display">Count: ' . $count . '</p>';

        $shortcodeHeader = $shortcodeHeader . $increment_button . $decrement_button . $count_display . "</div>";


        echo $shortcodeHeader;
    }

    public function shortcode_db()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->db_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            count mediumint(9) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Functions to handle the rest api
     * Can refactor in furutre to reduce code duplication
     * They are essentially doing same thing but with slight variations
     */
    public function increment_button_count()
    {
        global $wpdb;
        $count = intval($wpdb->get_var("SELECT count FROM $this->db_table_name WHERE id=1"));

        $count++;

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
        global $wpdb;
        $count = intval($wpdb->get_var("SELECT count FROM $this->db_table_name WHERE id=1"));

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
        global $wpdb;
        $count = intval($wpdb->get_var("SELECT count FROM $this->db_table_name WHERE id=1"));

        $response = new WP_REST_Response($count, 200);
        return $response;
    }

    public function update_button_count($request)
    {
        $num = $request->get_param('num');

        global $wpdb;
        $wpdb->update(
            $this->db_table_name,
            array('count' => $num),
            array('id' => 1)
        );

        $response = new WP_REST_Response($num, 200);
        return $response;
    }

    // Get all custom posts
    public function get_all_custom_posts()
    {
        $args = array(
            'post_type' => 'book_collection_post',
            'posts_per_page' => 10,
        );

        $loop = new WP_Query($args);
        $posts = $loop->posts;

        $response = new WP_REST_Response($posts, 200);
        return $response;
    }

    public function get_custom_post($request) {
        $id = $request->get_param('id');
        $args = array(
            'post_type' => 'book_collection_post',
            'posts_per_page' => 10,
        );

        $loop = new WP_Query($args);
        $posts = $loop->posts;

        // Loop through posts and find the one with the matching id
        foreach ($posts as $post) {
            if ($post->ID == $id) {
                $response = new WP_REST_Response($post, 200);
                return $response;
            }
        }
        $post = -1;
        $response = new WP_REST_Response($post, 200);
        return $response;
    }

    public function register_rest_api()
    {
        // Increment count
        register_rest_route($this->namespace, '/click', array(
            'methods' => 'POST',
            'callback' => [$this, 'increment_button_count'],
            'permission_callback' => '__return_true'
        ));

        // Decrement count
        register_rest_route($this->namespace, '/unclick', array(
            'methods' => 'POST',
            'callback' => [$this, 'decrement_button_count'],
            'permission_callback' => '__return_true'
        ));

        // Get count
        register_rest_route($this->namespace, '/get-count', array(
            'methods' => 'GET',
            'callback' => [$this, 'get-button-count'],
            'permission_callback' => '__return_true'
        ));

        // Update count
        register_rest_route($this->namespace, '/update-count/(?P<num>\d+)', array(
            'methods' => 'POST',
            'callback' => [$this, 'update_button_count'],
            'permission_callback' => '__return_true'
        ));

        register_rest_route($this->namespace, '/custom-posts', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_all_custom_posts'],
            'permission_callback' => '__return_true'
        ));

        register_rest_route($this->namespace, '/custom-post/(?P<id>\d+)', array(
            'methods' => 'Get',
            'callback' => [$this, 'get_custom_post'],
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * Adding custom post type
     * Register shortcode to display those posts
     */

    public function create_custom_post_type()
    {
        $args = array(
            'public' => true,
            'labels' => array(
                'name' => __('Book Collection'),
                'singular_name' => __('Book')
            ),
            'rewrite' => array('slug' => 'book_collection'),
            'supports' => array('title', 'editor', 'int'),
        );
        register_post_type('book_collection_post', $args);
    }

    public function book_post_shortcode()
    {
        $args = array(
            'post_type' => 'book_collection_post',
            'posts_per_page' => 10,
        );

        $loop = new WP_Query($args);
        ?>
            <head>
                <link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'css/poc-plugin.css'; ?>">
            </head>
            <div class="book-collection-container">
                <h1 class="title">Book Collection</h1>
                <p class="caption">Here we are showcasing use of custom post type and the WordPress Query class.</p>
                <p class="caption">This is displayed through shortcode [book_post_shortcode].</p>
                <div id="book-posts" class="book-posts-container">
                    <?php if ($loop->have_posts()) :
                        while ($loop->have_posts()) : $loop->the_post(); ?>
                            <div class="book-post">
                                <a href="<?php echo get_permalink(); ?>">
                                    <h2 class="book-post-title"><?php echo get_the_title(); ?></h2>
                                    <div class="book-post-meta">by <?php the_author(); ?> on <?php echo get_the_date() ?></div>
                                    <div class="book-post-content"><?php echo get_the_content(); ?></div>
                                </a>
                            </div>
                        <?php endwhile;
                    else : ?>
                        <div class="no-posts-found-message">No posts found</div>
                    <?php endif; ?>
                </div>
            </div>
    <?php
    }

    /**
     * Adding content to the different wp theme elements
     */
    public function interact_with_theme_elements()
    {
        add_action('wp_footer', [$this, 'content_echo_footer']);
        add_action('get_header', [$this, 'content_echo_header']);
        add_action('wp_body_open', [$this, 'content_echo_body']);
    }

    // Function that adds content to whatever page the hook is called on.

    public function content_echo_header()
    {
        echo '<h1>POC Plugin Header</h1>';
    }
    public function content_echo_footer()
    {
        echo '<h1>POC Plugin Footer</h1>';
    }
    public function content_echo_body()
    {
        echo '<h1>POC Plugin Body</h1>';
    }
}

// Create an instance of the class.
if (class_exists('POCPlugin'))
{
    $POCPlugin = new POCPlugin();
}
