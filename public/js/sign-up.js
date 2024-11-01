function refreshEntries($) {
    $.ajax({
        url: '/wp-json/sign-up-list/v1/entries',
        type: 'GET',
        dataType:'json',   
        success: function(response){
                $("#sul-entries-count").html(response[0]['entries_count']);
                $("#sul-entries-left").html(response[0]['entries_left']);
                $('#sul-entries tbody').empty();
                response[1].forEach( function (element) {
                    $('#sul-entries tbody').append('<tr><td>'+element+'</td></tr>');
                } );
                $('#sul-entries tfoot').empty();
                $('#sul-entries tfoot').append('<tr><td>'+response[0]['footer']+'</td></tr');
            }, 
        error: function(){
                console.log('Refresh failed');
            }
       });
}

function getCookie(cname) {
    let name = cname + "=";
    let ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }

jQuery(document).ready(function($){
    $("#sign-up-full").on('submit', function(e){
      e.preventDefault();
       formurl = $(this).attr("action"),
       formtype = $(this).attr("method");
       var formData = { };
        $(this).find('input[type=hidden], input[type=text]').each(function() {
            formData[$(this).attr("name")] = $(this).val();
        });
        if ( 'securitycode' in formData )  {
            securityhash = getCookie('securityhash');
            if ( securityhash.length > 0 ) {
                formData['securityhash'] = securityhash; 
            } 
        }
       $.ajax({
                url: formurl,
                type: formtype,
                dataType:'json',
                data: formData,   
                success: function(response){
                        $('#sign_up_form').css("display","none");
                        $("#success_msg").css("display","block");
                        refreshEntries($);
                    }, 
                error: function(data, message, message_details){
                        $("#error_msg").css("display","block");
                        $("#error_msg").html('<p>' + data.responseJSON.message + '</p>');
                    }
               });
      });
    });