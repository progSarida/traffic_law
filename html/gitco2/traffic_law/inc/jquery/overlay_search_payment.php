<?php

$str_OverlayPayment = '
    $(".fa-eur").click(function(){

        var id = $(this).attr("id");

        if(typeof id !== "undefined"){
            $.ajax({
                url: \'ajax/search_payment.php\',
                type: \'POST\',
                dataType: \'json\',
                cache: false,
                data: {Search_FineId: id, b_Page_Fine:1},
                success: function (data) {
                    console.log(data);
                    $(\'#FormPaymentTrespasser\').html(data.Payment);
                    $(\'#overlay\').fadeIn(\'fast\');
                    $(\'#overlay_PaymentView\').fadeIn(\'slow\');
                }
            });
        }

    });
    $("#overlay").click(function(){
        $(this).fadeOut(\'fast\');
        $(\'#overlay_PaymentView\').hide();

    });
';

echo $str_OverlayPayment;
