<?php
	global $upmp_private_page_data;
	extract($upmp_private_page_data);

	$content_tab_title = apply_filters('upmp_content_tab_title', __('Content','upmp') , array() );
	$filtered_main_content = apply_filters('the_content', $main_content); 
?>

<div class='upmp-private-page-container upmp-private-page-single'>

	<!-- <div class='upmp-group-title'><?php echo $group_data->post_title; ?></div> -->
	<div class='upmp-private-page-tabs'>
		<?php if($private_page_content_tab_status){ ?>
			<div class='upmp-private-page-tab upmp-private-page-content-tab' data-tab-id='upmp-private-page-content' ><?php echo $content_tab_title; ?></div>		
		<?php } ?>

		

		<div class="upmp-clear"></div>
	</div>
	<div class='upmp-private-page-tabs-content'>
		<?php if($private_page_content_tab_status){ ?>
		<!-- wpautop(do_shortcode() is removed. -->
		<div style="display:block;" class='upmp-private-page-tab-content upmp-private-page-content-tab-content'><?php echo $filtered_main_content; ?></div>
		<?php } ?>

		
		
		
		
	</div>
	<div class="upmp-clear"></div>
</div>



