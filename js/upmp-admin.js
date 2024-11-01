jQuery(document).ready(function($) {
    if($("#upmp_private_page_user").length){
        $("#upmp_private_page_user").upmp_select2({
          ajax: {
            url: UPMPAdmin.AdminAjax,
            dataType: 'json',
            delay: 250,
            method: "POST",
            data: function (params) {
              return {
                q: params.term, // search term
                page: params.page,
                action: 'upmp_load_private_page_users',
              };
            },
            processResults: function (data, page) {
              return {
                results: data.items
              };
            },
            cache: true
          },
          escapeMarkup: function (markup) { return markup; }, 
          minimumInputLength: 1,
          templateResult: upmp_formatRepo, 
          templateSelection: upmp_formatRepoSelection 
        });
    }
    
    $("#upmp_private_page_user_load_form").submit(function(e){
        
        $("#upmp-message").removeClass('upmp-message-info-error').removeClass('upmp-message-info-success').hide();
        
        if($("#upmp_private_page_user").val() == '0'){
            e.preventDefault();
            $("#upmp-message").addClass('upmp-message-info-error');
            $("#upmp-message").html(UPMPAdmin.Messages.userEmpty).show();
        }
    });

    if($("#upmp_private_page_id").length){
        $("#upmp_private_page_id").upmp_select2({
          ajax: {
            url: UPMPAdmin.AdminAjax,
            dataType: 'json',
            delay: 250,
            method: "POST",
            data: function (params) {
              return {
                q: params.term, // search term
                action: 'upmp_load_published_pages',
              };
            },
            processResults: function (data, page) {
              return {
                results: data.items
              };
            },
            cache: true
          },
          escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
          minimumInputLength: 1,
          templateResult: upmp_formatRepo, // omitted for brevity, see the source of this page
          templateSelection: upmp_formatRepoSelection // omitted for brevity, see the source of this page
        });
    }
    
    
});

function upmp_formatRepo (repo) {
    if (repo.loading) return repo.text;

    var markup = '<div class="clearfix">' +
    '<div class="col-sm-1">' +
    '' +
    '</div>' +
    '<div clas="col-sm-10">' +
    '<div class="clearfix">' +
    '<div class="col-sm-6">' + repo.name + '</div>' +
    '</div>';


    markup += '</div></div>';

    return markup;
}

function upmp_formatRepoSelection (repo) {
    return repo.name || repo.text;
}

String.prototype.upmp_format = function() {
  var args = arguments;
  return this.replace(/{(\d+)}/g, function(match, number) { 
    return typeof args[number] != 'undefined'
      ? args[number]
      : match
    ;
  });
};