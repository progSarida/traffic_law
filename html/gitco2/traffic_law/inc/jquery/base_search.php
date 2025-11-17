<?php
$str_BaseSearch = $str_Search_ViolationDisabled;

$str_BaseSearch .= '

$(\'.glyphicon-search\').click(function(){
    $(\'#f_Search\').submit();
});

$(\'#f_Search\').on(\'keyup keypress\', function(e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) {
        $("#f_Search").submit();
    }
});

$("#TypePlate").change(function(){
    if ($("#TypePlate").val()==\'F\'){
        $(\'#Search_Country\').prop("disabled", false);

    }else{
        $(\'#Search_Country\').prop("disabled", true);
    }
});
';

echo $str_BaseSearch;
