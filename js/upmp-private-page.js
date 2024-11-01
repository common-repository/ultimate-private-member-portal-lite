jQuery(document).ready(function($) {


    /* Private Page PRO functions */
    jQuery(".upmp-private-page-tab").click(function(){
        var tab_class = "."+$(this).attr('data-tab-id')+"-tab-content";

        $(this).closest('.upmp-private-page-single').find('.upmp-private-page-tab-content').hide();

        $(this).closest('.upmp-private-page-single').find(tab_class).show();

        $(".upmp-private-page-tab").removeClass('upmp-private-page-active-tab');
        $(this).addClass('upmp-private-page-active-tab');
    });
	

  

});