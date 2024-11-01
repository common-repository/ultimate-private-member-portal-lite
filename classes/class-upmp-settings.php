<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/* Manage settings */
class UPMP_Settings{
    
    public $template_locations;
    public $current_user;
    
    /* Intialize actions for plugin settings */
    public function __construct(){
        
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array(&$this, 'admin_settings_menu'), 9);
        add_action('init', array($this,'save_settings_page') );
        
        add_action('wp_ajax_upmp_load_private_page_users', array($this, 'upmp_load_private_page_users'));        
        add_action('wp_ajax_upmp_load_restriction_users', array($this, 'upmp_load_restriction_users'));
        
        
    }

    public function init(){
        $this->current_user = get_current_user_id(); 
    }
    
    /*  Save settings tabs */
    public function save_settings_page(){

        if(!is_admin())
            return;
        
        $upmp_settings_pages = array('upmp-settings','upmp-private-user-page');
        if(isset($_POST['upmp_tab']) && isset($_GET['page']) && in_array($_GET['page'],$upmp_settings_pages)){
            $tab = '';
            if ( isset ( $_POST['upmp_tab'] ) )
               $tab = sanitize_text_field($_POST['upmp_tab']); 

            if($tab != ''){
                $func = 'save_'.$tab;
                
                if(method_exists($this,$func)){
                    $this->$func();
                }else{
                    $this->save_custom_post_type_tab();
                }
            }
        }
    }
    
    /* Include necessary js and CSS files for admin section */
    public function include_scripts(){
        
        wp_register_style('upmp_admin_css', UPMP_PLUGIN_URL . 'css/upmp-admin.css');
        wp_enqueue_style('upmp_admin_css');
        
        wp_register_script('upmp_admin_js', UPMP_PLUGIN_URL . 'js/upmp-admin.js', array('jquery','jquery-ui-sortable','upmp_select2_js'));
        wp_enqueue_script('upmp_admin_js');
        
        $custom_js_strings = array(        
            'AdminAjax' => admin_url('admin-ajax.php'),
            'images_path' =>  UPMP_PLUGIN_URL . 'images/',
            'Messages'  => array(
                                'userEmpty' => __('Please select a user.','upmp'),
                                'addToPost' => __('Add to Post','upmp'), 
                                'insertToPost' => __('Insert Files to Post','upmp'),   
                                'removeGroupUser' => __('Removing User...','upmp'),    
                            ),
        );

        wp_localize_script('upmp_admin_js', 'UPMPAdmin', $custom_js_strings);
    }
    
    /* Intialize settings page and tabs */
    public function admin_settings_menu(){
        
        add_action('admin_enqueue_scripts', array($this,'include_scripts'));
        
        add_menu_page(__('Ultimate Private Member Portal', 'upmp' ), __('Ultimate Private Member Portal', 'upmp' ),'manage_options','upmp-private-user-page',array(&$this,'private_user_page'));
        
    }  
    
    

    /* Manage settings tabs for the plugin */
    public function plugin_options_tabs($type,$tab) {
        $current_tab = $tab;
        $this->plugin_settings_tabs = array();
        
        switch($type){           

            case 'private_page':
                $this->plugin_settings_tabs['upmp_section_private_page_general']  = __('General Settings','upmp');
                $this->plugin_settings_tabs['upmp_section_private_page_user']  = __('Private Member Portal','upmp');
                break;   

        }
        
        ob_start();
        ?>

        <h2 class="nav-tab-wrapper">
        <?php 
            foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        ?>
                <a class="nav-tab <?php echo $active; ?> " href="?page=<?php echo $page; ?>&tab=<?php echo $tab_key; ?>"><?php echo $tab_caption; ?></a>
            
        <?php } ?>
        </h2>

        <?php
                
        return ob_get_clean();
    }
    
    /* Manage settings tab contents for the plugin */
    public function plugin_options_tab_content($tab,$params = array()){
        global $upmp,$upmp_settings_data,$upmp_private_page_settings_data;
        
        $post_types = $upmp->posts->get_post_types();

        $private_content_settings = get_option('upmp_options');

        $this->load_upmp_select2_scripts_style();
        
        ob_start();
        switch($tab){
          


            case 'upmp_section_private_page_general':                
                $data = isset($private_content_settings['private_page_general']) ? $private_content_settings['private_page_general'] : array();

                $upmp_private_page_settings_data['tab'] = $tab;
                $upmp_private_page_settings_data['private_page_id'] = isset($data['private_page_id']) ? $data['private_page_id'] : '0';

                $upmp_private_page_settings_data = apply_filters('upmp_private_page_setting_data',$upmp_private_page_settings_data, array('data' => $data, 'section' => 'upmp_section_private_page_general' ) );
                $upmp->template_loader->get_template_part('private-page-general-settings');            
                break;

            case 'upmp_section_private_page_user':                
                global $upmp_private_page_params,$wpdb;
        
                $upmp_private_page_params = array();
                
                $this->load_upmp_select2_scripts_style();
                
                $private_page_user = 0;
                $tab_id = 0;
                if($_REQUEST && ( isset($_REQUEST['upmp_private_page_user_load']) || isset($_REQUEST['upmp_private_page_user']) ) && current_user_can('manage_options') ){
                    $private_page_user = isset($_REQUEST['upmp_private_page_user']) ? $_REQUEST['upmp_private_page_user'] : 0;
                    $tab_id = isset($_POST['upmp_tab_id']) ? (int) $_POST['upmp_tab_id'] : 0;  
                    $user = get_user_by( 'id', $private_page_user );
                    $upmp_private_page_params['display_name'] = $user->data->display_name;
                    $upmp_private_page_params['user_id'] = $private_page_user;
                }  

        
                if($_POST && isset($_POST['upmp_private_page_content_submit']) && current_user_can('manage_options') ){
                    if (isset( $_POST['upmp_private_page_nonce_field'] ) && wp_verify_nonce( $_POST['upmp_private_page_nonce_field'], 'upmp_private_page_nonce' ) ) {
                        $user_id = isset($_POST['upmp_user_id']) ? (int) $_POST['upmp_user_id'] : 0;
                        $tab_id = isset($_POST['upmp_tab_id']) ? (int) $_POST['upmp_tab_id'] : 0;
                        if($tab_id == '0'){
                            $private_content = isset($_POST['upmp_private_page_content']) ?  wp_kses_post($_POST['upmp_private_page_content']) : '';
                        
                        }else{
                            $private_content = isset($_POST['upmp_private_page_content_'.$tab_id]) ?  wp_kses_post($_POST['upmp_private_page_content_'.$tab_id]) : '';
                        
                        }  
                        $updated_date = date("Y-m-d H:i:s");
                        
                        $sql  = $wpdb->prepare( "SELECT content FROM " . $wpdb->prefix . UPMP_PRIVATE_CONTENT_TABLE . " WHERE user_id = %d 
                            and tab_id = %d ", $user_id, $tab_id );

                        $result = $wpdb->get_results($sql);
                        if($result){
                            $sql  = $wpdb->prepare( "Update " . $wpdb->prefix . UPMP_PRIVATE_CONTENT_TABLE ." set content=%s,updated_at=%s,tab_id=%d where user_id=%d and tab_id = %d",
                            $private_content,$updated_date,$tab_id , $user_id , $tab_id);
                        }else{
                            $sql  = $wpdb->prepare( "Insert into " . $wpdb->prefix . UPMP_PRIVATE_CONTENT_TABLE ."(user_id,content,type,updated_at,tab_id) values(%d,%s,%s,%s,%d)",
                             $user_id, $private_content, 'ADMIN', $updated_date,$tab_id );
                        }
                        
                        
                        if($wpdb->query($sql) === FALSE){
                            $upmp_private_page_params['message'] = __('Private content update failed.','upmp');
                            $upmp_private_page_params['message_status'] = FALSE;
                        }else{

                            $upmp->private_page->send_new_private_content_notification($user_id);
                            $upmp_private_page_params['message'] = __('Private content updated successfully.','upmp');
                            $upmp_private_page_params['message_status'] = TRUE;
                        }        
                    }
                }
        
                $sql  = $wpdb->prepare( "SELECT content FROM " . $wpdb->prefix . UPMP_PRIVATE_CONTENT_TABLE . " WHERE 
                    user_id = %d and tab_id = %d ", $private_page_user , $tab_id );
                $result = $wpdb->get_results($sql);
                if($result){
                    $upmp_private_page_params['private_content'] = stripslashes($result[0]->content);
                }else{
                    $upmp_private_page_params['private_content'] = '';
                }
                
                $private_page_user = isset($private_page_user) ? $private_page_user : 0;
                echo do_shortcode('[upmp_private_page_pro_admin user_id="'.$private_page_user.'"]');
           
                break;

            default:

                
                break;
        }
        
        $display = ob_get_clean();
        return $display;
        
    }

       
    
    /* Display private user page add content form */
    public function private_user_page(){
        global $upmp,$upmp_settings_data;

        add_settings_section( 'upmp_section_private_page_general', __('General Settings','upmp'), array( &$this, 'upmp_section_general_desc' ), 'upmp-private-page-general' );
        
        add_settings_section( 'upmp_section_private_page_user', __('Private Member Portal','upmp'), array( &$this, 'upmp_section_general_desc' ), 'upmp-private-page-general' );
        
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'upmp_section_private_page_general';
        $upmp_settings_data['tab'] = $tab;
        
        $tabs = $this->plugin_options_tabs('private_page',$tab);
   
        $upmp_settings_data['tabs'] = $tabs;
        
        $tab_content = $this->plugin_options_tab_content($tab);
        $upmp_settings_data['tab_content'] = $tab_content;
        
        ob_start();
        $upmp->template_loader->get_template_part( 'menu-page-container');
        $display = ob_get_clean();
        echo $display;

    }
    
    /* Load Select 2 library for settings section */
    public function load_upmp_select2_scripts_style(){
        wp_register_script('upmp_select2_js', UPMP_PLUGIN_URL . 'js/select2/upmp-select2.min.js');
        wp_enqueue_script('upmp_select2_js');
        
        wp_register_style('upmp_select2_css', UPMP_PLUGIN_URL . 'js/select2/upmp-select2.min.css');
        wp_enqueue_style('upmp_select2_css');
        
    }
    
    /* Get the users for the private page content form */
    public function upmp_load_private_page_users(){
        global $wpdb;
        
        $search_text  = isset($_POST['q']) ? sanitize_text_field($_POST['q']) : '';
        
        $args = array('number' => 20);
        if($search_text != ''){
            $args['search'] = "*".$search_text."*";
        }
        
        $user_results = array();
        $user_json_results = array();
        
        $user_query = new WP_User_Query( $args );
        $user_results = $user_query->get_results();

        foreach($user_results as $user){
            if($user->ID != $this->current_user){
                array_push($user_json_results , array('id' => $user->ID, 'name' => $user->data->display_name) ) ;
            }
                       
        }
        
        echo json_encode(array('items' => $user_json_results ));exit;
    }

    /* Display settings saved message */  
    public function admin_notices(){
        ?>
        <div class="updated">
          <p><?php esc_html_e( 'Settings saved successfully.', 'upmp' ); ?></p>
       </div>
        <?php
    }

    /* Help and information about the plugin */
    public function help(){
        global $upmp;
        ob_start();
        $upmp->template_loader->get_template_part('plugin-help');    
        $display = ob_get_clean();  
    
        echo $display;
    }

    /* Get the users for restrictions on various locations */
    public function upmp_load_restriction_users(){
        global $wpdb;
        
        $search_text  = isset($_POST['q']) ? sanitize_text_field($_POST['q']) : '';
        
        $args = array('number' => 20);
        if($search_text != ''){
            $args['search'] = "*".$search_text."*";
        }
        
        $user_results = array();
        $user_json_results = array();
        
        $user_query = new WP_User_Query( $args );
        $user_results = $user_query->get_results();

        foreach($user_results as $user){
            if($user->ID != $this->current_user){
                array_push($user_json_results , array('id' => $user->ID, 'name' => $user->data->display_name) ) ;
            }
                       
        }
        
        echo json_encode(array('items' => $user_json_results ));exit;
    }   
    

    public function save_upmp_section_private_page_general(){
        global $upmp;

        if(isset( $_POST['upmp_private_page_nonce_field'] ) && wp_verify_nonce( $_POST['upmp_private_page_nonce_field'], 'upmp_private_page_nonce' )){
            if(isset($_POST['upmp_private_page_general'])){
                foreach($_POST['upmp_private_page_general'] as $k=>$v){
                    $this->settings[$k] = $v;
                }            
            }
            
            $upmp_options = get_option('upmp_options');
            $upmp_options['private_page_general'] = $this->settings;
            update_option('upmp_options',$upmp_options);
            add_action( 'admin_notices', array( $this, 'admin_notices' ) );  
        }

        

        
    }        
}
