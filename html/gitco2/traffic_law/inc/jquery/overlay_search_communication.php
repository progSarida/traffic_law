<?php

$str_OverlayPayment = '

  $(".show-communication").hover(function(){
    $(this).css("color","#fff");
    $(this).css("cursor","pointer");    
  },function(){
    $(this).css("color","#000");
    $(this).css("cursor",""); 
  });


    $(".show-communication").click(function(){

        var id = $(this).data("id");

        if(typeof id !== "undefined"){
            $.ajax({
                url: \'ajax/search_communication.php\',
                type: \'POST\',
                dataType: \'json\',
                cache: false,
                data: {Search_FineId: id},
                success: function (data) {
                    $(\'#FormCommunicationTrespasser\').html(data.Communication);
                }
            });
    
            $(\'#overlay\').fadeIn(\'fast\');
            $(\'#overlay_CommunicationView\').fadeIn(\'slow\');
        
        }





    });
    $("#overlay").click(function(){
        $(this).fadeOut(\'fast\');
        $(\'#overlay_CommunicationView\').hide();

    });
';

echo $str_OverlayPayment;
