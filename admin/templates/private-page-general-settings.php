<?php 
    global $upmp_private_page_settings_data; 
    extract($upmp_private_page_settings_data);

    $user_query = new WP_User_Query( array( 'role' => 'administrator' ) );
    $user_results = $user_query->get_results();

?>

<form method="post" action="">
<table class="form-table upmp-settings-list">

                <tr>
                    <th><label for=""><?php echo __('Private Page','upmp'); ?></label></th>
                    <td style="width:500px;">
                        <select name="upmp_private_page_general[private_page_id]" id="upmp_private_page_id" class="upmp-select2-setting" placeholder="<?php _e('Select','upmp'); ?>" >
                            
                            <?php 
                                if($private_page_id != '0'){ ?>
                                    <option selected value="<?php echo $private_page_id; ?>"><?php echo get_the_title($private_page_id); ?></option>
                            <?php }  ?>
                        </select>
                        <div class='upmp-settings-help'><?php _e('This setting is used to define the private page with [upmp_private_page_pro] shortcode.','upmp'); ?></div>
                    </td>
                    
                </tr>
                
                        
                
    <input type="hidden" name="upmp_private_page_general[private_mod]"  value="1" />                        
    <input type="hidden" name="upmp_tab" value="<?php echo $tab; ?>" />
    <?php wp_nonce_field( 'upmp_private_page_nonce', 'upmp_private_page_nonce_field' ); ?>   
</table>

    <?php submit_button(); ?>
</form>