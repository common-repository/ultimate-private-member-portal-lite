<?php
/*
  Plugin Name: Ultimate Private Member Portal Lite
  Plugin URI: http://www.wpexpertdeveloper.com/ultimate-private-member-portal-lite
  Description: Private user portal functionaity for WordPress 
  private files and custom content tabs
  Version: 1.0
  Author: Rakhitha Nimesh
  Author URI: http://www.wpexpertdeveloper.com
 */



// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

register_activation_hook( __FILE__, 'upmp_install_db_tables' );

function upmp_get_plugin_version() {
    $default_headers = array('Version' => 'Version');
    $plugin_data = get_file_data(__FILE__, $default_headers, 'plugin');
    return $plugin_data['Version'];
}

/* Intializing the plugin on plugins_loaded action */
add_action( 'plugins_loaded', 'upmp_plugin_init' );

function upmp_plugin_init(){
    Ultimate_Private_Member_Portal();
}

/* Install database tables required for the plugin */
function upmp_install_db_tables(){
    global $wpdb;

    $table_name = $wpdb->prefix . 'upmp_private_page';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
              id int(11) NOT NULL AUTO_INCREMENT,
              user_id int(11) NOT NULL,
              content longtext NOT NULL,
              type varchar(20) NOT NULL,
              updated_at datetime NOT NULL,
              tab_id int(11) NOT NULL,
              PRIMARY KEY (id)
            );";

    

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

if( !class_exists( 'Ultimate_Private_Member_Portal' ) ) {
    
    class Ultimate_Private_Member_Portal{
    
        private static $instance;

        /* Create instances of plugin classes and initializing the features  */
        public static function instance() {
            
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Ultimate_Private_Member_Portal ) ) {
                self::$instance = new Ultimate_Private_Member_Portal();
                self::$instance->setup_constants();

                add_action('wp_enqueue_scripts',array(self::$instance,'load_scripts'),9);
                add_action('wp_enqueue_scripts',array(self::$instance,'include_styles'),9);
                

                add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
                self::$instance->includes();

                add_action('init', array( self::$instance, 'init_actions' ) );                
                 
                self::$instance->settings           = new UPMP_Settings();
                self::$instance->template_loader    = new UPMP_Template_Loader();
                self::$instance->roles_capability   = new UPMP_Roles_Capability();
                self::$instance->posts              = new UPMP_Posts();                
                self::$instance->private_page       = new UPMP_Private_Page();
                
                add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( self::$instance, 'plugin_listing_links' )  );
                
            }
            return self::$instance;
        }

        public function init_actions(){
            self::$instance->private_content_settings  = get_option('upmp_options');
        }

        /* Setup constants for the plugin */
        private function setup_constants() {
            
            // Plugin version
            if ( ! defined( 'UPMP_VERSION' ) ) {
                define( 'UPMP_VERSION', '1.0' );
            }

            // Plugin Folder Path
            if ( ! defined( 'UPMP_PLUGIN_DIR' ) ) {
                define( 'UPMP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            }

            // Plugin Folder URL
            if ( ! defined( 'UPMP_PLUGIN_URL' ) ) {
                define( 'UPMP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
            }
            
            if ( ! defined( 'UPMP_PRIVATE_CONTENT_TABLE' ) ) {
                define( 'UPMP_PRIVATE_CONTENT_TABLE', 'upmp_private_page' );
            }

            if ( ! defined( 'UPMP_ADMIN_GRAVATAR_EMAIL' ) ) {
                define( 'UPMP_ADMIN_GRAVATAR_EMAIL', get_option('admin_email') );
            }
        }
             
        /* Define the locations for template files */  
        public function template_loader_locations($locations){
            $location = trailingslashit( UPMP_PLUGIN_DIR ) . 'templates/';
            array_push($locations,$location);
            return $locations;
        }
        
        /* Include class files */
        private function includes() {

            require_once UPMP_PLUGIN_DIR . 'classes/class-upmp-settings.php';
            require_once UPMP_PLUGIN_DIR . 'classes/class-upmp-template-loader.php';
            require_once UPMP_PLUGIN_DIR . 'classes/class-upmp-private-page.php';
            require_once UPMP_PLUGIN_DIR . 'classes/class-upmp-roles-capability.php';
            require_once UPMP_PLUGIN_DIR . 'classes/class-upmp-posts.php';
            require_once UPMP_PLUGIN_DIR . 'functions.php';
            
            if ( is_admin() ) {}
        }

        public function load_scripts(){
            wp_register_script('upmp_front_js', UPMP_PLUGIN_URL . 'js/upmp-front.js', array('jquery'));
            wp_enqueue_script('upmp_front_js');

            $custom_js_strings = array(        
                'AdminAjax' => admin_url('admin-ajax.php'),
                'images_path' =>  UPMP_PLUGIN_URL . 'images/',
                'Messages'  => array(
                                    'userEmpty' => __('Please select a user.','upmp'),
                                    'addToPost' => __('Add to Post','upmp'), 
                                    'insertToPost' => __('Insert Files to Post','upmp'),   
                                    'removeGroupUser' => __('Removing User...','upmp'), 
                                    'fileNameRequired' => __('File Name is required.','upmp'),
                                    'fileRequired' => __('File is required.','upmp'),
                                    'confirmDelete' => __('This message and comments will be deleted and you won\'t be able to find it anymore.','upmp'),
                                    'messageEmpty' => __('Please enter a message.','uupm'),
                                    'selectMember' => __('Select a member','uupm'),

                                ),

                'nonce' => wp_create_nonce('upmp-front'),   
            );

            wp_localize_script('upmp_front_js', 'UPMPFront', $custom_js_strings);
        }

        public function include_styles(){

            wp_register_style('upmp_front_css', UPMP_PLUGIN_URL . 'css/upmp-front.css');
            wp_enqueue_style('upmp_front_css');
            
        }

        public function plugin_listing_links($links){
            $links[] = '<a href="http://www.wpexpertdeveloper.com/ultimate-private-member-portal-lite"><b>' . __( 'Documentation', 'upmp' ) . '</b></a>';
            $links[] = '<a href="http://www.wpexpertdeveloper.com/plugins/"><b>' . __( 'More Plugins by WP Expert Developer', 'upmp' ) . '</b></a>';

            return $links;
        }
        
    }
}


register_activation_hook( __FILE__, 'upmp_activation' );

/* Intialize Ultimate_Private_Member_Portal instance */
function Ultimate_Private_Member_Portal() {
  global $upmp;    
	$upmp = Ultimate_Private_Member_Portal::instance();
}

add_action('init', 'upmp_load_textdomain',100);
function upmp_load_textdomain() {
    load_plugin_textdomain( 'upmp', false,dirname(plugin_basename(__FILE__)).'/lang');            
}


if (!function_exists('upmp_activation')) {
    function upmp_activation() {
        
    }
}
