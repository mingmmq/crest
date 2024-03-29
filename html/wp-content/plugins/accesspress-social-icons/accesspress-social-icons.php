<?php
defined('ABSPATH') or die("No script kiddies please!");
/**
 * Plugin Name:AccessPress Social Icons
 * Plugin URI: https://accesspressthemes.com/wordpress-plugins/accesspress-social-icons/
 * Description: A plugin to add social icons in your site wherever you want dynamically with handful of configurable settings.
 * Version:1.3.4
 * Author:AccessPress Themes
 * Author URI:http://accesspressthemes.com/
 * Text Domain: aps-social
 * Domain Path: /languages/
 * License:GPLv2 or later
 * */
/**
 * Declartion of necessary constants for plugin
 * */
if (!defined('APS_IMAGE_DIR')) {
    define('APS_IMAGE_DIR', plugin_dir_url(__FILE__) . 'images');
}
if (!defined('APS_JS_DIR')) {
    define('APS_JS_DIR', plugin_dir_url(__FILE__) . 'js');
}
if (!defined('APS_CSS_DIR')) {
    define('APS_CSS_DIR', plugin_dir_url(__FILE__) . 'css');
}
if (!defined('APS_ICONS_DIR')) {
    define('APS_ICONS_DIR', plugin_dir_url(__FILE__) . 'icon-sets');
}
if (!defined('APS_LANG_DIR')) {
    define('APS_LANG_DIR', basename( dirname( __FILE__ ) ) . '/languages');
}
if(!defined('APS_VERSION'))
{
    define('APS_VERSION','1.3.4');
}
/**
 * Register of widgets
 * */
