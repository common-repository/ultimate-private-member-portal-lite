<?php
	global $upmp_private_page_data,$upmp_private_page_params;
	extract($upmp_private_page_params);
	extract($upmp_private_page_data);

	$content_tab_title = apply_filters('upmp_content_tab_title', __('Content','upmp') , array() );
	
	$message = isset($message) ? $message : '';
    $message_status = isset($message_status) ? $message_status : '';

    $display_css = "display:none;";
    $message_css = '';
    if($message != ''){
        $display_css = "display:block;";
        if($message_status){
            $message_css = 'upmp-message-info-success';
        }else{
            $message_css = 'upmp-message-info-error';
        }
    }

    $filtered_main_content = apply_filters('the_content', $main_content); 
?>

<div class="wrap">
    <h2><?php echo __('Private Member Portal Contents','upmp'); ?></h2>
    
    <div class="upmp-setting-panel">
        <div style="<?php echo $display_css; ?>" id="upmp-message" class="<?php echo $message_css; ?>" ><?php echo $message; ?></div>
        
        <form method="post" id="upmp_private_page_user_load_form" action="<?php echo admin_url( 'admin.php?page=upmp-private-user-page&tab=upmp_section_private_page_user' ); ?>">
            <div class="upmp-row">
                <div class="upmp-label"><?php echo __('Select User','upmp'); ?></div>
                <div class="upmp-field">
                    <select name="upmp_private_page_user" id="upmp_private_page_user" style="width:75%;" class=""  >
                        <option value="0"><?php echo __('Select','upmp'); ?></option>
                    </select>
                    <input type="submit" name="upmp_private_page_user_load" id="upmp_private_page_user_load" value="<?php _e('Load User','upmp'); ?>" class="upmp-button-primary" />
                </div>
                <div class="upmp-clear"></div>
            </div>
         </form>   
            
        
    </div>
</div>
<div style='background:#FFF'>
<div class='upmp-private-page-container upmp-private-page-single'>

	
	<?php 
	if($current_user_id == 0) { 
		echo "<div class='upmp-private-page-empty-user-message'>".apply_filters('upmp_private_page_empty_user_message' , __('No content found. Please select a valid user.','upmp'))."</div>";
	}else{

	?>


	<div class='upmp-private-page-tabs'>
		<?php if($private_page_content_tab_status){ ?>
			<div class='upmp-private-page-tab upmp-private-page-content-tab' data-tab-id='upmp-private-page-content' ><?php echo $content_tab_title; ?></div>		
		<?php } ?>

		
		<div class="upmp-clear"></div>
	</div>
	<div class='upmp-private-page-tabs-content'>

		<?php if($private_page_content_tab_status){ ?>
		<div style="display:block;" class='upmp-private-page-tab-content upmp-private-page-content-tab-content'>
			<div class="upmp-setting-panel">
		        <form method="post" id="" action="<?php echo admin_url( 'admin.php?page=upmp-private-user-page&&tab=upmp_section_private_page_user&upmp_private_page_user='.$current_user_id ); ?>" >
		            
		        <?php 
		            wp_nonce_field( 'upmp_private_page_nonce', 'upmp_private_page_nonce_field' );
		            if($_REQUEST && isset($_REQUEST['upmp_private_page_user'])){ 
		        ?> 
		            <div class="upmp-row" >
		                <div class="upmp-label"><?php echo __('Name','upmp'); ?></div>
		                <div class="upmp-field"><?php echo $display_name; ?></div>
		                <input type="hidden" name="upmp_user_id" value="<?php echo $current_user_id; ?>" />
		                <div class="upmp-clear"></div>
		            </div>
		            <div class="upmp-row" >
		                <div class="upmp-label"><?php echo __('Private content','upmp'); ?></div>
		                <div class="upmp-field"><?php wp_editor($main_content, 'upmp_private_page_content'); ?></div>
		                <div class="upmp-clear"></div>
		            </div>
		            <div class="upmp-row">
		                <div class="upmp-label">&nbsp;</div>
		                <div class="upmp-field">
		                    <input type="hidden" name="upmp_tab_id" value="0" />
		                    <input type="submit" name="upmp_private_page_content_submit" id="upmp_private_page_content_submit" value="<?php _e('Save','upmp'); ?>" class="upmp-button-primary" />
		                </div>
		                <div class="upmp-clear"></div>
		            </div>
		            <div class="upmp-clear"></div>
		        <?php } ?>
		        </form>
		    </div>
	    	<?php echo wpautop($filtered_main_content); ?>
		</div>
		<?php } ?>

		
		
		
		
	</div>
	<div class="upmp-clear"></div>

	<?php
	
	}	

	?>
</div>



