<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/* Manage content restriction shortcodes */
class UPMP_Private_Page{
    
    public $current_user;
    public $private_content_settings;
    
    /* intialize the settings and shortcodes */
    public function __construct(){
        global $upmp;

        add_action('init', array($this, 'init'));            
      
        add_shortcode('upmp_private_page_pro', array($this,'private_content_page_pro'));
        add_shortcode('upmp_private_page_pro_admin', array($this,'private_content_page_pro_admin'));
  
    }

    public function init(){
        $this->current_user = get_current_user_id(); 
        $this->private_content_settings  = get_option('upmp_options');
        $this->private_page_styles();        
    }

    public function private_page_styles(){
        wp_register_style('upmp_private_page_css', UPMP_PLUGIN_URL . 'css/upmp-private-page.css');
        wp_enqueue_style('upmp_private_page_css');

        wp_register_script('upmp_private_page_js', UPMP_PLUGIN_URL . 'js/upmp-private-page.js', array('jquery'));
        wp_enqueue_script('upmp_private_page_js');

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

            'nonce' => wp_create_nonce('upmp-private-page'),   
        );

        wp_localize_script('upmp_private_page_js', 'UPMPPage', $custom_js_strings);
            
    }
    
    /* Display private content for logged in user */
    public function private_content_page_pro($atts,$content){
        global $upmp,$wpdb,$upmp_private_page_data;
        if(isset($atts) && is_array($atts))
            extract($atts);

        $this->private_content_settings  = get_option('upmp_options');  

        if(is_user_logged_in()){
            $user_id = get_current_user_id();
            $upmp->settings->load_upmp_select2_scripts_style();

            $upmp_private_page_data['current_user_id'] = $user_id;

            // Private Page Content
            $sql  = $wpdb->prepare( "SELECT content FROM " . $wpdb->prefix . UPMP_PRIVATE_CONTENT_TABLE . " WHERE user_id = %d  and tab_id = %d ", $user_id , 0);
            $result = $wpdb->get_results($sql);

            if($result){
                $main_content_html = apply_filters('the_content', stripslashes($result[0]->content));
                $upmp_private_page_data['main_content'] = do_shortcode($main_content_html);               
                
            }else{
                $upmp_private_page_data['main_content'] = apply_filters('upmp_private_page_empty_message' , __('No content found.','upmp'));
            }

            $upmp_private_page_data['private_page_content_tab_status'] = apply_filters('upmp_private_page_content_tab_status', TRUE, array());
            
            ob_start();
            $upmp->template_loader->get_template_part( 'private-page-pro' );
            $display = ob_get_clean();

            return $display;
        }

        return apply_filters('upmp_private_page_empty_message' , __('Please login to view your private page.','upmp'));
    }

    public function private_content_page_pro_admin($atts,$content){
        global $upmp,$wpdb,$upmp_private_page_data;
        if(isset($atts) && is_array($atts))
            extract($atts);

        $this->private_content_settings  = get_option('upmp_options');  
        $upmp->settings->load_upmp_select2_scripts_style();
        if(is_user_logged_in() && current_user_can('manage_options')){
            
            $user_id = $atts['user_id'];
            $current_user_id = get_current_user_id();

            $upmp_private_page_data['current_user_id'] = $user_id;
            
            // Private Page Content
            $sql  = $wpdb->prepare( "SELECT content FROM " . $wpdb->prefix . UPMP_PRIVATE_CONTENT_TABLE . " WHERE user_id = %d  and tab_id = %d ", $user_id , 0 );
            $result = $wpdb->get_results($sql);

            if($result){
                $upmp_private_page_data['main_content'] = stripslashes(($result[0]->content));
            }else{
                $upmp_private_page_data['main_content'] = apply_filters('upmp_private_page_empty_message' , __('No content found.','upmp'));
            }

            $upmp_private_page_data['private_page_content_tab_status'] = apply_filters('upmp_private_page_content_tab_status', TRUE, array());
            
            ob_start();
            $upmp->template_loader->get_template_part( 'private-page-pro-admin' );
            $display = ob_get_clean();
            return $display;
        }

        return apply_filters('upmp_private_page_empty_message' , __('No content found.','upmp'));
    }    

    public function send_new_private_content_notification($user_id){

        $data = isset($this->private_content_settings['private_page_general']) ? $this->private_content_settings['private_page_general'] : array();
        $private_page_id = isset($data['private_page_id']) ? $data['private_page_id'] : '0';

        $subject = apply_filters('upmp_new_private_page_content_notification_subject', __('New Private Content Available','upmp') , array('user_id' => $user_id));
        $email_message = __("Hi","upmp"). "\r\n\r\n";

        $content_link = get_permalink($private_page_id) . "?upmp_pp_content=yes";

        $email_message .= __('You have new content on your private page.','upmp');
        $email_message .= __('Please click the following link to view the updated content.','upmp'). "\r\n\r\n";
        $email_message .= $content_link . "\r\n\r\n";

        $email_message .= __('Thanks','upmp').  "\r\n";
        $email_message .= get_bloginfo('name');

        $user = new WP_User( $user_id );
        $email = $user->user_email;
        
        $email_message = apply_filters('upmp_new_private_page_content_notification_message', $email_message , array('user_id' => $user_id));
        wp_mail($email, $subject, $email_message);
    }

}