include_once('inc/backend/widgets.php');
if (!class_exists('APS_Class')) {

    class APS_Class {
         /**
         * Initialization of plugin from constructor
         * */
        function __construct() {
            register_activation_hook(__FILE__, array($this, 'plugin_activation')); //calls plugin activation function
            add_action('init', array($this, 'plugin_text_domain')); //loads text domain for translation ready
            add_action('wp_enqueue_scripts', array($this, 'register_frontend_assets'));//registers assets for frontend
            add_action('admin_menu', array($this, 'add_aps_menu')); //adds plugin menu in wp-admin
            add_action('admin_enqueue_scripts', array($this, 'register_admin_assets')); //registers all the assets required for wp-admin
            add_action('admin_init', array($this, 'admin_session_init')); //intializes session 
            add_action('admin_post_aps_add_new_set', array($this, 'aps_add_new_set')); //add new set action
            add_action('admin_post_aps_edit_action', array($this, 'aps_edit_action')); //icon set edit action
            add_action('admin_post_aps_delete_action', array($this, 'aps_delete_action')); //icon set delete action
            add_action('admin_post_aps_copy_action', array($this, 'aps_copy_action')); //icon set copy action
            add_shortcode('aps-social', array($this, 'aps_social_shortcode')); //adds the aps-social shortcode
            add_action('wp_ajax_aps_icon_list_action', array($this, 'aps_icon_list_action')); //admin ajax action for icon listing 
            add_action('wp_ajax_nopriv_aps_icon_list_action', array($this, 'no_permission')); //action for unauthenticate admin ajax call
            add_action('wp_ajax_get_theme_icons', array($this, 'get_theme_icons')); //admin ajax for getting theme icons
            add_action('wp_ajax_nopriv_get_theme_icons', array($this, 'no_permission')); //ajax action for unathenticate admin ajax call
            add_action('widgets_init', array($this, 'register_aps_widget')); //register aps widget
            
        }

        //called when plugin is activated
        function plugin_activation() {
            include_once('inc/backend/activation.php');
        }

        //loads the text domain for translation
        function plugin_text_domain() {
            load_plugin_textdomain('aps-social', FALSE, APS_LANG_DIR);
        }

        //adds plugin menu in wp-admin
        function add_aps_menu() {
            add_menu_page('AccessPress Social', 'AccessPress <br/> Social Icons', 'manage_options', 'aps-social', array($this, 'main_page'), APS_IMAGE_DIR . '/si-icon.png');
            add_submenu_page('aps-social', __('Social Icons','aps-social'), __('Social Icons','aps-social'), 'manage_options', 'aps-social', array($this, 'main_page'));
            add_submenu_page('aps-social', __('Add New Set','aps-social'), __('Add New Set','aps-social'), 'manage_options', 'aps-social-add', array($this, 'add_new_set'));
            add_submenu_page('aps-social', __('How to use','aps-social'), __('How to use','aps-social'), 'manage_options', 'aps-social-how-to-use', array($this, 'how_to_use'));
            add_submenu_page('aps-social', __('About','aps-social'), __('About','aps-social'), 'manage_options', 'aps-about', array($this, 'about'));
        }

        //plugin's main page
        function main_page() {
            include_once('inc/backend/main-page.php');
        }

        //Add new set of social icons
        function add_new_set() {
            include_once('inc/backend/add-new-set.php');
        }

        //registers all the js and css in wp-admin
        function register_admin_assets() {
            //including the scripts in the plugins pages only
            if (isset($_GET['page']) && ($_GET['page'] == 'aps-social' || $_GET['page'] == 'aps-social-add' || $_GET['page'] == 'aps-about'|| $_GET['page'] == 'aps-social-how-to-use')) {
                $aps_script_variable = array('icon_preview' => __('Icon Preview', 'aps-social'),
                    'icon_link' => __('Icon Link', 'aps-social'),
                    'icon_link_target' => __('Icon Link Target','aps-social'),
                    'icon_delete_confirm' => __('Are you sure you want to delete this icon from this list?', 'aps-social'),
                    'set_name_required_message' => __('Please enter the name for the set', 'aps-social'),
                    'min_icon_required_message' => __('Please add at least one icon in the set', 'aps-social'),
                    'ajax_url' => admin_url() . 'admin-ajax.php',
                    'ajax_nonce' => wp_create_nonce('aps-ajax-nonce'),
                    'icon_warning' => __('Are you sure you want to discard the icons added previously?', 'aps-social'),
                    'icon_collapse' => __('Collapse All', 'aps-social'),
                    'icon_expand' => __('Expand All', 'aps-social'));
                /**
                 * Backend CSS
                 * */
                wp_enqueue_style('aps-admin-css', APS_CSS_DIR . '/backend.css',false,APS_VERSION); //registering plugin admin css
                wp_enqueue_style('aps-animate-css', APS_CSS_DIR . '/animate.css',false,APS_VERSION); //animate.css library
                wp_enqueue_style('thickbox'); //for including wp thickbox css
                wp_enqueue_style('wp-color-picker'); //for including color picker css
                

                /**
                 * Backend JS
                 * */
                wp_enqueue_script('jquery-ui-sortable');
                wp_enqueue_script('media-upload'); //for uploading image using wp native uploader
                wp_enqueue_script('thickbox'); //for uploading image using wp native uploader + thickbox 
                wp_enqueue_script('aps-admin-js', APS_JS_DIR . '/backend.js', array('jquery', 'jquery-ui-sortable', 'wp-color-picker'),APS_VERSION);//registering plugin's admin js
                wp_localize_script('aps-admin-js', 'aps_script_variable', $aps_script_variable); //localization of php variable in aps-admin-js
            }
        }

        //registers all the assets for frontend
        function register_frontend_assets() {
            /**
             * Frontend Style
             * */
            wp_enqueue_style('aps-animate-css', APS_CSS_DIR . '/animate.css',false,APS_VERSION);//registering animate.css
            wp_enqueue_style('aps-frontend-css', APS_CSS_DIR . '/frontend.css',false,APS_VERSION); //registering frontend css
            
            /**
             * Frontend JS
             * */
            wp_enqueue_script('aps-frontend-js', APS_JS_DIR . '/frontend.js', array('jquery'),APS_VERSION);//registering frontend js 
        }

        //action to save the set in db
        function aps_add_new_set() {
            if (isset($_POST['aps_add_set_nonce'], $_POST['aps_icon_set_submit']) && wp_verify_nonce($_POST['aps_add_set_nonce'], 'aps_add_new_set')) {
                include_once('inc/backend/save-set.php');
            } else {
                die('No script kiddies please!');
            }
        }

        //prints the array in pre format
        function print_array($array) {
            echo "<pre>";
            print_r($array);
            echo "</pre>";
        }

        //starts the session
        function admin_session_init() {
            if (!session_id()) {
                session_start();
            }
        }

        //Icon set delete section
        function aps_delete_action() {
            if (isset($_GET['action'], $_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'aps-delete-nonce')) {
                include_once('inc/backend/delete-icon-set.php');
            } else {
                die('No script kiddies please!');
            }
        }
        //Icon set copy section
        function aps_copy_action() {
            if (isset($_GET['action'], $_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'aps-copy-nonce')) {
                include_once('inc/backend/copy-icon-set.php');
            } else {
                die('No script kiddies please!');
            }
        }
        

        //Icon set edit action
        function aps_edit_action() {

            if (isset($_POST['aps_edit_set_nonce'], $_POST['aps_icon_set_submit']) && wp_verify_nonce($_POST['aps_edit_set_nonce'], 'aps_edit_action')) {
                include_once('inc/backend/save-set.php');
            } else {
                die('No script kiddies please!');
            }
        }

        
        

        //shortcode function
        function aps_social_shortcode($atts) {
            if (isset($atts['id'])) {
                //return (print_r($atts,true));
                ob_start();
                include('inc/frontend/shortcode.php');
                $html = ob_get_contents();
                ob_get_clean();
                return $html;
            }
        }

        //lists the available icons 
        function aps_icon_list_action() {
            if (wp_verify_nonce($_POST['_wpnonce'], 'aps-ajax-nonce')) {
                $plugin_path = plugin_dir_path(__FILE__);
                //include_once('inc/backend/list-icon-sets.php');
                for ($i = 1; $i <= 12; $i++) {
                    $icon_set_image_array = array();
                    ?>
                    <div class="aps-set-wrapper" id="aps-set-<?php echo $i; ?>">
                        <h3>Set <?php echo $i; ?></h3>
                        <div class="aps-row">
                            <?php
                            $handle = opendir(dirname(realpath(__FILE__)) . '/icon-sets/png/set' . $i);
                            while ($file = readdir($handle)) {
                                $filename_array = explode('.', $file);
                                $filename = ucfirst($filename_array[0]);
                                $ext = end($filename_array);
                                if ($file !== '.' && $file !== '..' && $ext == 'png') {
                                    $icon_set_image_array[] = $file;


                                    //echo '<img src="/'.$file.'" border="0" />';
                                }//if close
                            }//while close
                            if (count($icon_set_image_array) > 0) {
                                natsort($icon_set_image_array);
                                foreach ($icon_set_image_array as $file) {
                                    $filename_array = explode('.', $file);
                                    $filename = ucfirst($filename_array[0]);
                                    ?>
                                    <div class="aps-col-one-fourth">
                                        <div class="aps-set-image-wrapper">
                                            <a href='javascript:void(0);'>
                                                <img src="<?php echo APS_ICONS_DIR . '/png/set' . $i . '/' . $file; ?>" alt="<?php echo $filename; ?>" title="<?php echo $filename; ?>"/>
                                                <span class="aps-set-image-title"><?php echo $filename; ?></span>
                                            </a>
                                        </div>
                                    </div>
                            <?php
                        }
                    }
                    
                    ?>
                        </div>
                    </div><!--aps-set-wrapper-->
                    <div class="clear"></div>
                    <?php
                }
                die();
            } else {
                die('No script kiddies please!');
            }
        }

        //lists the icons of specific theme
        function get_theme_icons() {

            if (wp_verify_nonce($_POST['_wpnonce'], 'aps-ajax-nonce')) {
                $plugin_path = plugin_dir_path(__FILE__);
                $sub_folder = $_POST['sub_folder'];
                $folder = $_POST['folder'];
                $handle = opendir(dirname(realpath(__FILE__)) . '/icon-sets/' . $sub_folder . '/' . $folder);
                $icon_counter = 0;
                $set_image_array = array();
                while ($file = readdir($handle)) {
                    $filename_array = explode('.', $file);
                    $filename = ucfirst($filename_array[0]);
                    $ext = end($filename_array);
                    if ($file !== '.' && $file !== '..' && $ext == 'png') {
                        $icon_counter++;
                        $set_image_array[] = $file;
                    }
                }

                if (count($set_image_array) > 0) {
                    natsort($set_image_array);
                    $image_url_array = array();
                    foreach ($set_image_array as $file) {
                        $filename_array = explode('.', $file);
                        $filename = ucfirst($filename_array[0]);
                        if($_POST['url_only']=='yes')
                        {
                         $image_url_array[$filename] = APS_ICONS_DIR . '/' . $sub_folder . '/' . $folder . '/' . $file; 
                        }
                        else
                        {
                            include('inc/backend/theme-icon-set.php');
                        }
                        
                    }
                    if($_POST['url_only']=='yes')
                    {
                        die(json_encode($image_url_array));
                    }
                }
            } else {
                die('No script kiddies please');
            }
            die();
        }

        //prevents unauthorized ajax call
        function no_permission() {
            die('No script kiddies please!');
        }

        //returns the current page url
        function curPageURL() {
            $pageURL = 'http';
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
                $pageURL .= "s";
            }
            $pageURL .= "://";
            if ($_SERVER["SERVER_PORT"] != "80") {
                $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
            } else {
                $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
            }
            return $pageURL;
        }

        //registers the APS widget
        function register_aps_widget() {
            register_widget('APS_Widget');
        }

        //returns total number of displaying icons
        function get_total_display_icons($icons)
        {
            $counter = 0;
            foreach($icons as $icon)
            {
                if($icon['link']!='')
                {
                    $counter++;
                }
            }
            return $counter;
        }
        
        //about section
        function about()
        {
            include('inc/backend/about.php');
        }
        
        //how to use section
        function how_to_use()
        {
            include('inc/backend/how-to-use.php');
        }

    }

    //APS_Class termination

    $aps_object = new APS_Class();
}// class exists condition check
 
 